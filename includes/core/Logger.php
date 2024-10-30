<?php

namespace wsd\bw\core;

use wsd\bw\Context;

/**
 * Logger class.
 */
final class Logger {

	/** @var Context */
	protected $context;

	/**
	 * Logs admin screen handle.
	 *
	 * @var string
	 */
	protected $screen_handle;

	/**
	 * Open file handles.
	 *
	 * @var array
	 */
	protected $handles = [];

	/**
	 * Query var for viewing a log.
	 */
	const VIEW = 'bw-log';

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * Destructor.
	 *
	 * Cleans up open file handles.
	 */
	public function __destruct() {
		foreach($this->handles as $handle) {
			if(is_resource($handle)) {
				fclose($handle);
			}
		}
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_action('admin_menu', [$this, 'add_menu_items']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
	}

	/**
	 * Is logging enabled.
	 *
	 * @return bool
	 */
	public function logging_is_enabled() {
		return defined('BOOKING_WEIR_ENABLE_LOGS') && BOOKING_WEIR_ENABLE_LOGS;
	}

	/**
	 * Get the directory that should contain logs.
	 *
	 * @return string
	 */
	public function get_logs_dir() {
		if(defined('BOOKING_WEIR_LOGS_DIR')) {
			$dir = BOOKING_WEIR_LOGS_DIR;
		} else {
			$dir = $this->context->upload_dir() . '/logs';
		}
		$dir = apply_filters('BOOKING_WEIR_LOGS_DIR', $dir);
		return untrailingslashit($dir);
	}

	/**
	 * Is logs directory writable.
	 *
	 * @return bool
	 */
	public function logs_dir_is_writable() {
		if(!file_exists($this->get_logs_dir())) {
			if(!wp_mkdir_p($this->get_logs_dir())) {
				return false;
			}
		}
		return is_writable($this->get_logs_dir());
	}

	/**
	 * Returns the slug for the settings page.
	 *
	 * @return string
	 */
	public function get_menu_slug() {
		return sprintf('%s-logs', $this->context->plugin_slug());
	}

	public function add_menu_items() {
		$this->screen_handle = add_submenu_page(
			$this->context->get('admin')->get_menu_slug(),
			_x('Logs', 'Plugin admin menu item', 'booking-weir'),
			_x('Logs', 'Plugin admin menu item', 'booking-weir'),
			$this->context->get_admin_capability(),
			$this->get_menu_slug(),
			[$this, 'render_screen']
		);
	}

	public function enqueue_admin_assets() {
		if(!$this->is_logs_page()) {
			return;
		}
		wp_enqueue_style($this->context->get_style_handle('sui'));
	}

	/**
	 * Render the logs page.
	 */
	public function render_screen() {
		include $this->context->file('templates', 'logs.php');
	}

	/**
	 * Provides the logs screen handle.
	 *
	 * @return string
	 */
	public function get_screen_handle() {
		return $this->screen_handle;
	}

	public function is_logs_page() {
		$screen = get_current_screen();
		if($screen === null) {
			return false;
		}
		return $this->get_screen_handle() === $screen->id;
	}

	/**
	 * Open log file for writing.
	 *
	 * @param string $handle Log handle.
	 * @param string $mode Optional. File mode. Default 'a'.
	 * @return bool Success.
	 */
	protected function open($handle, $mode = 'a') {
		if($this->is_open($handle)) {
			return true;
		}

		$file = $this->get_path($handle);

		if($file) {
			if(!file_exists($file)) {
				$temphandle = @fopen($file, 'w+');
				@fclose($temphandle);
				if(defined('FS_CHMOD_FILE')) {
					@chmod($file, FS_CHMOD_FILE);
				}
			}
			if($resource = @fopen($file, $mode)) {
				$this->handles[$handle] = $resource;
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a handle is open.
	 *
	 * @param string $handle Log handle.
	 * @return bool True if $handle is open.
	 */
	protected function is_open($handle) {
		return array_key_exists($handle, $this->handles) && is_resource($this->handles[$handle]);
	}

	/**
	 * Close a handle.
	 *
	 * @param string $handle Log handle.
	 * @return bool success
	 */
	protected function close($handle) {
		$result = false;

		if($this->is_open($handle)) {
			$result = fclose($this->handles[$handle]);
			unset($this->handles[$handle]);
		}

		return $result;
	}

	/**
	 * Clear entries from chosen file.
	 *
	 * @param string $handle Log handle.
	 * @return bool
	 */
	public function clear($handle) {
		$this->close($handle);
		/**
		 * `w` - Open the file for writing only. Place the file pointer at
		 * the beginning of the file, and truncate the file to zero length.
		 */
		return $this->open($handle, 'w') && is_resource($this->handles[$handle]);
	}

	/**
	 * Format the log message.
	 *
	 * @param mixed $message
	 * @return string
	 */
	protected static function format($message) {
		if(is_bool($message)) {
			$message = $message ? 'true' : 'false';
		}

		if(is_array($message) || is_object($message)) {
			$message = json_encode($message, JSON_PRETTY_PRINT);
		}

		return '[' . date('d.m.Y h:i:s') . '] - ' . $message;
	}

	/**
	 * Log a message to file.
	 *
	 * @param mixed $message
	 * @param string $handle
	 * @return bool
	 */
	public function log($message, $handle = '') {
		if(!$this->logging_is_enabled()) {
			return false;
		}
		if(!$handle) {
			$handle = $this->context->plugin_slug();
		}
		if($this->open($handle) && is_resource($this->handles[$handle])) {
			return fwrite($this->handles[$handle], self::format($message) . PHP_EOL) !== false;
		}
		return false;
	}

	/**
	 * Get a log file path.
	 *
	 * @param string $handle Log name.
	 * @return bool|string The log file path.
	 */
	public function get_path($handle = '') {
		if(!$handle) {
			$handle = $this->context->plugin_slug();
		}
		if(!file_exists($this->get_logs_dir())) {
			if(!wp_mkdir_p($this->get_logs_dir())) {
				return false;
			} else {
				if(!file_exists($this->get_logs_dir() . '/index.php')) {
					touch($this->get_logs_dir() . '/index.php');
				}
				if(!file_exists($this->get_logs_dir() . '/.htaccess')) {
					file_put_contents($this->get_logs_dir() . '/.htaccess', 'Deny from all' . PHP_EOL);
				}
			}
		}
		return $this->get_logs_dir() . '/' . sanitize_file_name(implode('-', [
			$handle,
			date('Y-m', time()),
			wp_hash($handle),
		]) . '.log');
	}

	/**
	 * Get all log files in the log directory.
	 *
	 * @return array
	 */
	public function get_log_files() {
		$files  = @scandir($this->get_logs_dir());
		$result = [];

		if(!empty($files)) {
			foreach($files as $key => $value) {
				if(!in_array($value, ['.', '..'], true)) {
					if(!is_dir($value) && strstr($value, '.log')) {
						$result[sanitize_title($value)] = $value;
					}
				}
			}
		}

		return $result;
	}

	public function get_selected_log($logs = []) {
		$default = count($logs) > 0 ? array_shift($logs) : '';
		if(!isset($_GET[self::VIEW])) {
			return $default;
		}
		$id = $this->context->get('sanitizer')->sanitize_id(wp_unslash($_GET[self::VIEW])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return in_array($id, array_keys($logs)) ? $logs[$id] : $default;
	}

	public function get_contents($log) {
		$contents = file_get_contents($this->get_logs_dir() . '/' . $log);
		$contents = trim($contents);
		return $contents;
	}
}
