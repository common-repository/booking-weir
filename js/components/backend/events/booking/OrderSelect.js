import {__, sprintf} from '@wordpress/i18n';

import {
	useRef,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
	Input,
} from 'semantic-ui-react';

import {
	useCurrentCalendarId,
} from 'hooks';

import {
	updateEvent,
} from 'actions';

let OrderSelect;
export default OrderSelect = ({event}) => {
	const dispatch = useDispatch();
	const calendarId = useCurrentCalendarId();
	const inputRef = useRef();

	const attach = () => {
		const orderId = parseInt(inputRef.current.value.trim());
		if(!orderId) {
			dispatch({
				type: 'SET_ERROR',
				value: sprintf(__('Invalid value: %s', 'booking-weir'), orderId),
			});
			return;
		}
		dispatch(updateEvent(calendarId, event.id, {orderId}));
	};

	return (
		<Input fluid action className='compact'>
			<input
				ref={inputRef}
				type='text'
				placeholder={__('Order ID...', 'booking-weir')}
			/>
			<Button
				primary
				icon='plus'
				content={__('Attach', 'booking-weir')}
				onClick={attach}
			/>
		</Input>
	);
};
