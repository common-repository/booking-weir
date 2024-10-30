import {__} from '@wordpress/i18n';
import {useViewportMatch} from '@wordpress/compose';

import {
	Grid,
	Header,
	Message,
	Icon,
} from 'semantic-ui-react';

import PaymentTypeEdit from './PaymentTypeEdit';
import PaymentMethodEdit from './PaymentMethodEdit';

let PaymentEdit;
export default PaymentEdit = () => {
	const isHuge = useViewportMatch('huge');

	return (
		<Grid columns={isHuge ? 2 : 1}>
			<Grid.Column>
				<Message info>
					<Icon name='info' />
					{__(`Payment types allow the user to choose how big percentage of the total booking cost to pay up front. When a payment type with an amount less than 100 is used then the invoice will reflect the smaller value and upon successful payment the booking status will be changed to "Escrow paid" instead of "Paid in full".`, 'booking-weir')}
				</Message>
				<Header as='h3'>{__('Payment types', 'booking-weir')}</Header>
				<PaymentTypeEdit />
			</Grid.Column>
			<Grid.Column>
				<Message info>
					<Icon name='info' />
					{__(`When no payment methods are enabled an "invoice" e-mail is still sent confirming the booking. You can optionally include a PDF invoice in that e-mail by enabling the "Settings -> PDF -> Send invoice without payment method" option.`, 'booking-weir')}
				</Message>
				<Header as='h3'>{__('Payment methods', 'booking-weir')}</Header>
				<PaymentMethodEdit />
			</Grid.Column>
		</Grid>
	);
};
