import {__} from '@wordpress/i18n';

import {
	Button,
} from 'semantic-ui-react';

let DuplicateButton;
export default DuplicateButton = ({onClick, ...props}) => {
	return (
		<Button
			secondary
			icon='copy'
			onClick={onClick}
			data-tooltip={__('Duplicate', 'booking-weir')}
			data-position='top right'
			{...props}
		/>
	);
};
