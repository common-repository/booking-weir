import {__, _x, sprintf} from '@wordpress/i18n';

import {
	applyFilters,
} from '@wordpress/hooks';

import {
	useState,
	useEffect,
} from 'react';

import {
	useDispatch,
	useSelector,
} from 'react-redux';

import {
	Calendar as BigCalendar,
} from 'react-big-calendar/lib';

import Measure from 'react-measure';

import cx from 'classnames';

import Loader from 'components/ui/Loader';

import {
	BOOK_BUTTON_CLASS,
} from 'components/frontend/booking/modal/BookButton';

import {
	differenceInMinutes,
	addMinutes,
	isEqual,
	isBefore,
	isAfter,
	getHours,
	getMinutes,
	areIntervalsOverlapping,
} from 'date-fns';

import {
	toDate,
	toString,
	getCalendarNowDate,
	getCalendarDateProps,
} from 'utils/date';

import {
	getElementsWithChildren,
} from 'utils/html';

import Toolbar from './Toolbar';
import EventWrapper from './EventWrapper';
import TimezoneWarning from 'components/frontend/notices/TimezoneWarning';
import Services from './Services';

import {
	setToast,
	clearToast,
} from 'components/frontend/toast';

import {
	useCurrentCalendar,
	useSelectedService,
} from 'hooks';

import {
	getServiceEnd,
} from 'utils/services';

import MESSAGES from 'config/CALENDAR_STRINGS';

import {
	CLEAR_SELECTION_EVENT,
} from './clearSelection';

const useValidatedEvent = (events = []) => {
	const dispatch = useDispatch();
	const calendar = useCurrentCalendar();
	const selectedService = useSelectedService();
	const [event, setEvent] = useState({});
	const {settings} = calendar;

	const validate = (nextEvent) => {
		/**
		 * Values required for validation.
		 */
		if(!nextEvent.start || !nextEvent.end) {
			return false;
		}
		/**
		 * Check that values have changed.
		 */
		if(isEqual(toDate(event.start), nextEvent.start) && isEqual(toDate(event.end), nextEvent.end)) {
			return false;
		}

		const {start, end} = nextEvent;
		const {openingHour, closingHour} = settings;
		const now = getCalendarNowDate(settings);
		const startHour = getHours(start);
		const endHour = getHours(end);
		const endMinutes = getMinutes(end);
		const duration = differenceInMinutes(end, start);

		// if(isAfter(start, end)) {
		// 	dispatch(setToast(__(`Start time is later than end time.`, 'booking-weir')));
		// 	return false;
		// }

		// if(isAfter(now, start)) {
		// 	dispatch(setToast(__(`Start time is earlier than current time.`, 'booking-weir')));
		// 	return false;
		// }

		// if(startHour < openingHour) {
		// 	dispatch(setToast(__(`Start time is earlier than opening hours.`, 'booking-weir')));
		// 	return false;
		// }

		// if(
		// 	/**
		// 	 * When closing hour is <= 0, >= 24 or smaller than `openingHour` then
		// 	 * there is no effective closing hour other than the end of the day at
		// 	 * 23:59 which can't be booked past.
		// 	 */
		// 	(closingHour > 0 && closingHour < 24 && closingHour > openingHour)
		// 	&& (
		// 		endHour > closingHour
		// 		|| (endHour === closingHour && endMinutes !== 0)
		// 	)
		// ) {
		// 	dispatch(setToast(__(`End time is later than closing hours.`, 'booking-weir')));
		// 	return false;
		// }

		// if(duration < settings.step) {
		// 	if(endHour === 23 && endMinutes === 59 && duration + 1 === settings.step) {
		// 		/**
		// 		 * When booking to the end of the day add 1 minute allowance because the booking has to end at 23:59.
		// 		 */
		// 	} else {
		// 		dispatch(setToast(__(`Duration is shorter than minimum.`, 'booking-weir')));
		// 		return false;
		// 	}
		// }

		// if(settings.minDuration > 0 && duration < settings.minDuration) {
		// 	if(endHour === 23 && endMinutes === 59 && duration + 1 === settings.minDuration) {
		// 		/**
		// 		 * When booking to the end of the day add 1 minute allowance because the booking has to end at 23:59.
		// 		 */
		// 	} else {
		// 		dispatch(setToast(sprintf(__(`Bookings must be at least %s minutes long.`, 'booking-weir'), settings.minDuration)));
		// 		return false;
		// 	}
		// }

		// if(settings.maxDuration > 0 && duration > settings.maxDuration) {
		// 	dispatch(setToast(sprintf(__(`Bookings must not be longer than %s minutes.`, 'booking-weir'), settings.maxDuration)));
		// 	return false;
		// }

		// if(events.filter(existingEvent => areIntervalsOverlapping({start, end}, {
		// 	start: toDate(existingEvent.start),
		// 	end: toDate(existingEvent.end),
		// })).length) {
		// 	dispatch(setToast(__(`Overlapping with another event.`, 'booking-weir')));
		// 	return false;
		// }

		// const space = settings.space;
		// if(events.filter(existingEvent => {
		// 	if(existingEvent.type === 'unavailable') {
		// 		/**
		// 		 * Allow booking close to `unavailable` events.
		// 		 */
		// 		return false;
		// 	}
		// 	if(
		// 		isBefore(toDate(existingEvent.start), start)
		// 		&&
		// 		isAfter(addMinutes(toDate(existingEvent.end), space), start)
		// 	) {
		// 		dispatch(setToast(sprintf(__(`Please leave at least %s minutes after the previous event.`, 'booking-weir'), space)));
		// 		return true;
		// 	}
		// 	if(
		// 		isBefore(start, toDate(existingEvent.start))
		// 		&&
		// 		isAfter(addMinutes(end, space), toDate(existingEvent.start))
		// 	) {
		// 		dispatch(setToast(sprintf(__(`Please leave at least %s minutes before the next event.`, 'booking-weir'), space)));
		// 		return true;
		// 	}
		// 	return false;
		// }).length) {
		// 	/**
		// 	 * Not enough space before or after event.
		// 	 */
		// 	return false;
		// }

		/**
		 * Filter validation.
		 */
		const filter = applyFilters('bw_validate_event', true, nextEvent, events, calendar, selectedService);
		if((typeof filter === 'boolean' && !filter) || typeof filter === 'string') {
			if(typeof filter === 'string') {
				dispatch(setToast(filter));
			} else {
				dispatch(setToast(_x('Unable to book here.', 'Message when `bw_validate_event` filter returned false.', 'booking-weir')));
			}
			return false;
		}

		dispatch(clearToast());
		return true;
	};

	const update = nextEvent => {
		if(nextEvent === undefined) {
			setEvent({});
		} else if(validate(nextEvent)) {
			setEvent({
				title: _x('Selection', 'Title of the currently selected range in the calendar (displayed in Agenda)', 'booking-weir'),
				start: toString(nextEvent.start),
				end: toString(nextEvent.end),
			});
		}
	};

	return [event, update];
};

