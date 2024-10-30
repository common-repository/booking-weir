import {__} from '@wordpress/i18n';

import {
	useSelector,
} from 'react-redux';

import {
	Select,
} from 'semantic-ui-react';

import {
	useCurrentCalendarId,
} from 'hooks';

let CalendarSelect;
export default CalendarSelect = ({value, onChange}) => {
	const currentCalendarId = useCurrentCalendarId();
	const calendars = useSelector(state => state.calendars.present);
	const options = Object.keys(calendars).filter(id => id !== currentCalendarId).map(id => ({
		key: id,
		text: calendars[id].name,
		value: id,
	}));

	return (
		<Select
			search
			placeholder={__('Select calendar...', 'booking-weir')}
			options={options}
			value={value}
			clearable={true}
			selectOnBlur={false}
			selectOnNavigation={false}
			onChange={(e, {value}) => onChange(value)}
		/>
	);
};
