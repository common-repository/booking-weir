import {__, sprintf} from '@wordpress/i18n';

import {
	useSelector,
} from 'react-redux';

import {
	List,
	Icon,
	Segment,
	Message,
} from 'semantic-ui-react';

import {
	addSeconds,
} from 'date-fns';

import {
	formatDistance,
} from 'utils/date';

import {
	useCurrentCalendar,
	useCurrencyRenderer,
	useService,
	useBookableEvent,
} from 'hooks';

import {
	getBookableEventPriceText,
} from 'utils/bookable';

import Event from 'components/backend/events/Event';
import TransactionView from './TransactionView';
import EventEditable from 'components/backend/events/EventEditable';

import BOOKING_STATUSES from 'config/BOOKING_STATUSES';
import PAYMENT_METHODS from 'config/PAYMENT_METHODS';

const renderReminder = (reminderIn, settings) => {
	if(Number.isInteger(reminderIn)) {
		return formatDistance(
			new Date(0),
			addSeconds(new Date(0), reminderIn),
			settings
		);
	}
	switch(reminderIn) {
		case 'not-applicable':
			return __('Not applicable', 'booking-weir');
		case 'event-not-public':
			return __('Event is not public', 'booking-weir');
		case 'event-not-confirmed':
			return __('Event is not confirmed', 'booking-weir');
		case 'sent':
			return __('Sent', 'booking-weir');
		case 'reminders-not-enabled':
			return __('Automatic reminders are not enabled', 'booking-weir');
		case 'event-already-started':
			return __('Event already started', 'booking-weir');
		case 'event-created-too-late':
			return __('Event was created too late for a reminder', 'booking-weir');
		case 'pending':
			return __('Pending', 'booking-weir');
	}
	return reminderIn;
};

let BookingInfo;
export default BookingInfo = ({booking}) => {
	const calendar = useCurrentCalendar();
	const renderCurrency = useCurrencyRenderer();
	const {settings} = calendar;
	const {step} = settings;
	const isFetchingPrice = useSelector(state => state.ui.isFetchingPrice);

	const {
		id,
		start,
		end,
		price,
		paymentMethod,
		bookableEventId,
		serviceId,
		breakdown,
		status,
		notes,
		transactionId,
		orderId,
		data: {
			orderStatus,
			isWC,
			reminderIn,
		},
	} = booking;

	const service = useService(serviceId);
	const bookableEvent = useBookableEvent(bookableEventId);

	if(!start || !end) {
		return <Segment basic loading padded='very' />;
	}

	const paymentMethodLabel = PAYMENT_METHODS.find(({id}) => paymentMethod === id)?.label || paymentMethod;

	return <>
		<Event event={booking} />
		<List key={`booking-info-${id}`}>
			{settings.reminderEmailOffset > 0 && reminderIn && (
				<List.Item>
					<List.Content>
						<List.Header>{__('Time until reminder', 'booking-weir')}</List.Header>
						<List.Description>{renderReminder(reminderIn, settings)}</List.Description>
					</List.Content>
				</List.Item>
			)}
			{serviceId && !service && (
				<List.Item>
					<Message negative compact size='mini'>{sprintf(__('Service %s not found.', 'booking-weir'), serviceId)}</Message>
				</List.Item>
			)}
			{serviceId && service && <>
				<List.Item>
					<List.Content>
						<List.Header>{__('Service', 'booking-weir')}</List.Header>
						<List.Description>{service.name}</List.Description>
					</List.Content>
				</List.Item>
				<List.Item>
					<List.Content>
						<List.Header>{__('Price per hour', 'booking-weir')}</List.Header>
						<List.Description>{`${renderCurrency(service.price)}`}</List.Description>
					</List.Content>
				</List.Item>
			</>}
			{!!bookableEventId && (
				<List.Item>
					<List.Content>
						<List.Header>{__('Price', 'booking-weir')}</List.Header>
						<List.Description>{`${getBookableEventPriceText(start, end, bookableEvent, settings)}`}</List.Description>
					</List.Content>
				</List.Item>
			)}
			{!bookableEventId && !serviceId && (
				<List.Item>
					<List.Content>
						<List.Header>{__('Price per hour', 'booking-weir')}</List.Header>
						<List.Description>{`${renderCurrency(settings.price)}`}</List.Description>
					</List.Content>
				</List.Item>
			)}
			{breakdown && !!Object.keys(breakdown).length && (
				<List.Item>
					<List.Content>
						<List.Header>{__('Extras', 'booking-weir')}</List.Header>
						<List.Description>
							<List.List style={{paddingLeft: 0, paddingTop: '0.25em'}}>
								{Object.keys(breakdown).map(name => <List.Item key={name}>{`${name}: ${renderCurrency(breakdown[name])}`}</List.Item>)}
							</List.List>
						</List.Description>
					</List.Content>
				</List.Item>
			)}
			{price !== undefined && (
				<List.Item>
					<List.Content>
						<List.Header>{__('Total price', 'booking-weir')}</List.Header>
						<List.Description>
							{isFetchingPrice && <Icon loading name='spinner' />}
							{!isFetchingPrice && `${renderCurrency(price)}`}
						</List.Description>
					</List.Content>
				</List.Item>
			)}
			{paymentMethod && (
				<List.Item>
					<List.Content>
						<List.Header>{__('Payment method', 'booking-weir')}</List.Header>
						<List.Description>{paymentMethodLabel}</List.Description>
					</List.Content>
				</List.Item>
			)}
			{!isWC && (
				<List.Item>
					<List.Content>
						<List.Header>{__('Status', 'booking-weir')}</List.Header>
						<List.Description>
							<EventEditable
								eventId={id}
								value={status}
								schema={{
									key: 'status',
									label: __('Status', 'booking-weir'),
									type: 'select',
									options: BOOKING_STATUSES.filter(({wc}) => !wc),
								}}
							/>
						</List.Description>
					</List.Content>
				</List.Item>
			)}
			{isWC && !!orderId && <>
				<List.Item>
					<List.Content>
						<List.Header>{__('Order', 'booking-weir')}</List.Header>
						<List.Description>
							<a href={`/wp-admin/post.php?post=${orderId}&action=edit`} target='_blank' rel='noopener noreferrer'>{`#${orderId}`}</a>
						</List.Description>
					</List.Content>
				</List.Item>
				{orderStatus && (
					<List.Item>
						<List.Content>
							<List.Header>{__('Status', 'booking-weir')}</List.Header>
							<List.Description>{orderStatus[Object.keys(orderStatus)[0]]}</List.Description>
						</List.Content>
					</List.Item>
				)}
			</>}
			{isWC && !orderId && (
				<List.Item>
					<List.Content>
						<List.Header>{__('Status', 'booking-weir')}</List.Header>
						<List.Description>{BOOKING_STATUSES.find(({value}) => value === status).text}</List.Description>
					</List.Content>
				</List.Item>
			)}
			{notes && (
				<List.Item>
					<List.Content>
						<List.Header>{__('Notes', 'booking-weir')}</List.Header>
						<List.Description>{notes}</List.Description>
					</List.Content>
				</List.Item>
			)}
		</List>
		{paymentMethod && transactionId && <TransactionView />}
	</>;
};
