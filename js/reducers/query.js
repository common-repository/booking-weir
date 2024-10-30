import EVENT_STATUSES from 'config/EVENT_STATUSES';

const PAST_EVENTS_ENABLED = {
	'bw_filter[meta_query][0][key]': undefined,
	'bw_filter[meta_query][0][value]': undefined,
	'bw_filter[meta_query][0][type]': undefined,
	'bw_filter[meta_query][0][compare]': undefined,
};

const PAST_EVENTS_DISABLED = {
	'bw_filter[meta_query][0][key]': 'bw_start_timestamp',
	'bw_filter[meta_query][0][value]': Math.floor(new Date().getTime() / 1000) - (60 * 60 * 24 * 7 * 1),
	'bw_filter[meta_query][0][type]': 'NUMERIC',
	'bw_filter[meta_query][0][compare]': '>=',
};

const initialState = {
	'orderby': 'id',
	'order': 'asc',
	'bw_filter[meta_key]': 'bw_calendar_id',
	'bw_filter[meta_value]': undefined,
	'status': [
		EVENT_STATUSES[0].value, // publish/public
		// EVENT_STATUSES[1].value, // draft/private
		// EVENT_STATUSES[2].value, // trash
	],
	...PAST_EVENTS_DISABLED
};

const query = (state, action) => {
	if(typeof state === 'undefined') {
		return initialState;
	}

	switch(action.type) {

		case 'SET_QUERY_STATUS':
			return {
				...state,
				status: [...action.value],
			};

		case 'SET_QUERY_PAST_EVENTS_FILTER': {
			const filter = action.value ? PAST_EVENTS_ENABLED : PAST_EVENTS_DISABLED;
			return {
				...state,
				...filter
			};
		}

	}

	return state;
};

export default query;
