<?php

namespace wsd\bw\core\events;

use wsd\bw\Context;

class RepeatEvent extends Event {

	/** @var Context */
	protected $context;

	/**
	 * Event that is repeated.
	 *
	 * @var Event
	 */
	protected $event;

	/**
	 * Event ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Repeat event's start time.
	 *
	 * @var string
	 */
	protected $start;

	/**
	 * Repeat event's end time.
	 *
	 * @var string
	 */
	protected $end;

	public function __construct(Context $context, Event $event, $start, $end) {
		$this->context = $context;
		$this->event = $event;
		$this->id = $event->get_id();
		$this->start = $start;
		$this->end = $end;
	}

	public function get_start() {
		return $this->start;
	}

	public function get_end() {
		return $this->end;
	}

	public function repeats() {
		return false;
	}

	public function is_repeat() {
		return true;
	}
}
