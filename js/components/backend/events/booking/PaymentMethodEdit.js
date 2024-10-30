import {__} from '@wordpress/i18n';

import {
	Select,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
} from 'hooks';

import PAYMENT_METHODS from 'config/PAYMENT_METHODS';

let PaymentMethodEdit;
export default PaymentMethodEdit = props => {
	const {paymentMethods: enabledPaymentMethods} = useCurrentCalendar();

	const options = [{id: '', label: __('None', 'booking-weir')}].concat(PAYMENT_METHODS).map(({id, label}) => ({
		key: id,
		text: label,
		value: id,
		...(id && !enabledPaymentMethods.includes(id) && {
			label: {
				color: 'red',
				size: 'tiny',
				horizontal: true,
				content: __('Disabled', 'booking-weir'),
			},
		}),
	}));

	return (
		<Select
			selectOnBlur={false}
			{...props}
			options={options}
			{...(!props.value && {
				placeholder: __('None', 'booking-weir'),
			})}
		/>
	);
};
