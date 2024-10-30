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

let BookingLink;
export default BookingLink = ({event}) => {
	const calendar = useCurrentCalendar();
	const {type, billingKey} = event;

	if(type !== 'booking' || !billingKey) {
		return null;
	}

	return (
		<a
			href={addQueryArgs(getUrl(calendar), {[booking_weir_data.GET.booking.view]: billingKey})}
			title={__(`Public link for the booking`, 'booking-weir')}
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
