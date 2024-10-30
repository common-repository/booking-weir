import {__} from '@wordpress/i18n';

import {
	useState,
	useCallback,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Input,
	Button,
} from 'semantic-ui-react';

import {
	useCurrentCalendarId,
} from 'hooks';

import {
	addPaymentType,
} from 'actions';

let AddPaymentType;
export default AddPaymentType = () => {
	const dispatch = useDispatch();
	const currentCalendarId = useCurrentCalendarId();
	const [name, setName] = useState('');

	const submit = useCallback(e => {
		e.preventDefault();
		if(name.trim()) {
			dispatch(addPaymentType(currentCalendarId, name.trim()));
			setName('');
		}
	}, [currentCalendarId, name, dispatch]);

	return (
		<form onSubmit={submit}>
			<Input action className='stackable'>
				<input
					type='text'
					placeholder={__('Payment type name...', 'booking-weir')}
					value={name}
					onChange={e => setName(e.target.value)}
				/>
				<Button
					primary
					type='submit'
					icon='add'
					content={__('Add', 'booking-weir')}
				/>
			</Input>
		</form>
	);
};
