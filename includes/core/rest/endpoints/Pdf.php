<?php

namespace wsd\bw\core\rest\endpoints;

use wsd\bw\core\rest\Endpoint;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Pdf extends Endpoint {

	public function register_routes() {

		$this->register_private_route('test', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'test_pdf'],
			'args' => [
				'calendarId' => [
					'type' => 'string',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_calendar_id'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
			],
		]);

		$this->register_private_route('regenerate', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'regenerate_pdf'],
			'args' => [
				'bookingId' => [
					'type' => 'number',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_event_id'],
					'sanitize_callback' => 'absint',
				],
			],
		]);

		$this->register_private_route('delete', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'delete_pdf'],
			'args' => [
				'bookingId' => [
					'type' => 'number',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_event_id'],
					'sanitize_callback' => 'absint',
				],
			],
		]);
	}

	/**
	 * Generate a test PDF document.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function test_pdf(WP_REST_Request $request) {
		$calendar_id = $request->get_param('calendarId');
		$calendars = $this->context->get('calendars');
		$calendar = $calendars->get_calendar($calendar_id);
		$result = $this->context->get('pdf')->test($calendar);
		if(is_array($result)) {
			return $this->success($result['url']);
		}
		return $this->error($result);
	}

	/**
	 * Regenerate PDF document for a booking.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function regenerate_pdf(WP_REST_Request $request) {
		$booking_id = $request->get_param('bookingId');
		$event = $this->context->get('event-factory')->create($booking_id);
		$generated = $this->context->get('pdf')->generate_invoice($event);
		if(is_array($generated)) {
			return $this->success();
		}
		if(is_string($generated)) {
			return $this->error($generated);
		}
		return $this->error(esc_html__('Failed generating PDF.', 'booking-weir'));
	}

	/**
	 * Delete PDF document from the filesystem.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete_pdf(WP_REST_Request $request) {
		$id = absint($request->get_param('bookingId'));
		$deleted = $this->context->get('pdf')->delete_invoice($id, true);
		if(is_bool($deleted) && $deleted) {
			return $this->success();
		}
		if(is_string($deleted)) {
			return $this->error($deleted);
		}
		return $this->error(esc_html__('Failed deleting PDF.', 'booking-weir'));
	}
}
