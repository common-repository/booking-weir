import {__} from '@wordpress/i18n';

import {
	take,
	takeLatest,
	select,
	call,
	put,
	all,
} from 'redux-saga/effects';

import {
	push,
} from 'redux-first-history';

import {
	error,
} from 'sagas';

import {
	fetchSettingsSchema,
	fetchEvents,
} from 'api';

import {
	receiveSettingsSchema,
	receiveEvents,
	addCalendar,
} from 'actions';

import {
	getCurrentCalendarId,
	getCurrentPage,
	getSelectedEventId,
} from 'utils/params';

import {
	getRelatedCalendars,
} from 'utils/calendars';

/**
 * Load calendar settings schema.
 */
function* loadSettingsSchema(calendarId) {
	try {
		const settingsSchema = yield call(fetchSettingsSchema, calendarId);
		yield put(receiveSettingsSchema(calendarId, settingsSchema));
	} catch(e) {
		yield error(__('Failed fetching settings schema', 'booking-weir'), e);
	}
}

/**
 * Load calendar events.
 */
function* loadEvents(calendarId) {
	const query = yield select(state => state.query);
	try {
		const loaded = yield select(state => state.calendar.eventsLoaded.get(calendarId));
		if(loaded) {
			yield put(receiveEvents(calendarId, [])); // Clear previously loaded events.
		}
		const events = yield call(fetchEvents, calendarId, query);
		yield put(receiveEvents(calendarId, events));
	} catch(e) {
		yield error(__('Failed fetching events', 'booking-weir'), e, {type: 'RELOAD_EVENTS', calendarId});
	}
}

/**
 * Load events for a calendar and it's related calendars, if they haven't been loaded yet.
 */
function* loadCalendarsEvents(calendarId) {
	const calendars = yield select(state => state.calendars.present);
	const calendarsToLoad = [calendarId].concat(getRelatedCalendars(calendarId, calendars));
	const eventsLoadedStates = yield all(calendarsToLoad.map(id => select(state => !!state.calendar.eventsLoaded.get(id))));
	if(!eventsLoadedStates.includes(false)) {
		return;
	}
	yield put({type: 'IS_FETCHING_EVENTS', value: true});
	yield all(eventsLoadedStates.map((loaded, index) => !!loaded || call(loadEvents, calendarsToLoad[index])));
	yield put({type: 'IS_FETCHING_EVENTS', value: false});
}

/**
 * Reload events for a calendar and it's related calendars.
 */
function* reloadCalendarsEvents(calendarId) {
	if(!calendarId?.length) {
		const location = yield select(state => state.router.location);
		calendarId = getCurrentCalendarId(location);
	}
	if(!calendarId) {
		return;
	}
	const calendars = yield select(state => state.calendars.present);
	const calendarsToLoad = [calendarId].concat(getRelatedCalendars(calendarId, calendars));
	yield put({type: 'IS_FETCHING_EVENTS', value: true});
	yield all(calendarsToLoad.map(id => call(loadEvents, id)));
	yield put({type: 'IS_FETCHING_EVENTS', value: false});
}

/**
 * Trigger reload events/use different query for the current calendar and it's related calendars.
 */
export function* watchCurrentCalendar() {
	yield takeLatest('RELOAD_EVENTS', reloadCalendarsEvents);
}

function* addCalendarWithDefaultSettingsSchema({calendarName}) {
	try {
		const settingsSchema = yield call(fetchSettingsSchema);
		yield put(addCalendar(calendarName, settingsSchema));
	} catch(e) {
		yield error(__('Failed fetching settings schema', 'booking-weir'), e);
	}
}
export function* watchAddCalendar() {
	yield takeLatest('REQUEST_ADD_CALENDAR', addCalendarWithDefaultSettingsSchema);
}

function* routeToSelectedEvent({id}) {
	const location = yield select(state => state.router.location);
	const calendarId = getCurrentCalendarId(location);
	const selectedEventId = getSelectedEventId(location);
	if(!id || id === -1 || id === selectedEventId) {
		yield put(push(`/${calendarId}/events`));
	} else {
		yield put(push(`/${calendarId}/events/${id}`));
	}
}
export function* watchSelectedEvent() {
	yield takeLatest('SET_SELECTED_EVENT', routeToSelectedEvent);
}
export function* watchRoute() {
	yield takeLatest('@@router/LOCATION_CHANGE', function*({payload: {location}}) {
		const calendarId = getCurrentCalendarId(location);
		const page = getCurrentPage(location);
		if(calendarId) {
			switch(page) {
				/**
				 * Load calendar's (and related) events.
				 */
				case 'events': {
					const calendarLoaded = yield select(state => state.calendars.present[calendarId]);
					if(!calendarLoaded) {
						yield take('RECEIVED_CALENDARS'); // Wait for calendars to load before adding events to it.
					}
					yield call(loadCalendarsEvents, calendarId);
					break;
				}
				/**
				 * Load calendar's settings schema.
				 */
				case 'settings': {
					const settingsSchema = yield select(state => state.calendar.settingsSchemas.get(calendarId));
					if(!settingsSchema) {
						yield call(loadSettingsSchema, calendarId);
					}
					break;
				}
			}
		}
	});
}
