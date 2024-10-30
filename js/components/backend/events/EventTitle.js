import {
	useSelector,
} from 'react-redux';

import {
	Link,
} from 'react-router-dom';

import {
	Icon,
} from 'semantic-ui-react';

import {
	useCurrentCalendarId,
} from 'hooks';

import EventLink from './EventLink';
import BookingLink from './booking/BookingLink';
import StatusLabel from './booking/StatusLabel';

let EventTitle;
export default EventTitle = ({event}) => {
	const id = useCurrentCalendarId();
	const events = useSelector(state => state.calendars.present[id].events);

	const {
		status,
		type,
		bookableEventId,
		slotId,
		data: {
			titlePrefix,
			titleText,
		},
	} = event;

	let parent;
	let parentTitle;
	if(bookableEventId) {
		parent = events.find(event => bookableEventId === event.id);
	}
	if(slotId) {
		parent = events.find(event => slotId === event.id);
	}
	if(parent) {
		parentTitle = <>
			<Link to={`/${parent.calendarId}/events/${parent.id}`}>
				{parent.data.titleText}
			</Link>
			<span>
				<Icon
					disabled
					size='tiny'
					name='right chevron'
					style={{
						margin: '0 0.5em',
						transform: 'translateY(-3px)',
					}}
				/>
			</span>
		</>;
	}

	return <>
		{parentTitle}
		<BookingLink event={event} />
		<EventLink event={event} />
		{titlePrefix}
		{titleText}
		{(type === 'booking' && status) && (
			<StatusLabel status={status} />
		)}
	</>;
};
