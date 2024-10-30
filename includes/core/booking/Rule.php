<?php

namespace wsd\bw\core\booking;

/**
 * Event pricing rule.
 */
class Rule {

	/**
	 * Pricing rule from calendar settings.
	 *
	 * @var array
	 */
	protected $rule;

	/**
	 * Name of the first matching rule from a rule group.
	 *
	 * @var string
	 */
	protected $group_match_name;

	/**
	 * Assign default values to the provided rule.
	 *
	 * @param array $rule
	 */
	public function __construct($rule) {
		$this->rule = wp_parse_args($rule, [
			'name' => '',
			'modify' => 'hour',
			'modifierType' => 'percent',
			'modifier' => 0,
			'condition' => 'AND',
		]);
	}

	/**
	 * Determine if this rule applies to an event step.
	 *
	 * @param Step $step
	 * @return boolean
	 */
	public function applies_to($step) {
		if(!$this->is_enabled()) {
			return false;
		}
		if($this->get_type() === 'group') {
			return $this->group_applies_to(
				array_map(function($rule) use ($step) {
					$group_rule = new Rule($rule);
					$applies = $group_rule->applies_to($step);
					if($applies && !$this->group_match_name && $this->get('condition') === 'OR') {
						$this->set_group_match_name($group_rule->get('name'));
					}
					return $applies;
				}, $this->get_rules())
			);
		}
		return apply_filters('bw_rule_' . esc_attr($this->get_type()), false, $this, $step);
	}

	/**
	 * Determine if this rule group applies to an event step based on an array of condition check results.
	 *
	 * @param array $conditions [boolean]
	 * @return boolean
	 */
	protected function group_applies_to($conditions) {
		switch($this->get('condition')) {
			case 'OR':
				return in_array(true, $conditions);
			case 'AND':
			default:
				return !in_array(false, $conditions);
		}
	}

	/**
	 * Check if this pricing rule is currently enabled.
	 *
	 * @return boolean
	 */
	protected function is_enabled() {
		return !isset($this->rule['enabled']) || (bool)$this->rule['enabled'];
	}

	/**
	 * Get any value from the rule's array, depending on the rule it can contain different or even custom keys.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return isset($this->rule[$key]) ? $this->rule[$key] : false;
	}

	/**
	 * Get the type of the rule.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->get('type');
	}

	/**
	 * Get the name of the rule for breakdown.
	 * For an `OR` group the name of the first matching child rule is returned.
	 *
	 * @return string
	 */
	public function get_name() {
		if($this->group_match_name) {
			return $this->group_match_name;
		}
		return $this->get('name');
	}

	/**
	 * Get the rule's Modifier that can be used to modify a Price.
	 *
	 * @param Step $step
	 * @return Modifier
	 */
	public function get_modifier($step) {
		return new Modifier($this, $step);
	}

	/**
	 * Returns the child rules of a group.
	 *
	 * @return array
	 */
	protected function get_rules() {
		return array_map(function($rule) {
			/**
			 * Inherit values.
			 */
			$rule['condition'] = $this->get('condition');
			return $rule;
		}, $this->get('rules'));
	}

	/**
	 * Keep the name of the first matching rule of a group Rule.
	 * This can be used for breakdown of `OR` condition groups.
	 *
	 * @param string $name
	 */
	protected function set_group_match_name($name) {
		$this->group_match_name = $name;
	}
}
