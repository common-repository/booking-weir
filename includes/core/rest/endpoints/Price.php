<?php

namespace wsd\bw\core\rest\endpoints;

use wsd\bw\core\rest\Endpoint;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Price extends Endpoint {

	public function register_routes() {

		$this->register_public_route('', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'get_price'],
			'args' => [
				'id' => [
					'type' => 'string',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_calendar_id'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
				'start' => [
					'type' => 'string',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_datetime'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_datetime'],
				],
				'end' => [
					'type' => 'string',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_datetime'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_datetime'],
				],
				'extras' => [
					// 'type' => 'array',
					'default' => [],
					'required' => false,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_extras'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_extras'],
				],
				'coupon' => [
					'type' => 'string',
					'default' => '',
					'required' => false,
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_string'],
				],
				'serviceId' => [
					'type' => 'string',
					'default' => '',
					'required' => false,
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
				'bookableEventId' => [
					'type' => 'number',
					'required' => false,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_event_id'],
					'sanitize_callback' => 'absint',
				],
			],
		]);
	}

	/**
	 * Retrieve price for an event with specified parameters.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_price(WP_REST_Request $request) {
		$calendars = $this->context->get('calendars');
		$calendar = $calendars->get_calendar($request->get_param('id'));
		$price = $calendar->get_event_price(
			$request->get_param('start'),
			$request->get_param('end'),
			$request->get_param('extras'),
			$request->get_param('coupon'),
			$request->get_param('serviceId'),
			$request->get_param('bookableEventId')
		);
		if($price) {
			return $this->success($price);
		}
		return $this->error();
	}
}
