<?php

namespace wsd\bw\core\booking;

/**
 * Contains price value and allows to modify it and keep track of the modifications in a breakdown.
 */
class Price {

	/**
	 * The current value of the price.
	 *
	 * @var float
	 */
	protected $value;

	/**
	 * Information about price changes.
	 *
	 * @var array [(string)name => (float|integer)value]
	 */
	protected $breakdown;

	/**
	 * Modifiers to be applied before combining with another price.
	 *
	 * @var array [(string)modifier_id => (Modifier)modifier]
	 */
	protected $total_modifiers;

	/**
	 * Modifiers to be applied at the very end of price calculation.
	 *
	 * @var array [(string)modifier_id => (Modifier)modifier]
	 */
	protected $final_modifiers;

	/**
	 * Initialize price.
	 *
	 * @param integer|float $value
	 * @param array $breakdown [(string)name => (float|integer)value]
	 */
	public function __construct($value = 0, $breakdown = []) {
		$this->value = (float)$value;
		$this->breakdown = (array)$breakdown;
		$this->total_modifiers = [];
		$this->final_modifiers = [];
	}

	/**
	 * Returns the value of the Price.
	 *
	 * @return float
	 */
	public function get_value() {
		return (float)$this->value;
	}

	/**
	 * Returns the breakdown (changes applied to the price).
	 *
	 * @return array
	 */
	public function get_breakdown() {
		return $this->breakdown;
	}

	/**
	 * Add value to the price.
	 *
	 * @param integer|float $value
	 * @param string $name Supply a name to reflect the change in the breakdown.
	 */
	public function add($value, $name = '') {
		$value = (float)$value;
		$this->value += $value;
		$this->add_breakdown($name, $value);
	}

	/**
	 * Set the price value, resetting the breakdown.
	 *
	 * @param integer|float $value
	 * @param string $name Supply a name to reflect the change in the breakdown.
	 */
	public function set($value, $name = '') {
		$value = (float)$value;
		$diff = $value - $this->get_value();
		$this->value = $value;
		$this->reset_breakdown();
		$this->add_breakdown($name, $diff);
	}

	/**
	 * Set the final price value, resetting the breakdown and clearing other final modifiers.
	 *
	 * @param integer|float $value
	 * @param string $name Supply a name to reflect the change in the breakdown.
	 */
	public function set_final($value, $name = '') {
		$this->reset_final_modifiers();
		$this->set($value, $name);
	}

	/**
	 * Add value, breakdown and modifiers to the price from another price.
	 *
	 * @param Price $price
	 */
	public function add_price($price) {
		$this->add($price->get_value());
		$this->add_breakdowns($price->get_breakdown());
		$this->total_modifiers = array_merge($this->total_modifiers, $price->get_total_modifiers());
		$this->final_modifiers = array_merge($this->final_modifiers, $price->get_final_modifiers());
	}

	/**
	 * Modify the price using a modifier.
	 *
	 * @param Modifier $modifier
	 */
	public function modify($modifier) {
		/**
		 * Some modifiers should be applied immediately, some should be added to queues for later.
		 */
		switch($modifier->get_modify()) {
			case 'hour':
			case 'set-hour':
				$modifier->apply_to($this);
			break;
			case 'total':
				$this->total_modifiers[$modifier->get_id()] = $modifier;
			break;
			case 'final':
			case 'set-final':
				$this->final_modifiers[$modifier->get_id()] = $modifier;
			break;
		}
	}

	/**
	 * Get the total modifiers that the price currently has pending.
	 *
	 * @return array
	 */
	protected function get_total_modifiers() {
		return $this->total_modifiers;
	}

	/**
	 * Apply and remove the total modifiers that the price has accumulated.
	 *
	 * Note: should be called manually when adding up prices.
	 * @see PriceCalculator->calculate_total_price()
	 */
	public function apply_total_modifiers() {
		foreach($this->get_total_modifiers() as $id => $modifier) {
			$modifier->apply_to($this);
			unset($this->total_modifiers[$id]);
		}
	}

	/**
	 * Get the final modifiers that the price currently has pending.
	 *
	 * @return array
	 */
	protected function get_final_modifiers() {
		return $this->final_modifiers;
	}

	/**
	 * Apply and remove the final modifiers that the price has accumulated.
	 *
	 * Note: should be called manually when adding up prices.
	 * @see PriceCalculator->calculate_total_price()
	 *
	 * @return Price
	 */
	public function apply_final_modifiers() {
		foreach($this->get_final_modifiers() as $id => $modifier) {
			$modifier->apply_to($this);
			unset($this->final_modifiers[$id]);
		}
		return $this;
	}

	/**
	 * Clear final modifiers.
	 */
	protected function reset_final_modifiers() {
		$this->final_modifiers = [];
	}

	/**
	 * Add information about price changes to the breakdown.
	 *
	 * @param string $name Pricing rule name/etc.
	 * @param float|integer $value Changed amount.
	 */
	protected function add_breakdown($name, $value) {
		if(!$name) {
			return;
		}
		$value = round($value, 2);
		if(isset($this->breakdown[$name])) {
			$this->breakdown[$name] += $value;
		} else {
			$this->breakdown[$name] = $value;
		}
	}

	/**
	 * Add breakdowns to this price from an array of breakdowns.
	 *
	 * @param array $breakdowns [(string)name => (float|integer)value]
	 */
	protected function add_breakdowns($breakdowns) {
		foreach($breakdowns as $name => $value) {
			$this->add_breakdown($name, $value);
		}
	}

	/**
	 * Clear all breakdowns.
	 */
	protected function reset_breakdown() {
		$this->breakdown = [];
	}
}
