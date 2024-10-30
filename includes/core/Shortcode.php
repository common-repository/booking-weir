<?php

namespace wsd\bw\core;

use wsd\bw\Context;
use wsd\bw\core\calendars\Calendars;

/**
 * Shortcode class.
 */
final class Shortcode {

	/** @var Context */
	protected $context;

	/** @var Calendars */
	protected $calendars;

	/**
	 * Shortcode tag name.
	 */
	const TAG = 'bw-booking';

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context, Calendars $calendars) {
		$this->context = $context;
		$this->calendars = $calendars;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_shortcode(self::TAG, [$this, 'shortcode']);
		add_action('wp_enqueue_scripts', [$this, 'register_frontend_assets']);
	}

	/**
	 * Register assets used with the shortcode.
	 */
	public function register_frontend_assets() {
		$asset = require $this->context->build_path('shortcode.asset.php');

		wp_register_style(
			$this->context->get_style_handle('shortcode'),
			$this->context->build_url('shortcode.css'),
			$asset['version']
		);

		wp_register_script(
			$this->context->get_script_handle('shortcode'),
			$this->context->build_url('shortcode.js'),
			$asset['dependencies'],
			$asset['version']
		);

		wp_localize_script(
			$this->context->get_script_handle('shortcode'),
			'booking_weir_data',
			$this->context->get('script-data')->get_public_data()
		);

		if(function_exists('wp_set_script_translations')) {
			wp_set_script_translations(
				$this->context->get_script_handle('shortcode'),
				$this->context->plugin_slug(),
				$this->context->languages_path()
			);
		}
	}

	/**
	 * Render shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function shortcode($atts) {
		if(is_admin()) {
			return '';
		}

		$data = shortcode_atts([
			'id' => '', // Calendar ID.
			'type' => 'default',
			'classes' => '',
		], $atts, self::TAG);

		if(empty($data['id'])) {
			if($this->context->is_elevated()) {
				return esc_html__('No calendar ID specified in the `id` attribute.', 'booking-weir');
			}
			return '';
		}

		$data_id = wp_unique_id(sprintf('%s_', str_replace('-', '_', $this->context->plugin_slug())));
		if(!$calendar = $this->calendars->get_calendar($data['id'])) {
			if($this->context->is_elevated()) {
				return esc_html__('Calendar not found.', 'booking-weir');
			}
			return esc_html__('Booking unavailable.', 'booking-weir');
		}
		if($calendar->is_product()) {
			if(!class_exists('WooCommerce')) {
				if($this->context->is_elevated()) {
					return esc_html__("Please activate WooCommerce or remove this calendar's association with a WooCommerce product.", 'booking-weir');
				}
				return esc_html__('Booking unavailable.', 'booking-weir');
			}
			if(!is_product()) {
				$product_id = (int)$calendar->get_setting('product');
				$product = wc_get_product($product_id);
				if(!$product) {
					if($this->context->is_elevated()) {
						return esc_html__('Product associated with the calendar was not found.', 'booking-weir');
					}
					return esc_html__('Booking unavailable.', 'booking-weir');
				}
				if($product->get_type() !== 'bw_booking') {
					if($this->context->is_elevated()) {
						return esc_html__('Product associated with the calendar is not a "Booking product". Please change the product type.', 'booking-weir');
					}
					return esc_html__('Booking unavailable.', 'booking-weir');
				}
				return sprintf(
					esc_html__('%1$sView booking product%2$s', 'booking-weir'),
					'<a href="' . esc_url($product->get_permalink()) . '">',
					'</a>'
				);
			}
		}
		$data['calendar'] = $calendar->get_public_calendar();

		wp_enqueue_style($this->context->get_style_handle('shortcode'));
		wp_enqueue_script($this->context->get_script_handle('shortcode'));
		wp_localize_script($this->context->get_script_handle('shortcode'), $data_id, $data);

		if(!did_action('bw_shortcode_ready')) {
			do_action('bw_shortcode_ready');
		}

		return sprintf(
			'<div data-bw-id="%s" class="%s"></div>',
			esc_attr($data_id),
			esc_attr(trim(implode(' ', [self::TAG, $calendar->get_setting('classes'), $data['classes']])))
		);
	}
}
