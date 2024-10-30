import {__} from '@wordpress/i18n';

import {
	takeLatest,
	call,
	put,
	select,
} from 'redux-saga/effects';

import {
	error,
} from 'sagas';

import {
	addEvent,
	updateEvent,
	deleteEvent,
	deleteEventPermanently,
	fetchEvent,
	deletePdf,
	fetchTemplateStrings,
} from 'api';

import {
	eventToPost,
	postToEvent,
	patchEventToPost,
} from 'utils/event';

/**
 * Add event.
 */
export function* watchAddEvent() {
	yield takeLatest('ADD_EVENT', function*({calendarId, event}) {
		try {
			const response = yield call(addEvent, eventToPost({calendarId, ...event}));
			yield put({
				type: 'ADDED_EVENT',
				calendarId,
				event: postToEvent(response),
			});
		} catch(e) {
			yield error(__('Failed creating event', 'booking-weir'), e);
		}
	});
}

/**
 * Update event.
 */
export function* watchUpdateEvent() {
	yield takeLatest('UPDATE_EVENT', function*({calendarId, eventId, event}) {
		try {
			const response = yield call(updateEvent, eventId, patchEventToPost(event));
			yield put({
				type: 'UPDATED_EVENT',
				calendarId,
				event: postToEvent(response),
			});
		} catch(e) {
			yield error(__('Failed updating event', 'booking-weir'), e, {type: 'UPDATE_EVENT', calendarId, eventId, event});
		}
	});
}

/**
 * Strip personal data.
 */
export function* watchStripPersonalData() {
	yield takeLatest('STRIP_PERSONAL_DATA', function*({calendarId, eventId, notes}) {
		try {
			yield call(deletePdf, eventId);
			const response = yield call(updateEvent, eventId, patchEventToPost({
				firstName: '',
				lastName: '',
				email: '',
				phone: '',
				additionalInfo: '',
				notes: [notes, `[${__('Personal data removed', 'booking-weir')} - ${new Date().toLocaleDateString()}]`].join(' ').trim(),
				transactionId: '',
				status: 'archived',
			}));
			yield put({
				type: 'UPDATED_EVENT',
				calendarId,
				event: postToEvent(response),
			});
		} catch(e) {
			yield error(__('Failed removing personal data', 'booking-weir'), e, {type: 'STRIP_PERSONAL_DATA', calendarId, eventId, notes});
		}
	});
}

/**
 * Trash event.
 */
export function* watchDeleteEvent() {
	yield takeLatest('DELETE_EVENT', function*({calendarId, eventId}) {
		try {
			const response = yield call(deleteEvent, eventId);
			if(response.status === 'trash') {
				const statuses = yield select(state => state.query.status);
				if(statuses.includes('trash')) {
					yield put({
						type: 'UPDATED_EVENT',
						calendarId,
						event: postToEvent(response),
					});
				} else {
					yield put({
						type: 'DELETED_EVENT',
						calendarId,
						eventId,
					});
				}
			}
		} catch(e) {
			yield error(__('Failed deleting event', 'booking-weir'), e);
		}
	});
}

/**
 * Delete event.
 */
export function* watchDeleteEventPermanently() {
	yield takeLatest('DELETE_EVENT_PERMANENTLY', function*({calendarId, eventId}) {
		try {
			const response = yield call(deleteEventPermanently, eventId);
			if(response?.deleted && response?.previous?.id) {
				yield put({
					type: 'DELETED_EVENT',
					calendarId,
					eventId: parseInt(response.previous.id),
				});
			}
		} catch(e) {
			yield error(__('Failed deleting event permanently', 'booking-weir'), e);
		}
	});
}

/**
 * Fetch event.
 */
export function* watchFetchEvent() {
	yield takeLatest('FETCH_EVENT', function*({calendarId, eventId}) {
		try {
			yield put({type: 'IS_FETCHING_EVENTS', value: true});
			const response = yield call(fetchEvent, eventId);
			yield put({
				type: 'RECEIVED_EVENT',
				calendarId,
				event: postToEvent(response),
			});
		} catch(e) {
			yield error(__('Failed fetching event', 'booking-weir'), e);
		} finally {
			yield put({type: 'IS_FETCHING_EVENTS', value: false});
		}
	});
}

/**
 * Fetch template strings.
 */
export function* watchFetchTemplateStrings() {
	yield takeLatest('FETCH_TEMPLATE_STRINGS', function*({calendarId}) {
		try {
			const response = yield call(fetchTemplateStrings, calendarId);
			yield put({
				type: 'RECEIVED_TEMPLATE_STRINGS',
				value: response,
			});
		} catch(e) {
			yield error(__('Failed fetching template strings', 'booking-weir'), e, {type: 'FETCH_TEMPLATE_STRINGS', calendarId});
		}
	});
}
