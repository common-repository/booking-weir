import {_x} from '@wordpress/i18n';

import {
	durationInMinutes,
} from 'utils/date';

export const getBookableEventPriceText = (start, end, bookableEvent, settings) => {
	const {
		currency,
		currencySuffix,
	} = settings;

	const {
		booking: {
			price = 0,
		},
	} = bookableEvent;

	if(price <= 0) {
		return _x('Free', 'Price label when price is 0', 'booking-weir');
	}

	return `${currency}${durationInMinutes(start, end) / 60 * price}${currencySuffix}`;
};

export const withoutBookableEventChildren = event => {
	return !event.bookableEventId;
};
