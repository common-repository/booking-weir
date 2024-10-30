import {__} from '@wordpress/i18n';

import {
	take,
	takeLatest,
	select,
	call,
	put,
} from 'redux-saga/effects';

import {
	error,
} from 'sagas';

import {
	withoutDynamicData,
} from 'utils/calendars';

import {
	fetchCalendars,
	saveCalendars,
} from 'api';

import {
	receiveCalendars,
	savedCalendars,
} from 'actions';

import {ActionCreators} from 'redux-undo';

/**
 * Load calendars from WP option.
 */
function* loadCalendars() {
	try {
		yield put({type: 'IS_FETCHING_CALENDARS', value: true});
		const calendars = yield call(fetchCalendars);
		yield put(receiveCalendars(calendars));
	} catch(e) {
		yield error(__('Failed fetching calendars', 'booking-weir'), e, {type: 'FETCH_CALENDARS'});
	} finally {
		yield put({type: 'IS_FETCHING_CALENDARS', value: false});
	}
}
export function* watchFetchCalendars() {
	yield takeLatest('FETCH_CALENDARS', loadCalendars);
}

/**
 * Save calendars to WP option.
 */
function* saveCalendarsSaga() {
	try {
		yield put({type: 'IS_SAVING_CALENDARS', value: true});
		const calendars = withoutDynamicData(yield select(state => state.calendars.present));
		const returnedCalendars = yield call(saveCalendars, calendars);
		yield put(savedCalendars(returnedCalendars));
	} catch(e) {
		yield error(__('Failed saving calendars', 'booking-weir'), e, {type: 'SAVE_CALENDARS'});
	} finally {
		yield put({type: 'IS_SAVING_CALENDARS', value: false});
	}
}
export function* watchSaveCalendars() {
	yield takeLatest('SAVE_CALENDARS', saveCalendarsSaga);
}

const UNDOABLE_ACTIONS = [
	'ADD_CALENDAR',
	'IMPORTED_CALENDARS',
	'DUPLICATE_CALENDAR',
	'UPDATE_CALENDAR_NAME',
	'DELETE_CALENDAR',
	'UPDATE_SETTING',
	'ADD_EXTRA',
	'UPDATE_EXTRA',
	'DELETE_EXTRA',
	'ADD_SERVICE',
	'UPDATE_SERVICE',
	'DELETE_SERVICE',
	'ADD_PRICE',
	'IMPORT_PRICE',
	'UPDATE_PRICE',
	'DELETE_PRICE',
	'ADD_PRICE_RULE',
	'UPDATE_PRICE_RULE',
	'DELETE_PRICE_RULE',
	'ADD_FIELD',
	'IMPORT_FIELD',
	'SET_FIELDS',
	'UPDATE_FIELD',
	'DELETE_FIELD',
	'ADD_GRID_FIELD',
	'UPDATE_GRID_FIELD',
	'DELETE_GRID_FIELD',
	'ADD_PAYMENT_TYPE',
	'UPDATE_PAYMENT_TYPE',
	'DELETE_PAYMENT_TYPE',
	'TOGGLE_PAYMENT_METHOD',
	'UPDATE_PAYMENT_METHOD_DATA',
	'ARRAY_MOVE_UP',
	'ARRAY_MOVE_DOWN',
];

/**
 * Prevent undo to state without calendars.
 */
export function* watchCalendarsInit() {
	while(true) {
		yield take('RECEIVED_CALENDARS');
		yield put(ActionCreators.clearHistory());
	}
}
/**
 * Set calendars dirty when an undoable action was performed.
 * Upon saving calendars set them not dirty.
 */
export function* watchCalendarModifications() {
	while(true) {
		yield take(UNDOABLE_ACTIONS);
		yield put({type: 'SET_CALENDARS_DIRTY', value: true});
		yield take('SAVED_CALENDARS');
		yield put({type: 'SET_CALENDARS_DIRTY', value: false});
	}
}
/**
 * Increment available undo count when an undoable action was performed.
 */
export function* watchUndoableActions() {
	while(true) {
		yield take(UNDOABLE_ACTIONS);
		yield put({type: 'DID_UNDOABLE_ACTION'});
	}
}
