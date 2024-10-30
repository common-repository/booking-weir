import {__} from '@wordpress/i18n';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	Link,
} from 'react-router-dom';

import {
	saveCalendars,
} from 'actions';

let EditCalendar;
export default EditCalendar = ({calendarId}) => {
	const dispatch = useDispatch();
	const calendars = useSelector(state => state.calendars.present);
	const {local} = calendars[calendarId];

	if(local) {
		return (
			<Button
				positive
				icon='save'
				content={__('Save', 'booking-weir')}
				onClick={() => dispatch(saveCalendars())}
				className='last'
			/>
		);
	}

	return (
		<Link to={`/${calendarId}`}>
			<Button
				primary
				icon='edit'
				content={__('Manage', 'booking-weir')}
				className='last'
			/>
		</Link>
	);
};
