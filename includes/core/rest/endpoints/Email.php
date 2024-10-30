<?php

namespace wsd\bw\core\rest\endpoints;

use wsd\bw\core\rest\Endpoint;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Email extends Endpoint {

	public function register_routes() {

		$this->register_private_route('invoice', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'send_invoice'],
			'args' => [
				'eventId' => [
					'type' => 'number',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_event_id'],
					'sanitize_callback' => 'absint',
				],
			],
		]);

		$this->register_private_route('reminder', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'send_reminder'],
			'args' => [
				'eventId' => [
					'type' => 'number',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_event_id'],
					'sanitize_callback' => 'absint',
				],
			],
		]);

		$this->register_private_route('test', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'get_test_email'],
			'args' => [
				'calendarId' => [
					'type' => 'string',
					'required' => true,
					'validate_callback' => [$this->context->get('sanitizer'), 'validate_calendar_id'],
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_id'],
				],
				'settings' => [
					'default' => [],
					'required' => true,
					'validate_callback' => function($settings) {
						return is_array($settings);
					},
				],
				'type' => [
					'type' => 'string',
					'default' => '',
					'required' => true,
				],
			],
		]);
	}

	/**
	 * Send invoice email.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function send_invoice(WP_REST_Request $request) {
		$event_id = $request->get_param('eventId');
		$event = $this->context->get('event-factory')->create($event_id);
		if(!$event->get_email()) {
			return $this->error(__("Event doesn't have an e-mail specified.", 'booking-weir'));
		}
		if($this->context->get('payment')->send_invoice($event)) {
			return $this->success();
		}
		return $this->error(__('Failed sending invoice e-mail, check that your server is correctly configured for using the PHP mail() function.', 'booking-weir'));
	}

	/**
	 * Send reminder email.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function send_reminder(WP_REST_Request $request) {
		$event_id = $request->get_param('eventId');
		$event = $this->context->get('event-factory')->create($event_id);
		if($this->context->get('email')->send_reminder_email($event)) {
			return $this->success();
		}
		return $this->error(__('Failed sending reminder e-mail, check that your server is correctly configured for using the PHP mail() function.', 'booking-weir'));
	}

	/**
	 * Get test email HTML output.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_test_email(WP_REST_Request $request) {
		$calendar_id = $request->get_param('calendarId');
		$calendar = $this->context->get('calendars')->get_calendar($calendar_id);
		$settings = $request->get_param('settings');
		$type = $request->get_param('type');
		$template = $settings[$type];
		$event = $this->context->get('event-factory')->mock($calendar);

		$html = $this->context->get('email')->get_html(
			'Title',
			'Preview',
			strtr($template, $event->get_template_strings()),
			[
				'header' => $settings['templateEmailHeader'],
				'footer' => $settings['templateEmailFooter'],
			]
		);
		return $this->success($html);
	}
}
