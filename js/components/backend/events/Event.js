import {
	useSelector,
} from 'react-redux';

import EventInfo from './EventInfo';
import EventEdit from './EventEdit';

let Event;
export default Event = ({event}) => {
	const editMode = useSelector(state => state.ui.editMode);

	return <>
		{!editMode && <EventInfo event={event} />}
		{editMode && <EventEdit event={event} />}
	</>;
};
