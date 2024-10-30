import {__, _x} from '@wordpress/i18n';

import {
	Select,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
} from 'hooks';

let PaymentTypeEdit;
export default PaymentTypeEdit = props => {
	const {paymentTypes} = useCurrentCalendar();

	const types = [{
		id: 'default',
		name: _x('Default', 'Default payment type name', 'booking-weir'),
		amount: 100,
	}].concat(paymentTypes).map(({id, name, amount, enabled}) => ({
		key: id,
		text: `${name} (${amount}%)`,
		value: id,
		...(id !== 'default' && !enabled && {
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
			{...props}
			options={types}
			value={props.value || types[0].value}
		/>
	);
};
