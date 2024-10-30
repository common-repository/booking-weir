<?php

namespace wsd\bw\core;

use wsd\bw\Context;
use WP_Error;

/**
 * Sanitizer class validates and sanitizes values.
 */
final class Sanitizer {

	/** @var Context */
	protected $context;

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {}

	/**
	 * Validate that calendars conform to format.
	 *
	 * @param mixed $calendars
	 * @return bool|WP_Error
	 */
	public function validate_calendars($calendars) {
		if(!is_array($calendars)) {
			return false;
		}
		if(count($calendars) < 1) {
			return true;
		}
		$allowed_keys = [
			'name',
			'prices',
			'services',
			'extras',
			'fields',
			'paymentTypes',
			'paymentMethods',
			'paymentMethodData',
			'settings',
			'ver',
		];
		foreach($calendars as $id => $calendar) {
			if(is_numeric($id)) {
				return new WP_Error(
					'bw_invalid_value',
					sprintf(
						__('Invalid calendar ID: "%s".', 'booking-weir'),
						esc_html($id)
					)
				);
			}
			foreach(array_keys($calendar) as $key) {
				if(!in_array($key, $allowed_keys)) {
					return new WP_Error(
						'bw_invalid_value',
						sprintf(
							__('Invalid calendar key: "%s".', 'booking-weir'),
							esc_html($key)
						)
					);
				}
			}
		}
		return true;
	}

	/**
	 * Ensure date conforms to the `Y-m-d\TH:i` format.
	 *
	 * @param mixed $datetime
	 * @return true|WP_Error
	 */
	public function validate_datetime($datetime) {
		if(!is_string($datetime) || strlen($datetime) !== 16 || in_array(false, [
			strpos($datetime, '-') === 4,
			strpos($datetime, '-', 5) === 7,
			strpos($datetime, 'T') === 10,
			strpos($datetime, ':') === 13,
		])) {
			return new WP_Error(
				'bw_invalid_value',
				sprintf(
					__('Invalid datetime: "%s".', 'booking-weir'),
					is_scalar($datetime) ? esc_html($datetime) : gettype($datetime)
				)
			);
		}
		return true;
	}

	/**
	 * Numbers, `-`, `T` and `:` are allowed.
	 *
	 * @param string $datetime
	 * @return string
	 */
	public function sanitize_datetime($datetime) {
		return preg_replace('/[^0-9-T:]/', '', $datetime);
	}

	/**
	 * Ensure date conforms to the `Y-m-d\TH:i` format.
	 *
	 * @param mixed $date
	 * @return true|WP_Error
	 */
	public function validate_date($date) {
		if(!is_string($date) || strlen($date) !== 10 || in_array(false, [
			strpos($date, '-') === 4,
			strpos($date, '-', 5) === 7,
		])) {
			return new WP_Error(
				'bw_invalid_value',
				sprintf(
					__('Invalid date: "%s".', 'booking-weir'),
					is_scalar($date) ? esc_html($date) : gettype($date)
				)
			);
		}
		return true;
	}

	/**
	 * Numbers and `-` are allowed.
	 *
	 * @param string $date
	 * @return string
	 */
	public function sanitize_date($date) {
		return preg_replace('/[^0-9-]/', '', $date);
	}

	/**
	 * Ensure value can be used as price.
	 *
	 * @param float $price
	 * @return float|WP_Error
	 */
	public function validate_price($price) {
		if($price < 0 || !filter_var($price, FILTER_VALIDATE_FLOAT)) {
			return new WP_Error(
				'bw_invalid_value',
				sprintf(
					__('Invalid price: "%s".', 'booking-weir'),
					esc_html((string)$price)
				)
			);
		}
		return $price;
	}

	/**
	 * Lowercase alphanumeric characters, dashes, and underscores are allowed.
	 *
	 * @see https://developer.wordpress.org/reference/functions/sanitize_key/
	 * @param mixed $value
	 * @return string
	 */
	public function sanitize_key($value) {
		return sanitize_key($value);
	}

	/**
	 * Validate that given calendar exists.
	 *
	 * @param string $calendar_id
	 * @return true|WP_Error
	 */
	public function validate_calendar_id($calendar_id) {
		if(!$calendar_id) {
			return true;
		}
		if(!$this->context->get('calendars')->calendar_exists($calendar_id)) {
			return new WP_Error('bw_invalid_value', __('Calendar not found.', 'booking-weir'));
		}
		return true;
	}

