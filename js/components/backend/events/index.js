import {__} from '@wordpress/i18n';

import {
	Grid,
	Divider,
	Message,
	Icon,
	List,
	Label,
	Header,
} from 'semantic-ui-react';

import {
	Routes,
	Route,
} from 'react-router-dom';

import BookingCalendar from 'components/backend/calendar';
import EventView from './EventView';
import EventsQuerySelect from './EventsQuerySelect';

import EVENT_TYPES from 'config/EVENT_TYPES';

let EventsEdit;
export default EventsEdit = () => {
	return (
		<Grid>
			<Grid.Column
				id='calendar-view'
				mobile={16}
				tablet={8}
				computer={10}
			>
				<BookingCalendar />
				<Divider hidden style={{margin: '0.25rem 0'}} />
				<EventsQuerySelect />
			</Grid.Column>
			<Grid.Column
				id='event-view'
				mobile={16}
				tablet={8}
				computer={6}
			>
				<Routes>
					<Route
						index
						element={<>
							<Message info>
								<Icon name='info' />
								{__('Click and drag in the calendar to add a new event.', 'booking-weir')}
							</Message>
							<Message info>
								<Icon name='info' />
								{__('Click on an existing event in the calendar to edit it.', 'booking-weir')}
							</Message>
							<Header sub style={{marginTop: '1em'}}>{__('Legend', 'booking-weir')}</Header>
							<List id='event-legend' style={{marginTop: '0.33em'}}>
								{EVENT_TYPES.map(({key, text, color}) => (
									<List.Item
										key={key}
										icon={(
											<div className='paddingless image'>
												<Label
													circular
													empty
													color={color}
													size='large'
												/>
											</div>
										)}
										content={text}
									/>
								))}
							</List>
						</>}
					/>
					<Route path=':eventId' element={<EventView />} />
				</Routes>
			</Grid.Column>
		</Grid>
	);
};
