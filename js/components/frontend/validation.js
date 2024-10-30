import {__, _x, sprintf} from '@wordpress/i18n';

import {
	addFilter
} from '@wordpress/hooks';

import {
	isBefore,
	isAfter,
	setHours,
	setMinutes,
	getHours,
	getMinutes,
	addMinutes,
	differenceInMinutes,
	areIntervalsOverlapping,
	isSameDay,
} from 'date-fns';

import {
	toDate,
	getCalendarNowDate,
} from 'utils/date';

const addValidation = (id, callback) => {
	addFilter('bw_validate_event', id, (...args) => {
		const isValid = args[0];
		if(typeof isValid === 'boolean' && isValid) {
			return callback(...args);
		}
		return isValid;
	});
};

addValidation('bw_validate_event_end_gt_start', (isValid, event) => {
	if(isAfter(event.start, event.end)) {
		return __(`Start time is later than end time.`, 'booking-weir');
	}
	return isValid;
});

addValidation('bw_validate_event_start_gt_now', (isValid, event, events, calendar) => {
	const now = getCalendarNowDate(calendar.settings);
	if(isAfter(now, event.start)) {
		return __(`Start time is earlier than current time.`, 'booking-weir');
	}
	return isValid;
});

addValidation('bw_validate_event_start_gt_opening_hour', (isValid, event, events, calendar) => {
	const startHour = getHours(event.start);
	const {openingHour} = calendar.settings;
	if(startHour < openingHour) {
		return __(`Start time is earlier than opening hours.`, 'booking-weir');
	}
	return isValid;
});

addValidation('bw_validate_event_end_lt_closing_hour', (isValid, event, events, calendar) => {
	const endHour = getHours(event.end);
	const endMinutes = getMinutes(event.end);
	const {openingHour, closingHour} = calendar.settings;
	if(
		/**
		 * When closing hour is <= 0, >= 24 or smaller than `openingHour` then
		 * there is no effective closing hour other than the end of the day at
		 * 23:59 which can't be booked past.
		 */
		(closingHour > 0 && closingHour < 24 && closingHour > openingHour)
		&& (
			endHour > closingHour
			|| (endHour === closingHour && endMinutes !== 0)
		)
	) {
		return __(`End time is later than closing hours.`, 'booking-weir');
	}
	return isValid;
});

addValidation('bw_validate_event_start_end_same_day', (isValid, event) => {
	if(!isSameDay(event.start, event.end)) {
		return __(`End time is on the next day.`, 'booking-weir');
	}
	return isValid;
});

addValidation('bw_validate_event_duration_gt_step', (isValid, event, events, calendar) => {
	const duration = differenceInMinutes(event.end, event.start);
	const endHour = getHours(event.end);
	const endMinutes = getMinutes(event.end);
	const {step} = calendar.settings;
	if(duration < step) {
		if(endHour === 23 && endMinutes === 59 && duration + 1 === step) {
			/**
			 * When booking to the end of the day add 1 minute allowance because the booking has to end at 23:59.
			 */
		} else {
			return __(`Duration is shorter than minimum.`, 'booking-weir');
		}
	}
	return isValid;
});

addValidation('bw_validate_event_duration_gt_min', (isValid, event, events, calendar) => {
	const {minDuration} = calendar.settings;
	if(minDuration <= 0) {
		return isValid;
	}

	const duration = differenceInMinutes(event.end, event.start);
	const endHour = getHours(event.end);
	const endMinutes = getMinutes(event.end);
	if(duration < minDuration) {
		if(endHour === 23 && endMinutes === 59 && duration + 1 === minDuration) {
			/**
			 * When booking to the end of the day add 1 minute allowance because the booking has to end at 23:59.
			 */
		} else {
			return sprintf(__(`Bookings must be at least %s minutes long.`, 'booking-weir'), minDuration);
		}
	}
	return isValid;
});

addValidation('bw_validate_event_duration_lt_max', (isValid, event, events, calendar) => {
	const {maxDuration} = calendar.settings;
	if(maxDuration <= 0) {
		return isValid;
	}

	const duration = differenceInMinutes(event.end, event.start);
	if(duration > maxDuration) {
		return sprintf(__(`Bookings must not be longer than %s minutes.`, 'booking-weir'), settings.maxDuration);
	}
	return isValid;
});

addValidation('bw_validate_event_does_not_overlap', (isValid, event, events) => {
	if(events.filter(existingEvent => areIntervalsOverlapping(
		{
			start: event.start,
			end: event.end,
		},
		{
			start: toDate(existingEvent.start),
			end: toDate(existingEvent.end),
		}
	)).length) {
		return __(`Overlapping with another event.`, 'booking-weir');
	}
	return isValid;
});

addValidation('bw_validate_event_leaves_space', (isValid, event, events, calendar) => {
	const {start, end} = event;
	const {space} = calendar.settings;
	let message = false;
	if(events.filter(existingEvent => {
		if(existingEvent.type === 'unavailable') {
			/**
			 * Allow booking close to `unavailable` events.
			 */
			return false;
		}
		if(
			isBefore(toDate(existingEvent.start), start)
			&&
			isAfter(addMinutes(toDate(existingEvent.end), space), start)
		) {
			message = sprintf(__(`Please leave at least %s minutes after the previous event.`, 'booking-weir'), space);
			return true;
		}
		if(
			isBefore(start, toDate(existingEvent.start))
			&&
			isAfter(addMinutes(end, space), toDate(existingEvent.start))
		) {
			message = sprintf(__(`Please leave at least %s minutes before the next event.`, 'booking-weir'), space);
			return true;
		}
		return false;
	}).length) {
		/**
		 * Not enough space before or after event.
		 */
		return message;
	}
	return isValid;
});

addValidation('bw_validate_service_availability', (isValid, event, events, calendar, selectedService) => {
	if(!selectedService) {
		return isValid;
	}

	const {
		availability = 'default',
		availableFrom = '00:00',
		availableTo = '00:00',
	} = selectedService;

	switch(availability) {
		case 'time-range': {
			const {start, end} = event;
			const [fromHours, fromMinutes] = availableFrom.split(':');
			const [toHours, toMinutes] = availableTo.split(':');
			const availableStart = setMinutes(setHours(start, fromHours), fromMinutes);
			const availableEnd = setMinutes(setHours(end, toHours), toMinutes);
			if(
				isBefore(start, availableStart)
				|| isAfter(start, availableEnd)
				|| isBefore(end, availableStart)
				|| isAfter(end, availableEnd)
			) {
				return sprintf(__(`This service can only be booked between %s and %s.`, 'booking-weir'), availableFrom, availableTo);
			}
			break;
		}
	}

	return isValid;
});