	/**
	 * Validate that given event exists.
	 *
	 * @param string $id Event ID.
	 * @return bool|WP_Error
	 */
	public function validate_event_id($id) {
		$id = absint($id);
		if(!$id) {
			return false;
		}
		$event = $this->context->get('event-factory')->create($id);
		if(!$event->exists()) {
			return new WP_Error('bw_invalid_value', __('Event not found.', 'booking-weir'));
		}
		return true;
	}

	/**
	 * Alphanumeric characters, dashes, and underscores are allowed.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function sanitize_id($value) {
		return preg_replace('/[^A-Za-z0-9_\-]/', '', $value);
	}

	/**
	 * Changes a boolean-like value into the proper boolean value.
	 *
	 * @param bool|string|int $value
	 * @return bool
	 */
	public function sanitize_boolean($value) {
		if(is_string($value)) {
			$value = strtolower($value);
			if(in_array($value, ['false', '0', 'no'], true)) {
				$value = false;
			}
		}
		return (bool)$value;
	}

	/**
	 * Sanitize string as a text input.
	 *
	 * @see https://developer.wordpress.org/reference/functions/sanitize_text_field/
	 * @param string $value
	 * @return string
	 */
	public function sanitize_string($value) {
		return (string)sanitize_text_field($value);
	}

	/**
	 * Sanitize textarea.
	 *
	 * @see https://developer.wordpress.org/reference/functions/sanitize_textarea_field/
	 * @param string $value
	 * @return string
	 */
	public function sanitize_textarea($value) {
		return (string)sanitize_textarea_field($value);
	}

	/**
	 * Sanitize integer.
	 *
	 * @param mixed $value
	 * @return int
	 */
	public function sanitize_integer($value) {
		return (int)$value;
	}

	/**
	 * Sanitize float.
	 *
	 * @param mixed $value
	 * @return float
	 */
	public function sanitize_float($value) {
		return (float)$value;
	}

	/**
	 * Validate e-mail.
	 *
	 * @param string $value
	 * @return true|WP_Error
	 */
	public function validate_email($value) {
		if($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
			return new WP_Error('bw_invalid_value', sprintf(
				__('E-mail "%s" is not valid.', 'booking-weir'),
				esc_html($value)
			));
		}
		return true;
	}

	/**
	 * Sanitize e-mail.
	 *
	 * @see https://developer.wordpress.org/reference/functions/sanitize_email/
	 * @param string $value
	 * @return string
	 */
	public function sanitize_email($value) {
		return sanitize_email($value);
	}

	/**
	 * Validate file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/validate_file/
	 * @param string $value
	 * @return true|WP_Error
	 */
	public function validate_file($value) {
		if(validate_file($value) !== 0) {
			return new WP_Error('bw_invalid_value', sprintf(
				__('File "%s" is not valid.', 'booking-weir'),
				esc_html($value)
			));
		}
		return true;
	}

	/**
	 * Validate event type.
	 *
	 * @param string $value
	 * @return true|WP_Error
	 */
	public function validate_event_type($value) {
		$types = array_map(function($event_type) {
			return $event_type['value'];
		}, $this->context->get('event-types')->get());

		if(!in_array($value, $types)) {
			return new WP_Error('bw_invalid_value', sprintf(
				__('Invalid event type: "%s".', 'booking-weir'),
				esc_html($value)
			));
		}

		return true;
	}

	/**
	 * Sanitize event type.
	 *
	 * @param string $value
	 * @return string
	 */
	public function sanitize_event_type($value) {
		$types = array_map(function($event_type) {
			return $event_type['value'];
		}, $this->context->get('event-types')->get());

		if(!in_array($value, $types)) {
			$value = '';
		}

		return $value;
	}

	/**
	 * Validate booking status.
	 *
	 * @param string $value
	 * @return true|WP_Error
	 */
	public function validate_booking_status($value) {
		if(!$value) {
			return true;
		}

		$statuses = array_map(function($event_type) {
			return $event_type['value'];
		}, $this->context->get('booking-statuses')->get());

		if(!in_array($value, $statuses)) {
			return new WP_Error('bw_invalid_value', sprintf(
				__('Invalid booking status: "%s".', 'booking-weir'),
				esc_html($value)
			));
		}

		return true;
	}

