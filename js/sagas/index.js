import {
	all,
	fork,
	put,
} from 'redux-saga/effects';

import * as calendarsSagas from './calendars';
import * as calendarSagas from './calendar';
import * as eventSagas from './event';
import * as bookingSagas from './booking';
import * as messagesSagas from './messages';
import * as emailSagas from './email';
import * as navigationSagas from './navigation';

export function* success(text) {
	yield put({
		type: 'SET_MESSAGE',
		value: {
			positive: true,
			icon: 'check',
			content: text,
		},
	});
}

export function* error(text, e, retry) {
	console.error(text, e);
	yield put({
		type: 'SET_MESSAGE',
		value: {
			negative: true,
			icon: 'warning circle',
			header: text,
			content: e.message,
			...(retry && {retry}),
		},
	});
}

export default function* rootSaga() {
	yield all(
		[
			...Object.values(calendarsSagas),
			...Object.values(calendarSagas),
			...Object.values(eventSagas),
			...Object.values(bookingSagas),
			...Object.values(messagesSagas),
			...Object.values(emailSagas),
			...Object.values(navigationSagas),
		].map(fork)
	);
}
