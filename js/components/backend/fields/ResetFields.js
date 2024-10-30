import {__} from '@wordpress/i18n';

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
	setFields,
} from 'actions';

import DEFAULT_FIELDS from 'config/DEFAULT_FIELDS';


let ResetFields;
export default ResetFields = () => {
	const dispatch = useDispatch();
	const id = useCurrentCalendarId();

	const reset = () => {
		dispatch(setFields(id, DEFAULT_FIELDS));
		dispatch({
			type: 'SET_MESSAGE',
			value: {
				positive: true,
				content: __('Fields were reset to default', 'booking-weir'),
			},
		});
	};

	return (
		<Button
			content={__('Reset to default', 'booking-weir')}
			onClick={reset}
		/>
	);
};