	/**
	 * Sanitize booking status.
	 *
	 * @param string $value
	 * @return string
	 */
	public function sanitize_booking_status($value) {
		$statuses = array_map(function($event_type) {
			return $event_type['value'];
		}, $this->context->get('booking-statuses')->get());

		if(!in_array($value, $statuses)) {
			$value = '';
		}

		return $value;
	}

	/**
	 * @param array $value
	 * @return true|WP_Error
	 */
	public function validate_extras($value) {
		if(!is_array($value)) {
			return new WP_Error('bw_invalid_value', __('Invalid extras.', 'booking-weir'));
		}
		foreach($value as $id => $extra_value) {
			if(gettype($id) !== 'string') {
				return new WP_Error('bw_invalid_value', sprintf(__('Invalid extra ID type: "%s".', 'booking-weir'), gettype($id)));
			}
			if(!in_array(gettype($extra_value), ['boolean', 'integer', 'double'])) {
				return new WP_Error('bw_invalid_value', sprintf(__('Invalid extra value type: "%s".', 'booking-weir'), gettype($extra_value)));
			}
		}
		return true;
	}

	/**
	 * @param array $value
	 * @return array
	 */
	public function sanitize_extras($value) {
		$sanitized_value = [];
		foreach($value as $id => $extra_value) {
			$sanitized_extra_value = '';
			switch(gettype($extra_value)) {
				case 'boolean':
					$sanitized_extra_value = $this->sanitize_boolean($extra_value);
				break;
				case 'integer':
					$sanitized_extra_value = $this->sanitize_integer($extra_value);
				break;
				case 'double':
					$sanitized_extra_value = $this->sanitize_float($extra_value);
				break;
			}
			$sanitized_value[$this->sanitize_id($id)] = $sanitized_extra_value;
		}
		return $sanitized_value;
	}

	/**
	 * @param array $value
	 * @return true|WP_Error
	 */
	public function validate_fields($value) {
		if(!is_array($value)) {
			return new WP_Error('bw_invalid_value', __('Invalid fields.', 'booking-weir'));
		}
		foreach($value as $id => $field_value) {
			if(gettype($id) !== 'string') {
				return new WP_Error('bw_invalid_value', sprintf(__('Invalid field ID type: "%s".', 'booking-weir'), gettype($id)));
			}
			if(!in_array(gettype($field_value), ['integer', 'boolean', 'string'])) {
				return new WP_Error('bw_invalid_value', sprintf(__('Invalid field value type: "%s".', 'booking-weir'), gettype($field_value)));
			}
		}
		return true;
	}

	/**
	 * @param array $value
	 * @return array
	 */
	public function sanitize_fields($value) {
		$sanitized_value = [];
		foreach($value as $id => $field_value) {
			$sanitized_extra_value = '';
			switch(gettype($field_value)) {
				case 'boolean':
					$sanitized_extra_value = $this->sanitize_boolean($field_value);
				break;
				case 'integer':
					$sanitized_extra_value = $this->sanitize_integer($field_value);
				break;
				default:
					$sanitized_extra_value = $this->sanitize_textarea($field_value);
			}
			$sanitized_value[$this->sanitize_id($id)] = $sanitized_extra_value;
		}
		return (array)$sanitized_value;
	}

	/**
	 * @param array $value
	 * @return true|WP_Error
	 */
	public function validate_breakdown($value) {
		if(!is_array($value)) {
			return new WP_Error('bw_invalid_value', __('Invalid breakdown.', 'booking-weir'));
		}
		foreach($value as $title => $amount) {
			if(gettype($title) !== 'string') {
				return new WP_Error('bw_invalid_value', sprintf(__('Invalid breakdown title type: "%s".', 'booking-weir'), gettype($title)));
			}
			if(!is_numeric($amount)) {
				return new WP_Error('bw_invalid_value', sprintf(__('Invalid value for breakdown: "%s".', 'booking-weir'), esc_html($title)));
			}
		}
		return true;
	}

	/**
	 * @param array $value
	 * @return array
	 */
	public function sanitize_breakdown($value) {
		$sanitized_value = [];
		foreach($value as $title => $amount) {
			$sanitized_value[$this->sanitize_string($title)] = $this->sanitize_float($amount);
		}
		return (array)$sanitized_value;
	}

