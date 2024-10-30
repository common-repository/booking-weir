import {
	applyFilters,
} from '@wordpress/hooks';

import {
	useCallback,
	useState,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Segment,
} from 'semantic-ui-react';

import {
	Calendar as RBC,
} from 'react-big-calendar/lib';
import withDragAndDrop from 'react-big-calendar/lib/addons/dragAndDrop';
const BigCalendar = withDragAndDrop(RBC);

import Measure from 'react-measure';

import Toolbar from './Toolbar';
import EventWrapper from './EventWrapper';
import AgendaEvent from './AgendaEvent';

import {
	withEventData,
} from 'utils/event';

import {
	getCalendarDateProps,
	toString,
} from 'utils/date';

import {
	withoutBookableEventChildren,
} from 'utils/bookable';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	useSelectedEventId,
} from 'hooks/events';

import {
	setSelectedEvent,
	updateEvent,
	setDraftEvent,
	updateDraftEvent,
} from 'actions';


import MESSAGES from 'config/CALENDAR_STRINGS';
import EVENT_TYPES from 'config/EVENT_TYPES';

const DRAFT_EVENT = EVENT_TYPES.find(({value}) => value === 'draft');

const useCalendarProps = (width) => {
	const {settings} = useCurrentCalendar();
	const isWide = width >= settings.mobile;
	const currentView = useSelector(state => state.ui.view);
	const WEEK_VIEW = settings.weekend ? 'week' : 'work_week';
	const DEFAULT_VIEW = isWide ? WEEK_VIEW : 'day';

	return {
		key: `calendar-${DEFAULT_VIEW}`,
		selectable: ['day', 'week', 'work_week'].includes(currentView),
		components: {
			toolbar: Toolbar,
			eventWrapper: EventWrapper,
			agenda: {
				event: AgendaEvent,
			},
		},
		views: {
			...(isWide ? {
				month: true,
				[WEEK_VIEW]: true,
			} : {}),
			day: true,
			agenda: true,
		},
		defaultView: DEFAULT_VIEW,
		drilldownView: DEFAULT_VIEW,
		messages: MESSAGES,
		step: settings.step,
		length: 90, // Agenda length in days
		timeSlots: 1,
		allDayAccessor: () => false,
		showMultiDayTimes: true,
		resizable: isWide,
		...getCalendarDateProps(settings),
	};
};


let Calendar;
export default Calendar = () => {
	const dispatch = useDispatch();
	const [width, setWidth] = useState(1024);
	const props = useCalendarProps(width);
	const range = useSelector(state => state.ui.range);
	const calendars = useSelector(state => state.calendars.present);
	const selectedEventId = useSelectedEventId();
	const calendar = useCurrentCalendar();
	const relatedEvents = applyFilters('bw_related_events', [], calendar.id, calendars);
	const repeatEvents = applyFilters('bw_repeat_events', [], calendar.events.concat(relatedEvents), range);
	const draftEvent = withEventData(useSelector(state => state.ui.draftEvent));
	const isFetching = useSelector(state => state.ui.isFetchingCalendars);

	/**
	 * Setting event selected routes to the correct `/events/:eventId` path.
	 */
	const setSelected = useCallback(
		eventId => dispatch(setSelectedEvent(eventId)),
		[dispatch]
	);

	const onSelectSlot = ({action, start, end}) => {
		switch(action) {
			case 'select':
				dispatch(setDraftEvent({
					id: DRAFT_EVENT.value,
					type: DRAFT_EVENT.value,
					title: DRAFT_EVENT.text,
					start: toString(start),
					end: toString(end),
				}));
				if(selectedEventId !== 'draft') {
					setSelected('draft');
				}
			break;
			case 'doubleClick':
			case 'click':
				return;
		}
	};

	const moveEvent = ({event, start, end, isAllDay = false}) => {
		if(isAllDay) {
			return false;
		}

		/**
		 * Remove `59` ending when it's not needed.
		 */
		if(end.getMinutes() === 59 && end.getHours() !== 23) {
			end.setMinutes(60);
		}

		/**
		 * Keep event in the same day.
		 */
		if(start.getDate() < end.getDate()) {
			end.setDate(start.getDate());
			end.setHours(23);
			end.setMinutes(59);
		}

		const movedEvent = {
			start: toString(start),
			end: toString(end),
		};

		if(event.id === 'draft') {
			dispatch(updateDraftEvent(movedEvent));
		} else {
			dispatch(updateEvent(calendar.id, event.id, movedEvent));
		}
	};

	const onResizeEvent = e => moveEvent(e);
	const onMoveEvent = e => moveEvent(e);

	/**
	 * Wait to render.
	 */
	if(!calendar || isFetching) {
		return <Segment basic loading padded='very' />;
	}

	return (
		<Measure bounds onResize={({bounds: {width}}) => setWidth(width)}>
			{({measureRef}) => (
				<div ref={measureRef} style={{height: 720}}>
					<BigCalendar
						{...props}
						events={calendar.events.concat(relatedEvents).concat(repeatEvents).concat(draftEvent).filter(withoutBookableEventChildren)}
						onSelectSlot={onSelectSlot}
						onEventResize={props.resizable ? onResizeEvent : undefined}
						onEventDrop={props.resizable ? onMoveEvent : undefined}
					/>
				</div>
			)}
		</Measure>
	);
};
