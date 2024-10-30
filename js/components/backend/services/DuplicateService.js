import {__} from '@wordpress/i18n';

import {
	useCallback,
} from 'react';

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
	addService,
} from 'actions';

let DuplicateService;
export default DuplicateService = ({service}) => {
	const calendarId = useCurrentCalendarId();
	const dispatch = useDispatch();

	const duplicate = useCallback(() => {
		const {
			id,
			...duplicateService
		} = service;
		dispatch(addService(calendarId, duplicateService));
	}, [calendarId, service, dispatch]);

	return (
		<Button
			secondary
			icon='copy'
			onClick={duplicate}
			data-tooltip={__('Duplicate', 'booking-weir')}
			data-position='top right'
		/>
	);
};
