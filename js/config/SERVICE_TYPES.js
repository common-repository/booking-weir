import {__} from '@wordpress/i18n';

export default [
	{
		key: 'fixed',
		value: 'fixed',
		text: __('Fixed duration', 'booking-weir'),
		desc: __('Service with a fixed hourly price and duration.', 'booking-weir'),
	},
	{
		key: 'loose',
		value: 'loose',
		text: __('Loose duration', 'booking-weir'),
		desc: __('Service with a fixed hourly price for any duration.', 'booking-weir'),
	},
];
