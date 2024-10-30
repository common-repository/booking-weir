<?php

namespace wsd\bw\core\rest\endpoints;

use wsd\bw\core\rest\Endpoint;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Event extends Endpoint {

	public function register_routes() {

		$this->register_public_route('content', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'get_content'],
			'args' => [
				'id' => [
					'type' => 'number',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_event_id'],
					'sanitize_callback' => 'absint',
				],
				'start' => [
					'type' => 'string',
					'required' => false,
					'default' => '',
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_datetime'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_datetime'],
				],
				'end' => [
					'type' => 'string',
					'required' => false,
					'default' => '',
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_datetime'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_datetime'],
				],
			],
		]);

		$this->register_private_route('template-strings', [
			'methods' => WP_REST_Server::READABLE,
			'callback' => [$this, 'get_template_strings'],
			'args' => [
				'calendarId' => [
					'type' => 'string',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_calendar_id'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
			],
		]);

		$this->register_private_route('delete-file', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'delete_file'],
			'args' => [
				'id' => [
					'type' => 'number',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_event_id'],
					'sanitize_callback' => 'absint',
				],
				'fieldId' => [
					'type' => 'string',
					'required' => true,
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
				'fileName' => [
					'type' => 'string',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_file'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_string'],
				],
			],
		]);
	}

	/**
	 * Get event content.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_content(WP_REST_Request $request) {
		$event_id = $request->get_param('id');
		$event = $this->context->get('event-factory')->create($event_id);
		$start = $request->get_param('start');
		$end = $request->get_param('end');
		return $this->success($event->get_content($start, $end));
	}

	/**
	 * Get template strings available for use in template settings.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_template_strings(WP_REST_Request $request) {
		$calendar_id = $request->get_param('calendarId');
		$calendar = $this->context->get('calendars')->get_calendar($calendar_id);
		$event = $this->context->get('event-factory')->mock($calendar);
		return $this->success($event->get_template_strings());
	}

	/**
	 * Delete file attached to an event.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function delete_file(WP_REST_Request $request) {
		$event_id = $request->get_param('id');
		$field_id = $request->get_param('fieldId');
		$filename = $request->get_param('fileName');
		$dir = $this->context->upload_dir() . '/files';
		$file = $dir . '/' . $filename;
		if(validate_file($filename) !== 0 || !file_exists($file)) {
			return $this->error(__('File not found.', 'booking-weir'));
		}
		if(!@unlink($file)) {
			return $this->error(__('Failed deleting file.', 'booking-weir'));
		}
		$event = $this->context->get('event-factory')->create($event_id);
		$event->set_field($field_id, '');
		return $this->success();
	}
}
