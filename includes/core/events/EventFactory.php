<?php

namespace wsd\bw\core\events;

use wsd\bw\Context;
use wsd\bw\core\calendars\Calendar;

/**
 * Event factory.
 */
final class EventFactory {

	/** @var Context */
	protected $context;

	public function __construct(Context $context) {
		$this->context = $context;
	}

	public function create($id) {
		return new Event($this->context, $id);
	}

	public function repeat(Event $event, $start, $end) {
		return new RepeatEvent($this->context, $event, $start, $end);
	}

	public function mock(Calendar $calendar) {
		return new MockEvent($this->context, $calendar);
	}
}
