export const addField = (calendarId, field) => ({
	type: 'ADD_FIELD',
	calendarId,
	field,
});

export const importField = (calendarId, field, overwrite = false) => ({
	type: 'IMPORT_FIELD',
	calendarId,
	field,
	overwrite,
});

export const setFields = (calendarId, fields) => ({
	type: 'SET_FIELDS',
	calendarId,
	fields,
});

export const updateField = (calendarId, fieldId, setting, value) => ({
	type: 'UPDATE_FIELD',
	calendarId,
	fieldId,
	setting,
	value,
});

export const deleteField = (calendarId, fieldId) => ({
	type: 'DELETE_FIELD',
	calendarId,
	fieldId,
});

export const addGridField = (calendarId, gridId, field) => ({
	type: 'ADD_GRID_FIELD',
	calendarId,
	gridId,
	field,
});

export const updateGridField = (calendarId, gridId, fieldId, setting, value) => ({
	type: 'UPDATE_GRID_FIELD',
	calendarId,
	gridId,
	fieldId,
	setting,
	value,
});

export const deleteGridField = (calendarId, gridId, fieldId) => ({
	type: 'DELETE_GRID_FIELD',
	calendarId,
	gridId,
	fieldId,
});
