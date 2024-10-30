import {__} from '@wordpress/i18n';
import {useMediaQuery} from '@wordpress/compose';

import {
	useSelector,
} from 'react-redux';

import {
	Header,
	Grid,
	Divider,
	Button,
} from 'semantic-ui-react';

import EditCalendar from './EditCalendar';
import CopyShortcode from './CopyShortcode';
import DuplicateCalendar from './DuplicateCalendar';
import DeleteCalendar from './DeleteCalendar';
import AddCalendar from './AddCalendar';
import ImportCalendars from './ImportCalendars';
import AspectRatioSegment from './AspectRatioSegment';
import getActionButtons from './getActionButtons';

import ImportExport from 'components/backend/controls/ImportExport';
import JsonView from 'utils/JsonView';

import {
	withoutDynamicData,
} from 'utils/calendars';

let CalendarsList;
export default CalendarsList = () => {
	const calendars = useSelector(state => state.calendars.present);

	/**
	 * Grid column count.
	 */
	const three = useMediaQuery('(max-width: 1620px) and (min-width: 1280px)');
	const two = useMediaQuery('(max-width: 1279px) and (min-width: 768px)');

	return <>
		<Grid columns={three ? 3 : (two ? 2 : 4)} padded stretched stackable>
			{Object.keys(calendars).map(calendarId => (
				<Grid.Column key={calendarId}>
					<AspectRatioSegment raised textAlign='center' className='paddingless'>
						<Header
							size='large'
							style={{paddingTop: '0.4em'}}
						>
							{calendars[calendarId].name}
						</Header>
						<EditCalendar calendarId={calendarId} />
						<Divider
							hidden
							style={{marginTop: '0.75rem', marginBottom: 0}}
						/>
						<CopyShortcode calendarId={calendarId} />
						<DuplicateCalendar calendarId={calendarId} />
						<DeleteCalendar calendarId={calendarId} className='last' />
					</AspectRatioSegment>
				</Grid.Column>
			))}
			{!!booking_weir_data.is_admin && (
				<Grid.Column>
					<AspectRatioSegment raised secondary flex>
						<label htmlFor='calendar-name'>
							{__('A calendar contains bookings and events. Bookings in a calendar cannot overlap, but you can create multiple calendars such as "Room 1" and "Room 2" to have multiple bookings for different resources or services at the same time. Each calendar has their own settings for booking availability times, how much it costs, payment, notification e-mails etc.', 'booking-weir')}
						</label>
						<AddCalendar />
					</AspectRatioSegment>
				</Grid.Column>
			)}
			{!!booking_weir_data.is_admin && (
				<Grid.Column>
					<AspectRatioSegment raised secondary flex>
						<label style={{cursor: 'default'}}>
							{__(`Calendars can be exported with their settings, prices, extras, fields and payment configurations (events not included). Using the exported file it's then possible to import one or all the calendars from it. Calendar events are a WordPress custom post type, to export and import them Tools in the WordPress admin dashboard can be used.`, 'booking-weir')}
						</label>
						<div>
							<ImportExport id='calendars' data={withoutDynamicData(calendars)} Importer={ImportCalendars} />
							<JsonView id='calendars' obj={{calendars: withoutDynamicData(calendars)}} floated='right' />
						</div>
					</AspectRatioSegment>
				</Grid.Column>
			)}
			{!booking_weir_data.white_label && (
				<Grid.Column>
					<AspectRatioSegment raised secondary textAlign='center'>
						{getActionButtons()}
					</AspectRatioSegment>
				</Grid.Column>
			)}
		</Grid>
	</>;
};
