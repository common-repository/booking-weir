import {__, _x} from '@wordpress/i18n';

import {
	Loader,
} from '@fluentui/react-northstar';

import {
	List,
} from 'components/ui';

import {
	formatLong,
	formatDuration,
} from 'utils/date';

import {
	getServicePriceText,
} from 'utils/services';

import {
	getBookableEventPriceText,
} from 'utils/bookable';

import {
	useCurrentCalendar,
	useCurrencyRenderer,
} from 'hooks';

let BookingInfo;
export default BookingInfo = ({booking}) => {
	const calendar = useCurrentCalendar();
	const renderCurrency = useCurrencyRenderer();
	const {settings} = calendar;

	const {
		start,
		end,
		price,
		breakdown,
		service,
		bookableEvent,
		slot,
	} = booking;

	if(!start || !end) {
		return <Loader styles={{alignItems: 'start', justifyContent: 'start'}} />;
	}

	const items = [
		{
			key: 'start',
			header: __('Start', 'booking-weir'),
			content: formatLong(start, settings),
		},
		{
			key: 'end',
			header: __('End', 'booking-weir'),
			content: formatLong(end, settings),
		},
		{
			key: 'duration',
			header: __('Duration', 'booking-weir'),
			content: formatDuration(start, end, settings),
		},
	];

	if(service) {
		items.push({
			key: 'service-name',
			header: __('Service', 'booking-weir'),
			content: service.name,
		});
		items.push({
			key: 'service-price',
			header: __('Price', 'booking-weir'),
			content: getServicePriceText(service, settings),
		});
	} else if(bookableEvent) {
		items.push({
			key: 'event',
			header: __('Event', 'booking-weir'),
			content: bookableEvent.title,
		});
		items.push({
			key: 'event-price',
			header: __('Price', 'booking-weir'),
			content: getBookableEventPriceText(start, end, bookableEvent, settings),
		});
	} else {
		if(slot && slot.title && slot.title !== _x('Slot', 'Slot event public title', 'booking-weir')) {
			items.push({
				key: 'event-slot',
				header: __('Slot', 'booking-weir'),
				content: slot.title,
			});
		}
		items.push({
			key: 'price',
			header: __('Price per hour', 'booking-weir'),
			content: renderCurrency(settings.price),
		});
	}

	if(breakdown && !!Object.keys(breakdown).length) {
		const extraItems = Object.keys(breakdown).map(name => ({
			key: name,
			content: `${name}: ${renderCurrency(breakdown[name])}`,
		}));
		items.push({
			key: 'extras',
			header: __('Extras', 'booking-weir'),
			content: <List compact items={extraItems} />,
		});
	}

	items.push({
		key: 'total',
		header: __('Total price', 'booking-weir'),
		content: renderCurrency(price),
	});

	return <List items={items} />;
};
