import {__, _x} from '@wordpress/i18n';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Flex,
} from '@fluentui/react-northstar';

import {
	AcceptIcon,
} from '@fluentui/react-icons-northstar';

import {
	Dialog,
	Button,
	Grid,
} from 'components/ui';

import BookingInfo from './BookingInfo';
import BookerInfo from './BookerInfo';
import PaymentTypeSelect from './PaymentTypeSelect';
import PaymentMethodSelect from './PaymentMethodSelect';

import postBooking from './postBooking';

import {
	useBooking,
} from 'hooks';

let PaymentModal;
export default PaymentModal = () => {
	const isOpen = useSelector(state => state.ui.bookingModalStep === 3);
	const dispatch = useDispatch();
	const booking = useBooking();

	return (
		<Dialog
			className='bw-payment-modal'
			size='tiny'
			open={isOpen}
			header={_x('Confirm booking', 'Booking modal final step title', 'booking-weir')}
			content={(
				<Grid columns={2} stackable>
					<BookingInfo booking={booking} />
					<BookerInfo booking={booking} />
					{booking.price > 0 && <>
						<PaymentTypeSelect />
						<PaymentMethodSelect />
					</>}
				</Grid>
			)}
			footer={(
				<Flex gap='gap.small' hAlign='end'>
					<Button
						secondary
						content={_x('Back', 'Booking modal back button', 'booking-weir')}
						onClick={() => dispatch({type: 'BOOKING_MODAL_PREV_STEP'})}
					/>
					<Button
						positive
						content={_x('Book', 'Booking modal confirm booking button', 'booking-weir')}
						icon={<AcceptIcon />}
						iconPosition='after'
						onClick={() => postBooking(booking)}
					/>
				</Flex>
			)}
			closeIcon
			onClose={() => dispatch({type: 'SET_BOOKING_MODAL_STEP', step: 0})}
		/>
	);
};
