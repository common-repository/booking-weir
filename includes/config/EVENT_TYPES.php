<?php

namespace wsd\bw\config;

$types = [
	[
		'key' => 'default',
		'text' => _x('Event', 'Event type', 'booking-weir'),
		'value' => 'default',
		'color' => 'teal',
		'creatable' => true,
	],
	[
		'key' => 'draft',
		'text' => _x('New event', 'Event type', 'booking-weir'),
		'value' => 'draft',
		'color' => 'green',
		'creatable' => false,
	],
	[
		'key' => 'booking',
		'text' => _x('Booking', 'Event type', 'booking-weir'),
		'value' => 'booking',
		'color' => 'blue',
		'creatable' => true,
	],
	[
		'key' => 'slot',
		'text' => _x('Slot', 'Event type', 'booking-weir'),
		'value' => 'slot',
		'color' => 'purple',
		'creatable' => true,
	],
	[
		'key' => 'unavailable',
		'text' => _x('Unavailable', 'Event type', 'booking-weir'),
		'value' => 'unavailable',
		'color' => 'grey',
		'creatable' => true,
	],
	[
		'key' => '_draft',
		'text' => _x('Private', 'Event type', 'booking-weir'),
		'value' => '_draft',
		'color' => 'yellow',
		'creatable' => false,
	],
	[
		'key' => '_trash',
		'text' => _x('Deleted', 'Event type', 'booking-weir'),
		'value' => '_trash',
		'color' => 'red',
		'creatable' => false,
	],
];

return apply_filters('bw_event_types', $types);
