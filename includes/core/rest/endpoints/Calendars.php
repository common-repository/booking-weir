<?php

namespace wsd\bw\core\rest\endpoints;

use wsd\bw\core\rest\Endpoint;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Calendars extends Endpoint {

	public function register_routes() {

		$this->register_private_route('', [
			'methods' => WP_REST_Server::READABLE,
			'callback' => [$this, 'get_value'],
		]);

		$this->register_private_route('', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'update_value'],
			'args' => [
				'calendars' => [
					'default' => [],
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_calendars'],
				],
			],
		]);

		$this->register_private_route('get', [
			'methods' => WP_REST_Server::READABLE,
			'callback' => [$this, 'get_calendars'],
		]);
	}

	/**
	 * Retrieve calendars.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_value(WP_REST_Request $request) {
		$value = $this->context->get('calendars')->get_value();
		if(!$value) {
			$value = ''; // Don't return false if there are no calendars yet.
		}
		return $this->success($value);
	}

	/**
	 * Save calendars.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update_value(WP_REST_Request $request) {
		$next_calendars = $request->get_param('calendars');
		if(!is_array($next_calendars)) {
			return $this->error(esc_html__('Invalid calendars.', 'booking-weir'));
		}
		$calendars = $this->context->get('calendars');
		if($calendars->update_calendars($next_calendars)) {
			return $this->success($calendars->get_value());
		}

		/**
		 * Check if updating failed because there were no changes
		 * by stripping the dynamic data and comparing values.
		 */
		$current_calendars = $calendars->get_value();
		foreach($current_calendars as $id => $calendar) {
			if(isset($calendar['data'])) {
				unset($current_calendars[$id]['data']);
			}
		}
		if(json_encode($current_calendars) === json_encode($next_calendars)) {
			return $this->success($calendars->get_value());
		}

		return $this->error(esc_html__('Failed updating calendars.', 'booking-weir'));
	}

	/**
	 * Get all calendars as ID => Name pairs.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_calendars(WP_REST_Request $request) {
		$calendars = [];
		foreach($this->context->get('calendars')->get_calendars() as $calendar) {
			$calendars[$calendar->get_id()] = $calendar->get_name();
		}
		return $this->success($calendars);
	}
}
