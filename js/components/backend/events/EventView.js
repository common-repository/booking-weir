import {__, sprintf} from '@wordpress/i18n';

import {
	useState,
	useEffect,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Segment,
	Header,
	Grid,
	Button,
	Message,
	Icon,
} from 'semantic-ui-react';

import {
	Navigate,
} from 'react-router-dom';

import EventTitle from './EventTitle';
import AddEvent from './AddEvent';
import UnselectEvent from './UnselectEvent';
import EventActions from './EventActions';
import EventActionsDropdown from './EventActionsDropdown';
import EventLinkModal from './EventLinkModal';
import ToggleEditMode from './ToggleEditMode';

import Event from './Event';
import Booking from './booking/Booking';

import JsonView from 'utils/JsonView';

import {
	useCurrentCalendarId,
	useSelectedEventId,
	useSelectedEvent,
} from 'hooks';

import {
	setDraftEvent,
	setSelectedEvent,
	fetchEvent,
} from 'actions';

let EventView;
export default EventView = () => {
	const calendarId = useCurrentCalendarId();
	const selectedEventId = useSelectedEventId();
	const event = useSelectedEvent();
	const {id} = event;
	const dispatch = useDispatch();
	const eventsLoaded = useSelector(state => state.calendar.eventsLoaded.get(calendarId));
	const isFetchingEvents = useSelector(state => state.ui.isFetchingEvents);
	const [fetched, setFetched] = useState([]);

	useEffect(() => {
		/**
		 * Attempt to load event manually if it wasn't part of the initial query.
		 */
		if(!id && selectedEventId > 0 && eventsLoaded && !isFetchingEvents && !fetched.includes(selectedEventId)) {
			setFetched([...fetched, selectedEventId]);
			dispatch(fetchEvent(calendarId, selectedEventId));
		}
	}, [id, selectedEventId, eventsLoaded, isFetchingEvents, fetched, calendarId, dispatch]);

	if(eventsLoaded && !isFetchingEvents && selectedEventId === 'draft' && !event.id) {
		/**
		 * Draft event is selected but doesn't exist.
		 */
		return <Navigate to={`/${calendarId}/events`} />;
	}

	if(!id) {
		if(selectedEventId > 0) {
			if(eventsLoaded && !isFetchingEvents && fetched.includes(selectedEventId)) {
				return (
					<Message negative>
						<Icon name='warning' />
						{sprintf(__('Event %s not found.', 'booking-weir'), selectedEventId)}
					</Message>
				);
			}
			return <Segment basic loading />;
		}
		return null;
	}

	const {type} = event;
	const isDraft = id === 'draft';

	return <>
		<Grid verticalAlign='middle'>
			<Grid.Column>
				<Header size='large'>
					<EventTitle event={event} />
				</Header>
			</Grid.Column>
		</Grid>
		<Segment.Group>
			<Segment color={event?.data?.color}>
				<ToggleEditMode />
				{type === 'booking' && <Booking booking={event} />}
				{type !== 'booking' && <Event event={event} />}
			</Segment>
			<Segment secondary>
				<Grid columns={2} stackable>
					<Grid.Column width={10}>
						{isDraft && <AddEvent event={event} />}
						{!isDraft && <UnselectEvent />}
						<EventActionsDropdown event={event} />
					</Grid.Column>
					<Grid.Column width={6} textAlign='right'>
						{isDraft && (
							<Button
								color='red'
								icon='trash'
								data-tooltip={__('Discard draft', 'booking-weir')}
								data-position='top right'
								onClick={() => dispatch(setSelectedEvent(-1)) && dispatch(setDraftEvent({}))}
							/>
						)}
						{!isDraft && <EventActions event={event} />}
						<JsonView id='event' obj={event} />
					</Grid.Column>
				</Grid>
			</Segment>
		</Segment.Group>
		<EventLinkModal key={event.id} event={event} />
	</>;
};
