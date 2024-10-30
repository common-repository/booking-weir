import {__, _x} from '@wordpress/i18n';

import {
	addFilter,
} from '@wordpress/hooks';

import {
	useEffect,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Header,
	Segment,
	Grid,
	Label,
} from 'semantic-ui-react';

import {
	Routes,
	Route,
	Link,
} from 'react-router-dom';

import Actions from './Actions';
import CalendarEdit from './CalendarEdit';
import CalendarsList from './CalendarsList';
import Message from './Message';

import {
	fetchCalendars,
} from 'actions';

import 'components/ui/rbc.scss';
import 'components/ui/rbc.less';
import './style.less';


let BWAdmin;
export default BWAdmin = () => {
	const dispatch = useDispatch();
	const isFetching = useSelector(state => state.ui.isFetchingCalendars);

	/**
	 * Notify of initialization and load calendars.
	 */
	useEffect(() => {
		dispatch({type: 'ADMIN_INIT'});
		dispatch(fetchCalendars());
	}, [dispatch]);

	return <>
		<Segment basic style={{marginRight: 8}}>
			<Grid columns='equal' stackable>
				<Grid.Column verticalAlign='middle' className='mca'>
					<Link to='/' style={{display: 'inline-block'}}>
						<Header size='huge' as='h1'>
							{booking_weir_data.white_label ? _x('Booking', 'White label plugin name', 'booking-weir') : __('Booking Weir', 'booking-weir')}
							<Label horizontal color='blue'>{BOOKING_WEIR_VER}</Label>
						</Header>
					</Link>
				</Grid.Column>
				<Grid.Column textAlign='right' className='mca'>
					<Actions />
				</Grid.Column>
			</Grid>
		</Segment>
		<Segment
			loading={isFetching}
			style={{
				marginRight: 20,
				marginTop: '-1em',
				marginBottom: '1em',
				minHeight: 140,
			}}
		>
			<Routes>
				<Route index element={<CalendarsList />} />
				<Route path='/:calendarId/*' element={<CalendarEdit />} />
			</Routes>
		</Segment>
		<Message />
	</>;
};
