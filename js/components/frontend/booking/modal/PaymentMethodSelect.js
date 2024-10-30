import {__} from '@wordpress/i18n';

import {
	useState,
	useEffect,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	List,
	RadioGroup,
} from 'components/ui';

import {
	useBooking,
	useCurrentCalendar,
} from 'hooks';

import {
	updateBooking,
} from 'actions';

import PAYMENT_METHODS from 'config/PAYMENT_METHODS';

let PaymentMethodSelect;
export default PaymentMethodSelect = () => {
	const dispatch = useDispatch();
	const booking = useBooking();
	const {paymentMethods} = useCurrentCalendar();

	const enabledPaymentMethods = paymentMethods.filter(
		// Filter out unavailable payment methods (e.g. from a plugin that was disabled).
		enabledPaymentMethod => PAYMENT_METHODS.findIndex(availablePaymentMethod => availablePaymentMethod.id === enabledPaymentMethod) > -1
	);

	const [selectedPaymentMethod, setMethod] = useState(booking.paymentMethod || enabledPaymentMethods[0]);

	useEffect(() => {
		if(booking.paymentMethod !== selectedPaymentMethod) {
			dispatch(updateBooking({
				...booking,
				paymentMethod: selectedPaymentMethod,
			}));
		}
	}, [selectedPaymentMethod, booking, dispatch]);

	const items = enabledPaymentMethods.map(id => ({
		name: id,
		key: id,
		label: PAYMENT_METHODS.find(availablePaymentMethod => availablePaymentMethod.id === id).label,
		value: id,
	}));

	if(!enabledPaymentMethods.length) {
		return null;
	}

	return (
		<List relaxed>
			<List.Item
				header={__('Payment method', 'booking-weir')}
				content={(
					<RadioGroup
						vertical
						defaultCheckedValue={selectedPaymentMethod}
						items={items}
						onCheckedValueChange={(e, {value}) => setMethod(value)}
					/>
				)}
			/>
		</List>
	);
};
