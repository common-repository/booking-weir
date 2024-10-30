<?php

namespace wsd\bw\core\events;

use wsd\bw\Context;
use wsd\bw\core\booking\Booking;
use wsd\bw\core\calendars\Calendar;
use wsd\bw\util\datetime;
use WP_Query;
use WP_Post;
use WC_Order_Item_Product;
use NumberFormatter;

/**
 * Event class.
 */
class Event {

	/** @var Context */
	protected $context;

	/**
	 * Event ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * GET variable name for viewing an event on the front end.
	 */
	const VIEW = 'bw-event';

	/**
	 * GET variable name for specifying viewed event's start time.
	 */
	const START = 'bw-start';

	/**
	 * GET variable name for specifying viewed event's action.
	 */
	const ACTION = 'bw-action';

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context, $id) {
		$this->context = $context;
		$this->id = (int)$id;
	}

	/**
	 * Event ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Check if event exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return get_post_type($this->get_id()) === $this->context->get('event-post-type')::SLUG;
	}

	/**
	 * Get event type.
	 *
	 * @return string
	 */
	public function get_type() {
		$type = get_post_meta($this->get_id(), 'bw_type', true);
		return $type ?: 'unavailable';
	}

	/**
	 * Get event type display name.
	 *
	 * @return string
	 */
	public function get_type_name() {
		$type = $this->get_type();
		foreach($this->context->get('event-types') as $event_type) {
			if($event_type['value'] === $type) {
				return $event_type['text'];
			}
		}
		return $type;
	}

	/**
	 * Get event title.
	 *
	 * @return string
	 */
	public function get_title() {
		return get_the_title($this->get_id());
	}

	/**
	 * Get event title for front end.
	 *
	 * @return string
	 */
	public function get_public_title() {
		switch($this->get_type()) {
			case 'default':
				return $this->get_title();
			case 'booking':
				if($this->get_status() === 'cart') {
					return _x('Reserved', 'Booked event in WC cart, checkout not yet completed', 'booking-weir');
				}
				return _x('Booked', 'Booked event public title', 'booking-weir');
			case 'slot':
				$default_title = _x('Slot', 'Slot event public title', 'booking-weir');
				$custom_title = $this->get_title();
				if($default_title !== $custom_title) {
					return $custom_title;
				}
				return $default_title;
			default:
				return _x('Unavailable', 'Unavailable event public title', 'booking-weir');
		}
	}

	/**
	 * Get event content, stored in WordPress excerpt field.
	 *
	 * @param string $start Repeat events should specify their start time.
	 * @param string $end Repeat events should specify their end time.
	 * @return string
	 */
	public function get_content($start = '', $end = '') {
		if(!empty($start) && !empty($end)) {
			$repeat = $this->context->get('event-factory')->repeat($this, $start, $end);
			return $repeat->get_content();
		}
		return strtr(
			apply_filters('the_content', get_the_excerpt($this->get_id())),
			$this->get_template_strings()
		);
	}

	/**
	 * @return string|false
	 */
	public function get_service_id() {
		return get_post_meta($this->get_id(), 'bw_service_id', true);
	}

	/**
	 * @return array|false
	 */
	public function get_service() {
		if($id = $this->get_service_id()) {
			return $this->get_calendar()->get_service($id);
		}
		return false;
	}

	/**
	 * Event is a booking for a service.
	 *
	 * @return bool
	 */
	public function is_service() {
		return $this->get_service() !== false;
	}

	/**
	 * @return string
	 */
	public function get_service_name() {
		$service = $this->get_service();
		if(!is_array($service) || !isset($service['name'])) {
			return '';
		}
		return $service['name'];
	}

	/**
	 * @return string|false
	 */
	public function get_billing_id() {
		return get_post_meta($this->get_id(), 'bw_billing_id', true);
	}

	/**
	 * @return string|false
	 */
	public function get_billing_key() {
		return get_post_meta($this->get_id(), 'bw_billing_key', true);
	}

	/**
	 * @return int
	 */
	public function get_order_id() {
		return (int)get_post_meta($this->get_id(), 'bw_order_id', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_order_id($order_id) {
		return update_post_meta($this->get_id(), 'bw_order_id', $order_id);
	}

	/**
	 * @return string|false
	 */
	public function get_transaction_id() {
		return get_post_meta($this->get_id(), 'bw_transaction_id', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_transaction_id($transaction_id) {
		return update_post_meta($this->get_id(), 'bw_transaction_id', $transaction_id);
	}

	/**
	 * @return int|bool
	 */
	public function set_return_url($return_url) {
		return update_post_meta($this->get_id(), 'bw_return_url', $return_url);
	}

	/**
	 * @return string
	 */
	public function get_return_url() {
		$url = get_post_meta($this->get_id(), 'bw_return_url', true);
		return esc_url($url ?: home_url('/'));
	}

	/**
	 * @return string|false
	 */
	public function get_calendar_id() {
		return get_post_meta($this->get_id(), 'bw_calendar_id', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_calendar_id($id) {
		return update_post_meta($this->get_id(), 'bw_calendar_id', $id);
	}

	/**
	 * @return Calendar|false
	 */
	public function get_calendar() {
		return $this->context->get('calendars')->get_calendar($this->get_calendar_id());
	}

	/**
	 * @return string|false
	 */
	public function get_first_name() {
		return get_post_meta($this->get_id(), 'bw_first_name', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_first_name($first_name = '') {
		return update_post_meta($this->get_id(), 'bw_first_name', $first_name);
	}

	/**
	 * @return string|false
	 */
	public function get_last_name() {
		return get_post_meta($this->get_id(), 'bw_last_name', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_last_name($last_name = '') {
		return update_post_meta($this->get_id(), 'bw_last_name', $last_name);
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return trim($this->get_first_name() . ' ' . $this->get_last_name());
	}

	/**
	 * @return string|false
	 */
	public function get_email() {
		return get_post_meta($this->get_id(), 'bw_email', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_email($email = '') {
		return update_post_meta($this->get_id(), 'bw_email', $this->context->get('sanitizer')->sanitize_email($email));
	}

	/**
	 * @return string|false
	 */
	public function get_phone() {
		return get_post_meta($this->get_id(), 'bw_phone', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_phone($phone = '') {
		return update_post_meta($this->get_id(), 'bw_phone', $phone);
	}

	/**
	 * @return string|false
	 */
	public function get_userip() {
		return get_post_meta($this->get_id(), 'bw_userip', true);
	}

	/**
	 * @return string|false
	 */
	public function get_version() {
		return get_post_meta($this->get_id(), 'bw_version', true);
	}

	/**
	 * @return bool
	 */
	public function get_terms() {
		return (bool)get_post_meta($this->get_id(), 'bw_terms', true);
	}

	/**
	 * @return string|false
	 */
	public function get_start() {
		return get_post_meta($this->get_id(), 'bw_start', true);
	}

	/**
	 * @return string|false
	 */
	public function get_end() {
		return get_post_meta($this->get_id(), 'bw_end', true);
	}

	/**
	 * Is this event bookable.
	 *
	 * @return bool
	 */
	public function is_bookable() {
		return get_post_meta($this->get_id(), 'bw_bookable', true) === '1';
	}

	/**
	 * Data used when this event is bookable.
	 *
	 * @return array
	 */
	public function get_booking_data() {
		return wp_parse_args(get_post_meta($this->get_id(), 'bw_booking', true), [
			'price' => 0,
			'limit' => 0,
		]);
	}

	/**
	 * How many bookings does this bookable event have.
	 *
	 * @return int
	 */
	public function get_bookable_event_children_count() {
		$children_query = new WP_Query([
			'post_type' => $this->context->get('event-post-type')::SLUG,
			'posts_per_page' => -1,
			'meta_key' => 'bw_bookable_event_id',
			'meta_value' => $this->get_id(),
			'post_status' => 'publish',
		]);
		return $children_query->post_count;
	}

	/**
	 * Is bookable event at capacity.
	 *
	 * @return boolean
	 */
	public function is_at_capacity() {
		$data = $this->get_booking_data();
		if(!is_numeric($data['limit'])) {
			// Prevent booking due to logic error.
			return true;
		}
		$limit = (int)$data['limit'];
		if($limit < 1) {
			return false;
		}
		return $this->get_bookable_event_children_count() >= $limit;
	}

	/**
	 * Get the parent event's ID when this is a booking for a bookable event.
	 *
	 * @return int
	 */
	public function get_bookable_event_id() {
		$id = get_post_meta($this->get_id(), 'bw_bookable_event_id', true);
		return is_numeric($id) ? (int)$id : 0;
	}

	/**
	 * Is this event a child of a bookable event.
	 *
	 * @return bool
	 */
	public function is_child_of_bookable_event() {
		return $this->get_bookable_event_id() > 0;
	}

	/**
	 * Get the parent event's title when this is a booking for a bookable event.
	 *
	 * @return string
	 */
	public function get_bookable_event_title() {
		$event = $this->context->get('event-factory')->create($this->get_bookable_event_id());
		if(!$event->exists()) {
			return __('Event', 'booking-weir');
		}
		return $event->get_title();
	}

	/**
	 * Get the ID of a slot that this event was booked on.
	 *
	 * @return int
	 */
	public function get_slot_id() {
		$id = get_post_meta($this->get_id(), 'bw_slot_id', true);
		return is_numeric($id) ? (int)$id : 0;
	}

	/**
	 * Is this event booked on a slot.
	 *
	 * @return bool
	 */
	public function is_in_slot() {
		return $this->get_slot_id() > 0;
	}

	/**
	 * Get the title of the slot that the event is booked on.
	 *
	 * @return string
	 */
	public function get_slot_title() {
		$event = $this->context->get('event-factory')->create($this->get_slot_id());
		if(!$event->exists()) {
			return _x('Slot', 'Slot event public title', 'booking-weir');
		}
		return $event->get_public_title();
	}

	/**
	 * Is the event in a named slot.
	 *
	 * @return bool
	 */
	public function is_in_named_slot() {
		return $this->is_in_slot() && $this->get_slot_title() !== _x('Slot', 'Slot event public title', 'booking-weir');
	}

	/**
	 * @return bool
	 */
	public function repeats() {
		return get_post_meta($this->get_id(), 'bw_repeat', true) === '1';
	}

	/**
	 * @return array
	 */
	public function get_repeater() {
		return wp_parse_args(get_post_meta($this->get_id(), 'bw_repeater', true), [
			'type' => 'interval',
			'days' => [],
			'dates' => [],
			'interval' => 1,
			'units' => 'days',
			'limit' => 0,
			'until' => '',
			'preventOverlap' => false,
			'ignore' => [],
		]);
	}

	/**
	 * Overridden by `RepeatEvent` class to indicate it's a copy.
	 *
	 * @return boolean
	 */
	public function is_repeat() {
		return false;
	}

	/**
	 * Set event UTC offset.
	 *
	 * @param int $offset
	 * @return boolean
	 */
	public function set_utc_offset($offset) {
		return update_post_meta($this->get_id(), 'bw_utc_offset', (int)$offset);
	}

	/**
	 * UTC offset of the calendar at the time of creating the event.
	 * Since the start time string does not contain a timezone and
	 * the start timestamp is in UTC the offset needs to be removed
	 * from the timestamp to get an accurate time server-side.
	 *
	 * @return int UTC offset.
	 */
	public function get_utc_offset() {
		return (int)get_post_meta($this->get_id(), 'bw_utc_offset', true);
	}

	/**
	 * @return int UTC offset in seconds.
	 */
	public function get_utc_offset_seconds() {
		return $this->get_utc_offset() * 60;
	}

	/**
	 * Event start timestamp based on the event start string `Y-m-d\TH:i`.
	 * Due to potential differences in the calendar and server timezones
	 * it is not an accurate means to determine when the event starts.
	 * For the real timestamp UTC offset should be accounted for as well.
	 *
	 * @see $this->starts_in()
	 * @return int
	 */
	public function get_start_timestamp() {
		return (int)get_post_meta($this->get_id(), 'bw_start_timestamp', true);
	}

	/**
	 * Event end timestamp.
	 *
	 * @see $this->get_start_timestamp()
	 * @return int
	 */
	public function get_end_timestamp() {
		return (int)get_post_meta($this->get_id(), 'bw_end_timestamp', true);
	}

	/**
	 * Event creation timestamp.
	 *
	 * Should be same as `get_post_time('U', false, $this->get_id())`.
	 *
	 * @see $this->get_start_timestamp()
	 * @return int
	 */
	public function get_created_timestamp() {
		return (int)get_post_meta($this->get_id(), 'bw_created_timestamp', true);
	}

	/**
	 * Amount of seconds until event start time.
	 *
	 * @return int
	 */
	public function starts_in() {
		return $this->get_start_timestamp() - $this->get_utc_offset_seconds() - datetime\utcstrtotime('now');
	}

	/**
	 * @return string
	 */
	public function starts_in_formatted() {
		$starts_in = $this->starts_in();
		if($starts_in <= 0) {
			return __('Started', 'booking-weir');
		}
		return human_time_diff(time() - $starts_in);
	}

	/**
	 * Amount of seconds until the reminder is sent.
	 *
	 * @return int|string
	 */
	public function reminder_in() {
		/**
		 * Filter for custom reminder logic.
		 */
		if($value = apply_filters('bw_event_reminder_in', false, $this)) {
			return is_numeric($value) ? (int)$value : $this->context->get('sanitizer')->sanitize_key($value);
		}
		if($this->get_type() !== 'booking') {
			return 'not-applicable';
		}
		if(get_post_status($this->get_id()) !== 'publish') {
			return 'event-not-public';
		}
		if($this->get_status() === 'awaiting') {
			return 'event-not-confirmed';
		}
		if($this->get_reminder_email_sent()) {
			return 'sent';
		}
		if(!$calendar = $this->get_calendar()) {
			return 'reminders-not-enabled';
		}
		$offset = $calendar->get_reminder_email_offset();
		if($offset < 1) {
			return 'reminders-not-enabled';
		}
		$starts_in = $this->starts_in();
		if($starts_in <= 0) {
			return 'event-already-started';
		}
		$created = $this->get_created_timestamp() - $this->get_utc_offset_seconds();
		if($this->get_start_timestamp() - $created <= $offset) {
			return 'event-created-too-late';
		}
		$remind_in = $starts_in - $offset;
		if($remind_in <= 0) {
			return 'pending';
		}
		return $remind_in;
	}

	/**
	 * Format event start time according to WordPress date settings.
	 *
	 * @see WordPress admin dashboard -> Settings -> General -> Date Format/Time Format
	 * @return string
	 */
	public function get_start_formatted() {
		return datetime\wpdatetime($this->get_start());
	}

	/**
	 * Format event end time according to WordPress date settings.
	 *
	 * @see WordPress admin dashboard -> Settings -> General -> Date Format/Time Format
	 * @return string
	 */
	public function get_end_formatted() {
		return datetime\wpdatetime($this->get_end());
	}

	/**
	 * Format event start and end time according to WordPress date settings.
	 *
	 * @see WordPress admin dashboard -> Settings -> General -> Date Format/Time Format
	 * @return string
	 */
	public function get_date_formatted() {
		$start = strtotime($this->get_start());
		$end = strtotime($this->get_end());

		$date = date_i18n(get_option('date_format', 'd.m.Y'), $start);
		$start_time = date_i18n(get_option('time_format', 'H:i'), $start);
		$end_time = date_i18n(get_option('time_format', 'H:i'), $end);
		return sprintf(
			esc_html_x('%1$s, %2$s - %3$s', 'Booking date formatted - date, start time, end time', 'booking-weir'),
			esc_html($date),
			esc_html($start_time),
			esc_html($end_time)
		);
	}

	/**
	 * @return string|false
	 */
	public function get_status() {
		return get_post_meta($this->get_id(), 'bw_status', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_status($status) {
		return update_post_meta($this->get_id(), 'bw_status', $status);
	}

	/**
	 * @return string|false
	 */
	public function get_status_text() {
		$current_status = $this->get_status();
		if($current_status === 'wc' && class_exists('WooCommerce') && $order_id = $this->get_order_id()) {
			if($order = wc_get_order($order_id)) {
				return wc_get_order_status_name($order->get_status());
			}
		}
		foreach($this->context->get('booking-statuses') as $status) {
			if($status['value'] === $current_status) {
				return $status['text'];
			}
		}
		return $current_status;
	}

	/**
	 * @return array
	 */
	public function get_payment_type() {
		$payment_type = get_post_meta($this->get_id(), 'bw_payment_type', true);
		$calendar = $this->get_calendar();
		return $calendar->get_payment_type($payment_type);
	}

	/**
	 * @return string
	 */
	public function get_payment_type_name() {
		$type = $this->get_payment_type();
		return $type['name'] ?? esc_html_x('Unknown', 'Payment type', 'booking-weir');
	}

	/**
	 * @return float
	 */
	public function get_price() {
		$price = (float)get_post_meta($this->get_id(), 'bw_price', true);
		return (float)number_format($price, 2);
	}

	/**
	 * @return string
	 */
	public function get_price_formatted() {
		return $this->format_currency($this->get_price());
	}

	/**
	 * @return string|false
	 */
	public function get_payment_method() {
		return get_post_meta($this->get_id(), 'bw_payment_method', true);
	}

	/**
	 * @return string|false
	 */
	public function get_payment_method_name() {
		$methods = $this->context->get('payment')->get_methods();
		$method = $this->get_payment_method();
		return $methods[$method] ?? $method;
	}

	/**
	 * @return float
	 */
	public function get_payment_amount() {
		$amount = $this->get_price();
		$payment_type = $this->get_payment_type();
		if($payment_type['amount'] > 0 && $payment_type['amount'] < 100) {
			$amount = ($amount * $payment_type['amount']) / 100;
		}
		return (float)number_format($amount, 2);
	}

	/**
	 * @return string
	 */
	public function get_payment_amount_formatted() {
		return $this->format_currency($this->get_payment_amount());
	}

	/**
	 * @return string
	 */
	public function get_additional_info() {
		return wp_kses_post(get_post_meta($this->get_id(), 'bw_additional_info', true));
	}

	/**
	 * @return int|bool
	 */
	public function set_additional_info($additional_info = '') {
		return update_post_meta($this->get_id(), 'bw_additional_info', wp_kses_post($additional_info));
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		$fields = get_post_meta($this->get_id(), 'bw_fields', true);
		return is_array($fields) ? $fields : [];
	}

	/**
	 * @param array $fields
	 * @return int|bool
	 */
	public function set_fields($fields) {
		return update_post_meta($this->get_id(), 'bw_fields', $fields);
	}

	/**
	 * Get field value.
	 *
	 * @param string $id Field ID.
	 * @return mixed
	 */
	public function get_field($id) {
		$fields = $this->get_fields();
		return $fields[$id] ?? '';
	}

	/**
	 * Set field value.
	 *
	 * @param string $id Field ID.
	 * @param mixed $value Value.
	 * @return int|bool
	 */
	public function set_field($id, $value) {
		$fields = $this->get_fields();
		$fields[$id] = $value;
		return $this->set_fields($fields);
	}

	/**
	 * Get field value formatted for display.
	 *
	 * @param string $id Field ID.
	 * @return string
	 */
	public function get_field_formatted($id) {
		$field = $this->get_calendar()->get_field($id);
		$value = $this->get_field($id);
		return $field->format_value($value);
	}

	/**
	 * Get fields formatted for display.
	 *
	 * @return array [label => value]
	 */
	public function get_fields_formatted() {
		$fields = [];
		foreach($this->get_calendar()->get_fields() as $field) {
			$value = '';
			if(!$field->is_enabled()) {
				continue;
			}
			switch($field->get_id()) {
				case 'firstName':
					$value = $this->get_first_name();
				break;
				case 'lastName':
					$value = $this->get_last_name();
				break;
				case 'email':
					$value = $this->get_email();
				break;
				case 'phone':
					$value = $this->get_phone();
				break;
				case 'additionalInfo':
					$value = $this->get_additional_info();
				break;
				case 'terms':
					continue 2;
				default:
					$value = $this->get_field_formatted($field->get_id());
			}
			$fields[esc_html($field->get_label())] = $value;
		}
		return $fields;
	}

	/**
	 * @return bool
	 */
	public function get_invoice_email_sent() {
		return (bool)get_post_meta($this->get_id(), 'bw_invoice_email_sent', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_invoice_email_sent($value = true) {
		return update_post_meta($this->get_id(), 'bw_invoice_email_sent', $value);
	}

	/**
	 * @return bool
	 */
	public function get_reminder_email_sent() {
		return (bool)get_post_meta($this->get_id(), 'bw_reminder_email_sent', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_reminder_email_sent($value = true) {
		return update_post_meta($this->get_id(), 'bw_reminder_email_sent', $value);
	}

	/**
	 * @return bool
	 */
	public function get_status_email_sent() {
		$status = $this->get_status();
		return (bool)get_post_meta($this->get_id(), 'bw_status_' . $status . '_email_sent', true);
	}

	/**
	 * @return int|bool
	 */
	public function set_status_email_sent($value = true) {
		$status = $this->get_status();
		return update_post_meta($this->get_id(), 'bw_status_' . $status . '_email_sent', $value);
	}

	/**
	 * @return array|false
	 */
	public function get_breakdown() {
		return get_post_meta($this->get_id(), 'bw_breakdown', true);
	}

	/**
	 * @return string
	 */
	public function get_breakdown_formatted() {
		$breakdown = $this->get_breakdown();
		if(!is_array($breakdown) || count($breakdown) < 1) {
			return '';
		}
		$content = '<ul>';
		foreach($breakdown as $name => $value) {
			$content .= sprintf(
				'<li><strong>%1$s</strong> - %2$s</li>',
				esc_html($name),
				$this->format_currency($value)
			);
		}
		$content .= '</ul>';
		return $content;
	}

	/**
	 * @return bool
	 */
	public function has_extras() {
		$extras = $this->get_extras();
		return is_array($extras) && count($extras) > 0;
	}

	/**
	 * @return array|false
	 */
	public function get_extras() {
		return get_post_meta($this->get_id(), 'bw_extras', true);
	}

	/**
	 * @return string|false
	 */
	public function get_coupon() {
		return get_post_meta($this->get_id(), 'bw_coupon', true);
	}

	/**
	 * @return string
	 */
	public function get_extra_name($id) {
		$calendar = $this->get_calendar();
		$extra = $calendar->get_extra($id);
		return $extra['name'] ?? esc_html_x('Unknown', 'Unknown extra name', 'booking-weir');
	}

	/**
	 * @return string
	 */
	public function get_extra_type($id) {
		$calendar = $this->get_calendar();
		$extra = $calendar->get_extra($id);
		return $extra['pricingType'] ?? 'fixed';
	}

	/**
	 * @return string
	 */
	public function get_extras_formatted($format = 'list') {
		$extras = $this->get_extras();
		if(!is_array($extras) || count($extras) < 1) {
			return '';
		}
		$formatted = [];
		foreach($extras as $extra_id => $value) {
			$name = $this->get_extra_name($extra_id);
			$type = $this->get_extra_type($extra_id);
			switch($type) {
				case 'perhour':
					$value .= esc_html_x('min', 'Minutes', 'booking-weir');
				break;
				case 'units':
					$value .= esc_html_x('pcs', 'Units', 'booking-weir');
				break;
			}
			$formatted[] = [
				'type' => esc_html($type),
				'name' => esc_html($name),
				'value' => esc_html($value),
			];
		}
		switch($format) {
			case 'flat':
				return implode(', ', array_map(function($extra) {
					return sprintf(
						'%1$s%2$s%3$s',
						esc_html($extra['name']),
						$extra['type'] === 'fixed' ? '' : ' - ',
						$extra['type'] === 'fixed' ? '' : esc_html($extra['value'])
					);
				}, $formatted));
			case 'list':
			default:
				return '<ul class="ui list">' . implode('', array_map(function($extra) {
					return sprintf(
						'<li><strong>%1$s</strong>%2$s%3$s</li>',
						esc_html($extra['name']),
						$extra['type'] === 'fixed' ? '' : ' - ',
						$extra['type'] === 'fixed' ? '' : esc_html($extra['value'])
					);
				}, $formatted)) . '</ul>';
		}
	}

	/**
	 * @return string
	 */
	public function get_spelled_out_price() {
		$price = $this->get_price();
		return $this->spell_out($price);
	}

	/**
	 * @return string
	 */
	public function get_spelled_out_payment_amount() {
		$price = $this->get_payment_amount();
		return $this->spell_out($price);
	}

	/**
	 * Payment instructions that can be included in the invoice e-mail.
	 *
	 * @return string
	 */
	public function get_payment_instructions() {
		return wp_kses_post($this->context->get('payment')->get_instructions($this));
	}

	/**
	 * @return string
	 */
	public function get_booking_link() {
		$key = $this->get_billing_key();
		$url = $this->get_return_url();
		return add_query_arg($this->context->get('booking')::VIEW, $key, $url);
	}

	/**
	 * @return string
	 */
	public function get_invoice_url() {
		return $this->context->get('pdf')->get_invoice_url($this);
	}

	/**
	 * @return string
	 */
	public function get_invoice_path() {
		return $this->context->get('pdf')->get_invoice_path($this);
	}

	/**
	 * Dynamic backend data attached to the event REST response.
	 *
	 * @return array
	 */
	public function get_data() {
		$data = [
			'startsIn' => $this->starts_in(),
			'reminderIn' => $this->reminder_in(),
		];
		/**
		 * Add WooCommerce order status.
		 */
		if(class_exists('WooCommerce') && $order_id = $this->get_order_id()) {
			if($order = wc_get_order($order_id)) {
				$data['orderStatus'] = [$order->get_status() => wc_get_order_status_name($order->get_status())];
			}
		}
		return $data;
	}

	/**
	 * Dynamic frontend data attached to the event.
	 *
	 * @param Calendar $calendar Calendar that the event is displayed in.
	 * @return array
	 */
	public function get_public_data(Calendar $calendar) {
		$data = [];
		if(in_array($this->get_type(), ['default', 'slot'])) {
			$data['hasContent'] = !empty($this->get_content());
		}
		if($this->get_type() === 'booking') {
			if(isset($_GET[Booking::VIEW]) && $_GET[Booking::VIEW] === $this->get_billing_key()) {
				$data['isSelected'] = true;
			}
		}
		if(isset($_GET[self::VIEW]) && (int)$_GET[self::VIEW] === $this->get_id()) {
			$data['isSelected'] = true;
			if($this->repeats()) {
				/**
				 * Repeat events require start time to be specified.
				 */
				if(isset($_GET[self::START])) {
					$start = $this->context->get('sanitizer')->sanitize_datetime(wp_unslash($_GET[self::START])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					if($this->context->get('sanitizer')->validate_datetime($start)) {
						$data['selectedStart'] = $start;
					}
				} else {
					$data['selectedStart'] = $this->get_start();
				}
			}
			if(isset($_GET[self::ACTION]) && !did_action('woocommerce_add_to_cart')) {
				if($_GET[self::ACTION] === 'book') {
					$data['forceBook'] = true;
				}
				if($_GET[self::ACTION] === 'view') {
					$data['viewContent'] = true;
				}
			}
		}
		if($calendar->get_id() !== $this->get_calendar_id()) {
			$data['isRelated'] = true;
		}
		if($this->is_bookable()) {
			$data['isAtCapacity'] = $this->is_at_capacity();
		}
		return $data;
	}

	/**
	 * Event related string replacement for templates.
	 *
	 * @return array
	 */
	public function get_template_strings() {
		return apply_filters('bw_template_strings', [
			'%bw_first_name%' => $this->get_first_name(),
			'%bw_space_first_name%' => ($name = $this->get_first_name()) ? ' ' . $name : '',
			'%bw_last_name%' => $this->get_last_name(),
			'%bw_space_last_name%' => ($name = $this->get_last_name()) ? ' ' . $name : '',
			'%bw_name%' => $this->get_name(),
			'%bw_start%' => $this->get_start_formatted(),
			'%bw_end%' => $this->get_end_formatted(),
			'%bw_date%' => $this->get_date_formatted(),
			'%bw_event_title%' => $this->get_bookable_event_title(),
			'%bw_service_name%' => $this->get_service_name(),
			'%bw_price%' => $this->get_price_formatted(),
			'%bw_price_spelled_out%' => $this->get_spelled_out_price(),
			'%bw_payment_amount%' => $this->get_payment_amount_formatted(),
			'%bw_payment_amount_spelled_out%' => $this->get_spelled_out_payment_amount(),
			'%bw_payment_method%' => $this->get_payment_method_name(),
			'%bw_payment_instructions%' => $this->get_payment_instructions(),
			'%bw_payment_type%' => $this->get_payment_type_name(),
			'%bw_payment_type_amount%' => sprintf('%d%%', $this->get_payment_type()['amount']),
			'%bw_booking_link%' => $this->get_booking_link(),
			'%bw_reminder_offset%' => $this->get_calendar()->get_setting('reminderEmailOffset'),
		], $this);
	}

	/**
	 * @return string
	 */
	public function format_currency($value) {
		if(!$calendar = $this->get_calendar()) {
			return number_format($value, 2);
		}
		return sprintf(
			'%1$s%2$s%3$s',
			esc_html($calendar->get_setting('currency')),
			number_format($value, 2),
			esc_html($calendar->get_setting('currencySuffix'))
		);
	}

	/**
	 * Spell out currency with words.
	 *
	 * @param float $value
	 * @return string
	 */
	public function spell_out($value) {
		if(!class_exists('NumberFormatter')) {
			return '';
		}
		$calendar = $this->get_calendar();
		$value = number_format($value, 2);
		$parts = explode('.', (string)$value);
		$spelled = '';
		foreach($parts as $index => $part) {
			$formatter = new NumberFormatter($calendar->get_setting('locale'), NumberFormatter::SPELLOUT);
			$spelled .= $formatter->format($part);
			if($index === 0) {
				$spelled .= ' ' . esc_html($calendar->get_setting('currencyPlural'));
				if(isset($parts[1])) {
					$spelled .= ', ';
				}
			}
			if($index === 1) {
				$spelled .= ' ' . esc_html_x('cents', 'Spelled out currency', 'booking-weir');
			}
		}
		return esc_html($spelled);
	}

	/**
	 * Direct link to this event in the admin calendar.
	 *
	 * @return string
	 */
	public function get_admin_url() {
		return sprintf(
			'%1$s#/%2$s/events/%3$d',
			$this->context->get('admin')->get_url(),
			$this->get_calendar()->get_id(),
			$this->get_id()
		);
	}

	/**
	 * Get event actions.
	 *
	 * @return array
	 */
	public function get_actions() {
		$actions = [];

		if($this->get_status() === 'awaiting') {
			$actions['confirm'] = _x('Confirm', 'Event action', 'booking-weir');
		}

		return $actions;
	}

	/**
	 * @param string $action
	 * @return string
	 */
	public function get_action_url($action) {
		return add_query_arg([
			EventPostType::ID => $this->get_id(),
			EventPostType::ACTION => $this->context->get('sanitizer')->sanitize_key($action),
			EventPostType::NONCE => wp_create_nonce($action),
		], admin_url('/'));
	}

	/**
	 * Delete the event.
	 *
	 * @return WP_Post|false|null Post data on success, false or null on failure.
	 */
	public function delete_permanently() {
		$this->context->get('logger')->log('Delete permanently: ' . $this->get_id(), $this->get_calendar_id());
		if($calendar = $this->get_calendar()) {
			$calendar->remove_event($this->get_id());
		}
		return wp_delete_post($this->get_id(), true);
	}

	/**
	 * How many minutes ago was this event created.
	 *
	 * @return integer
	 */
	public function get_created_ago_minutes() {
		return (int)((datetime\utcstrtotime('now') - $this->get_created_timestamp()) / 60);
	}

	/**
	 * Is an event created with WooCommerce.
	 *
	 * @return boolean
	 */
	public function is_WC() {
		return in_array($this->get_status(), ['cart', 'detached', 'wc']);
	}

	/**
	 * Attach a WooCommerce order to event.
	 *
	 * @param int $order_id
	 * @param bool $add Add event as an order item to the order.
	 * @return true|string
	 */
	public function attach_order($order_id, $add = true) {
		if(!class_exists('WooCommerce')) {
			return __('WooCommerce is not enabled.', 'booking-weir');
		}
		if(!$order = wc_get_order(absint($order_id))) {
			return __('Order not found.', 'booking-weir');
		}
		if(!$product = wc_get_product($this->get_calendar()->get_product_id())) {
			return __('Product not found.', 'booking-weir');
		}

		if($add) {
			if($item_id = wc_add_order_item($order->get_id(), [
				'order_item_name' => $product->get_name(),
				'order_item_type' => 'line_item',
			])) {
				wc_add_order_item_meta($item_id, 'bw_event_id', (string)$this->get_id());
				wc_add_order_item_meta($item_id, '_qty', '1');
				wc_add_order_item_meta($item_id, '_product_id', (string)$product->get_id());
				// wc_add_order_item_meta($item_id, '_line_total', $this->get_price());
			} else {
				return __('Failed adding order item.', 'booking-weir');
			}
		}

		$this->set_order_id($order->get_id());
		$this->set_status('wc');
		$this->set_first_name($order->get_billing_first_name());
		$this->set_last_name($order->get_billing_last_name());
		$this->set_email($order->get_billing_email());
		$this->set_phone($order->get_billing_phone());
		$this->set_additional_info($order->get_customer_note());
		return true;
	}

	/**
	 * Detach a WooCommerce order from event.
	 *
	 * @param bool $remove Remove the item from the order.
	 * @return bool|string
	 */
	public function detach_order($remove = true) {
		if($remove && class_exists('WooCommerce') && $order = wc_get_order($this->get_order_id())) {
			foreach($order->get_items() as $item) {
				if(!$item instanceof WC_Order_Item_Product) {
					continue;
				}
				if($product = $item->get_product()) {
					if($product->get_type() === 'bw_booking' && (int)$item->get_meta('bw_event_id') === $this->get_id()) {
						$order->remove_item($item->get_id());
						$order->save();
						break;
					}
				}
			}
		}
		$this->set_status('detached');
		$this->set_order_id(0);
		$this->set_first_name('');
		$this->set_last_name('');
		$this->set_email('');
		$this->set_phone('');
		$this->set_additional_info('');
		return true;
	}
}
