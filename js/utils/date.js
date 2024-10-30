import {__, _x} from '@wordpress/i18n';

import {
	memoize,
} from 'lodash';

import {
	format,
	lightFormat,
	formatDistance as _formatDistance,
	formatDistanceStrict,
	parse,
	startOfDay,
	startOfWeek as _startOfWeek,
	endOfWeek as _endOfWeek,
	startOfYear,
	endOfYear,
	getDay,
	eachDayOfInterval,
	eachMonthOfInterval,
	addHours,
	subHours,
	addMinutes,
	subMinutes,
	addDays,
	differenceInHours,
	differenceInMinutes,
	isWeekend,
} from 'date-fns';

import {
	utcToZonedTime,
	getTimezoneOffset,
	toDate as stringToDate,
} from 'date-fns-tz';

import {
	dateFnsLocalizer,
} from 'react-big-calendar/lib';

/**
 * Convert `2020-02-23T07:00` to Date using `date-fns-tz` toDate as opposed to `date-fns` toDate which can't do it.
 */
export const toDate = string => {
	return stringToDate(string);
};

/**
 * Format Date object for storing in database.
 * @param {Date} date
 * @returns `2020-02-23T07:00`
 */
export const toString = date => {
	return lightFormat(date, `yyyy-MM-dd'T'HH:mm`);
};

/**
 * Get the current Date of the calendar.
 * Update memo once a minute.
 */
const _getCalendarNowDate = memoize((min, {timezone}) => {
	return utcToZonedTime(Date.now(), timezone);
});
export const getCalendarNowDate = settings => {
	return _getCalendarNowDate(
		Math.round(new Date().getTime() / 1000 / 60),
		settings
	);
};

/**
 * Get first available date in a calendar that can be booked on.
 *
 * @param {*} settings Calendar settings.
 * @returns Date
 */
export const getFirstAvailableDate = settings => {
	const now = getCalendarNowDate(settings);
	if(settings.weekend) {
		return now;
	}
	let available = now;
	while(isWeekend(available)) {
		available = addDays(available, 1);
	}
	return available;
};

/**
 * Get the opening hour Date for React Big Calendar.
 */
export const getRBCCalendarOpeningHourDateProp = settings => {
	const {openingHour} = settings;

	if(!openingHour || openingHour <= 0) {
		return undefined;
	}

	const start = startOfDay(getCalendarNowDate(settings));
	return addHours(start, openingHour);
};

/**
 * Get the closing hour Date for React Big Calendar.
 * Removes 1 minute to prevent displaying extra hour in the calendar.
 */
export const getRBCCalendarClosingHourDateProp = settings => {
	const {
		openingHour,
		closingHour,
	} = settings;

	if(!closingHour || closingHour <= 0 || closingHour >= 24 || closingHour < openingHour) {
		return undefined;
	}

	const start = startOfDay(getCalendarNowDate(settings));
	const closing = addHours(start, closingHour);
	return subMinutes(closing, 1);
};

/**
 * Get the opening hour Date of the calendar.
 */
export const getCalendarOpeningHourDate = (settings, date) => {
	const {
		openingHour: openingHourSetting,
	} = settings;

	const openingHour = (
		!openingHourSetting
		|| isNaN(openingHourSetting)
		|| openingHourSetting < 0
	) ? 0 : openingHourSetting;

	const start = date ? startOfDay(date) : startOfDay(getCalendarNowDate(settings));
	return addHours(start, openingHour);
};

/**
 * Get the closing hour Date of the calendar.
 */
export const getCalendarClosingHourDate = (settings, date) => {
	const {
		closingHour: closingHourSetting,
	} = settings;

	const closingHour = (
		!closingHourSetting
		|| isNaN(closingHourSetting)
		|| closingHourSetting <= 0
		|| closingHourSetting > 24
	) ? 24 : closingHourSetting;

	const start = date ? startOfDay(date) : startOfDay(getCalendarNowDate(settings));
	return addHours(start, closingHour);
};

/**
 * Get the calendar's `date-fns` locale.
 */
export const getCalendarLocale = settings => {
	const locale = settings?.locale || 'en-GB';
	return require(`date-fns/locale/${locale}/index.js`);
}

/**
 * Get the calendar's localizer.
 */
export const getCalendarLocalizer = settings => {
	const locale = settings?.locale || 'en-GB';
	return dateFnsLocalizer({
		format,
		parse,
		startOfWeek: _startOfWeek,
		getDay,
		locales: {
			[locale]: getCalendarLocale(settings),
		},
	});
};

/**
 * Get the calendar's culture.
 */
export const getCalendarCulture = ({locale}) => {
	return locale;
};

/**
 * Get the calendar's timezone UTC offset.
 */
export const getCalendarUTCOffset = ({timezone}) => {
	return Math.round(getTimezoneOffset(timezone) / 1000 / 60);
};

