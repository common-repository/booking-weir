import {
	matchPath,
} from 'react-router-dom';

export function getCurrentCalendarId(location) {
	if(!location || !location.pathname) {
		return null;
	}
	const match = matchPath({path: ':calendarId/*'}, location.pathname);
	return match?.params?.calendarId;
}

export function getCurrentPage(location) {
	if(!location || !location.pathname) {
		return null;
	}
	const match = matchPath({path: ':calendarId/:page/*'}, location.pathname);
	return match?.params?.page;
}

export function getSelectedEventId(location) {
	if(!location || !location.pathname) {
		return null;
	}
	const match = matchPath({path: ':calendarId/events/:eventId/*'}, location.pathname);
	const id = match?.params?.eventId;
	if(!id) {
		return -1;
	}
	if(id === 'draft') {
		return id;
	}
	return parseInt(id);
}
