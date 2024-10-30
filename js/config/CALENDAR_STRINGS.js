import {__, sprintf} from '@wordpress/i18n';

export default {
	date:            __('Date', 'booking-weir'),
	time:            __('Time', 'booking-weir'),
	event:           __('Event', 'booking-weir'),
	allDay:          __('All day', 'booking-weir'),
	week:            __('Week', 'booking-weir'),
	work_week:       __('Week', 'booking-weir'),
	day:             __('Day', 'booking-weir'),
	month:           __('Month', 'booking-weir'),
	previous:        __('Previous', 'booking-weir'),
	next:            __('Next', 'booking-weir'),
	yesterday:       __('Yesterday', 'booking-weir'),
	tomorrow:        __('Tomorrow', 'booking-weir'),
	today:           __('Today', 'booking-weir'),
	agenda:          __('Agenda', 'booking-weir'),
	noEventsInRange: __('There are no events in this range.', 'booking-weir'),
	showMore:        total => sprintf(__('+%s more', 'booking-weir'), total),
};
