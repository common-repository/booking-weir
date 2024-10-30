export const addService = (calendarId, service) => ({
	type: 'ADD_SERVICE',
	calendarId,
	service,
});

export const updateService = (calendarId, serviceId, setting, value) => ({
	type: 'UPDATE_SERVICE',
	calendarId,
	serviceId,
	setting,
	value,
});

export const deleteService = (calendarId, serviceId) => ({
	type: 'DELETE_SERVICE',
	calendarId,
	serviceId,
});
