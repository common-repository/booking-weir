import {__} from '@wordpress/i18n';
import {addQueryArgs} from '@wordpress/url';

import {
	Icon,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	getUrl,
} from 'utils/calendar';

let EventLink;
export default EventLink = ({event}) => {
	const calendar = useCurrentCalendar();
	const {type, id} = event;

	if(!['default', 'slot'].includes(type)) {
		return null;
	}

	return (
		<a
			href={addQueryArgs(getUrl(calendar), {[booking_weir_data.GET.event.view]: id})}
			title={__(`Public link for the event`, 'booking-weir')}
		>
			<Icon
				link
				name='linkify'
				size='small'
				style={{
					marginRight: '0.33em',
					transform: 'translateY(-1px)',
				}}
			/>
		</a>
	);
};
