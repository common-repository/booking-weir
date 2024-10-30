import {__} from '@wordpress/i18n';

import {
	Button,
} from 'semantic-ui-react';

let ToggleButton;
export default ToggleButton = ({enabled, onClick, ...props}) => {
	return (
		<Button
			color={enabled ? 'green' : 'yellow'}
			icon={enabled ? 'check' : 'close'}
			onClick={onClick}
			data-tooltip={enabled ? __('Disable', 'booking-weir') : __('Enable', 'booking-weir')}
			data-position='top right'
			{...props}
		/>
	);
};
