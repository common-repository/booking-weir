import {__} from '@wordpress/i18n';
import {
	applyFilters,
} from '@wordpress/hooks';

import {
	Icon,
	Menu,
	Label,
} from 'semantic-ui-react';

import {
	NavLink,
} from 'react-router-dom';

import {
	countFields,
	hasField,
	DEFAULT_FIELD_IDS,
} from 'utils/field';


const getMenuItems = args => {
	const {
		calendar,
		eventsLoaded,
	} = args;

	if(!calendar) {
		return [];
	}

	const {
		id,
		events,
		fields,
		paymentMethods,
	} = calendar;

	const isProduct = calendar?.settings?.product > 0;
	const fieldCount = countFields(fields);
	const hasFields = fieldCount > 0;
	const hasInvalidFields = isProduct && DEFAULT_FIELD_IDS.filter(defaultFieldId => hasField(fields, defaultFieldId)).length > 0;

	return applyFilters('bw_admin_menu_items', [
		<Menu.Item
			key='events'
			as={NavLink}
			to={`/${id}/events`}
		>
			<Icon name='calendar plus outline' />
			{__('Events', 'booking-weir')}
			{eventsLoaded && (
				<Label size='small' color='blue' style={{marginRight: 0}}>
					{events.length}
				</Label>
			)}
		</Menu.Item>,
		<Menu.Item
			key='fields'
			as={NavLink}
			to={`/${id}/fields`}
		>
			<Icon name='clipboard outline' />
			{__('Fields', 'booking-weir')}
			<Label size='small' color={hasFields && hasInvalidFields ? 'yellow' : 'blue'} style={{marginRight: 0}}>
				{fieldCount}
			</Label>
		</Menu.Item>,
		<Menu.Item
			key='payment'
			as={NavLink}
			to={`/${id}/payment`}
		>
			<Icon name='payment' />
			{__('Payment', 'booking-weir')}
			{isProduct && (
				<Label size='small' color='pink' style={{marginRight: 0}}>
					{__('WC', 'booking-weir')}
				</Label>
			)}
			{!isProduct && (
				<Label size='small' color='blue' style={{marginRight: 0}}>
					{paymentMethods.length}
				</Label>
			)}
		</Menu.Item>,
		<Menu.Item
			key='settings'
			as={NavLink}
			to={`/${id}/settings`}
		>
			<Icon name='cog' />
			{__('Settings', 'booking-weir')}
		</Menu.Item>,
	], args);
};


export default getMenuItems;
