import {__, _x, _n} from '@wordpress/i18n';

import {
	useState,
} from 'react';

import {
	Form,
	Grid,
	Input,
	Segment,
	Label,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
	useEvent,
	useOnChange,
} from 'hooks';

import {
	getBookableEventPriceText,
} from 'utils/bookable';

let BookableEventEdit;
export default BookableEventEdit = ({eventId, onChange}) => {
	const {settings} = useCurrentCalendar();
	const event = useEvent(eventId);
	const {booking: currentBooking} = event;
	const [booking, setBooking] = useState(currentBooking || {});
	useOnChange(booking, onChange);

	const {
		start,
		end,
	} = event;

	const {
		price = 0,
		limit = 0,
	} = booking;

	return (
		<Segment secondary>
			<Grid stackable>
				<Grid.Row columns={2}>
					<Grid.Column>
						<Form.Field>
							<label htmlFor='booking-price'>{__('Price per hour', 'booking-weir')}</label>
							<Input fluid labelPosition='right'>
								<input
									id='booking-price'
									type='number'
									min='1'
									step='1'
									value={price}
									onChange={e => setBooking({...booking, price: parseInt(e.target.value)})}
								/>
								<Label
									content={__('Price', 'booking-weir')}
									detail={getBookableEventPriceText(start, end, event, settings)}
								/>
							</Input>
							<p className='description'>{__('Determines the price for booking this event.', 'booking-weir')}</p>
						</Form.Field>
					</Grid.Column>
					<Grid.Column>
						<Form.Field>
							<label htmlFor='booking-limit'>{__('Limit', 'booking-weir')}</label>
							<Input
								id='booking-limit'
								type='number'
								fluid
								min='0'
								step='1'
								value={limit}
								onChange={(e, {value}) => setBooking({...booking, limit: parseInt(value)})}
								labelPosition='right'
								label={{
									content: limit > 0 ? _x('bookings', 'Limit: n bookings', 'booking-weir') : __('unlimited', 'booking-weir'),
								}}
							/>
							<p className='description'>{__('How many times can this event be booked.', 'booking-weir')}</p>
						</Form.Field>
					</Grid.Column>
				</Grid.Row>
			</Grid>
		</Segment>
	);
};
