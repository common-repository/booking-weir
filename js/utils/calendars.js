import {
	postsToEvents,
} from 'utils/event';

export const withDefaults = (calendars, isPublic = true) => {
	return Object.keys(calendars).reduce((acc, id) => {
		const {
			events = [],
			prices = [],
			services = [],
			extras = [],
			fields = [],
			paymentTypes = [],
			paymentMethods = [],
			paymentMethodData = [],
			settings = {},
			data = {},
			...rest
		} = calendars[id];

		acc[id] = {
			events: isPublic ? postsToEvents(events) : [],
			prices,
			services,
			extras,
			fields,
			paymentTypes,
			paymentMethods,
			paymentMethodData,
			settings,
			data,
			...rest
		};

		return acc;
	}, {});
};

export const withoutDynamicData = calendars => {
	return Object.keys(calendars).reduce((nextCalendars, id) => {
		const {
			events,
			data,
			local,
			...rest
		} = calendars[id];

		nextCalendars[id] = rest;

		return nextCalendars;
	}, {});
};

export const getRelatedCalendars = (calendarId, calendars) => {
	const parentId = calendars[calendarId].settings.parent;
	return Object.keys(calendars).reduce((related, id) => {
		const isParent = id === parentId;
		const isChild = calendars[id].settings.parent === calendarId;
		if(isParent || isChild) {
			related.push(id);
		}
		return related;
	}, []);
};
