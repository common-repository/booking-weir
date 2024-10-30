import {__, _x} from '@wordpress/i18n';

import EVENT_SCHEMA from 'config/EVENT_SCHEMA';
import EVENT_TYPES from 'config/EVENT_TYPES';

const getDefault = type => {
	switch(type) {
		case 'string':
			return '';
		case 'number':
		case 'integer':
			return 0;
		case 'boolean':
			return false;
		case 'object':
			return {};
		case 'array':
			return [];
	}
	return undefined;
};

export const eventToPost = event => {
	return EVENT_SCHEMA.reduce((acc, {key, name, type, show_in_rest}) => {
		if(show_in_rest) {
			acc[key] = event[name] || getDefault(type);
		}
		return acc;
	}, {});
};

/**
 * @param {*} post `bw_event` from REST response or `{id, title, bw_type, bw_start, bw_end}` for front end.
 */
export const postToEvent = post => {
	const event = EVENT_SCHEMA.reduce((acc, {key, name, type}) => {
		let value = post[key] || getDefault(type);
		switch(name) {
			case 'title':
				value = value?.raw || value?.rendered || value || '';
			break;
			case 'excerpt':
				value = value?.raw || value?.rendered || '';
			break;
		}
		switch(type) {
			case 'integer':
				value = parseInt(value);
			break;
			case 'number':
				value = parseFloat(value);
			break;
		}
		acc[name] = value;
		return acc;
	}, {});

	/**
	 * Back end posts have `guid`.
	 */
	if(post.guid) {
		event.data = getEventData(event, post);
		/**
		 * Include the original post in the back end.
		 */
		delete post._links; // Useless
		event.post = post;
	} else {
		event.data = {
			...post.bw_data, // Dynamic data for front end.
			color: EVENT_TYPES.find(t => t.value === event.type)?.color || 'black',
		};
	}

	return event;
};

export const postsToEvents = posts => {
	return posts.map(post => postToEvent(post)).filter(event => {
		if(!event.start || !event.end) {
			console.error('Invalid event', event);
			return false;
		}
		return true;
	});
};

export const patchEventToPost = event => {
	const keys = Object.keys(event);
	return EVENT_SCHEMA.reduce((acc, {key, name, type}) => {
		if(keys.includes(name)) {
			delete acc[name];
			acc[key] = event[name];
		}
		return acc;
	}, {...event});
};

export const getEventData = (event, post = {}) => {
	const {
		type,
		title,
		status,
		orderId,
	} = event;

	const {
		status: post_status,
		bw_data,
	} = post;

	const typeOrStatus = post_status ? (post_status === 'publish' ? type : `_${post_status}`) : type;

	let titlePrefix = '';
	switch(post_status) {
		case 'trash':
			titlePrefix = __('Trashed', 'booking-weir') + ': ';
		break;
		case 'draft':
			titlePrefix = __('Private', 'booking-weir') + ': ';
		break;
	}

	return {
		color: EVENT_TYPES.find(t => t.value === typeOrStatus)?.color || 'black',
		titlePrefix,
		titleText: (
			(type === 'default' && title)
			|| (type === 'slot' && title !== _x('Slot', 'Slot event public title', 'booking-weir'))
		) ? title : (EVENT_TYPES.find(t => t.value === type)?.text || EVENT_TYPES.find(t => t.value === 'default').text),
		isWC: ['cart', 'detached', 'wc'].includes(status),
		...bw_data
	};
};

export const withEventData = event => {
	if(!event.id) {
		return {};
	}
	return {
		...event,
		data: getEventData(event),
	};
};

/**
 * Is event selected with front end data?
 * Repeat events should have start time specified.
 */
export const isSelected = event => {
	return !!event?.data?.isSelected && (!event?.data?.selectedStart || event.data.selectedStart === event.start);
};
