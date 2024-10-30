<?php

namespace wsd\bw\core;

use wsd\bw\Context;

/**
 * Notices class.
 */
final class Notices {

	/** @var Context */
	protected $context;

	/**
	 * Notices key in `$GLOBALS`.
	 */
	const GLOBAL_ID = 'bw_notices';

	/**
	 * Notices key in `$_SESSION`.
	 */
	const SESSION_ID = 'bw_notices';

	/**
	 * Notices JavaScript `window` variable name.
	 */
	const JS_ID = 'bw_notices';

	/**
	 * All added notices are kept in this so they can be saved
	 * and restored when necessary.
	 *
	 * @var array
	 */
	protected $notices = [];

	/**
	 * A script is a JS string that pushes a message object
	 * to `window.bw_notices` variable for React to render.
	 *
	 * @var array
	 */
	protected $scripts = [];

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
		$this->notices = [];
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_action('bw_booking', [$this, 'init']);
		add_action('wp_footer', [$this, 'print_scripts'], 100);
	}

	public function init() {
		if(!isset($GLOBALS[self::GLOBAL_ID])) {
			$GLOBALS[self::GLOBAL_ID] = [];
		}
		if(!session_id() && !defined('BOOKING_WEIR_TEST')) {
			session_start();
		}
		$this->add_script(sprintf('window.%s = [];', self::JS_ID));
		$this->restore();
	}

	public function get_notices() {
		return $this->notices;
	}

	protected function add_script($script) {
		$this->scripts[] = $script;
	}

	public function print_scripts() {
		if(function_exists('is_amp_endpoint') && is_amp_endpoint()) { // @phpstan-ignore-line
			return;
		}
		printf('<script type="text/javascript" id="bw-notices">%s</script>', implode("\n", $this->scripts)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function save() {
		$_SESSION[self::SESSION_ID] = $this->notices;
	}

	public function restore() {
		if(isset($_SESSION[self::SESSION_ID]) && is_array($_SESSION[self::SESSION_ID]) && count($_SESSION[self::SESSION_ID]) > 0) {
			foreach($_SESSION[self::SESSION_ID] as $notice) {
				$this->add($notice['header'], $notice['text'], $notice['icon'], $notice['type']);
			}
			unset($_SESSION[self::SESSION_ID]);
		}
	}

	public function clear() {
		$this->notices = [];
		$this->scripts = [];
		if(isset($_SESSION)) {
			unset($_SESSION[self::SESSION_ID]);
		}
		unset($GLOBALS[self::GLOBAL_ID]);
	}

	public function add($header = '', $text = '', $icon = '', $type = 'default') {
		if($this->has_notice($header, $text, $icon, $type)) {
			return;
		}
		$script = sprintf(
			"%s.push({header: '%s', text: '%s', icon: '%s', type: '%s'});",
			self::JS_ID,
			esc_attr($header),
			esc_attr($text),
			esc_attr($icon),
			esc_attr($type)
		);
		$this->add_script($script);
		$this->notices[] = [
			'header' => $header,
			'text' => $text,
			'icon' => $icon,
			'type' => $type,
		];
	}

	public function add_success($header = '', $text = '') {
		$this->add($header, $text, 'accept', 'positive');
	}

	public function add_info($header = '', $text = '') {
		$this->add($header, $text, 'info', 'info');
	}

	public function add_warning($header = '', $text = '') {
		$this->add($header, $text, 'exclamation-triangle', 'warning');
	}

	public function add_error($header = '', $text = '') {
		$this->add($header, $text, 'exclamation-circle', 'negative');
	}

	public function add_errors($header = '', $errors = []) {
		if(!is_array($errors) || count($errors) < 1) {
			return;
		}
		$content = '<ul>';
		foreach($errors as $message) {
			$content .= sprintf('<li>%s</li>', esc_html($message));
		}
		$content .= '</ul>';
		$this->add_error($header, $content);
	}

	/**
	 * Check if this notice already exists.
	 *
	 * @return boolean
	 */
	public function has_notice($header = '', $text = '', $icon = '', $type = 'default') {
		foreach($this->notices as $notice) {
			if(
				$notice['header'] === $header
				&& $notice['text'] === $text
				&& $notice['icon'] === $icon
				&& $notice['type'] === $type
			) {
				return true;
			}
		}
		return false;
	}
}
