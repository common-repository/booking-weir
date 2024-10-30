import {__} from '@wordpress/i18n';

import {
	useState,
	useEffect,
} from 'react';

import EditField from './EditField';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	flattenFields,
} from 'utils/field';

let EditFields;
export default EditFields = ({
	eventId,
	value: initialValue,
	onChange,
}) => {
	const calendar = useCurrentCalendar();
	const [currentValue, setCurrentValue] = useState(initialValue);

	useEffect(() => {
		onChange(null, {value: currentValue});
	}, [currentValue, onChange]);

	return flattenFields(calendar.fields, true).map(field => {
		return (
			<div className='field' key={`${eventId}-${field.id}`}>
				<label htmlFor={`edit-field-${field.id}`}>
					{field.label || __('Field', 'booking-weir')}
				</label>
				<EditField
					id={`edit-field-${field.id}`}
					field={field}
					value={currentValue[field.id]}
					onChange={value => setCurrentValue({...currentValue, [field.id]: value})}
				/>
			</div>
		);
	});
};
