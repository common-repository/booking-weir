import {__} from '@wordpress/i18n';
import {
	applyFilters,
} from '@wordpress/hooks';

import {
	Form,
	Message,
	Icon,
} from 'semantic-ui-react';

import EventEditable from 'components/backend/events/EventEditable';
import BookerInfo from './BookerInfo';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	DEFAULT_FIELD_IDS,
	hasField,
	getFieldProp,
} from 'utils/field';


const EDITABLES = applyFilters('bw_booker_editables', [
	{
		key: 'firstName',
		label: __('First name', 'booking-weir'),
		type: 'text',
	},
	{
		key: 'lastName',
		label: __('Last name', 'booking-weir'),
		type: 'text',
	},
	{
		key: 'email',
		label: __('E-mail', 'booking-weir'),
		type: 'email',
	},
	{
		key: 'phone',
		label: __('Phone', 'booking-weir'),
		type: 'text',
	},
	{
		key: 'additionalInfo',
		label: __('Additional info', 'booking-weir'),
		type: 'textarea',
	},
]);

let BookerEdit;
export default BookerEdit = ({booking}) => {
	const {fields} = useCurrentCalendar();
	const {
		id,
		orderId,
		data: {
			isWC,
		},
	} = booking;

	if(!id || (isWC && !orderId)) {
		return null;
	}

	return (
		<Form as='div' key={`booker-info-edit-${id}`}>
			{isWC && <>
				<BookerInfo booking={booking} hideFields />
				<Message info>
					<Icon name='info' />
					{__('Billing info can be changed by editing the order: ', 'booking-weir')}
					<a href={`/wp-admin/post.php?post=${orderId}&action=edit`} target='_blank' rel='noopener noreferrer'>{`#${orderId}`}</a>
				</Message>
			</>}
			{Object.keys(EDITABLES).map(i => {
				const {key} = EDITABLES[i];
				if(isWC && DEFAULT_FIELD_IDS.includes(key)) {
					return null;
				}
				const label = hasField(fields, key) ? getFieldProp(fields, key, 'label') : EDITABLES[i].label;
				return (
					<div className='field' key={key}>
						{label && <label htmlFor={`${id}-${key}`}>{label}</label>}
						<EventEditable
							eventId={id}
							value={booking[key] || undefined}
							schema={EDITABLES[i]}
						/>
					</div>
				);
			})}
		</Form>
	);
};