/**
 * Get the client's timezone UTC offset.
 */
export const getClientUTCOffset = () => {
	return new Date().getTimezoneOffset() * -1;
};

/**
 * Start of week with `weekStartsOn` based on calendar locale.
 */
export const startOfWeek = (date, settings) => {
	const locale = getCalendarLocale(settings);
	return _startOfWeek(date, {weekStartsOn: locale.options.weekStartsOn});
};

/**
 * End of week with `weekStartsOn` based on calendar locale.
 */
export const endOfWeek = (date, settings) => {
	const locale = getCalendarLocale(settings);
	return _endOfWeek(date, {weekStartsOn: locale.options.weekStartsOn});
};

/**
 * @param {string} date `2020-02-23T07:00`
 * @param {*} settings
 * @returns `February 23rd, 2020 at 7:00 AM`
 */
export const formatLong = (date, settings) => {
	return format(toDate(date), 'PPPp', {
		locale: getCalendarLocale(settings),
	});
};

/**
 * @param {string} date `2020-02-23T07:00`
 * @param {*} settings
 * @returns February 23rd, 2020
 */
export const formatDate = (date, settings) => {
	return format(toDate(date), 'PPP', {
		locale: getCalendarLocale(settings),
	});
};

export const durationInMinutes = (start, end) => {
	const startDate = toDate(start);
	const endDate = toDate(end);
	return differenceInMinutes(endDate, startDate);
};

export const formatDuration = (start, end, settings) => {
	const startDate = toDate(start);
	const endDate = toDate(end);
	const locale = getCalendarLocale(settings);
	const hours = differenceInHours(endDate, startDate);
	const minutes = differenceInMinutes(subHours(endDate, hours), startDate);
	const durationHours = formatDistanceStrict(startDate, addHours(startDate, hours), {
		unit: 'hour',
		locale,
	});
	if(minutes > 0) {
		const durationMinutes = formatDistanceStrict(startDate, addMinutes(startDate, minutes), {
			unit: 'minute',
			locale,
		});
		if(hours > 0) {
			return `${durationHours}, ${durationMinutes}`;
		}
		return durationMinutes;
	}
	return durationHours;
};

export const formatMinutes = (minutes, settings) => {
	return formatDuration(new Date(), addMinutes(new Date(), minutes), settings);
};

export const formatDistance = (start, end, settings) => {
	const locale = getCalendarLocale(settings);
	return formatDistanceStrict(start, end, {
		locale,
	});
};

/**
 * Time range format.
 */
const selectRangeFormat = ({start, end}, culture, local) => {
	/**
	 * Calendar needs to end at 59 minutes in order to not show the next hour.
	 * This changes the Date object (mutable) to make the selection include the final minute.
	 * How ever if the calendar ends at 23:59, then keep the 59 otherwise event will end on next day.
	 */
	if(end.getMinutes() === 59 && end.getHours() !== 23) {
		end.setMinutes(60);
	}
	return `${local.format(start, 'p', culture)} – ${local.format(end, 'p', culture)}`;
};

export const getCalendarFormats = () => {
	return {
		dayFormat: 'dd cccccc', // `23 Su`
		dayHeaderFormat: 'EEEE PP', // `Sunday Feb 23, 2020`
		agendaDateFormat: 'cccccc MMM dd', // `Su Feb 23`
		selectRangeFormat, // `10:00 - 12:00`
		agendaHeaderFormat: ({start, end}, culture, local) => `${local.format(start, 'PP', culture)} – ${local.format(end, 'PP', culture)}`, // `Feb 23, 2020 - Feb 24, 2020`
		eventTimeRangeStartFormat: ({start}, culture, local) => `${local.format(start, 'p', culture)} –`, // Multi-day event: `22:00 –`
		eventTimeRangeEndFormat: ({end}, culture, local) => `– ${local.format(end, 'p', culture)}`, // Multi-day event: `– 02:00`
	};
};

export const getCalendarTimezoneDiff = settings => {
	const offset = getCalendarUTCOffset(settings);
	const clientOffset = getClientUTCOffset();
	const diff = offset - clientOffset;
	if(diff !== 0) {
		const locale = getCalendarLocale(settings);
		return {
			clientTimeZone: Intl && Intl.DateTimeFormat().resolvedOptions().timeZone || __('Unknown', 'booking-weir'),
			diffFormatted: (diff < 0 ? '-' : '+') + formatDistanceStrict(new Date(), addMinutes(new Date(), diff), {
				unit: 'minute',
				locale,
			}),
		};
	}
	return false;
};

/**
 * Get date related props for React Big Calendar based on calendar settings.
 */
