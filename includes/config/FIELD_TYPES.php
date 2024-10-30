<?php

namespace wsd\bw\config;

$field_types = [
	[
		'key' => 'text',
		'text' => __('Text', 'booking-weir'),
		'value' => 'text',
		'supports' => ['label', 'placeholder', 'defaultValue', 'required'],
	],
	[
		'key'=> 'textarea',
		'text' => __('Textarea', 'booking-weir'),
		'value'=> 'textarea',
		'supports' => ['label', 'placeholder', 'defaultValue', 'required'],
	],
	[
		'key'=> 'number',
		'text' => __('Number', 'booking-weir'),
		'value'=> 'number',
		'supports' => ['label', 'defaultValue', 'min-max-step'],
	],
	[
		'key'=> 'select',
		'text' => __('Select', 'booking-weir'),
		'value'=> 'select',
		'supports' => ['label', 'placeholder', 'options', 'defaultOption', 'required'],
	],
	[
		'key'=> 'radio',
		'text' => __('Radio', 'booking-weir'),
		'value'=> 'radio',
		'supports' => ['label', 'options', 'defaultOption', 'horizontal'],
	],
	[
		'key'=> 'checkbox',
		'text' => __('Checkbox', 'booking-weir'),
		'value'=> 'checkbox',
		'supports' => ['label', 'defaultChecked', 'required'],
	],
	[
		'key'=> 'file',
		'text' => __('File', 'booking-weir'),
		'value'=> 'file',
		'supports' => ['label', 'required', 'accept', 'maxFileSize'],
	],
	[
		'key'=> 'email',
		'text' => __('E-mail', 'booking-weir'),
		'value'=> 'email',
		'supports' => ['label', 'required'],
	],
	[
		'key'=> 'terms',
		'text' => __('Terms', 'booking-weir'),
		'value'=> 'terms',
		'supports' => ['link'],
		'insertable' => false,
	],
	[
		'key'=> 'grid',
		'text' => __('Grid', 'booking-weir'),
		'value'=> 'grid',
		'supports' => [],
	],
];

return apply_filters('bw_field_types', $field_types);
