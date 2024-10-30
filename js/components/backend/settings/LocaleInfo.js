import {__} from '@wordpress/i18n';

import {
	List,
} from 'semantic-ui-react';

import SettingsSegment from './SettingsSegment';

import {
	utcToZonedTime,
} from 'date-fns-tz';

import {
	getCalendarLocale,
	getWeekdaysData,
	formatLong,
	getCalendarUTCOffset,
} from 'utils/date';

let LocaleInfo;
export default LocaleInfo = ({settings}) => {
	if(!settings.locale || !settings.timezone) {
		return null;
	}

	const locale = getCalendarLocale(settings);
	const weekDaysData = getWeekdaysData(settings);

	return (
		<SettingsSegment>
			<List horizontal>
				<List.Item>
					<List.Header>{__('Current time in selected timezone', 'booking-weir')}</List.Header>
					<List.Description>{formatLong(utcToZonedTime(Date.now(), settings.timezone), {...settings, locale: undefined})}</List.Description>
				</List.Item>
				<List.Item>
					<List.Header>{__('UTC offset', 'booking-weir')}</List.Header>
					<List.Description>{getCalendarUTCOffset(settings)}</List.Description>
				</List.Item>
				<List.Item>
					<List.Header>{__('Localized time', 'booking-weir')}</List.Header>
					<List.Description>{formatLong(utcToZonedTime(Date.now(), settings.timezone), settings)}</List.Description>
				</List.Item>
				<List.Item>
					<List.Header>{__('Week starts on', 'booking-weir')}</List.Header>
					<List.Description>{weekDaysData.localInEnOrder[locale.options.weekStartsOn]}</List.Description>
				</List.Item>
			</List>
		</SettingsSegment>
	);
};
