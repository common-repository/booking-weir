<?php

namespace wsd\bw\config;

use function wsd\bw\util\helpers\flatten_fields;

$fields = [
	[
		'id' => 'grid',
		'type' => 'grid',
		'columns' => 2,
		'enabled' => true,
		'fields' => [
			[
				'id' => 'firstName',
				'label' => __('First name', 'booking-weir'),
				'type' => 'text',
				'enabled' => true,
				'required' => true,
			],
			[
				'id' => 'lastName',
				'label' => __('Last name', 'booking-weir'),
				'type' => 'text',
				'enabled' => true,
			],
			[
				'id' => 'email',
				'label' => __('E-mail', 'booking-weir'),
				'type' => 'email',
				'enabled' => true,
				'required' => true,
			],
			[
				'id' => 'phone',
				'label' => __('Phone', 'booking-weir'),
				'type' => 'text',
				'enabled' => true,
			],
		],
	],
	[
		'id' => 'additionalInfo',
		'label' => __('Additional info', 'booking-weir'),
		'type' => 'textarea',
		'enabled' => true,
	],
	[
		'id' => 'terms',
		'label' => __('Terms', 'booking-weir'),
		'type' => 'terms',
		'enabled' => true,
		'required' => true,
	],
];

$fields = apply_filters('bw_default_fields', $fields);

/**
 * Allow submitting default fields directly with booking `$_POST` request.
 */
add_filter('bw_booking_post_whitelist', function($whitelist) use ($fields) {
	$whitelist = array_merge($whitelist, array_map(function($field) {
		return $field['id'];
	}, flatten_fields($fields)));
	return array_unique($whitelist);
});

return $fields;
