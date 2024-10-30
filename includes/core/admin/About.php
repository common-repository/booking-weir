<?php

namespace wsd\bw\core\admin;

use wsd\bw\Context;
use wsd\bw\util\helpers;

/**
 * Logger class.
 */
final class About {

	/** @var Context */
	protected $context;

	/**
	 * About page admin screen handle.
	 *
	 * @var string
	 */
	protected $screen_handle;

	/**
	 * Query var for restoring calendars.
	 */
	const RESTORE_QUERY_VAR = 'bw-restore';

	/**
	 * Nonce action for restoring calendars.
	 */
	const RESTORE_ACTION = 'bw-restore-calendars';

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_action('admin_menu', [$this, 'add_menu_items']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
		add_action('admin_init', [$this, 'handle_restore_calendars']);
	}

	/**
	 * Returns the slug for the settings page.
	 *
	 * @return string
	 */
	public function get_menu_slug() {
		return sprintf('%s-about', $this->context->plugin_slug());
	}

	/**
	 * Add About page to admin menu.
	 */
	public function add_menu_items() {
		$this->screen_handle = add_submenu_page(
			$this->context->get('admin')->get_menu_slug(),
			_x('About', 'About page menu item name', 'booking-weir'),
			_x('About', 'About page menu item name', 'booking-weir'),
			$this->context->get_admin_capability(),
			$this->get_menu_slug(),
			[$this, 'render_screen']
		);
	}

	/**
	 * Enqueue admin assets for the About page.
	 */
	public function enqueue_admin_assets() {
		if(!$this->is_about_page()) {
			return;
		}
		wp_enqueue_style($this->context->get_style_handle('sui'));
	}

	/**
	 * Render the about page.
	 */
	public function render_screen() {
		include $this->context->file('templates', 'about.php');
	}

	/**
	 * Provides the logs screen handle.
	 *
	 * @return string
	 */
	public function get_screen_handle() {
		return $this->screen_handle;
	}

	/**
	 * Check if current page is the About page.
	 *
	 * @return boolean
	 */
	public function is_about_page() {
		$screen = get_current_screen();
		if($screen === null) {
			return false;
		}
		return $this->get_screen_handle() === $screen->id;
	}

	/**
	 * Info to display on the About page.
	 *
	 * @return array
	 */
	public function get_info() {
		$info = [
			[
				'title' => __('Plugin version', 'booking-weir'),
				'value' => BOOKING_WEIR_VER,
			],
			[
				'title' => __('Upload dir', 'booking-weir'),
				'value' => $this->context->upload_dir(),
				'type' => 'code',
			],
			[
				'title' => __('Logs dir', 'booking-weir'),
				'value' => $this->context->get('logger')->get_logs_dir(),
				'type' => 'code',
			],
			[
				'title' => __('WordPress version', 'booking-weir'),
				'value' => get_bloginfo('version'),
			],
			[
				'title' => __('WooCommerce version', 'booking-weir'),
				'value' => class_exists('WooCommerce') ? $GLOBALS['woocommerce']->version : '',
			],
			[
				'title' => __('PHP version', 'booking-weir'),
				'value' => phpversion(),
			],
			[
				'title' => __('PHP NumberFormatter', 'booking-weir'),
				'value' => class_exists('NumberFormatter'),
				'type' => 'bool',
			],
			[
				'title' => __('Server', 'booking-weir'),
				'value' => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '',
			],
			[
				'title' => __('Default timezone is UTC', 'booking-weir'),
				'value' => date_default_timezone_get() === 'UTC',
				'type' => 'bool',
			],
		];
		return $info;
	}

	/**
	 * Restore selected calendars from the history.
	 */
	public function handle_restore_calendars() {
		if(!isset($_GET[self::RESTORE_QUERY_VAR])) {
			return;
		}
		if(!check_admin_referer(self::RESTORE_ACTION)) {
			helpers\admin_notice(__('Invalid session.', 'booking-weir'), 'error');
			return;
		}
		$time = absint($_GET[self::RESTORE_QUERY_VAR]);
		$calendars = $this->context->get('calendars');
		$history = $calendars->get_history();
		if(isset($history[$time])) {
			if($calendars->update_calendars($history[$time])) {
				helpers\admin_notice(__('Restored calendar.', 'booking-weir'), 'success');
			} else {
				helpers\admin_notice(__('Failed restoring calendar.', 'booking-weir'), 'warning');
			}
		} else {
			helpers\admin_notice(__('Calendar not found.', 'booking-weir'), 'error');
		}
	}
}
