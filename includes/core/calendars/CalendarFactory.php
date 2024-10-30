<?php

namespace wsd\bw\core\calendars;

use wsd\bw\Context;

/**
 * Calendar factory.
 */
final class CalendarFactory {

	/** @var Context */
	protected $context;

	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * @param Calendars $calendars
	 * @param string $id
	 * @param array $calendar
	 * @return Calendar
	 */
	public function create(Calendars $calendars, $id, $calendar) {
		return new Calendar($this->context, $calendars, $id, $calendar);
	}
}
