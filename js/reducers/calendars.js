import {_x, sprintf} from '@wordpress/i18n';

import produce from 'immer';

import getUniqueId from 'utils/getUniqueId';

import {
	withDefaults,
} from 'utils/calendars';

import {
	getDefaultSettings,
} from 'utils/settings';

import {
	postsToEvents,
} from 'utils/event';

import DEFAULT_FIELDS from 'config/DEFAULT_FIELDS';

const initialState = {};

/**
 * This state is stored in a WP Option.
 */
export default (state, action) => {
	if(typeof state === 'undefined') {
		return initialState;
	}

	return produce(state, draft => {

		switch(action.type) {
			case 'RECEIVED_CALENDARS': {
				draft = withDefaults(action.calendars, false) || initialState;
				break;
			}

			case 'SAVED_CALENDARS': {
				Object.keys(state).forEach(id => {
					if(state[id].local) {
						delete draft[id].local;
					}
				});
				break;
			}

			case 'RECEIVED_EVENTS': {
				const {calendarId, events} = action;
				draft[calendarId].events = postsToEvents(events);
				break;
			}

			case 'USE_CALENDAR': {
				const {id, calendar} = action;
				draft = withDefaults({[id]: calendar});
				break;
			}

			case 'ADD_CALENDAR': {
				draft[getUniqueId()] = {
					name: action.calendarName,
					events: [],
					prices: [],
					services: [],
					extras: [],
					fields: DEFAULT_FIELDS,
					paymentTypes: [],
					paymentMethods: [],
					paymentMethodData: [],
					settings: getDefaultSettings(action.settingsSchema),
					data: {},
					ver: BOOKING_WEIR_VER,
					local: true,
				};
				break;
			}

			case 'DUPLICATE_CALENDAR': {
				draft[getUniqueId()] = {
					...draft[action.calendarId],
					name: draft[action.calendarId].name + sprintf(
						_x(' (copy %s)', 'Suffix for a duplicated calendar, %s = current date', 'booking-weir'),
						new Date().toLocaleDateString()
					),
					events: [],
					local: true,
				};
				break;
			}

			case 'IMPORT_CALENDAR': {
				const {calendarId, calendar, overwrite} = action;
				const id = overwrite ? calendarId : getUniqueId();
				draft[id] = {
					...calendar,
					events: [],
					local: true,
				};
				break;
			}

			case 'UPDATE_CALENDAR_NAME': {
				const {calendarId, name} = action;
				draft[calendarId].name = name;
				break;
			}

			case 'DELETE_CALENDAR': {
				delete draft[action.calendarId];
				break;
			}

			case 'UPDATE_SETTING': {
				const {calendarId, setting, value} = action;
				draft[calendarId].settings[setting] = value;
				break;
			}

			case 'ADDED_EVENT': {
				const {calendarId, event} = action;
				draft[calendarId].events.push(event);
				break;
			}

			case 'RECEIVED_EVENT': {
				const {calendarId, event} = action;
				draft[calendarId].events.push(event);
				break;
			}

			case 'UPDATED_EVENT': {
				const {calendarId, event} = action;
				const eventId = event.id;
				const eventIndex = draft[calendarId].events.findIndex(event => event.id === eventId);
				draft[calendarId].events[eventIndex] = event;
				break;
			}

			case 'DELETED_EVENT': {
				const {calendarId, eventId} = action;
				draft[calendarId].events.splice(draft[calendarId].events.findIndex(event => event.id === eventId), 1);
				break;
			}
















			case 'SET_FIELDS': {
				let {calendarId, fields} = action;
				draft[calendarId].fields = fields;
				break;
			}

			case 'UPDATE_FIELD': {
				let {calendarId, fieldId, setting, value} = action;
				const index = draft[calendarId].fields.findIndex(field => field.id === fieldId);
				draft[calendarId].fields[index][setting] = value;
				break;
			}



			case 'UPDATE_GRID_FIELD': {
				let {calendarId, gridId, fieldId, setting, value} = action;
				const gridIndex = draft[calendarId].fields.findIndex(grid => grid.id === gridId);
				const fieldIndex = draft[calendarId].fields[gridIndex].fields.findIndex(rule => rule.id === fieldId);
				draft[calendarId].fields[gridIndex].fields[fieldIndex][setting] = value;
				break;
			}


			case 'ADD_PAYMENT_TYPE': {
				let {calendarId, name} = action;
				draft[calendarId].paymentTypes.push({
					id: getUniqueId(),
					name,
					amount: 100,
					enabled: true,
				});
				break;
			}

			case 'UPDATE_PAYMENT_TYPE': {
				let {calendarId, typeId, setting, value} = action;
				const index = draft[calendarId].paymentTypes.findIndex(paymentType => paymentType.id === typeId);
				draft[calendarId].paymentTypes[index][setting] = value;
				break;
			}

			case 'DELETE_PAYMENT_TYPE': {
				const {calendarId, typeId} = action;
				draft[calendarId].paymentTypes.splice(draft[calendarId].paymentTypes.findIndex(paymentType => paymentType.id === typeId), 1);
				break;
			}

			case 'TOGGLE_PAYMENT_METHOD': {
				const {calendarId, method} = action;
				const currentMethods = draft[calendarId].paymentMethods;
				if(currentMethods.includes(method)) {
					draft[calendarId].paymentMethods.splice(draft[calendarId].paymentMethods.findIndex(paymentMethod => paymentMethod === method), 1);
				} else {
					draft[calendarId].paymentMethods.push(method);
				}
				break;
			}

			case 'UPDATE_PAYMENT_METHOD_DATA': {
				const {calendarId, paymentMethodId, optionId, value} = action;
				const currentData = draft[calendarId].paymentMethodData;
				const currentMethodData = currentData.find(({id}) => id === paymentMethodId) || {id: paymentMethodId};
				const nextMethodData = {
					...currentMethodData,
					[optionId]: value,
				};
				const nextData = currentData.filter(({id}) => id !== paymentMethodId);
				draft[calendarId].paymentMethodData = nextData.concat(nextMethodData);
				break;
			}

			case 'ARRAY_MOVE_UP': {
				const {arrayName, calendarId, index, parent, parentIndex} = action;
				const array = parent
					? draft[calendarId][parent][parentIndex][arrayName]
					: draft[calendarId][arrayName];
				const element = array[index]; // Store element that is being moved.
				array.splice(index, 1); // Remove element that is being moved from it's current index.
				array.splice(index - 1, 0, element); // Insert stored element to a lesser index.
				parent // Replace current array with modified array.
					? (draft[calendarId][parent][parentIndex][arrayName] = array)
					: (draft[calendarId][arrayName] = array);
				break;
			}

			case 'ARRAY_MOVE_DOWN': {
				const {arrayName, calendarId, index, parent, parentIndex} = action;
				const array = parent
					? draft[calendarId][parent][parentIndex][arrayName]
					: draft[calendarId][arrayName];
				const element = array[index]; // Store element that is being moved.
				array.splice(index, 1); // Remove element that is being moved from it's current index.
				array.splice(index + 1, 0, element); // Insert stored element to a greater index.
				parent // Replace current array with modified array.
					? (draft[calendarId][parent][parentIndex][arrayName] = array)
					: (draft[calendarId][arrayName] = array);
				break;
			}

		}

		return draft;

	});
};
