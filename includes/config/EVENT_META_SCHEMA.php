<?php

namespace wsd\bw\config;

if(!isset($this)) {
	return [];
}

$sanitizer = $this->context->get('sanitizer');

$meta_schema = [
	'calendar_id' => [
		'name' => 'calendarId',
		'type' => 'string', // 'string', 'boolean', 'integer', 'number' (float)
		'description' => __('Calendar ID that the event is in', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true, // REST is private so everything is shown. `false` is for values that shouldn't be updated via REST.
		'sanitize_callback' => [$sanitizer, 'sanitize_id'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'start' => [
		'name' => 'start',
		'type' => 'string',
		'description' => __('Event start time', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_datetime'],
		'sanitize_callback' => [$sanitizer, 'sanitize_datetime'],
		'auth_callback' => [$this->context, 'is_elevated'],
		'public' => true,
	],
	'start_timestamp' => [
		'name' => 'startTimestamp',
		'type' => 'integer',
		'description' => __('Event start timestamp', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_integer'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'end' => [
		'name' => 'end',
		'type' => 'string',
		'description' => __('Event end time', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_datetime'],
		'sanitize_callback' => [$sanitizer, 'sanitize_datetime'],
		'auth_callback' => [$this->context, 'is_elevated'],
		'public' => true,
	],
	'end_timestamp' => [
		'name' => 'endTimestamp',
		'type' => 'integer',
		'description' => __('Event end timestamp', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_integer'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'created_timestamp' => [
		'name' => 'createdTimestamp',
		'type' => 'integer',
		'description' => __('Event creation timestamp', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_integer'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'utc_offset' => [
		'name' => 'utcOffset',
		'type' => 'integer',
		'description' => __('UTC offset of the client', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_integer'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'type' => [
		'name' => 'type',
		'type' => 'string',
		'description' => __('Event type', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_event_type'],
		'sanitize_callback' => [$sanitizer, 'sanitize_event_type'],
		'auth_callback' => [$this->context, 'is_elevated'],
		'public' => true,
	],
	'service_id' => [
		'name' => 'serviceId',
		'type' => 'string',
		'description' => __('Service ID that the event was booked for', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_id'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'extras' => [
		'name' => 'extras',
		'type' => 'object',
		'description' => __('Booking extras', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_extras'],
		'sanitize_callback' => [$sanitizer, 'sanitize_extras'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'coupon' => [
		'name' => 'coupon',
		'type' => 'string',
		'description' => __('Coupon used when booking', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'price' => [
		'name' => 'price',
		'type' => 'number',
		'description' => __('Booking price', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_float'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'breakdown' => [
		'name' => 'breakdown',
		'type' => 'object',
		'description' => __('Booking breakdown', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_breakdown'],
		'sanitize_callback' => [$sanitizer, 'sanitize_breakdown'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'payment_method' => [
		'name' => 'paymentMethod',
		'type' => 'string',
		'description' => __('Booking payment method', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'payment_type' => [
		'name' => 'paymentType',
		'type' => 'string',
		'description' => __('Booking payment type ID', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'billing_id' => [
		'name' => 'billingId',
		'type' => 'string',
		'description' => __('Billing ID, year and event ID combined', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'billing_key' => [
		'name' => 'billingKey',
		'type' => 'string',
		'description' => __('Unique identifier for billing', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'transaction_id' => [
		'name' => 'transactionId',
		'type' => 'string',
		'description' => __('Transaction ID when used with a payment method that supports it', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'order_id' => [
		'name' => 'orderId',
		'type' => 'integer',
		'description' => __('WooCommerce Order ID', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_integer'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'return_url' => [
		'name' => 'returnUrl',
		'type' => 'string',
		'description' => __('URL of the page that the calendar was on at the time of the booking', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'invoice_email_sent' => [
		'name' => 'invoiceEmailSent',
		'type' => 'boolean',
		'description' => __('Whether the e-mail after initial booking has been sent', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_boolean'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'status_confirmed_email_sent' => [
		'name' => 'statusConfirmedEmailSent',
		'type' => 'boolean',
		'description' => __('Whether the e-mail after status changed to "confirmed" has been sent', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_boolean'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'reminder_email_sent' => [
		'name' => 'reminderEmailSent',
		'type' => 'boolean',
		'description' => __('Whether the reminder e-mail has been sent', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_boolean'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'status' => [
		'name' => 'status',
		'type' => 'string',
		'description' => __('Booking status', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_booking_status'],
		'sanitize_callback' => [$sanitizer, 'sanitize_booking_status'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'first_name' => [
		'name' => 'firstName',
		'type' => 'string',
		'description' => __('Booking booker first name', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'last_name' => [
		'name' => 'lastName',
		'type' => 'string',
		'description' => __('Booking booker last name', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'email' => [
		'name' => 'email',
		'type' => 'string',
		'description' => __('Booking booker e-mail', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_email'],
		'sanitize_callback' => [$sanitizer, 'sanitize_email'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'phone' => [
		'name' => 'phone',
		'type' => 'string',
		'description' => __('Booking booker phone number', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'additional_info' => [
		'name' => 'additionalInfo',
		'type' => 'string',
		'description' => __('Booking additional info', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_textarea'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'fields' => [
		'name' => 'fields',
		'type' => 'object',
		'description' => __('Booking fields', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_fields'],
		'sanitize_callback' => [$sanitizer, 'sanitize_fields'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'notes' => [
		'name' => 'notes',
		'type' => 'string',
		'description' => __('Admin notes for the event', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_textarea'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'terms' => [
		'name' => 'terms',
		'type' => 'boolean',
		'description' => __('Whether booker has agreed to terms and conditions', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_boolean'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'version' => [
		'name' => 'version',
		'type' => 'string',
		'description' => __('Plugin version when event was created', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'userip' => [
		'name' => 'userip',
		'type' => 'string',
		'description' => __('User IP address', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'useragent' => [
		'name' => 'useragent',
		'type' => 'string',
		'description' => __('User agent', 'booking-weir'),
		'single' => true,
		'show_in_rest' => false,
		'sanitize_callback' => [$sanitizer, 'sanitize_string'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'bookable' => [
		'name' => 'bookable',
		'type' => 'boolean',
		'description' => __('Whether the event is bookable', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_boolean'],
		'auth_callback' => [$this->context, 'is_elevated'],
		'public' => true,
	],
	'booking' => [
		'name' => 'booking',
		'type' => 'object',
		'description' => __('Holds info about the event booking when `bookable` is true', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_bookable_event_settings'],
		'sanitize_callback' => [$sanitizer, 'sanitize_bookable_event_settings'],
		'auth_callback' => [$this->context, 'is_elevated'],
		'public' => true,
	],
	'bookable_event_id' => [
		'name' => 'bookableEventId',
		'type' => 'integer',
		'description' => __('ID of a bookable event that this booking was created for', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_integer'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'slot_id' => [
		'name' => 'slotId',
		'type' => 'integer',
		'description' => __('ID of a slot that this event was booked on', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_integer'],
		'auth_callback' => [$this->context, 'is_elevated'],
	],
	'repeat' => [
		'name' => 'repeat',
		'type' => 'boolean',
		'description' => __('Whether the event repeats', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'sanitize_callback' => [$sanitizer, 'sanitize_boolean'],
		'auth_callback' => [$this->context, 'is_elevated'],
		'public' => true,
	],
	'repeater' => [
		'name' => 'repeater',
		'type' => 'object',
		'description' => __('Holds info about how to repeat the event when `repeat` is true', 'booking-weir'),
		'single' => true,
		'show_in_rest' => true,
		'validate_callback' => [$sanitizer, 'validate_repeater'],
		'sanitize_callback' => [$sanitizer, 'sanitize_repeater'],
		'auth_callback' => [$this->context, 'is_elevated'],
		'public' => true,
	],
];

$meta_schema = apply_filters('bw_event_meta_schema', $meta_schema);
$namespaced_schema = [];
foreach($meta_schema as $meta_key => $args) {
	$namespaced_schema['bw_' . sanitize_key($meta_key)] = $args;
}

return $namespaced_schema;
