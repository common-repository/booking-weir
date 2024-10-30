import {__, _x, _n, sprintf} from '@wordpress/i18n';

import {
	useState,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Text,
} from '@fluentui/react-northstar';

import {
	AcceptIcon,
} from '@fluentui/react-icons-northstar';

import {
	Button,
} from 'components/ui';

import {
	useCurrentCalendar,
	useSelectedService,
} from 'hooks';

import {
	updateBooking,
} from 'actions';

import {
	toDate,
	getCalendarUTCOffset,
	getCalendarNowDate,
} from 'utils/date';

import {
	startOfDay,
	addDays,
	isBefore,
	isAfter,
} from 'date-fns';

import {
	isServiceBookable,
} from 'utils/services';

import {
	isSelected as isEventSelected,
} from 'utils/event';

export const BOOK_BUTTON_CLASS = 'bw-book-button';

/**
 * Button that opens booking modal.
 * Rendered in `EventWrapper`, from which it receives the event data.
 */
let BookButton;
export default BookButton = ({event, color, compact = true, styles = {}, onClick, context}) => {
	const {
		id: calendarId,
		settings,
		data: {
			nonce,
		},
	} = useCurrentCalendar();
	const selectedService = useSelectedService();
	const bookingModalStep = useSelector(state => state.ui.bookingModalStep);
	const dispatch = useDispatch();

	const {
		type,
		start,
		end,
		bookable,
	} = event;
	const isSelected = isEventSelected(event);
	const isAtCapacity = !!event?.data?.isAtCapacity;
	const [forceBook, setForceBook] = useState(!!event?.data?.forceBook && isSelected); // Open booking modal when `forceBook` is enabled (`Booking::BOOK` GET variable is set).

	const startDate = toDate(start);
	const now = getCalendarNowDate(settings);
	const isInPast = isBefore(startDate, now);

	const open = e => {
		if(e) {
			e.preventDefault();
			e.stopPropagation();
		}

		if(isInPast) {
			return false;
		}

		onClick && onClick();

		dispatch(updateBooking({
			calendarId,
			start,
			end,
			nonce,
			utcOffset: getCalendarUTCOffset(settings),
			...(selectedService && {
				service: selectedService,
			}),
			...(bookable && {
				bookableEvent: event,
			}),
			...(type === 'slot' && {
				slot: event,
			}),
		}));
		dispatch({type: 'SET_BOOKING_MODAL_STEP', step: 1});
		return false;
	};

	/**
	 * Prevent booking too far in advance.
	 */
	const {future} = settings;
	if(future > 0 && isAfter(startDate, addDays(startOfDay(new Date), future))) {
		return (
			<Text
				content={sprintf(
					__(`Booking is only allowed less than %s in advance.`, 'booking-weir'),
					`${future} ${_n('day', 'days', future, 'booking-weir')}`
				)}
				align='center'
			/>
		);
	}

	const {services} = settings;
	const isIncompatibleWithSelectedService = selectedService && (
		bookable // Bookable events are not compatible with services.
		|| !isServiceBookable(selectedService, event, settings) // Slots with predefined durations are not compatible with services with predefined durations.
	);
	const disabled = (
		!calendarId // Calendar is required to book into, this is a logic error.
		|| bookingModalStep > 0 // Booking modal is already open.
		|| isInPast // Can't make bookings in the past.
		|| (services && !selectedService) // Service needs to be selected.
		|| isIncompatibleWithSelectedService // Services can be incompatible with slots and bookable events.
		|| isAtCapacity // Bookable events can have a limited amount of bookings available.
	);

	if(forceBook && !disabled) {
		open();
		setForceBook(false);
	}

	return (
		<Button
			className={BOOK_BUTTON_CLASS}
			key={color} // Prevents background color transition
			fluid
			compact={compact}
			color={color}
			inverted={isSelected && context !== 'dialog'}
			content={_x('Book', 'Button that triggers booking, displayed in the calendar for selected or predefined time slots', 'booking-weir')}
			icon={<AcceptIcon />}
			iconPosition='after'
			disabled={disabled}
			onClick={open}
			styles={{
				margin: 0,
				maxWidth: '140px',
				...styles,
			}}
		/>
	);
}