export const getCalendarDateProps = settings => {
	return {
		localizer: getCalendarLocalizer(settings),
		culture: getCalendarCulture(settings),
		getNow: () => getCalendarNowDate(settings),
		min: getRBCCalendarOpeningHourDateProp(settings),
		max: getRBCCalendarClosingHourDateProp(settings),
		formats: getCalendarFormats(),
		startAccessor: ({start}) => toDate(start),
		endAccessor: ({end}) => toDate(end),
	};
};

/**
 * Get data needed to render week days picker.
 */
const _getWeekdaysData = memoize(settings => {
	const locale = getCalendarLocale(settings);
	const now = new Date();

	const weekDays = eachDayOfInterval({
		start: _startOfWeek(now),
		end: _endOfWeek(now),
	});

	const localWeekDays = eachDayOfInterval({
		start: startOfWeek(now, settings),
		end: endOfWeek(now, settings),
	});

	const data = weekDays.reduce((acc, cur, index) => {
		acc.en.push(format(cur, 'EEEE'));
		acc.localInEnOrder.push(format(cur, 'EEEE', {locale}));
		acc.localInLocalOrder.push(format(localWeekDays[index], 'EEEE', {locale}));
		acc.labels.push(format(localWeekDays[index], 'EEEEEE', {locale}));
		acc.labelsInEnOrder.push(format(cur, 'EEEEEE', {locale}));
		acc.enInLocalOrder.push(format(localWeekDays[index], 'EEEE'));
		return acc;
	}, {
		en: [],
		localInEnOrder: [],
		localInLocalOrder: [],
		labels: [],
		labelsInEnOrder: [],
		enInLocalOrder: [],
	});

	return data;
});
export const getWeekdaysData = settings => {
	return _getWeekdaysData(settings);
};

export const getLocalizedMonths = (settings, pattern = 'MMMM') => {
	const locale = getCalendarLocale(settings);
	const months = eachMonthOfInterval({
		start: startOfYear(new Date),
		end: endOfYear(new Date),
	});
	return months.map(month => format(month, pattern, {locale}));
};

/**
 * Props for localizing react-northstar datepicker.
 *
 * @param {*} settings Calendar settings.
 * @returns
 */
export const getLocalizedDatepickerProps = settings => {
	const locale = getCalendarLocale(settings);
	const weekDaysData = getWeekdaysData(settings);

	const props = {
		months: getLocalizedMonths(settings),
		shortMonths: getLocalizedMonths(settings, 'MMM'),
		days: weekDaysData.localInEnOrder, // ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
		shortDays: weekDaysData.labelsInEnOrder, // ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
		firstDayOfWeek: locale.options.weekStartsOn,
		// formatDay,
		// formatYear,
		// formatMonthDayYear,
		// formatMonthYear,
		goToToday: _x('Go to today', 'Datepicker', 'booking-weir'),
		openCalendarTitle: _x('Open calendar', 'Datepicker', 'booking-weir'),
		inputPlaceholder: _x('Select a date...', 'Datepicker', 'booking-weir'),
		weekNumberFormatString: _x('Week number {0}', 'Datepicker', 'booking-weir'),
		prevMonthAriaLabel: _x('Previous month', 'Datepicker', 'booking-weir'),
		nextMonthAriaLabel: _x('Next month', 'Datepicker', 'booking-weir'),
		prevYearAriaLabel: _x('Previous year', 'Datepicker', 'booking-weir'),
		nextYearAriaLabel: _x('Next year', 'Datepicker', 'booking-weir'),
		prevYearRangeAriaLabel: _x('Previous year range', 'Datepicker', 'booking-weir'),
		nextYearRangeAriaLabel: _x('Next year range', 'Datepicker', 'booking-weir'),
		closeButtonAriaLabel: _x('Close', 'Datepicker', 'booking-weir'),
		selectedDateFormatString: _x('Selected date {0}', 'Datepicker', 'booking-weir'),
		todayDateFormatString: _x("Today's date {0}", 'Datepicker', 'booking-weir'),
		monthPickerHeaderAriaLabel: _x('{0}, select to change the year', 'Datepicker', 'booking-weir'),
		yearPickerHeaderAriaLabel: _x('{0}, select to change the month', 'Datepicker', 'booking-weir'),
		isRequiredErrorMessage: _x('A date selection is required', 'Datepicker', 'booking-weir'),
		invalidInputErrorMessage: _x('Manually entered date is not in correct format.', 'Datepicker', 'booking-weir'),
		isOutOfBoundsErrorMessage: _x('The selected date is from the restricted range.', 'Datepicker', 'booking-weir'),
		inputAriaLabel: _x('Select a date.', 'Datepicker', 'booking-weir'),
		inputBoundedFormatString: _x('Input a date between {0} and {1}.', 'Datepicker', 'booking-weir'),
		inputMinBoundedFormatString: _x('Input a date starting from {0}.', 'Datepicker', 'booking-weir'),
		inputMaxBoundedFormatString: _x('Input a date ending at {0}.', 'Datepicker', 'booking-weir'),
	};

	return props;
};
