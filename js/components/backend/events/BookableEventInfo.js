import {__} from '@wordpress/i18n';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	push,
} from 'redux-first-history';

import {
	List,
	Table,
	Button,
} from 'semantic-ui-react';

import StatusLabel from 'components/backend/events/booking/StatusLabel';

import {
	formatDate,
	getCalendarUTCOffset,
} from 'utils/date';

import {
	getBookableEventPriceText,
} from 'utils/bookable';

import {
	useCurrentCalendar,
	useCurrencyRenderer,
} from 'hooks';

import {
	addEvent,
} from 'actions';

import EVENT_TYPES from 'config/EVENT_TYPES';

let BookableEventInfo;
export default BookableEventInfo = ({event}) => {
	const dispatch = useDispatch();
	const {id, settings} = useCurrentCalendar();
	const renderCurrency = useCurrencyRenderer();
	const bookings = useSelector(state => state.calendars.present[id].events.filter(({bookableEventId}) => bookableEventId === event.id));

	const {
		start,
		end,
		repeat,
		booking: {
			limit = 0,
		},
	} = event;

	return <>
		<List>
			<List.Item>
				<List.Content>
					<List.Header>{__('Price', 'booking-weir')}</List.Header>
					<List.Description>{getBookableEventPriceText(start, end, event, settings)}</List.Description>
				</List.Content>
			</List.Item>
			{bookings.length > 0 && (
				<List.Item>
					<List.Content>
						<List.Header>{`${__('Bookings', 'booking-weir')} (${bookings.length}/${limit || '∞'})`}</List.Header>
						<List.Description>
							<Table compact>
								<Table.Header>
									<Table.Row>
										{repeat && <Table.HeaderCell>{__('Date', 'booking-weir')}</Table.HeaderCell>}
										<Table.HeaderCell>{__('Name', 'booking-weir')}</Table.HeaderCell>
										<Table.HeaderCell collapsing>{__('Price', 'booking-weir')}</Table.HeaderCell>
										<Table.HeaderCell>{__('Status', 'booking-weir')}</Table.HeaderCell>
										<Table.HeaderCell collapsing></Table.HeaderCell>
									</Table.Row>
								</Table.Header>
								<Table.Body>
									{bookings.map(event => {
										const name = `${event.firstName} ${event.lastName}`.trim();
										return (
											<Table.Row
												key={event.id}
												warning={event.post_status === 'draft'}
												negative={event.post_status === 'trash'}
											>
												{repeat && <Table.Cell>{formatDate(event.start)}</Table.Cell>}
												<Table.Cell>{name}</Table.Cell>
												<Table.Cell>{renderCurrency(event.price)}</Table.Cell>
												<Table.Cell>
													<StatusLabel horizontal status={event.status} />
												</Table.Cell>
												<Table.Cell textAlign='right'>
													<Button
														compact
														primary
														content={__('View', 'booking-weir')}
														icon='eye'
														onClick={() => dispatch(push(`/${event.calendarId}/events/${event.id}`))}
													/>
												</Table.Cell>
											</Table.Row>
										);
									})}
								</Table.Body>
							</Table>
						</List.Description>
					</List.Content>
				</List.Item>
			)}
		</List>
		<Button
			primary
			icon='add'
			content={__('Add booking', 'booking-weir')}
			onClick={() => {
				dispatch(addEvent(id, {
					start,
					end,
					type: 'booking',
					title: EVENT_TYPES.find(({value}) => value === 'booking').text,
					firstName: __('Booker', 'booking-weir'),
					email: 'changeme@email.com',
					bookableEventId: event.id,
					utcOffset: getCalendarUTCOffset(settings),
				}));
			}}
		/>
	</>;
};
