import {__} from '@wordpress/i18n';

import {
	useState,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
	Dropdown,
	Modal,
	Header,
	Segment,
	Divider,
} from 'semantic-ui-react';

import OrderSelect from './booking/OrderSelect';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	regeneratePdf,
} from 'actions';

import {
	updateEvent,
	stripPersonalData,
} from 'actions';

const getActions = (event, calendar) => {
	const actions = [];

	if(event.id === 'draft') {
		return actions;
	}

	const {
		type,
		status,
		orderId,
		billingKey,
		paymentMethod,
		transactionId,
		invoiceEmailSent,
		reminderEmailSent,
		notes,
		data: {
			isWC,
		},
	} = event;

	const post_status = event?.post?.status;

	const {
		settings,
	} = calendar;

	const isActionableBooking = type === 'booking' && post_status == 'publish' && status !== 'archived' && status !== 'cart';

	actions.push({
		id: 'send-invoice',
		condition: isActionableBooking && !isWC && !invoiceEmailSent,
		text: __('Send invoice', 'booking-weir'),
		description: __(`When a booking's public link is visited then an invoice e-mail is sent automatically. This action allows you to send it if the booking was created in the admin dashboard and you would like to notify the client.`, 'booking-weir'),
		icon: 'mail outline',
		action: {
			type: 'SEND_INVOICE_EMAIL',
			calendarId: calendar.id,
			eventId: event.id,
		},
	});

	actions.push({
		id: 'mark-invoice-sent',
		condition: isActionableBooking && !isWC && !invoiceEmailSent,
		text: __('Mark invoice sent', 'booking-weir'),
		description: __(`When a booking's public link is visited then an invoice e-mail is sent automatically. If you don't want that to happen you can mark the e-mail as "sent" without sending it.`, 'booking-weir'),
		icon: 'mail',
		action: updateEvent(calendar.id, event.id, {invoiceEmailSent: true}),
	});

	actions.push({
		id: 'resend-invoice',
		condition: isActionableBooking && !isWC && invoiceEmailSent,
		text: __('Resend invoice', 'booking-weir'),
		description: __('Send an invoice e-mail again.', 'booking-weir'),
		icon: 'mail outline',
		action: {
			type: 'SEND_INVOICE_EMAIL',
			calendarId: calendar.id,
			eventId: event.id,
		},
	});

	actions.push({
		id: 'send-reminder',
		condition: isActionableBooking && !reminderEmailSent,
		text: __('Send reminder', 'booking-weir'),
		description: __('Send a reminder e-mail. Once sent, if automatic reminders are enabled it will not send another reminder for this event.', 'booking-weir'),
		icon: 'clock outline',
		action: {
			type: 'SEND_REMINDER_EMAIL',
			calendarId: calendar.id,
			eventId: event.id,
		},
	});

	actions.push({
		id: 'mark-reminder-sent',
		condition: isActionableBooking && !reminderEmailSent && settings.reminderEmailOffset > 0,
		text: __('Mark reminder sent', 'booking-weir'),
		description: __(`If you don't want to send a reminder for this booking you can mark it as "sent" without sending it which also prevents sending an automatic reminder in the future.`, 'booking-weir'),
		icon: 'clock',
		action: updateEvent(calendar.id, event.id, {reminderEmailSent: true}),
	});

	actions.push({
		id: 'resend-reminder',
		condition: isActionableBooking && reminderEmailSent,
		text: __('Resend reminder', 'booking-weir'),
		description: __('Send a reminder e-mail again.', 'booking-weir'),
		icon: 'clock outline',
		action: {
			type: 'SEND_REMINDER_EMAIL',
			calendarId: calendar.id,
			eventId: event.id,
		},
	});

	actions.push({
		id: 'view-pdf-invoice',
		condition: isActionableBooking && !isWC && settings.invoicePdfEnabled,
		text: __('View PDF invoice', 'booking-weir'),
		description: __('Opens a link to the generated PDF invoice.', 'booking-weir'),
		icon: 'pdf file',
		action: () => window.open(`${booking_weir_data.upload_url}/invoice-${billingKey}.pdf`, '_blank', 'noopener noreferrer'),
		instant: true,
	});

	actions.push({
		id: 'regenerate-pdf-invoice',
		condition: isActionableBooking && !isWC && settings.invoicePdfEnabled,
		text: __('Regenerate PDF invoice', 'booking-weir'),
		description: __('Regenerate the PDF invoice. The invoice can be automatically regenerated whenever event data changes if the "Settings -> PDF -> Automatic regeneration" option is enabled, but you may wish to manually regenerate when you modify the PDF settings or template.', 'booking-weir'),
		icon: 'refresh',
		action: regeneratePdf(event.id),
	});

	actions.push({
		id: 'view-transaction-data',
		condition: isActionableBooking && !isWC && paymentMethod && transactionId,
		text: __('View transaction data', 'booking-weir'),
		description: __('Displays the raw transaction data that payment gateway provides based on the stored transaction ID.', 'booking-weir'),
		icon: 'exchange',
		action: {
			type: 'SET_TRANSACTION_VIEW',
			value: {paymentMethod, transactionId},
		},
		instant: true,
	});

	actions.push({
		id: 'detach-order',
		condition: isActionableBooking && isWC && orderId,
		text: __('Detach from order', 'booking-weir'),
		description: __(`Removes this booking from a WooCommerce order. You can then attach it to another order by specifying the order ID.`, 'booking-weir'),
		icon: 'unlink',
		action: updateEvent(calendar.id, event.id, {orderId: -1}),
	});

	actions.push({
		id: 'remove-personal-data',
		condition: isActionableBooking && !isWC,
		text: __('Remove personal data', 'booking-weir'),
		description: __('Strips all personal data from the booking (name, e-mail, phone, additional info, transaction ID, PDF invoice) and sets the status to "Archived".', 'booking-weir'),
		icon: 'user secret',
		action: stripPersonalData(calendar.id, event.id, notes),
	});

	actions.push({
		id: 'get-event-link',
		condition: ['default', 'slot'].includes(type),
		text: __('Get link', 'booking-weir'),
		description: __('Provides a direct link to an event.', 'booking-weir'),
		icon: 'linkify',
		action: {
			type: 'EVENT_LINK_MODAL_OPEN',
			value: true,
		},
		instant: true,
	});

	return actions.filter(({condition}) => condition);
};

