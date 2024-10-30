<?php

namespace wsd\bw\core\booking;

/**
 * Pricing rules of a calendar.
 */
class Ruleset {

	/**
	 * Calendar rules as Rule classes.
	 *
	 * @var array[Rule]
	 */
	protected $rules;

	/**
	 * Load pricing rules from calendar settings.
	 *
	 * @param array $rules
	 */
	public function __construct($rules) {
		$this->rules = [];
		foreach($rules as $rule) {
			$this->rules[] = new Rule($rule);
		}
	}

	/**
	 * Returns modifiers of each rule that applies to the provided event step.
	 *
	 * @param Step $step
	 * @return array[Modifier]
	 */
	public function get_modifiers($step) {
		$modifiers = [];
		foreach($this->rules as $rule) {
			if($rule->applies_to($step)) {
				$modifiers[] = $rule->get_modifier($step);
			}
		}
		return $modifiers;
	}
}
