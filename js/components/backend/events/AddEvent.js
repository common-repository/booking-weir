import {__} from '@wordpress/i18n';

import {
	useState,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Input,
	Select,
	Button,
} from 'semantic-ui-react';

import EVENT_TYPES from 'config/EVENT_TYPES';
const TYPES = EVENT_TYPES
	.filter(({creatable}) => creatable)
	.map(({key, value, text}) => ({key, value, text}));

import {
	useCurrentCalendarId,
} from 'hooks';

import {
	addEvent,
} from 'actions';

let AddEvent;
export default AddEvent = ({event}) => {
	const dispatch = useDispatch();
	const currentCalendarId = useCurrentCalendarId();
	const [type, setType] = useState(TYPES[0].value);

	const submit = e => {
		e.preventDefault();
		dispatch(
			addEvent(
				currentCalendarId,
				{
					...event,
					type,
					title: EVENT_TYPES.find(({value}) => value === type).text,
					...(type === 'booking' && {
						firstName: __('Booker', 'booking-weir'),
						email: 'changeme@email.com',
					}),
				}
			)
		);
		/**
		 * `ADD_EVENT` triggers clearing of `state.ui.draftEvent`,
		 * which triggers router redirect from `/events/draft` to `/events`.
		 */
	};

	return (
		<Input action actionPosition='left' as='form' onSubmit={submit} className='stackable'>
			<Select
				compact
				options={TYPES}
				value={type}
				onChange={(e, {value}) => setType(value)}
				style={{minWidth: 200}}
			/>
			<Button
				positive
				type='submit'
				icon='add'
				content={__('Create', 'booking-weir')}
				style={{borderTopRightRadius: 4, borderBottomRightRadius: 4}}
			/>
		</Input>
	);
};
