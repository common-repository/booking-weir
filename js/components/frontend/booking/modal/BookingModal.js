import {__, _x} from '@wordpress/i18n';

import {
	applyFilters,
} from '@wordpress/hooks';

import {
	useState,
	useEffect,
	useCallback,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Flex,
} from '@fluentui/react-northstar';

import {
	ChevronEndMediumIcon,
	AddIcon,
} from '@fluentui/react-icons-northstar';

import {
	Dialog,
	Button,
	Grid,
} from 'components/ui';

import equal from 'fast-deep-equal';

import BookingInfo from './BookingInfo';
import BookingExtras from './BookingExtras';
import CouponInput from './CouponInput';
import Errors from './Errors';
import postBooking from './postBooking';

import {
	useCurrentCalendar,
	useBooking,
} from 'hooks';

import {
	updateBooking,
	updateBookingDebounced,
} from 'actions';

import {
	flattenFields,
	DEFAULT_FIELD_IDS,
} from 'utils/field';

/**
 * Does the calendar have extras that the current booking can utilize.
 *
 * @param {*} extras Calendar extras.
 * @param {*} booking Current booking state.
 * @returns bool
 */
const hasVisibleExtras = (extras, booking) => {
	const calendarHasExtras = !!Object.keys(extras).length;
	if(!calendarHasExtras) {
		return false;
	}
	return extras.filter(({enabled = true}) => enabled).filter(({services = []}) => {
		if(!services.length) {
			return true;
		}
		return services.includes(booking?.service?.id);
	}).length > 0;
};

let BookingModal;
export default BookingModal = () => {
	const calendar = useCurrentCalendar();
	const booking = useBooking();
	const {isOpen, isFetchingPrice} = useSelector(state => ({
		isOpen: state.ui.bookingModalStep === 1,
		isFetchingPrice: state.ui.isFetchingPrice,
	}));
	const dispatch = useDispatch();
	const [selectedExtras, setSelectedExtras] = useState({});

	const updateExtras = useCallback(value => setSelectedExtras(value), []);

	/**
	 * Update booking with new selected extras.
	 */
	const extrasAreSynced = equal(booking.extras || {}, selectedExtras);
	const isLoading = isFetchingPrice || !extrasAreSynced;
	useEffect(() => {
		if(isOpen && !extrasAreSynced) {
			dispatch(updateBookingDebounced({
				...booking,
				extras: selectedExtras,
				price: undefined, // Request new price
			}));
		}
	}, [dispatch, isOpen, booking, extrasAreSynced, selectedExtras]);

	const hasExtras = hasVisibleExtras(calendar.extras, booking);
	const isProduct = !!parseInt(calendar?.data?.product);
	const hasNonDefaultFields = isProduct && flattenFields(calendar.fields).filter(({id}) => !DEFAULT_FIELD_IDS.includes(id)).length > 0;
	const addToCart = isProduct && !hasNonDefaultFields;

	const disabled = isLoading || isNaN(booking.price);
	const close = () => {
		dispatch({type: 'SET_BOOKING_MODAL_STEP', step: 0});
		/**
		 * Get rid of extras, they may not be compatible with next event/service.
		 */
		setSelectedExtras({});
		dispatch(updateBooking({
			...booking,
			extras: {},
		}));
	};

	return (
		<Dialog
			className='bw-booking-modal'
			size={applyFilters('bw_booking_modal_size', hasExtras ? 'tiny' : 'mini', hasExtras)}
			open={isOpen}
			header={_x('Booking', 'Booking modal first step title', 'booking-weir')}
			content={<>
				<Errors />
				<Grid columns={hasExtras ? 2 : 1} stackable>
					<BookingInfo booking={booking} />
					{hasExtras && <BookingExtras onChange={updateExtras} />}
					<CouponInput />
				</Grid>
			</>}
			footer={(
				<Flex gap='gap.small' hAlign='end'>
					<Button
						secondary
						content={_x('Back', 'Booking modal back button', 'booking-weir')}
						onClick={close}
					/>
					{!addToCart && (
						<Button
							positive
							content={_x('Proceed', 'Booking modal forward button', 'booking-weir')}
							disabled={disabled}
							onClick={() => dispatch({type: 'BOOKING_MODAL_NEXT_STEP'})}
							icon={<ChevronEndMediumIcon />}
							iconPosition='after'
						/>
					)}
					{addToCart && (
						<Button
							positive
							content={_x('Add to cart', 'Booking modal add to cart button', 'booking-weir')}
							disabled={disabled}
							onClick={() => postBooking(booking)}
							icon={<AddIcon />}
							iconPosition='after'
						/>
					)}
				</Flex>
			)}
			closeIcon
			onClose={close}
		/>
	);
};
