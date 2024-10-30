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

import EventEditable from './EventEditable';


let EventEdit;
export default EventEdit = ({event}) => {
	const {
		id,
		type,
		repeat,
		bookable,
	} = event;

	const EDITABLES = useMemo(() => {
		return applyFilters('bw_event_editables', [
			...(['default', 'slot'].includes(type) ? [
				{
					key: 'title',
					label: __('Title', 'booking-weir'),
					type: 'text',
				},
				{
					key: 'excerpt',
					label: __('Content', 'booking-weir'),
					type: 'editor',
				},
			] : []),
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
			...(type === 'default' ? [
				{
					key: 'bookable',
					label: __('Bookable', 'booking-weir'),
					type: 'toggle',
				},
			...(bookable ? [{
				key: 'booking',
				type: 'booking',
			}] : []),
			] : []),
		], {type, repeat});
	}, [type, repeat, bookable]);

	if(!id) {
		return null;
	}

	return (
		<Form as='div'>
			{EDITABLES.map(schema => {
				const {key, label} = schema;
				return (
					<Form.Field key={key}>
						{label && <label htmlFor={`${id}-${key}`}>{label}</label>}
						<EventEditable
							eventId={id}
							value={event[key]}
							schema={schema}
						/>
					</Form.Field>
				);
			})}
		</Form>
	);
};
