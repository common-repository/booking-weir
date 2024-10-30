<?php

namespace wsd\bw\core;

use wsd\bw\Context;
use wsd\bw\config\Config;
use wsd\bw\core\booking\Booking;
use wsd\bw\core\calendars\Calendar;
use wsd\bw\core\events\Event;

use function wsd\bw\util\datetime\get_supported_locales;

/**
 * ScriptData class.
 */
final class ScriptData {

	/** @var Context */
	protected $context;

	/** @var Config */
	protected $event_meta;

	/** @var Config */
	protected $event_types;

	/** @var Config */
	protected $booking_statuses;

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context, Config $event_meta, Config $event_types, Config $booking_statuses) {
		$this->context = $context;
		$this->event_meta = $event_meta;
		$this->event_types = $event_types;
		$this->booking_statuses = $booking_statuses;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {}

	public function get_api_data() {
		return [
			'api_url' => rest_url($this->context->plugin_slug() . '/v1/'),
			'api_nonce' => wp_create_nonce('wp_rest'),
			'nonce_endpoint' => admin_url('admin-ajax.php?action=' . $this->context->get('rest')::NONCE_ENDPOINT),
		];
	}

	public function get_public_data() {
		$data = array_merge(
			$this->get_api_data(),
			[
				'event_types' => $this->event_types->get(),
				'event_schema' => $this->context->get('event-post-type')->get_schema(),
				'field_types' => $this->context->get('field-types')->get(),
				'default_fields' => $this->context->get('default-fields')->get(),
				'payment_methods' => $this->context->get('payment')->get_methods(),
			]
		);

		return apply_filters('bw_script_data', $this->with_debug_data($data));
	}

	public function get_admin_data() {
		$data = array_merge(
			$this->get_public_data(),
			[
				'event_schema' => $this->context->get('event-post-type')->get_schema(false),
				'booking_statuses' => $this->booking_statuses->get(),
				'price_types' => $this->context->get('payment')->get_price_types(),
				'payment_method_data' => $this->context->get('payment')->get_method_data(),
				'home_url' => untrailingslashit(home_url('/')),
				'upload_url' => $this->context->upload_url(),
				'blog_name' => get_bloginfo('name'),
				'locales' => get_supported_locales(),
				/**
				 * $_GET variable names.
				 */
				'GET' => [
					'booking' => [
						'view' => Booking::VIEW,
					],
					'service' => [
						'view' => Calendar::SELECTED_SERVICE,
					],
					'event' => [
						'view' => Event::VIEW,
						'start' => Event::START,
						'action' => Event::ACTION,
					],
				],
			]
		);

		return apply_filters('bw_admin_script_data', $this->with_context($data));
	}

	public function with_context($data) {
		if($this->context->is_elevated()) {
			$data = array_merge($data, [
				'is_admin' => $this->context->is_admin(),
				'white_label' => $this->context->is_white_label(),
			]);
		}
		return $data;
	}

	public function with_debug_data($data) {
		if($this->context->is_elevated()) {
			$data = array_merge($data, [
				'debug_email' => $this->context->get('email')::DEBUG_EMAIL_PLACEHOLDER,
				'log' => $this->context->is_admin(),
			]);
		}
		return $data;
	}
}
