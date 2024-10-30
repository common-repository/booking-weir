import {__} from '@wordpress/i18n';

import {
	useRef,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	requestAddCalendar,
} from 'actions';

let AddCalendar;
export default AddCalendar = () => {
	const dispatch = useDispatch();
	const input = useRef(null);

	return (
		<form
			onSubmit={e => {
				e.preventDefault();
				if(!input.current.value.trim()) {
					return;
				}
				dispatch(requestAddCalendar(input.current.value.trim()));
				input.current.value = '';
			}}
		>
			<div className='ui fluid action input'>
				<input
					id='calendar-name'
					type='text'
					placeholder={__('Calendar name...', 'booking-weir')}
					ref={input}
				/>
				<Button
					primary
					type='submit'
					icon='add'
					content={__('Add', 'booking-weir')}
				/>
			</div>
		</form>
	);
};
