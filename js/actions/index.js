export * from './events';
export * from './payment';
export * from './fields';
export * from './extras';
export * from './prices';
export * from './services';

export const fetchCalendars = () => ({
	type: 'FETCH_CALENDARS',
});

export const receiveCalendars = calendars => ({
	type: 'RECEIVED_CALENDARS',
	calendars,
});

export const receiveEvents = (calendarId, events) => ({
	type: 'RECEIVED_EVENTS',
	calendarId,
	events,
});

export const importCalendar = (calendarId, calendar, overwrite = false) => ({
	type: 'IMPORT_CALENDAR',
	calendarId,
	calendar,
	overwrite,
});

export const saveCalendars = () => ({
	type: 'SAVE_CALENDARS',
});

export const savedCalendars = calendars => ({
	type: 'SAVED_CALENDARS',
	calendars,
});

export const requestAddCalendar = calendarName => ({
	type: 'REQUEST_ADD_CALENDAR',
	calendarName,
});

export const addCalendar = (calendarName, settingsSchema) => ({
	type: 'ADD_CALENDAR',
	calendarName,
	settingsSchema,
});

export const duplicateCalendar = calendarId => ({
	type: 'DUPLICATE_CALENDAR',
	calendarId,
});

export const updateCalendarName = (calendarId, name) => ({
	type: 'UPDATE_CALENDAR_NAME',
	calendarId,
	name,
});

export const deleteCalendar = calendarId => ({
	type: 'DELETE_CALENDAR',
	calendarId,
});

export const useCalendar = (id, calendar) => ({
	type: 'USE_CALENDAR',
	id,
	calendar,
});

export const setCurrentCalendar = calendarId => ({
	type: 'SET_CURRENT_CALENDAR',
	calendarId,
});

export const receiveSettingsSchema = (calendarId, settingsSchema) => ({
	type: 'RECEIVED_SETTINGS_SCHEMA',
	calendarId,
	settingsSchema,
});

export const updateSetting = (calendarId, setting, value) => ({
	type: 'UPDATE_SETTING',
	calendarId,
	setting,
	value,
});

export const updateBooking = booking => ({
	type: 'UPDATE_BOOKING_MAYBE_ASYNC',
	booking,
});

export const updateBookingDebounced = (booking, debounce = 1000) => ({
	type: 'UPDATE_BOOKING_DEBOUNCED',
	booking,
	debounce,
});

export const updatedBooking = (booking) => ({
	type: 'UPDATED_BOOKING',
	booking,
});

export const regeneratePdf = bookingId => ({
	type: 'REGENERATE_PDF',
	bookingId,
});

export const arrayMoveUp = (arrayName, calendarId, index, parent = false, parentIndex = 0) => ({
	type: 'ARRAY_MOVE_UP',
	arrayName,
	calendarId,
	index,
	parent,
	parentIndex,
});

export const arrayMoveDown = (arrayName, calendarId, index, parent = false, parentIndex = 0) => ({
	type: 'ARRAY_MOVE_DOWN',
	arrayName,
	calendarId,
	index,
	parent,
	parentIndex,
});
