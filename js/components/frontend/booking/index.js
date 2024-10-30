import {__, sprintf} from '@wordpress/i18n';

import {
	useEffect,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import Loader from 'components/ui/Loader';
import BookingCalendar from 'components/frontend/calendar';
import Services from 'components/frontend/services';
import Status from 'components/frontend/booking/Status';
import Notices from 'components/frontend/notices';
import Toast from 'components/frontend/toast';
import BookingModals from './modal';
import ServiceSelector from './ServiceSelector';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	useCalendar,
	setCurrentCalendar
} from 'actions';

/**
 * Render booking calendar with errors and booking modal.
 */
let Booking;
export default Booking = ({id, calendar, type}) => {
	const currentCalendar = useCurrentCalendar();
	const dispatch = useDispatch();

	useEffect(() => {
		// eslint-disable-next-line react-hooks/rules-of-hooks
		dispatch(useCalendar(id, calendar));
		dispatch(setCurrentCalendar(id));
	}, [id, calendar, dispatch]);

	if(!window.booking_weir_data) {
		console.error(`"booking_weir_data" JavaScript variable is not defined. If you're using an optimization plugin configure it to preserve the correct script order or disable it for this page.`);
		return sprintf(
			__(`Booking unavailable. Error: %s.`, 'booking-weir'),
			__('No data', 'booking-weir')
		);
	}

	if(!id) {
		return sprintf(
			__(`Booking unavailable. Error: %s.`, 'booking-weir'),
			__('No calendar ID', 'booking-weir')
		);
	}

	if(!calendar) {
		return sprintf(
			__(`Booking unavailable. Error: %s.`, 'booking-weir'),
			sprintf(__('Calendar with ID %s not found', 'booking-weir'), id)
		);
	}

	if(!currentCalendar) {
		return <Loader />;
	}

	return <>
		<Notices />
		<Status />
		<Toast />
		<ServiceSelector />
		{type === 'default' && <BookingCalendar />}
		{type === 'services' && <Services />}
		<BookingModals />
	</>;
};
