import {__} from '@wordpress/i18n';

import cx from 'classnames';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	deleteCalendar,
} from 'actions';

let DeleteCalendar;
export default DeleteCalendar = ({calendarId, ...props}) => {
	const dispatch = useDispatch();

	return (
		<Button
			basic
			disabled={!booking_weir_data.is_admin}
			onClick={() => dispatch(deleteCalendar(calendarId))}
			icon='trash'
			size='small'
			data-tooltip={__('Delete calendar', 'booking-weir')}
			data-position='bottom center'
			{...props}
			className={cx('shadowless', props.className)}
		/>
	);
};