	/**
	 * @param array $value
	 * @return true|WP_Error
	 */
	public function validate_bookable_event_settings($value) {
		if(!is_array($value)) {
			return new WP_Error('bw_invalid_value', __('Invalid booking value.', 'booking-weir'));
		}
		$allowed_keys = ['price', 'limit'];
		foreach($value as $key => $value) {
			if(!in_array($key, $allowed_keys)) {
				return new WP_Error('bw_invalid_value', __('Repeater contains invalid keys.', 'booking-weir'));
			}
			switch($key) {
				case 'price':
				case 'limit':
					if(!is_numeric($value)) {
						return new WP_Error('bw_invalid_value', sprintf(__('Invalid booking value for: "%s".', 'booking-weir'), esc_html($key)));
					}
				break;
			}
		}
		return true;
	}

	/**
	 * @param array $value
	 * @return array
	 */
	public function sanitize_bookable_event_settings($value) {
		$sanitized_value = [];
		foreach($value as $key => $value) {
			switch($key) {
				case 'price':
				case 'limit':
					$sanitized_value[$this->sanitize_string($key)] = $this->sanitize_integer($value);
				break;
				default:
					$sanitized_value[$this->sanitize_string($key)] = $this->sanitize_string($value);
			}
		}
		return $sanitized_value;
	}

	/**
	 * @param array $value
	 * @return true|WP_Error
	 */
	public function validate_repeater($value) {
		if(!is_array($value)) {
			return new WP_Error('bw_invalid_value', __('Invalid repeater value.', 'booking-weir'));
		}
		$allowed_keys = ['type', 'days', 'dates', 'interval', 'units', 'limit', 'until', 'preventOverlap', 'ignore'];
		foreach($value as $key => $value) {
			if(!in_array($key, $allowed_keys)) {
				return new WP_Error('bw_invalid_value', __('Repeater contains invalid keys.', 'booking-weir'));
			}
			switch($key) {
				case 'days':
					if(!is_array($value)) {
						return new WP_Error('bw_invalid_value', sprintf(__('Invalid repeater value for: "%s".', 'booking-weir'), esc_html($key)));
					}
					foreach($value as $day) {
						if(!in_array($day, [
							'Monday',
							'Tuesday',
							'Wednesday',
							'Thursday',
							'Friday',
							'Saturday',
							'Sunday',
						])) {
							return new WP_Error('bw_invalid_value', __('Invalid day value for repeater.', 'booking-weir'));
						}
					}
				break;
				case 'dates':
				case 'ignore':
					if(!is_array($value)) {
						return new WP_Error('bw_invalid_value', sprintf(__('Invalid repeater value for: "%s".', 'booking-weir'), esc_html($key)));
					}
					foreach($value as $val) {
						$is_valid = $this->validate_date($val);
						if(is_wp_error($is_valid)) {
							return $is_valid;
						}
					}
				break;
				case 'interval':
				case 'limit':
					if(!is_numeric($value)) {
						return new WP_Error('bw_invalid_value', sprintf(__('Invalid repeater value for: "%s".', 'booking-weir'), esc_html($key)));
					}
				break;
				case 'preventOverlap':
					if(!is_bool($value)) {
						return new WP_Error('bw_invalid_value', sprintf(__('Invalid repeater value for: "%s".', 'booking-weir'), esc_html($key)));
					}
				break;
				default:
					if(!is_string($value)) {
						return new WP_Error('bw_invalid_value', sprintf(__('Invalid repeater value for: "%s".', 'booking-weir'), esc_html($key)));
					}
			}
		}
		return true;
	}

	/**
	 * @param array $value
	 * @return array
	 */
	public function sanitize_repeater($value) {
		$sanitized_value = [];
		foreach($value as $key => $value) {
			switch($key) {
				case 'days':
					$values = [];
					foreach($value as $val) {
						$values[] = $this->sanitize_string($val);
					}
					$sanitized_value[$this->sanitize_string($key)] = $values;
				break;
				case 'dates':
				case 'ignore':
					$values = [];
					foreach($value as $val) {
						$values[] = $this->sanitize_date($val);
					}
					$sanitized_value[$this->sanitize_string($key)] = array_filter($values);
				break;
				case 'interval':
				case 'limit':
					$sanitized_value[$this->sanitize_string($key)] = $this->sanitize_integer($value);
				break;
				case 'preventOverlap':
					$sanitized_value[$this->sanitize_string($key)] = $this->sanitize_boolean($value);
				break;
				default:
					$sanitized_value[$this->sanitize_string($key)] = $this->sanitize_string($value);
			}
		}
		return $sanitized_value;
	}
}
