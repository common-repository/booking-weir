import EventSegment from 'components/backend/calendar/EventSegment';

let AgendaEvent;
export default AgendaEvent = ({event}) => {
	return <EventSegment event={event} type='agenda' />;
};
