import {__} from '@wordpress/i18n';
import {useMediaQuery} from '@wordpress/compose';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	setSelectedEvent,
} from 'actions';

let UnselectEvent;
export default UnselectEvent = () => {
	const dispatch = useDispatch();
	const isStacked = useMediaQuery('(max-width: 768px) and (min-width: 320px)');

	if(!isStacked) {
		return null;
	}

	return (
		<Button
			basic
			icon='caret up'
			labelPosition='right'
			content={__('Back to calendar', 'booking-weir')}
			style={{float: 'none', marginBottom: '0.25em'}}
			onClick={() => dispatch(setSelectedEvent(-1))}
		/>
	);
};
