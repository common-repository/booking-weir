<?php

namespace wsd\bw\core\rest\endpoints;

use wsd\bw\core\rest\Endpoint;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Calendar extends Endpoint {

	public function register_routes() {

		$this->register_public_route('service-description', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'get_service_description'],
			'args' => [
				'calendarId' => [
					'type' => 'string',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_calendar_id'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
				'id' => [
					'type' => 'string',
					'required' => true,
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
			],
		]);
	}

	/**
	 * Get service description.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_service_description(WP_REST_Request $request) {
		$service_id = $request->get_param('id');
		$calendar_id = $request->get_param('calendarId');
		$calendar = $this->context->get('calendars')->get_calendar($calendar_id);
		$service = $calendar->get_service($service_id);
		return $this->success($service['description']);
	}
}
