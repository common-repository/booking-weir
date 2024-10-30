import {
	useSelector,
} from 'react-redux';

import BookingModal from './BookingModal';
import InfoModal from './InfoModal';
import PaymentModal from './PaymentModal';
import {FileInputContainer} from './FileInput';

/**
 * Renders the modals that handle creating a booking.
 */
const BookingModals = () => {
	const isOpen = useSelector(state => state.ui.bookingModalStep > 0);
	return isOpen ? <>
		<BookingModal />
		<InfoModal />
		<PaymentModal />
		<FileInputContainer />
	</> : null;
};

export default BookingModals;
