import {__} from '@wordpress/i18n';

import {
	Icon,
	Message,
} from 'semantic-ui-react';

import PaymentEdit from './PaymentEdit';

import {
	useCurrentCalendar,
} from 'hooks';

let Payment;
export default Payment = () => {
	const {settings} = useCurrentCalendar();
	const isProduct = settings?.product > 0;

	if(isProduct) {
		return (
			<Message info icon>
				<Icon name='cart' />
				<Message.Content>
					<Message.Header>{__('Payment is handled by WooCommerce', 'booking-weir')}</Message.Header>
					{__('Bookings in this calendar are added to WC cart and payment is handled in WC checkout.', 'booking-weir')}
					<br />
					{__(`If you wish to stop using WC then remove this calendar's association with a WC product from Settings -> Calendar -> Product.`, 'booking-weir')}
				</Message.Content>
			</Message>
		);
	}

	return <PaymentEdit />;
};
