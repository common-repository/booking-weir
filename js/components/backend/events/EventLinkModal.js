import {__} from '@wordpress/i18n';
import {addQueryArgs} from '@wordpress/url';

import {
	useState,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Button,
	Modal,
	Form,
	Checkbox,
	Message,
	Icon,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	toDate,
	toString,
} from 'utils/date';

import {
	addMonths,
} from 'date-fns';

import {
	copy,
} from 'utils/clipboard';


import {
	DateInput,
} from 'semantic-ui-calendar-react';

import {
	getUrl as getCalendarUrl,
} from 'utils/calendar';

// import {
// 	getAdminbarHeight,
// } from 'utils/html';

const DateInputProps = {
	mountNode: document.getElementById('bw-sui-root'),
	clearable: false,
	popupPosition: 'left center',
	animation: 'none',
	closable: true,
	closeOnMouseLeave: false,
	closeOnScroll: false,
	dateFormat: 'YYYY-MM-DD',
	popupProps: {
		className: 'bw-calendar-picker',
		style: {
			// top: getAdminbarHeight(),
			padding: 0,
			zIndex: 99999,
			minWidth: 310,
		},
	},
};

let EventLinkModal;
export default EventLinkModal = ({event}) => {
	const {
		id,
		type,
		start,
		bookable,
		repeat,
	} = event;
	const isBookable = type === 'slot' || bookable;
	const hasContent = !!event.excerpt;
	const calendar = useCurrentCalendar();
	const {
		settings,
	} = calendar;
	const {
		url,
	} = settings;
	const dispatch = useDispatch();
	const isOpen = useSelector(state => state.ui.eventLinkModalOpen);
	const onClose = () => {
		dispatch({type: 'CLEAR_MESSAGE'});
		dispatch({type: 'EVENT_LINK_MODAL_OPEN', value: false});
	};
	const [openBookingModal, setOpenBookingModal] = useState(false);
	const [showContent, setShowContent] = useState(false);
	const [eventStart, setEventStart] = useState(start.split('T')[0]);

	const link = addQueryArgs(
		getCalendarUrl(calendar),
		{
			[booking_weir_data.GET.event.view]: id,
			...(repeat && eventStart !== start.split('T')[0] && {
				[booking_weir_data.GET.event.start]: `${eventStart}T${start.split('T')[1]}`,
			}),
			...(isBookable && openBookingModal && {
				[booking_weir_data.GET.event.action]: 'book',
			}),
			...(hasContent && showContent && {
				[booking_weir_data.GET.event.action]: 'view',
			}),
		}
	);

	return (
		<Modal
			mountNode={document.getElementById('bw-no-sui')}
			open={isOpen}
			onClose={onClose}
			size='mini'
		>
			<Modal.Header style={{margin: 0}}>{__('Event link', 'booking-weir')}</Modal.Header>
			<Modal.Content scrolling className='sui-root'>
				<Form as='div'>
					{!url && (
						<Message visible warning>
							<Icon name='warning' />
							{__('Specify the page URL that the calendar is on at Settings -> Calendar -> URL.', 'booking-weir')}
						</Message>
					)}
					<Form.Field>
						<p>{__(`Following a link to an event navigates the calendar to the event's date and highlights it.`, 'booking-weir')}</p>
					</Form.Field>
					{repeat && (
						<Form.Field>
							<label htmlFor='event-link-date'>
								{__('Event date', 'booking-weir')}
							</label>
							<DateInput
								{...DateInputProps}
								id='event-link-date'
								placeholder={__('Event date...', 'booking-weir')}
								dateFormat='YYYY-MM-DD'
								value={eventStart}
								marked={[toDate(event.start)].concat(getRepeatEvents([event], {
									start: toString(Date.now()),
									end: toString(addMonths(Date.now(), 12)),
								}).map(event => toDate(event.start)))}
								markColor={event?.data?.color || 'blue'}
								onChange={(e, {value}) => setEventStart(value)}
								// localization={locale} // TODO: needs moment locale
							/>
						</Form.Field>
					)}
					{isBookable && (
						<Form.Field>
							<Checkbox
								key={id}
								toggle
								label={__('Open booking modal automatically', 'booking-weir')}
								checked={openBookingModal}
								onChange={() => {
									const nextValue = !openBookingModal;
									if(nextValue && showContent) {
										setShowContent(false);
									}
									setOpenBookingModal(nextValue);
								}}
							/>
						</Form.Field>
					)}
					{hasContent && (
						<Form.Field>
							<Checkbox
								key={id}
								toggle
								label={__('Open event content modal automatically', 'booking-weir')}
								checked={showContent}
								onChange={() => {
									const nextValue = !showContent;
									if(nextValue && openBookingModal) {
										setOpenBookingModal(false)
									}
									setShowContent(nextValue);
								}}
							/>
						</Form.Field>
					)}
					<Form.Field data-tooltip={__('Opens in new tab', 'booking-weir')}>
						<code
							style={{
								maxWidth: '100%',
								textOverflow: 'ellipsis',
								display: 'inline-block',
								overflow: 'hidden',
							}}
						>
							<a
								href={link}
								target='_blank'
								rel='noopener noreferrer'
								style={{whiteSpace: 'nowrap'}}
							>
								{link}
							</a>
						</code>
					</Form.Field>
				</Form>
			</Modal.Content>
			<Modal.Actions className='sui-root'>
				<Button
					primary
					icon='clipboard'
					content={__('Copy link', 'booking-weir')}
					onClick={() => {
						dispatch({type: 'CLEAR_MESSAGE'});
						copy(link);
						dispatch({
							type: 'SET_MESSAGE',
							value: {
								positive: true,
								icon: 'check',
								content: __('Copied to clipboard', 'booking-weir'),
							},
						});
					}}
				/>
				<Button
					inverted
					color='red'
					content={__('Close', 'booking-weir')}
					onClick={onClose}
				/>
			</Modal.Actions>
		</Modal>
	);
};
