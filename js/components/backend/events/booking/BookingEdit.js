import {__} from '@wordpress/i18n';
import {
	applyFilters,
} from '@wordpress/hooks';

import {
	useMemo,
} from 'react';

import {
	Form,
} from 'semantic-ui-react';

import EventEditable from 'components/backend/events/EventEditable';

import BOOKING_STATUSES from 'config/BOOKING_STATUSES';



let BookingEdit;
export default BookingEdit = ({booking}) => {
	const {
		id,
		data: {
			isWC,
		},
	} = booking;

	const EDITABLES = useMemo(() => {
		return applyFilters('bw_booking_editables', [
			{
				key: 'start',
				label: __('Start', 'booking-weir'),
				type: 'datetime',
			},
			{
				key: 'end',
				label: __('End', 'booking-weir'),
				type: 'datetime',
			},
			{
				key: 'price',
				label: __('Total price', 'booking-weir'),
				type: 'disabled',
			},
			...(!isWC ? [{
				key: 'paymentType',
				label: __('Payment type', 'booking-weir'),
				type: 'paymentType',
			}] : []),
			...(!isWC ? [{
				key: 'paymentMethod',
				label: __('Payment method', 'booking-weir'),
				type: 'paymentMethod',
				wc: false,
			}] : []),
			...(!isWC ? [{
				key: 'status',
				label: __('Status', 'booking-weir'),
				type: 'select',
				options: BOOKING_STATUSES.filter(({wc}) => !wc),
				wc: false,
			}] : []),
			{
				key: 'notes',
				label: __('Notes', 'booking-weir'),
				type: 'textarea',
			},
		]);
	}, [isWC]);

	if(!id) {
		return null;
	}

	return (
		<Form as='div'>
			{EDITABLES.map(schema => {
				const {key, label} = schema;
				return (
					<Form.Field key={key}>
						<label htmlFor={`${id}-${key}`}>{label}</label>
						<EventEditable
							eventId={id}
							value={booking[key]}
							schema={schema}
						/>
					</Form.Field>
				);
			})}
		</Form>
	);
};
