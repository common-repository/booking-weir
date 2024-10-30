import {__} from '@wordpress/i18n';

import {
	List,
	Icon,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	hasField,
	getFieldProp,
	getFieldDisplayValue,
} from 'utils/field';

let BookerInfo;
export default BookerInfo = ({booking, hideFields = false}) => {
	const calendar = useCurrentCalendar();

	const {
		id,
		firstName,
		lastName,
		email,
		phone,
		additionalInfo,
		fields,
		status,
		data: {
			isWC,
		},
	} = booking;

	const blank = <Icon name='window minimize outline' disabled />;

	if(isWC && status === 'detached') {
		return null;
	}

	return (
		<List key={`booker-info-${id}`}>
			{(firstName || hasField(calendar.fields, 'firstName')) && (
				<List.Item>
					<List.Content>
						<List.Header>{getFieldProp(calendar.fields, 'firstName', 'label') || __('First name', 'booking-weir')}</List.Header>
						<List.Description>{firstName || blank}</List.Description>
					</List.Content>
				</List.Item>
			)}
			{(lastName || hasField(calendar.fields, 'lastName')) && (
				<List.Item>
					<List.Content>
						<List.Header>{getFieldProp(calendar.fields, 'lastName', 'label') || __('Last name', 'booking-weir')}</List.Header>
						<List.Description>{lastName || blank}</List.Description>
					</List.Content>
				</List.Item>
			)}
			{(email || hasField(calendar.fields, 'email')) && (
				<List.Item>
					<List.Content>
						<List.Header>{getFieldProp(calendar.fields, 'email', 'label') || __('E-mail', 'booking-weir')}</List.Header>
						<List.Description style={{overflow: 'hidden', textOverflow: 'ellipsis'}}>
							{email ? <a href={`mailto:${email}`}>{email}</a> : blank}
						</List.Description>
					</List.Content>
				</List.Item>
			)}
			{(phone || hasField(calendar.fields, 'phone')) && (
				<List.Item>
					<List.Content>
						<List.Header>{getFieldProp(calendar.fields, 'phone', 'label') || __('Phone', 'booking-weir')}</List.Header>
						<List.Description>
							{phone ? <a href={`tel:${phone}`}>{phone}</a> : blank}
						</List.Description>
					</List.Content>
				</List.Item>
			)}
			{(additionalInfo || hasField(calendar.fields, 'additionalInfo')) && (
				<List.Item>
					<List.Content>
						<List.Header>{getFieldProp(calendar.fields, 'additionalInfo', 'label') || __('Additional info', 'booking-weir')}</List.Header>
						<List.Description>{additionalInfo || blank}</List.Description>
					</List.Content>
				</List.Item>
			)}
			{fields && !hideFields && Object.keys(fields).map(fieldId => {
				if(!hasField(calendar.fields, fieldId)) {
					return null;
				}
				return (
					<List.Item key={fieldId}>
						<List.Content>
							<List.Header>{getFieldProp(calendar.fields, fieldId, 'label')}</List.Header>
							<List.Description>{getFieldDisplayValue(calendar.fields, fieldId, fields[fieldId], blank)}</List.Description>
						</List.Content>
					</List.Item>
				);
			})}
		</List>
	);
};
