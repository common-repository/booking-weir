import {__} from '@wordpress/i18n';

import {
	List,
} from 'components/ui';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	getFieldProp,
	getFieldDisplayValue,
	DEFAULT_FIELD_IDS,
} from 'utils/field';

let BookerInfo;
export default BookerInfo = ({booking}) => {
	const calendar = useCurrentCalendar();
	const {
		firstName,
		lastName,
		email,
		phone,
		additionalInfo,
		fields,
	} = booking;

	const items = [];

	if(firstName && lastName) {
		items.push({
			key: 'name',
			header: __('Name', 'booking-weir'),
			content: `${firstName} ${lastName}`,
		});
	} else {
		if(firstName) {
			items.push({
				key: 'firstName',
				header: getFieldProp(calendar.fields, 'firstName', 'label') || __('First name', 'booking-weir'),
				content: firstName,
			});
		}
		if(lastName) {
			items.push({
				key: 'lastName',
				header: getFieldProp(calendar.fields, 'lastName', 'label') || __('Last name', 'booking-weir'),
				content: lastName,
			});
		}
	}

	if(email) {
		items.push({
			key: 'email',
			header: getFieldProp(calendar.fields, 'email', 'label') || __('E-mail', 'booking-weir'),
			content: email,
		});
	}

	if(phone) {
		items.push({
			key: 'phone',
			header: getFieldProp(calendar.fields, 'phone', 'label') || __('Phone', 'booking-weir'),
			content: phone,
		});
	}

	if(additionalInfo) {
		items.push({
			key: 'additionalInfo',
			header: getFieldProp(calendar.fields, 'additionalInfo', 'label') || __('Additional info', 'booking-weir'),
			content: (
				<div
					style={{
						maxWidth: 240,
						overflow: 'hidden',
						whiteSpace: 'nowrap',
						textOverflow: 'ellipsis',
					}}
				>
					{additionalInfo}
				</div>
			),
		});
	}

	fields && Object.keys(fields).forEach(fieldId => {
		if(DEFAULT_FIELD_IDS.includes(fieldId)) {
			return;
		}
		const value = getFieldDisplayValue(calendar.fields, fieldId, fields[fieldId], 'bw_no_value');
		if(value !== 'bw_no_value') {
			items.push({
				key: fieldId,
				header: getFieldProp(calendar.fields, fieldId, 'label'),
				content: value,
			});
		}
	});

	return <List items={items} />;
};
