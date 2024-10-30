import {__} from '@wordpress/i18n';
import {useViewportMatch} from '@wordpress/compose';

import {
	useSelector,
} from 'react-redux';

import {
	Grid,
	Message,
	Icon,
} from 'semantic-ui-react';

import BookingInfo from './BookingInfo';
import BookingEdit from './BookingEdit';
import BookerInfo from './BookerInfo';
import BookerEdit from './BookerEdit';

let BookingView;
export default BookingView = ({booking}) => {
	const isHuge = useViewportMatch('huge');
	const editMode = useSelector(state => state.ui.editMode);

	if(editMode && booking.status === 'archived') {
		return (
			<Message compact className='marginless'>
				<Icon name='archive' />
				{__('Event is archived.', 'booking-weir')}
			</Message>
		);
	}

	return (
		<Grid columns={isHuge ? 2 : 1}>
			<Grid.Column>
				{editMode && <BookingEdit booking={booking} />}
				{!editMode && <BookingInfo booking={booking} />}
			</Grid.Column>
			<Grid.Column>
				{editMode && <BookerEdit booking={booking} />}
				{!editMode && <BookerInfo booking={booking} />}
			</Grid.Column>
		</Grid>
	);
};
