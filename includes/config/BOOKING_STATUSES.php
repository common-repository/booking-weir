<?php

namespace wsd\bw\config;

$statuses = [
	[
		'key' => 'cart',
		'text' => _x('In cart', 'Booking status', 'booking-weir'),
		'value' => 'cart',
		'color' => 'brown',
		'wc' => true,
	],
	[
		'key' => 'detached',
		'text' => _x('Detached', 'Booking status', 'booking-weir'),
		'value' => 'detached',
		'color' => 'orange',
		'wc' => true,
	],
	[
		'key' => 'wc',
		'text' => _x('WooCommerce', 'Booking status', 'booking-weir'),
		'value' => 'wc',
		'color' => 'pink',
		'wc' => true,
	],
	[
		'key' => 'awaiting',
		'text' => _x('Awaiting', 'Booking status', 'booking-weir'),
		'value' => 'awaiting',
		'color' => 'yellow',
	],
	[
		'key' => 'confirmed',
		'text' => _x('Confirmed', 'Booking status', 'booking-weir'),
		'value' => 'confirmed',
		'color' => 'purple',
	],
	[
		'key' => 'pending',
		'text' => _x('Pending payment', 'Booking status', 'booking-weir'),
		'value' => 'pending',
		'color' => 'orange',
	],
	[
		'key' => 'escrow',
		'text' => _x('Escrow paid', 'Booking status', 'booking-weir'),
		'value' => 'escrow',
		'color' => 'olive',
	],
	[
		'key' => 'paid',
		'text' => _x('Paid in full', 'Booking status', 'booking-weir'),
		'value' => 'paid',
		'color' => 'teal',
	],
	[
		'key' => 'completed',
		'text' => _x('Completed', 'Booking status', 'booking-weir'),
		'value' => 'completed',
		'color' => 'green',
	],
	[
		'key' => 'cancelled',
		'text' => _x('Cancelled', 'Booking status', 'booking-weir'),
		'value' => 'cancelled',
		'color' => 'red',
	],
	[
		'key' => 'refunded',
		'text' => _x('Refunded', 'Booking status', 'booking-weir'),
		'value' => 'refunded',
		'color' => 'black',
	],
	[
		'key' => 'archived',
		'text' => _x('Archived', 'Booking status', 'booking-weir'),
		'value' => 'archived',
		'color' => 'grey',
	],
];

return apply_filters('bw_booking_statuses', $statuses);
