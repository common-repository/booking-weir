import {__} from '@wordpress/i18n';
import {
	applyFilters,
} from '@wordpress/hooks';

import {
	Button,
	Divider,
} from 'semantic-ui-react';

const getActionButtons = () => {
	return applyFilters('bw_action_buttons', [
		<Button
			key='premium'
			positive
			size='big'
			icon='gem'
			href={`${BOOKING_WEIR_URL}/premium`}
			target='_blank'
			rel='noopener noreferrer'
			content={__('Premium', 'booking-weir')}
			className='last'
		/>,
		<Divider hidden key='d-1' />,
		<Button
			key='docs'
			primary
			size='big'
			icon='book'
			href={`${BOOKING_WEIR_URL}/docs`}
			target='_blank'
			rel='noopener noreferrer'
			content={__('Documentation', 'booking-weir')}
			className='last'
		/>,
		<Divider hidden key='d-2' />,
		<Button
			key='support'
			secondary
			size='big'
			icon='help'
			href='https://wordpress.org/support/plugin/booking-weir'
			target='_blank'
			rel='noopener noreferrer'
			content={__('Support', 'booking-weir')}
			className='last'
		/>,
	]);
};


export default getActionButtons;
