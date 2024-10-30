<?php

namespace wsd\bw\core;

use wsd\bw\Context;

/**
 * Blocks class.
 */
final class Blocks {

	/** @var Context */
	protected $context;

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
		if(version_compare($GLOBALS['wp_version'], '5.8', '<')) {
			add_filter('block_categories', [$this, 'add_categories']);
		} else {
			add_filter('block_categories_all', [$this, 'add_categories']);
		}
		add_filter('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
		add_action('init', [$this, 'register_blocks']);
	}

	/**
	 * Register Booking Weir block category.
	 *
	 * @param array $categories
	 * @return array
	 */
	public function add_categories(array $categories = []) {
		return array_merge(
			$categories,
			[
				[
					'slug' => 'booking-weir',
					'title' => esc_attr__('Booking Weir', 'booking-weir'),
				],
			]
		);
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_editor_assets() {
		$asset = require $this->context->build_path('blocks.asset.php');

		wp_enqueue_style(
			$this->context->get_style_handle('blocks'),
			$this->context->build_url('blocks.css'),
			[],
			$asset['version']
		);

		wp_enqueue_script(
			$this->context->get_script_handle('blocks'),
			$this->context->build_url('blocks.js'),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		$script_data = $this->context->get('script-data');
		wp_localize_script(
			$this->context->get_script_handle('blocks'),
			'booking_weir_data',
			$script_data->with_context($script_data->get_api_data())
		);

		if(function_exists('wp_set_script_translations')) {
			wp_set_script_translations(
				$this->context->get_script_handle('blocks'),
				$this->context->plugin_slug(),
				$this->context->languages_path()
			);
		}
	}

	/**
	 * Register blocks.
	 */
	public function register_blocks() {
		register_block_type(
			'booking-weir/calendar',
			[
				'render_callback' => [$this, 'render_calendar'],
			]
		);
	}

	/**
	 * Render calendar block.
	 *
	 * @param array $attributes
	 * @param string $content
	 * @return string
	 */
	public function render_calendar($attributes = [], $content = '') {
		if(!isset($attributes['calendarId']) || empty($attributes['calendarId'])) {
			return '';
		}

		$classes = isset($attributes['className']) ? $attributes['className'] : '';

		if(isset($attributes['align'])) {
			switch($attributes['align']) {
				case 'full':
					$classes .= ' alignfull';
				break;
				case 'wide':
					$classes .= ' alignwide';
				break;
			}
		}

		return sprintf(
			'[%s id="%s" type="%s" classes="%s" /]',
			$this->context->get('shortcode')::TAG,
			esc_attr($attributes['calendarId']),
			isset($attributes['type']) ? esc_attr($attributes['type']) : 'default',
			trim(esc_attr($classes))
		);
	}
}