/**
 * Custom BigCalendar components.
 */
const COMPONENTS = {
	toolbar: Toolbar,
	eventWrapper: EventWrapper,
};

let Calendar;
export default Calendar = () => {
	const dispatch = useDispatch();
	const calendar = useCurrentCalendar();
	const calendarEvents = calendar?.events || [];
	const range = useSelector(state => state.ui.range);
	const repeatEvents = applyFilters('bw_repeat_events', [], calendarEvents, range);
	const allEvents = calendarEvents.concat(repeatEvents);
	const [event, setEvent] = useValidatedEvent(allEvents);
	const [width, setWidth] = useState(1024);
	const selectedService = useSelectedService();

	useEffect(() => {
		const clear = () => setEvent(undefined);
		document.addEventListener(CLEAR_SELECTION_EVENT, clear);
		return () => document.removeEventListener(CLEAR_SELECTION_EVENT, clear);
	}, [setEvent]);

	if(!calendar) {
		return <Loader />;
	}

	const {settings} = calendar;
	const isWide = width >= settings.mobile;

	/**
	 * Filter out filled slots.
	 */
	const events = allEvents.filter(({type, start, end}, index, self) => {
		return type !== 'slot' || self.findIndex(({type: t, start: s, end: e}) => t !== 'slot' && start === s && end === e) === -1;
	});

	const onSelectSlot = ({action, start, end, box}) => {
		if(settings.services && !selectedService) {
			dispatch(setToast(__(`Please select a service from above.`, 'booking-weir')));
			return;
		}
		switch(action) {
			case 'select':
				if(selectedService) {
					setEvent({start, end: getServiceEnd(selectedService, settings, start, end)});
				} else {
					setEvent({start, end});
				}
			break;
			case 'click': {
				if(selectedService) {
					/**
					 * Prevent click action when the target was the "Book" button.
					 */
					const bookButtonsElements = getElementsWithChildren(document.getElementsByClassName(BOOK_BUTTON_CLASS));
					const clickedElement = document.elementFromPoint(box.clientX, box.clientY);
					if(bookButtonsElements.includes(clickedElement)) {
						break;
					}
					setEvent({start, end: getServiceEnd(selectedService, settings, start, end)});
				}
				break;
			}
		}
	};

	const WEEK_VIEW = settings.weekend ? 'week' : 'work_week';
	const VIEWS = isWide ? {
		[WEEK_VIEW]: true,
		agenda: true,
	} : {
		day: true,
		agenda: true,
	};
	const DEFAULT_VIEW = isWide ? WEEK_VIEW : 'day';

	const HEIGHT = parseInt(settings.height) || 500;

	return <>
		<TimezoneWarning settings={settings} />
		<Services services={calendar.services} isWide={isWide} />
		<Measure bounds onResize={({bounds: {width}}) => setWidth(width)}>
			{({measureRef}) => (
				<div ref={measureRef} style={{height: HEIGHT}}>
					<BigCalendar
						key={`calendar-${DEFAULT_VIEW}`}
						selectable={settings.slots ? false : 'ignoreEvents'}
						messages={MESSAGES}
						events={event.start ? events.concat(event) : events}
						views={VIEWS}
						defaultView={DEFAULT_VIEW}
						drilldownView={DEFAULT_VIEW}
						step={settings.step}
						timeSlots={1}
						allDayAccessor={() => false}
						showMultiDayTimes={true}
						onSelectSlot={onSelectSlot}
						components={COMPONENTS}
						{...getCalendarDateProps(settings)}
						className={cx('bw-calendar', {'bw-mode-slots': !!settings.slots})}
					/>
				</div>
			)}
		</Measure>
	</>;
};