let EventActionsDropdown;
export default EventActionsDropdown = ({event}) => {
	const dispatch = useDispatch();
	const calendar = useCurrentCalendar();
	const [showHelp, setShowHelp] = useState(false);
	const [currentAction, setCurrentAction] = useState(false);

	if(event?.data?.isWC && event?.status === 'detached') {
		return <OrderSelect event={event} />; // TODO: better place for it
	}

	const actions = getActions(event, calendar);

	if(!actions.length) {
		return null;
	}

	actions.push({
		id: 'help',
		text: __('Help', 'booking-weir'),
		icon: 'help',
		action: () => setShowHelp(true),
		instant: true,
	});

	const trigger = actionId => {
		const action = actions.find(({id}) => id === actionId).action;
		if(typeof action === 'function') {
			action();
		} else {
			dispatch(action);
		}
	};

	return <>
		<Button.Group>
			<Dropdown
				button
				floating
				className='primary right labeled icon'
				text={currentAction ? actions.find(({id}) => id === currentAction).text : __('Choose an action...', 'booking-weir')}
				value={currentAction}
				selectOnBlur={false}
				selectOnNavigation={false}
				options={actions.map(({id, condition, description, action, instant, ...rest}) => ({
					key: id,
					value: id,
					...rest,
				}))}
				onChange={(e, {value}) => {
					if(actions.find(({id}) => id === value).instant) {
						setCurrentAction(false);
						trigger(value);
					} else {
						setCurrentAction(value);
					}
				}}
			/>
			{currentAction && (
				<Button
					positive
					icon='angle double right'
					aria-label={__('Perform selected action', 'booking-weir')}
					onClick={() => {
						trigger(currentAction);
						setCurrentAction(false);
					}}
				/>
			)}
		</Button.Group>
		<Modal
			mountNode={document.getElementById('bw-no-sui')}
			open={showHelp}
			size='small'
			closeIcon={true}
			onClose={() => setShowHelp(false)}
		>
			<Modal.Header style={{margin: 0}}>{__('Help', 'booking-weir')}</Modal.Header>
			<Modal.Content scrolling className='sui-root'>
				{actions.filter(({id}) => id !== 'help').map(({id, text, description, icon}) => (
					<Segment key={id} basic size='big' className='paddingless'>
						<Header content={text} icon={icon} size='tiny' />
						<p>{description}</p>
						<Divider hidden />
					</Segment>
				))}
			</Modal.Content>
		</Modal>
	</>;
};
