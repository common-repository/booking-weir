<?php

namespace wsd\bw\core\admin;

use wsd\bw\Context;
use wsd\bw\core\events\EventPostType;
use wsd\bw\core\ScriptData;
use wsd\bw\util\helpers;

/**
 * Admin class.
 *
 * Responsible for initializing the plugin.
 */
final class Admin {

	/** @var Context */
	protected $context;

	/** @var EventPostType */
	protected $event_post_type;

	/** @var ScriptData */
	protected $script_data;

	/**
	 * Menu item icon.
	 *
	 * `calendar` from `@wordpress/icons`
	 *
	 * @var string
	 */
	const ICON_BASE64_SVG = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgaWQ9InN2ZzEyIgogICB2ZXJzaW9uPSIxLjEiCiAgIGZvY3VzYWJsZT0iZmFsc2UiCiAgIGFyaWEtaGlkZGVuPSJ0cnVlIgogICByb2xlPSJpbWciCiAgIHZpZXdCb3g9IjAgMCA2NCA2NCIKICAgaGVpZ2h0PSI2NCIKICAgd2lkdGg9IjY0Ij4KICA8ZGVmcwogICAgIGlkPSJkZWZzMTYiIC8+CiAgPHBhdGgKICAgICBzdHlsZT0ic3Ryb2tlLXdpZHRoOjMuMzMzMzMzNDkiCiAgICAgaWQ9InBhdGgxMCIKICAgICBmaWxsPSIjYTBhNWFhIgogICAgIGQ9Ik0gNTUuMzMzMzMzLDIgSCA4LjY2NjY2NjcgQyA1LjAwMDAwMDQsMiAyLDUgMiw4LjY2NjY2NyBWIDU1LjMzMzMzNiBDIDIsNTkgNS4wMDAwMDA0LDYyIDguNjY2NjY2Nyw2MiBIIDU1LjMzMzMzMyBDIDU5LjAwMDAwNCw2MiA2Miw1OSA2Miw1NS4zMzMzMzYgViA4LjY2NjY2NyBDIDYyLDUgNTkuMDAwMDA0LDIgNTUuMzMzMzMzLDIgWiBtIDEuNjY2NjY4LDUzLjMzMzMzNiBjIDAsMC45OTk5OTQgLTAuNjY2NjY0LDEuNjY2NjYxIC0xLjY2NjY2OCwxLjY2NjY2MSBIIDguNjY2NjY2NyBjIC0wLjk5OTk5OTcsMCAtMS42NjY2NjY1LC0wLjY2NjY2NyAtMS42NjY2NjY1LC0xLjY2NjY2MSBWIDE1LjMzMzMzMyBIIDU3LjAwMDAwMSBaIG0gLTM1LC0zMC4wMDAwMDQgSCAxNS4zMzMzMzQgViAzMiBoIDYuNjY2NjY3IHogbSAwLDEzLjMzMzMzNiBoIC02LjY2NjY2NyB2IDYuNjY2NjYzIGggNi42NjY2NjcgeiBNIDM1LjMzMzMzNCwyNS4zMzMzMzIgSCAyOC42NjY2NjYgViAzMiBoIDYuNjY2NjY4IHogbSAxMy4zMzMzMzUsMCBIIDQyLjAwMDAwMSBWIDMyIGggNi42NjY2NjggeiBNIDM1LjMzMzMzNCwzOC42NjY2NjcgaCAtNi42NjY2NjggdiA2LjY2NjY2NCBoIDYuNjY2NjY4IHogbSAxMy4zMzMzMzUsMCBoIC02LjY2NjY2OCB2IDYuNjY2NjY0IGggNi42NjY2NjggeiIgLz4KPC9zdmc+Cg==';

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context, EventPostType $event_post_type, ScriptData $script_data) {
		$this->context = $context;
		$this->event_post_type = $event_post_type;
		$this->script_data = $script_data;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_action('admin_init', [$this, 'register_admin_assets']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
		add_action('admin_menu', [$this, 'add_menu_items'], 8);
		(new EventList($this->context, $this->event_post_type))->register();
	}

	/**
	 * Returns the slug for the settings page.
	 *
	 * @return string
	 */
	public function get_menu_slug() {
		return $this->context->plugin_slug();
	}

	public function add_menu_items() {
		add_menu_page(
			_x('Booking', 'Plugin admin root menu item', 'booking-weir'),
			_x('Booking', 'Plugin admin root menu item', 'booking-weir'),
			$this->context->get_required_capability(),
			$this->get_menu_slug(),
			[$this, 'render_screen'],
			self::ICON_BASE64_SVG,
			apply_filters('bw_menu_item_priority', 25)
		);
		add_submenu_page(
			$this->get_menu_slug(),
			_x('Calendars', 'Plugin admin menu item', 'booking-weir'),
			_x('Calendars', 'Plugin admin menu item', 'booking-weir'),
			$this->context->get_required_capability(),
			$this->get_menu_slug(),
			[$this, 'render_screen']
		);
		/**
		 * Events submenu item added by `EventPostType`.
		 */
		/**
		 * Logger submenu item added by `Logger`.
		 */
	}

	public function register_admin_assets() {
		$asset = require $this->context->build_path('admin.asset.php');
		$sui = require $this->context->path('sui/admin-sui.asset.php');

		/**
		 * Styles.
		 */
		wp_register_style(
			$this->context->get_style_handle('sui'),
			$this->context->url('sui/admin-sui.css'),
			[],
			$sui['version']
		);

		wp_register_style(
			$this->context->get_style_handle('admin'),
			$this->context->build_url('admin.css'),
			[
				'wp-edit-post',
				$this->context->get_style_handle('sui'),
			],
			$asset['version']
		);

		/**
		 * Scripts.
		 */
		wp_register_script(
			$this->context->get_script_handle('admin'),
			$this->context->build_url('admin.js'),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_localize_script(
			$this->context->get_script_handle('admin'),
			'booking_weir_data',
			$this->script_data->get_admin_data()
		);

		if(function_exists('wp_set_script_translations')) {
			wp_set_script_translations(
				$this->context->get_script_handle('admin'),
				$this->context->plugin_slug(),
				$this->context->languages_path()
			);
		}

		helpers\localize_script($this->context->get_script_handle('admin'), 'BOOKING_WEIR_VER', BOOKING_WEIR_VER);
		helpers\localize_script($this->context->get_script_handle('admin'), 'BOOKING_WEIR_URL', 'https://chap.website/booking-weir');
	}

	public function enqueue_admin_assets() {
		if(!$this->is_settings_page()) {
			return;
		}

		do_action('bw_admin_init');
		$this->enqueue_admin_styles();
		$this->enqueue_admin_scripts();
	}

	public function enqueue_admin_styles() {
		wp_enqueue_style($this->context->get_style_handle('sui'));
		wp_enqueue_style($this->context->get_style_handle('admin'));
	}

	public function enqueue_admin_scripts() {
		/**
		 * Gutenberg deps.
		 */
		wp_enqueue_media();
		wp_tinymce_inline_scripts();
		wp_enqueue_editor();
		wp_enqueue_style('wp-edit-post');
		wp_enqueue_style('wp-format-library');

		/**
		 * Admin script.
		 */
		wp_enqueue_script($this->context->get_script_handle('admin'));
	}

	/**
	 * Provides the settings screen handle.
	 *
	 * @return string
	 */
	public function get_screen_handle() {
		return sprintf('toplevel_page_%s', $this->get_menu_slug());
	}

	public function is_settings_page() {
		$screen = get_current_screen();
		if($screen === null) {
			return false;
		}
		return $this->get_screen_handle() === $screen->id;
	}

	/**
	 * Get admin (calendars) page URL.
	 *
	 * @return string
	 */
	public function get_url() {
		return add_query_arg('page', $this->get_menu_slug(), admin_url('admin.php'));
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public function render_screen() {
		?>
		<div id="bw-admin" class="bw-root">
			<div id="bw-no-sui"></div>
			<div id="bw-sui-root" class="sui-root"></div>
		</div>
		<?php
	}
}
