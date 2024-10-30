export const addPaymentType = (calendarId, name) => ({
	type: 'ADD_PAYMENT_TYPE',
	calendarId,
	name,
});

export const updatePaymentType = (calendarId, typeId, setting, value) => ({
	type: 'UPDATE_PAYMENT_TYPE',
	calendarId,
	typeId,
	setting,
	value,
});

export const deletePaymentType = (calendarId, typeId) => ({
	type: 'DELETE_PAYMENT_TYPE',
	calendarId,
	typeId,
});

export const togglePaymentMethod = (calendarId, method) => ({
	type: 'TOGGLE_PAYMENT_METHOD',
	calendarId,
	method,
});
