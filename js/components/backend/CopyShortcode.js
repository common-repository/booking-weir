import {__} from '@wordpress/i18n';

import cx from 'classnames';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	copy,
} from 'utils/clipboard';

let CopyShortcode;
export default CopyShortcode = ({calendarId, ...props}) => {
	const dispatch = useDispatch();

	return (
		<Button
			basic
			onClick={() => {
				dispatch({type: 'CLEAR_MESSAGE'});
				copy(`[bw-booking id="${calendarId}" /]`);
				dispatch({
					type: 'SET_MESSAGE',
					value: {
						positive: true,
						icon: 'check',
						header: __('Copied shortcode to clipboard', 'booking-weir'),
						content: __('Paste it to any page to display the booking calendar.', 'booking-weir'),
					},
				});
			}}
			icon='code'
			size='small'
			data-tooltip={__('Copy shortcode', 'booking-weir')}
			data-position='bottom center'
			{...props}
			className={cx('shadowless', props.className)}
		/>
	);
};
