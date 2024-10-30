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
	deletePaymentType,
} from 'actions';

let DeletePaymentType;
export default DeletePaymentType = ({id}) => {
	const calendarId = useCurrentCalendarId();
	const dispatch = useDispatch();

	const DELETE = useCallback(() => {
		dispatch(deletePaymentType(calendarId, id));
	}, [calendarId, id]);

	return (
		<Button
			negative
			icon='trash'
			onClick={DELETE}
		/>
	);
};
