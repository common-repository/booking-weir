import {__} from '@wordpress/i18n';

import {
	List,
} from 'semantic-ui-react';

import BookableEventInfo from './BookableEventInfo';

import {
	formatLong,
	formatDuration,
} from 'utils/date';

import {
	useCurrentCalendar,
} from 'hooks';

let EventInfo;
export default EventInfo = ({event}) => {
	const {settings} = useCurrentCalendar();
	const {
		start,
		end,
		bookable,
	} = event;

	return <>
		<List>
			<List.Item>
				<List.Content>
					<List.Header>{__('Start', 'booking-weir')}</List.Header>
					<List.Description>{formatLong(start, settings)}</List.Description>
				</List.Content>
			</List.Item>
			<List.Item>
				<List.Content>
					<List.Header>{__('End', 'booking-weir')}</List.Header>
					<List.Description>{formatLong(end, settings)}</List.Description>
				</List.Content>
			</List.Item>
			<List.Item>
				<List.Content>
					<List.Header>{__('Duration', 'booking-weir')}</List.Header>
					<List.Description>{formatDuration(start, end, settings)}</List.Description>
				</List.Content>
			</List.Item>
		</List>
		{bookable && <BookableEventInfo event={event} />}
	</>;
};
