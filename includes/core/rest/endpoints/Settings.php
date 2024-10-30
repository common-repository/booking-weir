<?php

namespace wsd\bw\core\rest\endpoints;

use wsd\bw\core\rest\Endpoint;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Settings extends Endpoint {

	public function register_routes() {

		$this->register_private_route('schema', [
			'methods' => WP_REST_Server::READABLE,
			'callback' => [$this, 'get_value'],
			'args' => [
				'calendarId' => [
					'type' => 'string',
					'required' => false,
					'default' => '',
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_calendar_id'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
			],
		]);

		$this->register_private_route('schema/(?P<calendarId>\w+)', [
			'methods' => WP_REST_Server::READABLE,
			'callback' => [$this, 'get_value'],
			'args' => [
				'calendarId' => [
					'type' => 'string',
					'required' => false,
					'default' => '',
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_calendar_id'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
			],
		]);
	}

	/**
	 * Retrieve settings schema.
	 *
	 * @param string $calendarId
	 * @return array
	 */
	protected function get_settings_schema($calendarId) {
		$calendars = $this->context->get('calendars');
		if(!empty($calendarId)) {
			$calendar = $calendars->get_calendar($calendarId);
			$settings = $calendar->get_settings_schema();
		} else {
			$settings = $calendars->get_default_settings_schema();
		}
		return $settings;
	}

	/**
	 * Get a calendar's settings schema.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_value(WP_REST_Request $request) {
		$value = $this->get_settings_schema($request->get_param('calendarId'));
		if($value) {
			return $this->success($value);
		}
		return $this->error();
	}
}
