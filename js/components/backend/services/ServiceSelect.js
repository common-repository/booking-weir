import {__} from '@wordpress/i18n';

import {
	Select,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
} from 'hooks';

let ServiceSelect;
export default ServiceSelect = props => {
	const {services} = useCurrentCalendar();
	const placeholder = props.placeholder || __('None', 'booking-weir');

	if(!services.length) {
		return null;
	}

	const options = (props.multiple ? [] : ([{
		key: 'none',
		text: placeholder,
		value: '',
	}])).concat(services.map(({id, name}) => ({
		key: id,
		text: name,
		value: id,
	})));

	return (
		<Select
			selectOnBlur={false}
			{...props}
			options={options}
			{...(!props.value && {placeholder})}
		/>
	);
};
