import {
	take,
	select,
	put,
} from 'redux-saga/effects';

/**
 * Clear messages before performing an action that may produce a message.
 */
export function* watchMessageActions() {
	while(true) {
		yield take([
			'@@router/LOCATION_CHANGE',
			'UPDATE_BOOKING_MAYBE_ASYNC',
			'UPDATE_BOOKING_DEBOUNCED',
			'REGENERATE_PDF',
			'UPDATE_EVENT',
			'STRIP_PERSONAL_DATA',
			'SAVE_CALENDARS',
			'FETCH_CALENDARS',
			'RELOAD_EVENTS',
			'FETCH_TEMPLATE_STRINGS',
		]);
		const message = yield select(state => state.ui.message);
		if(message.content) {
			yield put({type: 'CLEAR_MESSAGE'});
		}
	}
}
