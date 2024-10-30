import {__} from '@wordpress/i18n';

import {
	put,
	call,
	takeLatest,
} from 'redux-saga/effects';

import {
	success,
	error,
} from 'sagas';

import {
	fetchPriceForBooking,
	testPdf,
	regeneratePdf,
} from 'api';

import {
	clearSelection,
} from 'components/frontend/calendar/clearSelection';

const delay = (ms) => new Promise(res => setTimeout(res, ms));

function* updateBooking({booking}) {
	const {
		calendarId,
		start,
		end,
		price,
	} = booking;

	const nextBooking = {...booking};

	/**
	 * Fetch price if booking has enough data but no price.
	 * Price can be unset when updating a booking to receive a new price.
	 */
	if(calendarId && start && end && !price) {
		yield put({type: 'SET_IS_FETCHING_PRICE', value: true});
		try {
			const {value, breakdown, info} = yield call(fetchPriceForBooking, booking);
			nextBooking.price = value;
			nextBooking.breakdown = breakdown;
			nextBooking.info = info;
		} catch(e) {
			yield error(__('Failed fetching price', 'booking-weir'), e, {type: 'UPDATE_BOOKING_MAYBE_ASYNC', booking});
		} finally {
			yield put({type: 'SET_IS_FETCHING_PRICE', value: false});
		}
	}

	yield put({type: 'UPDATED_BOOKING', booking: nextBooking});
}

function* updateBookingDebounced({booking, debounce = 1000}) {
	yield delay(debounce);
	yield call(updateBooking, {booking});
}

export function* watchBooking() {
	yield takeLatest('UPDATE_BOOKING_MAYBE_ASYNC', updateBooking);
	yield takeLatest('UPDATE_BOOKING_DEBOUNCED', updateBookingDebounced);
}

function* testPdfSaga({calendarId}) {
	yield put({type: 'IS_REGENERATING_PDF', value: true});
	try {
		const response = yield call(testPdf, calendarId);
		if(response.includes('/test.pdf')) {
			yield success(__('Generated test PDF', 'booking-weir'));
			yield put({type: 'TEST_PDF_URL', value: response});
		} else {
			throw new Error(__('Invalid response.', 'booking-weir'));
		}
	} catch(e) {
		yield error(__('Failed generating test PDF', 'booking-weir'), e);
	} finally {
		yield put({type: 'IS_REGENERATING_PDF', value: false});
	}
}
export function* watchTestPdf() {
	yield takeLatest('TEST_PDF', testPdfSaga);
}

function* regeneratePdfSaga({bookingId}) {
	yield put({type: 'IS_REGENERATING_PDF', value: true});
	try {
		const response = yield call(regeneratePdf, bookingId);
		if(response === true) {
			yield success(__('Regenerated PDF', 'booking-weir'));
		} else {
			throw new Error(__('Invalid response.', 'booking-weir'));
		}
	} catch(e) {
		yield error(__('Failed regenerating PDF', 'booking-weir'), e);
	} finally {
		yield put({type: 'IS_REGENERATING_PDF', value: false});
	}
}
export function* watchRegeneratePdf() {
	yield takeLatest('REGENERATE_PDF', regeneratePdfSaga);
}

/**
 * Clear selected event from the calendar when a service is selected.
 */
export function* watchSelectedService() {
	yield takeLatest('SELECT_SERVICE', clearSelection);
}
