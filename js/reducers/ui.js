const initialState = {
	currentCalendarId: undefined,
	selectedEvent: {},
	selectedService: undefined,
	draftEvent: {},
	price: undefined,
	booking: {},
	bookingModalStep: 0,
	isFetchingPrice: false,
	editMode: false,
	isRegeneratingPdf: false,
	isFetchingCalendars: true, // Not really true, but should be fetched asap.
	isFetchingEvents: false,
	isSavingCalendars: false,
	calendarsDirty: false,
	eventDirty: false,
	date: undefined, // Current calendar date
	view: 'week', // Current calendar view
	range: {}, // Current calendar view's range
	message: {},
	toast: '',
	testPdf: '',
	transactionView: {},
	templateStrings: {},
	undo: 0,
	redo: 0,
	navigateTo: undefined, // Set to date to request calendar navigation
	eventLinkModalOpen: false,
};

const ui = (state, action) => {
	if(typeof state === 'undefined') {
		return initialState;
	}

	switch(action.type) {

		case 'SET_CURRENT_CALENDAR':
			return {
				...state,
				currentCalendarId: action.calendarId,
			};

		case 'SET_DRAFT_EVENT':
			return {
				...state,
				draftEvent: action.event,
			};

		case 'UPDATE_DRAFT_EVENT':
			return {
				...state,
				draftEvent: {
					...state.draftEvent,
					...action.event
				},
			};

		case 'ADD_EVENT': // Clear draft when an event is created.
			return {
				...state,
				draftEvent: {},
			};

		case 'UPDATED_BOOKING':
			return {
				...state,
				booking: action.booking,
			};

		case 'RESET_BOOKING_FIELD': {
			const fields = state.booking?.fields || {};

			const {
				[action.id]: reset,
				...nextFields
			} = fields;

			const nextBooking = {
				...state.booking,
				fields: nextFields,
			};

			return {
				...state,
				booking: nextBooking,
			};
		}

		case 'SET_BOOKING_MODAL_STEP':
			return {
				...state,
				bookingModalStep: action.step,
			};

		case 'BOOKING_MODAL_NEXT_STEP':
			return {
				...state,
				bookingModalStep: state.bookingModalStep + 1,
			};

		case 'BOOKING_MODAL_PREV_STEP':
			return {
				...state,
				bookingModalStep: state.bookingModalStep - 1,
			};

		case 'SET_IS_FETCHING_PRICE':
			return {
				...state,
				isFetchingPrice: action.value,
			};

		case 'SET_EDIT_MODE':
			return {
				...state,
				editMode: action.value,
			};

		case 'IS_REGENERATING_PDF': {
			return {
				...state,
				isRegeneratingPdf: action.value,
			};
		}

		case 'IS_FETCHING_CALENDARS':
			return {
				...state,
				isFetchingCalendars: action.value,
			};

		case 'IS_FETCHING_EVENTS':
			return {
				...state,
				isFetchingEvents: action.value,
			};

		case 'IS_SAVING_CALENDARS':
			return {
				...state,
				isSavingCalendars: action.value,
			};

		case 'SET_CALENDARS_DIRTY':
			return {
				...state,
				calendarsDirty: action.value,
				undo: action.value ? state.undo : 0,
				redo: action.value ? state.redo : 0,
			};

		case 'SET_EVENT_DIRTY':
			return {
				...state,
				eventDirty: action.value,
			};

		case 'UPDATED_EVENT':
			return {
				...state,
				eventDirty: false,
			};

		case 'DID_UNDOABLE_ACTION':
			return {
				...state,
				undo: state.undo + 1,
				redo: 0,
			};

		case '@@redux-undo/REDO':
			return {
				...state,
				undo: state.undo + 1,
				redo: state.redo - 1,
			};

		case '@@redux-undo/UNDO':
			return {
				...state,
				undo: state.undo - 1,
				redo: state.redo + 1,
			};

		case 'SET_DATE':
			return {
				...state,
				date: action.value,
			};

		case 'SET_VIEW':
			return {
				...state,
				view: action.value,
			};

		case 'SET_RANGE':
			return {
				...state,
				range: action.value,
			};

		case 'SET_MESSAGE':
			return {
				...state,
				message: action.value,
			};

		case 'SET_ERROR':
			return {
				...state,
				message: {
					negative: true,
					icon: 'warning circle',
					content: action.value,
				},
			};

		case 'CLEAR_MESSAGE':
			return {
				...state,
				message: {},
			};

		case 'SET_TOAST':
			return {
				...state,
				toast: action.value,
			};

		case 'CLEAR_TOAST':
			return {
				...state,
				toast: '',
			};

		case 'TEST_PDF_URL':
			return {
				...state,
				testPdf: action.value,
			};

		case 'SET_TRANSACTION_VIEW':
			return {
				...state,
				transactionView: action.value,
			};

		case 'RECEIVED_TEMPLATE_STRINGS':
			return {
				...state,
				templateStrings: action.value,
			};

		case 'NAVIGATE_TO':
			return {
				...state,
				navigateTo: action.value,
			};

		case 'NAVIGATED_TO':
			return {
				...state,
				navigateTo: undefined,
			};

		case 'SELECT_SERVICE':
			return {
				...state,
				selectedService: action.value,
			};

		case 'EVENT_LINK_MODAL_OPEN':
			return {
				...state,
				eventLinkModalOpen: action.value,
			};

	}

	return state;
};

export default ui;
