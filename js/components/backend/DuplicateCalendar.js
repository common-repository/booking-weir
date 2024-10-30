import {__} from '@wordpress/i18n';

import cx from 'classnames';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	useCurrentCalendarId,
} from 'hooks';

import {
	duplicateCalendar,
} from 'actions';

let DuplicateCalendar;
export default DuplicateCalendar = ({calendarId, ...props}) => {
	const currentCalendarId = useCurrentCalendarId();
	const dispatch = useDispatch();

	return (
		<Button
			basic
			disabled={currentCalendarId === calendarId || !booking_weir_data.is_admin}
			onClick={() => dispatch(duplicateCalendar(calendarId))}
			icon='copy'
			size='small'
			data-tooltip={__('Duplicate calendar', 'booking-weir')}
			data-position='bottom center'
			{...props}
			className={cx('shadowless', props.className)}
		/>
	);
};
