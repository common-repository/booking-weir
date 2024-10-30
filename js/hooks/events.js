import {
	useSelector,
} from 'react-redux';

import {
	getEventData,
} from 'utils/event';

import {
	getCalendarUTCOffset,
} from 'utils/date';

import {
	getSelectedEventId,
} from 'utils/params';

import {
	useCurrentCalendar,
} from 'hooks';

export function useEvent(eventId) {
	const {events} = useCurrentCalendar();
	return events[events.findIndex(event => event.id === eventId)];
}

export function useSelectedEventId() {
	const location = useSelector(state => state.router.location);
	return getSelectedEventId(location);
}

export function useSelectedEvent() {
	const {events, settings} = useCurrentCalendar();
	const draft = useSelector(state => state.ui.draftEvent);
	const id = useSelectedEventId();
	if(id === -1) {
		return {};
	}
	if(id === 'draft') {
		if(!draft.id) {
			return {};
		}
		const draftEvent = {
			...draft,
			utcOffset: getCalendarUTCOffset(settings),
		};
		draftEvent.data = getEventData(draftEvent);
		return draftEvent;
	}
	return events.find(event => event.id === id) || {};
}
