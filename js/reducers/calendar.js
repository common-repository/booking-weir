import produce, {enableMapSet} from 'immer';
enableMapSet();

const initialState = {
	staticCalendarId: false,
	eventsLoaded: new Map(),
	settingsSchemas: new Map(),
};

const calendar = (state, action) => {
	if(typeof state === 'undefined') {
		return initialState;
	}

	return produce(state, draft => {

		switch(action.type) {

			case 'USE_CALENDAR': {
				const {id} = action;
				draft.staticCalendarId = id;
				break;
			}

			case 'RECEIVED_EVENTS': {
				const {calendarId} = action;
				draft.eventsLoaded.set(calendarId, true);
				break;
			}

			case 'RECEIVED_SETTINGS_SCHEMA': {
				const {calendarId, settingsSchema} = action;
				draft.settingsSchemas.set(calendarId, settingsSchema);
				break;
			}
		}

		return draft;
	});
};

export default calendar;
