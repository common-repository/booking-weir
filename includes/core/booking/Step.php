<?php

namespace wsd\bw\core\booking;

/**
 * An increment of time of an event.
 */
class Step {

	/**
	 * PriceCalculator instance.
	 *
	 * @var PriceCalculator
	 */
	protected $price_calculator;

	/**
	 * Step start timestamp.
	 *
	 * @var integer
	 */
	protected $start;

	/**
	 * Step end timestamp.
	 *
	 * @var integer
	 */
	protected $end;

	/**
	 * Coupons applied to the event.
	 *
	 * @var array
	 */
	protected $coupons;

	/**
	 * Step length in minutes.
	 *
	 * @var integer
	 */
	protected $duration;

	/**
	 * Total duration of the whole event in seconds.
	 *
	 * @var integer
	 */
	protected $total_duration;

	/**
	 * Calendar's initial price per hour that the event is in.
	 *
	 * @var float
	 */
	protected $price_per_hour;

	/**
	 * Initialize step.
	 *
	 * @param PriceCalculator $price_calculator Price calculator instance.
	 * @param integer $start Step start timestamp.
	 * @param integer $end Step end timestamp.
	 * @param array $coupons Coupons applied to the event.
	 * @param integer $duration Step duration in minutes.
	 * @param integer $total_duration Total duration of the whole event in seconds.
	 * @param float $price_per_hour
	 */
	public function __construct($price_calculator, $start, $end, $coupons, $duration, $total_duration, $price_per_hour) {
		$this->price_calculator = $price_calculator;
		$this->start = $start;
		$this->end = $end;
		$this->coupons = $coupons;
		$this->duration = $duration;
		$this->total_duration = $total_duration;
		$this->price_per_hour = $price_per_hour;
	}

	/**
	 * Get the price calculator instance.
	 *
	 * @return PriceCalculator
	 */
	public function get_price_calculator() {
		return $this->price_calculator;
	}

	public function get_start() {
		return $this->start;
	}

	public function get_end() {
		return $this->end;
	}

	public function has_coupon($coupon) {
		return in_array(mb_strtolower($coupon), $this->coupons);
	}

	public function get_duration() {
		return $this->duration;
	}

	public function get_total_duration() {
		return $this->total_duration;
	}

	public function get_total_duration_minutes() {
		return $this->total_duration / 60;
	}

	public function get_price_per_hour() {
		return $this->price_per_hour;
	}

	/**
	 * Get step price.
	 *
	 * @param boolean|int $price_per_hour If supplied, use this price as the hourly price instead of default hourly price.
	 * @return float
	 */
	public function get_price_per_step($price_per_hour = false) {
		if(!$price_per_hour) {
			$price_per_hour = $this->get_price_per_hour();
		}
		return ($price_per_hour / 60) * $this->get_duration();
	}
}
