import {__} from '@wordpress/i18n';

import {
	Icon,
	Message,
	Grid,
} from 'semantic-ui-react';

import FieldsEdit from './FieldsEdit';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	DEFAULT_FIELD_IDS,
	hasField,
} from 'utils/field';

let Fields;
export default Fields = () => {
	const {
		settings,
		fields,
	} = useCurrentCalendar();

	const isProduct = settings?.product > 0;
	const hasInvalidFields = isProduct && DEFAULT_FIELD_IDS.filter(defaultFieldId => hasField(fields, defaultFieldId)).length > 0;

	return (
		<Grid columns={1}>
			<Grid.Column>
				<Message info>
					<Icon name='info' />
					{__('Fields can be used to collect info about the booker and their preferences.', 'booking-weir')}
				</Message>
				{hasInvalidFields && (
					<Message warning>
						<Icon name='warning' />
						{__('Default fields (First name, Last name, E-mail, Phone, Additional info and Terms, marked in red) are populated automatically from WooCommerce order info.', 'booking-weir')}
					</Message>
				)}
			</Grid.Column>
			<Grid.Column>
				<FieldsEdit />
			</Grid.Column>
		</Grid>
	);
};
