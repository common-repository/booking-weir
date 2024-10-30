import {__} from '@wordpress/i18n';

import {
	takeLatest,
	call,
	put,
} from 'redux-saga/effects';

import {
	success,
	error,
} from 'sagas';

import {
	sendInvoiceEmail,
	sendReminderEmail,
} from 'api';

import {
	updateEvent,
} from 'actions';

/**
 * Send invoice e-mail.
 */
export function* watchSendInvoiceEmail() {
	yield takeLatest('SEND_INVOICE_EMAIL', function*({calendarId, eventId}) {
		try {
			const response = yield call(sendInvoiceEmail, eventId);
			if(response === true) {
				yield put(updateEvent(calendarId, eventId, {invoiceEmailSent: true}));
				yield success(__('Sent invoice e-mail', 'booking-weir'));
			} else {
				throw new Error(__('Invalid response.', 'booking-weir'));
			}
		} catch(e) {
			yield error(__('Failed sending invoice e-mail', 'booking-weir'), e);
		}
	});
}

/**
 * Send reminder e-mail.
 */
export function* watchSendReminderEmail() {
	yield takeLatest('SEND_REMINDER_EMAIL', function*({calendarId, eventId}) {
		try {
			const response = yield call(sendReminderEmail, eventId);
			if(response === true) {
				yield put(updateEvent(calendarId, eventId, {reminderEmailSent: true}));
				yield success(__('Sent reminder e-mail', 'booking-weir'));
			} else {
				throw new Error(__('Invalid response.', 'booking-weir'));
			}
		} catch(e) {
			yield error(__('Failed sending reminder e-mail', 'booking-weir'), e);
		}
	});
}
