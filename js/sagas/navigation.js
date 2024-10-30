import {__} from '@wordpress/i18n';

import {
	takeLatest,
	put,
	select,
} from 'redux-saga/effects';

import {
	getSelectedEventId,
} from 'utils/params';

/**
 * If received events include active event, navigate to it.
 */
export function* watchReceivedEvents() {
	yield takeLatest('RECEIVED_EVENTS', function*({events}) {
		const location = yield select(state => state.router.location);
		const selectedEventId = getSelectedEventId(location);
		if(selectedEventId) {
			const has = events.filter(({id}) => id === selectedEventId);
			if(has.length > 0) {
				yield put({type: 'NAVIGATE_TO', value: has[0].bw_start});
			}
		}
	});
}

/**
 * If received event is active, navigate to it.
 */
export function* watchReceivedEvent() {
	yield takeLatest('RECEIVED_EVENT', function*({event}) {
		const location = yield select(state => state.router.location);
		const selectedEventId = getSelectedEventId(location);
		if(selectedEventId === event.id) {
			yield put({type: 'NAVIGATE_TO', value: event.start});
		}
	});
}

/**
 * Navigate to event that is marked selected.
 */
export function* watchUseCalendar() {
	yield takeLatest('USE_CALENDAR', function*({calendar: {events}}) {
		const selectedEvent = events.find(({bw_data: {isSelected}}) => !!isSelected);
		if(selectedEvent) {
			yield put({type: 'NAVIGATE_TO', value: selectedEvent.bw_data?.selectedStart || selectedEvent.bw_start});
		}
	});
}
