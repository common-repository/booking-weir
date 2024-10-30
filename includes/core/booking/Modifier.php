<?php

namespace wsd\bw\core\booking;

/**
 * A pricing rule's price modifier that can be applied to a Price.
 */
class Modifier {

	/**
	 * The pricing rule whos modifier is to be applied.
	 *
	 * @var Rule
	 */
	protected $rule;

	/**
	 * The step of an event whos price the modifier should modify.
	 *
	 * @var Step
	 */
	protected $step;

	/**
	 * Initialize modifier.
	 *
	 * @param Rule $rule
	 * @param Step $step
	 */
	public function __construct($rule, $step) {
		$this->rule = $rule;
		$this->step = $step;
	}

	/**
	 * Calculate the modification that should be added to or replace the provided price.
	 *
	 * @param integer|float $price Current price of the step.
	 * @return integer|float Modification.
	 */
	public function get_modification($price) {
		switch($this->get_modify()) {
			case 'hour':
				switch($this->get_type()) {
					case 'percent':
						/**
						 * Add modifier % to the step price.
						 */
						return ($this->step->get_price_per_step() * $this->get_modifier()) / 100;
					case 'money':
						/**
						 * Add modifier to hourly price,
						 * find the new step price based on that,
						 * subtract original step price to get the difference.
						 */
						return ((($this->step->get_price_per_hour() + $this->get_modifier()) / 60) * $this->step->get_duration()) - $this->step->get_price_per_step();
				}
			case 'set-hour':
				/**
				 * Return the price per step calculated using the new hourly price.
				 */
				return $this->step->get_price_per_step($this->get_modifier());
			case 'total':
				/**
				 * Set price to this amount.
				 */
				return $this->get_modifier();
			case 'final':
				switch($this->get_type()) {
					case 'percent':
						/**
						 * Add modifier % to the final price.
						 */
						return ($price * $this->get_modifier()) / 100;
					case 'money':
						/**
						 * Modify final price by modifier.
						 */
						return $this->get_modifier();
				}
			case 'set-final':
				/**
				 * Return the final price that should be set.
				 */
				return $this->get_modifier();
		}
		return 0;
	}

	/**
	 * Apply the modifier's modification to a price.
	 *
	 * @param Price $price
	 */
	public function apply_to($price) {
		switch($this->get_modify()) {
			case 'hour':
			case 'final':
				$price->add(
					$this->get_modification($price->get_value()),
					$this->get_name()
				);
			break;
			case 'set-hour':
				$price->set(
					$this->get_modification($price->get_value()),
					$this->get_name()
				);
			break;
			case 'total':
			case 'set-final':
				$price->set_final(
					$this->get_modification($price->get_value()),
					$this->get_name()
				);
			break;
		}
	}

	/**
	 * The unique ID of the rule whos modifier this is.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->rule->get('id');
	}

	/**
	 * The name of the rule whos modifier this is.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->rule->get_name();
	}

	/**
	 * What should be modified by this modifier.
	 *
	 * @return string
	 */
	public function get_modify() {
		return $this->rule->get('modify');
	}

	/**
	 * How should this modifier modify the price.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->rule->get('modifierType');
	}

	/**
	 * The value that is used for the modification.
	 *
	 * @return integer|float
	 */
	public function get_modifier() {
		return $this->rule->get('modifier');
	}
}
