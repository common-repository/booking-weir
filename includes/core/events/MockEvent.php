<?php

namespace wsd\bw\core\events;

use wsd\bw\Context;
use wsd\bw\core\calendars\Calendar;

class MockEvent extends Event {

	/** @var Context */
	protected $context;

	/**
	 * Event ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Event calendar (not a mock).
	 *
	 * @var Calendar
	 */
	protected $calendar;

	public function __construct(Context $context, Calendar $calendar) {
		$this->context = $context;
		$this->id = 123;
		$this->calendar = $calendar;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_start() {
		return date('Y-m-d\T16:00', strtotime('+1 week'));
	}

	public function get_end() {
		return date('Y-m-d\T17:30', strtotime('+1 week'));
	}

	public function get_calendar() {
		return $this->calendar;
	}

	public function get_price() {
		return 120.5;
	}

	public function get_billing_id() {
		return 'ABC21323';
	}

	public function get_payment_method() {
		return 'bankTransfer';
	}

	public function get_first_name() {
		return 'John';
	}

	public function get_last_name() {
		return 'Doe';
	}

	public function get_name() {
		return 'John Doe';
	}

	public function get_booking_link() {
		return '#';
	}

	/**
	 * @return array|false
	 */
	public function get_service() {
		return [
			'id' => 'mock-service',
			'name' => 'Service',
			'description' => '',
			'duration' => 3,
			'price' => 10,
		];
	}
}
