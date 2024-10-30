import {__} from '@wordpress/i18n';
import {addQueryArgs} from '@wordpress/url';

import {
	useCallback,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	getUrl as getCalendarUrl,
} from 'utils/calendar';

import {
	copy,
} from 'utils/clipboard';

let CopyServiceLink;
export default CopyServiceLink = ({service}) => {
	const calendar = useCurrentCalendar();
	const dispatch = useDispatch();

	const link = addQueryArgs(
		getCalendarUrl(calendar),
		{
			[booking_weir_data.GET.service.view]: service.id,
		}
	);

	const copyLink = useCallback(() => {
		dispatch({type: 'CLEAR_MESSAGE'});
		copy(link);
		dispatch({
			type: 'SET_MESSAGE',
			value: {
				positive: true,
				icon: 'check',
				content: __('Copied to clipboard', 'booking-weir'),
			},
		});
	}, [link, dispatch]);

	return (
		<Button
			primary
			icon='linkify'
			onClick={copyLink}
			data-tooltip={__('Copy link', 'booking-weir')}
			data-position='top right'
		/>
	);
};
