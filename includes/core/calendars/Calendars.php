<?php

namespace wsd\bw\core\calendars;

use wsd\bw\Context;
use wsd\bw\core\admin\Admin;
use wsd\bw\config\Config;

/**
 * Calendars class.
 */
final class Calendars {

	/** @var Context */
	protected $context;

	/** @var Config */
	protected $schema;

	/** @var CalendarFactory */
	protected $calendar_factory;

	/** @var Admin */
	protected $admin;

	/**
	 * Calendars are loaded.
	 *
	 * @var boolean
	 */
	protected $loaded = false;

	/**
	 * Calendar option value.
	 *
	 * @var array
	 */
	protected $value;

	/**
	 * Calendar classes.
	 *
	 * @var array [id => Calendar]
	 */
	protected $calendars;

	/**
	 * Calendar objects.
	 *
	 * @var array [id => array]
	 */
	protected $calendar_objects;

	/**
	 * WP Option that stores calendars.
	 */
	const OPTION = 'bw_calendars';

	/**
	 * WP Option that stores calendars history.
	 */
	const HISTORY = 'bw_calendars_history';

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context, Config $schema, CalendarFactory $calendar_factory, Admin $admin) {
		$this->context = $context;
		$this->schema = $schema;
		$this->calendar_factory = $calendar_factory;
		$this->admin = $admin;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {}

	/**
	 * Loads all calendars.
	 */
	public function init() {
		$calendars = get_option(self::OPTION, []);
		$this->value = $calendars;
		$this->setup_calendars();
		$this->loaded = true;
	}

	/**
	 * Load calendars if they aren't loaded yet.
	 */
	protected function lazyload() {
		if(!$this->loaded) {
			$this->init();
		}
	}

	/**
	 * Reinitialize after calendars option has been updated.
	 */
	public function reload() {
		$this->init();
	}

	/**
	 * Load calendars into class.
	 */
	protected function setup_calendars() {
		$this->calendars = [];
		$this->calendar_objects = [];
		foreach($this->value as $id => $object) {
			$calendar = $this->calendar_factory->create($this, $id, $object);
			$this->calendars[$id] = $calendar;
			$this->calendar_objects[$id] = $calendar->get_calendar();
		}
	}

	/**
	 * @return array
	 */
	public function get_value() {
		$this->lazyload();
		return $this->calendar_objects;
	}

	/**
	 * Get calendars.
	 *
	 * @return Calendar[]
	 */
	public function get_calendars() {
		$this->lazyload();
		return $this->calendars;
	}

	/**
	 * @param string $id
	 * @return Calendar|false
	 */
	public function get_calendar($id) {
		$this->lazyload();
		return $this->calendars[$id] ?? false;
	}

	/**
	 * @param string $id
	 * @return array|false
	 */
	public function get_calendar_object($id) {
		$this->lazyload();
		return $this->calendar_objects[$id] ?? false;
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function calendar_exists($id) {
		$this->lazyload();
		return isset($this->calendars[$id]);
	}

	/**
	 * Get a calendar that is assigned to a WooCommerce product.
	 *
	 * @param int $id Product ID.
	 * @return Calendar|false
	 */
	public function get_calendar_for_product($id) {
		foreach($this->get_calendars() as $calendar) {
			if($calendar->get_product_id() === $id) {
				return $calendar;
			}
		}
		return false;
	}

	/**
	 * Save calendars value to WP option.
	 *
	 * @return array|false
	 */
	public function update_calendars($next_calendars) {
		$this->update_history();
		if(update_option(self::OPTION, $next_calendars)) {
			$this->reload();
			return $this->get_value();
		}
		return false;
	}

	/**
	 * Get option containing 10 last states of calendars.
	 *
	 * @return array
	 */
	public function get_history() {
		$history = get_option(self::HISTORY, []);
		if(!is_array($history)) {
			$history = [];
		}
		return $history;
	}

	/**
	 * Store last 10 states of calendars in a separate option as backup.
	 *
	 * @return bool
	 */
	protected function update_history() {
		$history = get_option(self::HISTORY, []);
		if(!is_array($history)) {
			$history = [];
		}
		$history[time()] = get_option(self::OPTION, []);
		while(count($history) > 10) {
			$history = array_slice($history, 1, null, true);
		}
		return update_option(self::HISTORY, $history);
	}

	/**
	 * Default calendar settings schema.
	 *
	 * @return array
	 */
	public function get_default_settings_schema() {
		return (array)$this->schema;
	}
}
