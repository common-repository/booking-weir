import {__} from '@wordpress/i18n';

import {
	useRef,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	addService,
} from 'actions';

let AddService;
export default AddService = () => {
	const dispatch = useDispatch();
	const {
		id,
		settings: {
			price,
		},
	} = useCurrentCalendar();
	const input = useRef(null);

	return (
		<form
			className='ui action input'
			onSubmit={e => {
				e.preventDefault();
				if(!input.current.value.trim()) {
					return;
				}
				dispatch(addService(id, {
					name: input.current.value.trim(),
					price,
				}));
				input.current.value = '';
			}}
		>
			<input
				type='text'
				placeholder={__('Service name...', 'booking-weir')}
				ref={input}
			/>
			<button type='submit' className='ui primary button'>
				<i className='add icon'></i>
				{__('Add', 'booking-weir')}
			</button>
		</form>
	);
};
