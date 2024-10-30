import {__, sprintf} from '@wordpress/i18n';

import Alert from 'components/ui/Alert';

import {
	ExclamationTriangleIcon,
} from '@fluentui/react-icons-northstar';

import {
	getCalendarTimezoneDiff,
} from 'utils/date';

let TimezoneWarning;
export default TimezoneWarning = ({settings}) => {
	if(!settings.timezoneWarning) {
		return null;
	}

	const timezoneDiff = getCalendarTimezoneDiff(settings);
	if(!timezoneDiff) {
		return null;
	}

	return (
		<Alert
			warning
			icon={<ExclamationTriangleIcon />}
			header={__('Timezone difference', 'booking-weir')}
			content={sprintf(
				__(`Times are booked in the %s timezone, your browser is using %s timezone. The time difference is %s.`, 'booking-weir'),
				settings.timezone,
				timezoneDiff.clientTimeZone,
				timezoneDiff.diffFormatted
			)}
		/>
	);
};
