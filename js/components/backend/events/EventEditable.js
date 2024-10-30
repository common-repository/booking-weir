import {__} from '@wordpress/i18n';

import {
	useState,
	useEffect,
	useCallback,
} from 'react';

import {
	useDispatch,
	useSelector,
} from 'react-redux';

import {
	Select,
	Input,
	Checkbox,
	TextArea,
	List,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
	useDebounce,
} from 'hooks';

import {
	updateDraftEvent,
	updateEvent,
} from 'actions';

import DateTimeEdit from 'components/backend/controls/DateTimeEdit';
import SelectExtras from 'components/backend/extras/SelectExtras';
import EditFields from 'components/backend/fields/EditFields';
import BookableEventEdit from 'components/backend/events/BookableEventEdit';
import Repeater from 'components/backend/events/Repeater';
import ServiceSelect from 'components/backend/services/ServiceSelect';
import PaymentMethodEdit from 'components/backend/events/booking/PaymentMethodEdit';
import PaymentTypeEdit from 'components/backend/events/booking/PaymentTypeEdit';
import Editor from 'components/backend/editor';

let EventEditable;
export default EventEditable = ({eventId, value: originalValue, schema}) => {
	const dispatch = useDispatch();
	const {id: calendarId, settings} = useCurrentCalendar();
	const {step} = settings;
	const [hasPendingChanges, setHasPendingChanges] = useState(false);
	const [initialValue, setInitialValue] = useState(originalValue);
	const [currentValue, setCurrentValue] = useState(originalValue);
	const debouncedValue = useDebounce(currentValue, 1000);
	const dirty = useSelector(state => state.ui.eventDirty);

	const {
		key,
		label,
		type,
		options,
	} = schema;

	useEffect(() => {
		if(!dirty && hasPendingChanges) {
			dispatch({type: 'SET_EVENT_DIRTY', value: true});
		}
	}, [dirty, hasPendingChanges, dispatch]);

	/**
	 * Change the value and mark for pending changes only if the value changed.
	 */
	const onChange = useCallback((e, {value}) => {
		if(JSON.stringify(currentValue) !== JSON.stringify(value)) {
			setCurrentValue(value);
			setHasPendingChanges(true);
		}
	}, [currentValue]);

	/**
	 * Watch for the `value` change that is passed down with props (`originalValue`).
	 * If the value is changed somewhere else in the app, reflect the value in the
	 * editable without setting pending changes to true.
	 * For example `start` and `end` can be changed by drag and drop in the calendar.
	 */
	useEffect(() => {
		if(hasPendingChanges) {
			return;
		}
		if(JSON.stringify(initialValue) !== JSON.stringify(originalValue)) {
			setCurrentValue(originalValue);
			setInitialValue(originalValue);
		}
	}, [hasPendingChanges, initialValue, originalValue]);

	/**
	 * Update the value.
	 */
	useEffect(() => {
		if(
			!hasPendingChanges
			|| type === 'disabled'
			|| JSON.stringify(debouncedValue) === JSON.stringify(originalValue)
		) {
			return;
		}
		if(eventId === 'draft') {
			dispatch(updateDraftEvent({[key]: debouncedValue}));
		} else {
			dispatch(updateEvent(calendarId, eventId, {[key]: debouncedValue}));
		}
		setHasPendingChanges(false);
	}, [dispatch, hasPendingChanges, type, calendarId, eventId, key, debouncedValue, originalValue]);

	/**
	 * Render the control.
	 */
	switch(type) {

		case 'datetime': {
			return (
				<DateTimeEdit
					key={eventId}
					id={`${eventId}-${key}`}
					eventId={eventId}
					calendarId={calendarId}
					value={currentValue}
					onChange={onChange}
					step={step}
				/>
			);
		}

		case 'serviceId': {
			return (
				<ServiceSelect
					key={eventId}
					value={currentValue}
					onChange={onChange}
				/>
			);
		}

		case 'extras': {
			return (
				<List relaxed className='marginless'>
					<SelectExtras
						key={eventId}
						eventId={eventId}
						onChange={onChange}
					/>
				</List>
			);
		}

		case 'fields': {
			return (
				<EditFields
					key={JSON.stringify({eventId: initialValue})}
					eventId={eventId}
					value={currentValue}
					onChange={onChange}
				/>
			);
		}

		case 'select':
			return (
				<Select
					id={`${eventId}-${key}`}
					placeholder={label}
					options={options}
					value={currentValue || options[0].value}
					onChange={onChange}
				/>
			);

		case 'disabled':
			return (
				<input
					id={`${eventId}-${key}`}
					disabled
					value={originalValue || ''}
					style={{
						opacity: 0.8,
						pointerEvents: 'auto',
						cursor: 'no-drop',
					}}
				/>
			);

		case 'text':
			return (
				<Input
					id={`${eventId}-${key}`}
					value={currentValue || ''}
					onChange={onChange}
				/>
			);

		case 'toggle':
			return (
				<Checkbox
					id={`${eventId}-${key}`}
					toggle
					checked={!!currentValue}
					onChange={() => {
						setCurrentValue(!currentValue);
						setHasPendingChanges(true);
					}}
				/>
			);

		case 'email':
			return (
				<Input
					id={`${eventId}-${key}`}
					type='email'
					value={currentValue || ''}
					onChange={onChange}
				/>
			);

		case 'textarea':
			return (
				<TextArea
					id={`${eventId}-${key}`}
					value={currentValue || ''}
					onChange={onChange}
				/>
			);

		case 'paymentType': {
			return (
				<PaymentTypeEdit
					id={`${eventId}-${key}`}
					placeholder={label}
					value={currentValue}
					onChange={onChange}
				/>
			);
		}

		case 'paymentMethod': {
			return (
				<PaymentMethodEdit
					id={`${eventId}-${key}`}
					placeholder={label}
					value={currentValue}
					onChange={onChange}
				/>
			);
		}

		case 'booking': {
			return (
				<BookableEventEdit
					key={eventId}
					eventId={eventId}
					onChange={onChange}
				/>
			);
		}

		case 'repeater': {
			return (
				<Repeater
					key={eventId}
					eventId={eventId}
					onChange={onChange}
				/>
			);
		}

		case 'editor': {
			return (
				<Editor
					label={label}
					value={currentValue}
					onChange={e => onChange(e, {value: e.target.value})}
				/>
			);
		}

		default:
			return JSON.stringify(currentValue);

	}
};
