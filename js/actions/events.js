export const setSelectedEvent = id => ({
	type: 'SET_SELECTED_EVENT',
	id,
});

export const setDraftEvent = event => ({
	type: 'SET_DRAFT_EVENT',
	event,
});

export const updateDraftEvent = event => ({
	type: 'UPDATE_DRAFT_EVENT',
	event,
});

export const addEvent = (calendarId, event) => ({
	type: 'ADD_EVENT',
	calendarId,
	event,
});

export const updateEvent = (calendarId, eventId, event) => ({
	type: 'UPDATE_EVENT',
	calendarId,
	eventId,
	event,
});

export const stripPersonalData = (calendarId, eventId, notes) => ({
	type: 'STRIP_PERSONAL_DATA',
	calendarId,
	eventId,
	notes, // Notes are needed to append text to it.
});

export const deleteEvent = (calendarId, eventId) => ({
	type: 'DELETE_EVENT',
	calendarId,
	eventId,
});

export const deleteEventPermanently = (calendarId, eventId) => ({
	type: 'DELETE_EVENT_PERMANENTLY',
	calendarId,
	eventId,
});

export const fetchEvent = (calendarId, eventId) => ({
	type: 'FETCH_EVENT',
	calendarId,
	eventId,
});
