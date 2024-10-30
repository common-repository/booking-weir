import {
	addQueryArgs,
} from '@wordpress/url';

import fetch from './fetch';

const receive = response => response.success ? response.value : response.message;

export async function fetchCalendars() {
	const response = await fetch({
		method: 'GET',
		path: 'calendars',
	});
	return receive(response);
}

export async function getCalendars() {
	const response = await fetch({
		method: 'GET',
		path: 'calendars/get',
	});
	return receive(response);
}

export async function saveCalendars(calendars) {
	const response = await fetch({
		method: 'POST',
		path: 'calendars',
		data: {
			calendars,
		},
	});
	return receive(response);
}

export async function fetchEvents(calendarId, query) {
	const response = await fetch({
		method: 'GET',
		path: addQueryArgs('events', {
			...query,
			...(!!query['bw_filter[meta_query][0][key]'] && {
				'bw_filter[meta_query][relation]': 'OR',
				'bw_filter[meta_query][1][key]': 'bw_repeat',
				'bw_filter[meta_query][1][value]': '1',
			}),
			'bw_filter[meta_value]': calendarId,
		}),
	});
	return response;
}

export async function fetchEvent(eventId) {
	const response = await fetch({
		method: 'GET',
		path: `events/${eventId}`,
	});
	return response;
}

export async function addEvent(event) {
	const response = await fetch({
		method: 'POST',
		path: 'events',
		data: {
			...event,
			status: 'publish',
		},
	});
	return response;
}

export async function updateEvent(eventId, event) {
	const response = await fetch({
		method: 'PATCH',
		path: `events/${eventId}`,
		data: event,
	});
	return response;
}

export async function deleteEvent(eventId) {
	const response = await fetch({
		method: 'DELETE',
		path: `events/${eventId}`,
	});
	return response;
}

export async function deleteEventPermanently(eventId) {
	const response = await fetch({
		method: 'DELETE',
		path: `events/${eventId}?force=true`,
	});
	return response;
}

export async function fetchPriceForBooking(booking) {
	const response = await fetch({
		method: 'POST',
		path: 'price',
		data: {
			id: booking.calendarId,
			start: booking.start,
			end: booking.end,
			extras: booking.extras,
			coupon: booking.coupon,
			serviceId: booking?.service?.id,
			bookableEventId: booking?.bookableEvent?.id,
		},
	});
	return receive(response);
}

export async function fetchTransaction(paymentMethod, transactionId) {
	const response = await fetch({
		method: 'POST',
		path: 'payment/transaction',
		data: {
			paymentMethod,
			transactionId,
		},
	});
	return receive(response);
}

export async function fetchSettingsSchema(calendarId = '') {
	const response = await fetch({
		method: 'GET',
		path: `settings/schema/${calendarId}`,
	});
	return receive(response);
}

export async function testPdf(calendarId) {
	const response = await fetch({
		method: 'POST',
		path: 'pdf/test',
		data: {
			calendarId,
		},
	});
	return receive(response);
}

export async function regeneratePdf(bookingId) {
	const response = await fetch({
		method: 'POST',
		path: 'pdf/regenerate',
		data: {
			bookingId,
		},
	});
	return receive(response);
}

export async function deletePdf(bookingId) {
	const response = await fetch({
		method: 'POST',
		path: 'pdf/delete',
		data: {
			bookingId,
		},
	});
	return receive(response);
}

export async function fetchTestEmail(calendarId, settings, emailType) {
	const response = await fetch({
		method: 'POST',
		path: 'email/test',
		data: {
			calendarId,
			settings,
			type: emailType,
		},
	});
	return receive(response);
}

export async function sendInvoiceEmail(eventId) {
	const response = await fetch({
		method: 'POST',
		path: 'email/invoice',
		data: {
			eventId,
		},
	});
	return receive(response);
}

export async function sendReminderEmail(eventId) {
	const response = await fetch({
		method: 'POST',
		path: 'email/reminder',
		data: {
			eventId,
		},
	});
	return receive(response);
}

export async function fetchTemplateStrings(calendarId) {
	const response = await fetch({
		method: 'GET',
		path: addQueryArgs('event/template-strings', {
			calendarId,
		}),
	});
	return receive(response);
}

export async function fetchEventContent(eventId = '', start = '', end = '') {
	const response = await fetch({
		method: 'POST',
		path: 'event/content',
		data: {
			id: eventId,
			start,
			end,
		},
	});
	return receive(response);
}

export async function fetchServiceDescription(calendarId = '', serviceId = '') {
	const response = await fetch({
		method: 'POST',
		path: 'calendar/service-description',
		data: {
			calendarId,
			id: serviceId,
		},
	});
	return receive(response);
}

export async function deleteFile(eventId = '', fieldId = '', fileName = '') {
	const response = await fetch({
		method: 'POST',
		path: 'event/delete-file',
		data: {
			id: eventId,
			fieldId,
			fileName,
		},
	});
	return receive(response);
}
