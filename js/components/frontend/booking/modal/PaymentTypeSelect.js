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

let PaymentTypeSelect;
export default PaymentTypeSelect = () => {
	const dispatch = useDispatch();
	const booking = useBooking();
	const {paymentTypes} = useCurrentCalendar();
	const enabledPaymentTypes = paymentTypes.filter(({enabled}) => !!enabled);

	if(!enabledPaymentTypes.length) {
		return null;
	}

	const [type, setType] = useState(booking.paymentType || enabledPaymentTypes[0].id);

	useEffect(() => {
		if(booking.paymentType !== type) {
			dispatch(updateBooking({
				...booking,
				paymentType: type,
			}));
		}
	}, [type, booking, dispatch]);

	const items = enabledPaymentTypes.map(({id, name, amount}) => ({
		name,
		key: id,
		label: `${name} (${amount}%)`,
		value: id,
	}));

	return (
		<List relaxed>
			<List.Item
				header={__('Payment type', 'booking-weir')}
				content={(
					<RadioGroup
						vertical
						defaultCheckedValue={type}
						items={items}
						onCheckedValueChange={(e, {value}) => setType(value)}
					/>
				)}
			/>
		</List>
	);
};
