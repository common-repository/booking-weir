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
	deleteService,
} from 'actions';

let DeleteService;
export default DeleteService = ({serviceId}) => {
	const dispatch = useDispatch();
	const currentCalendarId = useCurrentCalendarId();

	return (
		<Button
			color='red'
			icon='trash'
			onClick={() => dispatch(deleteService(currentCalendarId, serviceId))}
		/>
	);
};
