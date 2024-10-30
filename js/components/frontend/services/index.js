import {__, _x, sprintf} from '@wordpress/i18n';

import {
	applyFilters,
} from '@wordpress/hooks';

import {
	useState,
} from 'react';

import {
	useDispatch,
	useSelector,
} from 'react-redux';

import {
	Flex,
	List,
	Header,
} from '@fluentui/react-northstar';

import {
	AcceptIcon,
	ChevronEndMediumIcon,
} from '@fluentui/react-icons-northstar';

import {
	Button,
	Loader,
	Dialog,
	Grid,
	Datepicker as DatepickerCalendar,
} from 'components/ui';

import {
	format,
	addMinutes,
	subMinutes,
	addDays,
	addYears,
	isEqual,
	isBefore,
	isAfter,
	getHours,
	getMinutes,
	startOfDay,
	endOfDay,
	areIntervalsOverlapping,
	eachWeekendOfInterval,
} from 'date-fns';

import {
	toDate,
	toString,
	getCalendarLocale,
	getCalendarNowDate,
	getCalendarUTCOffset,
	getCalendarOpeningHourDate,
	getCalendarClosingHourDate,
	getFirstAvailableDate,
	getLocalizedDatepickerProps,
} from 'utils/date';

import TimezoneWarning from 'components/frontend/notices/TimezoneWarning';
import ServiceSegments from 'components/frontend/services/ServiceSegments';

import {
	useCurrentCalendar,
	useSelectedService,
} from 'hooks';

import {
	updateBooking,
} from 'actions';

/**
 * Styles for Date and Time pickers.
 */
const controlStyles = {
	maxWidth: '242px',
	margin: '0 auto',
};

const Datepicker = ({value, onChange}) => {
	const calendar = useCurrentCalendar();
	const {
		settings,
	} = calendar;
	const now = getCalendarNowDate(settings);

	return (
		<DatepickerCalendar
			{...getLocalizedDatepickerProps(settings)}
			selectedDate={value}
			onDateChange={(e, {value: {originalDate: date}}) => onChange(date)}
			today={now}
			minDate={now}
			{...settings.future > 0 ? {
				maxDate: addDays(now, settings.future),
			} : {}}
			restrictedDates={settings.weekend ? [] : eachWeekendOfInterval({
				start: now,
				end: settings.future > 0 ? addDays(now, settings.future) : addYears(now, 5),
			})}
			styles={controlStyles}
		/>
	);
};

const validateEvent = (event, events, calendar, selectedService) => {
	// const {start, end} = event;
	// const {settings} = calendar;
	// const now = getCalendarNowDate(settings);
	// const dayEnd = getCalendarClosingHourDate(settings, start);

	// /**
	//  * Check current time.
	//  */
	// if(isAfter(now, start)) {
	// 	return false;
	// }

	// /**
	//  * Check event doesn't go past closing hour.
	//  */
	// if(isAfter(end, dayEnd)) {
	// 	return false;
	// }

	// /**
	//  * Check overlap.
	//  */
	// if(events.filter(existingEvent => areIntervalsOverlapping(event, {
	// 	start: toDate(existingEvent.start),
	// 	end: toDate(existingEvent.end),
	// })).length) {
	// 	return false;
	// }

	// const space = settings.space;
	// if(space > 0 && events.filter(existingEvent => {
	// 	if(existingEvent.type === 'unavailable') {
	// 		/**
	// 		 * Allow booking close to `unavailable` events.
	// 		 */
	// 		return false;
	// 	}
	// 	if(
	// 		isBefore(toDate(existingEvent.start), start)
	// 		&&
	// 		isAfter(addMinutes(toDate(existingEvent.end), space), start)
	// 	) {
	// 		return true;
	// 	}
	// 	if(
	// 		isBefore(start, toDate(existingEvent.start))
	// 		&&
	// 		isAfter(addMinutes(end, space), toDate(existingEvent.start))
	// 	) {
	// 		return true;
	// 	}
	// 	return false;
	// }).length) {
	// 	/**
	// 	 * Not enough space before or after event.
	// 	 */
	// 	return false;
	// }

	const filter = applyFilters('bw_validate_event', true, event, events, calendar, selectedService);
	if((typeof filter === 'boolean' && !filter) || typeof filter === 'string') {
		return false;
	}

	return true;
};

const Timepicker = ({date, selected, onChange}) => {
	const calendar = useCurrentCalendar();
	const {
		settings,
	} = calendar;
	const {
		step,
	} = settings;
	const calendarEvents = calendar?.events || [];
	const range = {
		start: toString(startOfDay(date)),
		end: toString(endOfDay(date)),
	};
	const repeatEvents = applyFilters('bw_repeat_events', [], calendarEvents, range);
	const events = calendarEvents.concat(repeatEvents);

	const selectedService = useSelectedService();
	const {
		duration,
	} = selectedService;

	const items = [];

	const dayStart = getCalendarOpeningHourDate(settings, date);
	const dayEnd = getCalendarClosingHourDate(settings, date);
	let current = dayStart;
	while(isBefore(current, dayEnd)) {
		const start = current;
		let end = addMinutes(start, duration * step);
		if(getHours(end) === 0 && getMinutes(end) === 0) {
			end = subMinutes(end, 1);
		}

		if(validateEvent({start, end}, events, calendar, selectedService)) {
			items.push({
				start: toString(start),
				end: toString(end),
			});
		}

		current = addMinutes(current, step);
	}

	if(!items.length) {
		return <p>{sprintf(_x('No times are available on %s.', 'Localized formatted date', 'booking-weir'), format(date, 'PPPP', {locale: getCalendarLocale(settings)}))}</p>;
	}

	return (
		<List
			selectable
			selectedIndex={items.findIndex(({start}) => start === selected?.start)}
			onSelectedIndexChange={(e, {selectedIndex}) => onChange(items[selectedIndex])}
			items={items.map((item, index) => ({
				key: index,
				content: format(toDate(item.start), 'p', {locale: getCalendarLocale(settings)}),
			}))}
			variables={{
				rootPadding: '0.5em',
			}}
			styles={{
				display: 'flex',
				flexWrap: 'wrap',
				'& li': {
					flexBasis: '33%',
					textAlign: 'center',
				},
				'& li .ui-list__itemcontent': {
					whiteSpace: 'nowrap',
					marginRight: 0,
				},
				...controlStyles,
			}}
		/>
	);
};

const SERVICE_SEGMENTS_PROPS = applyFilters('bw_services_props', {
	columns: 3,
	titleSize: 'larger',
	titleWeight: 'bold',
});

let Services;
export default Services = () => {
	const dispatch = useDispatch();
	const calendar = useCurrentCalendar();
	const {
		id: calendarId,
		settings,
		data: {
			nonce,
		},
	} = calendar;
	const selectedService = useSelectedService();
	const [date, setDate] = useState(getFirstAvailableDate(settings));
	const [event, setEvent] = useState({});

	const bookingModalOpen = useSelector(state => state.ui.bookingModalStep > 0);
	const isOpen = !!selectedService && !bookingModalOpen;
	const close = () => {
		dispatch({type: 'SELECT_SERVICE', value: undefined});
		setEvent({});
	};

	if(!calendar) {
		return <Loader />;
	}

	return <>
		<TimezoneWarning settings={settings} />
		<Dialog
			size='tiny'
			open={isOpen}
			header={selectedService?.name}
			content={<>
				<Grid columns={2} stackable>
					<div>
						<Header as='h3' content={__('Date', 'booking-weir')} align='center' />
						<Datepicker
							value={date}
							onChange={value => {
								if(!isEqual(date, value)) {
									setEvent({});
									setDate(value);
								}
							}}
						/>
					</div>
					<div>
						<Header as='h3' content={__('Time', 'booking-weir')} align='center' />
						<Timepicker
							key={toString(date)}
							date={date}
							selected={event}
							onChange={value => setEvent(value)}
						/>
					</div>
				</Grid>
			</>}
			footer={(
				<Flex gap='gap.small' hAlign='end'>
					<Button
						secondary
						content={_x('Back', 'Booking modal back button', 'booking-weir')}
						onClick={close}
					/>
					<Button
						positive
						content={_x('Proceed', 'Booking modal forward button', 'booking-weir')}
						disabled={!event.start}
						onClick={e => {
							if(e) {
								e.preventDefault();
								e.stopPropagation();
							}
							dispatch(updateBooking({
								calendarId,
								start: event.start,
								end: event.end,
								nonce,
								utcOffset: getCalendarUTCOffset(settings),
								...(selectedService && {
									service: selectedService,
								}),
							}));
							dispatch({type: 'SET_BOOKING_MODAL_STEP', step: 1});
						}}
						icon={<ChevronEndMediumIcon />}
						iconPosition='after'
					/>
				</Flex>
			)}
			closeIcon
			onClose={close}
		/>
		<ServiceSegments
			filter={({type = 'fixed'}) => type === 'fixed'}
			descriptionFooter={({service: {id}, close}) => (
				<Button
					color='primary'
					content={_x('Book', 'Button that triggers booking, displayed in the calendar for selected or predefined time slots', 'booking-weir')}
					icon={<AcceptIcon />}
					iconPosition='after'
					onClick={() => {
						close();
						dispatch({type: 'SELECT_SERVICE', value: id});
					}}
				/>
			)}
			{...SERVICE_SEGMENTS_PROPS}
		>
			{({service: {id}, color, isSelected}) => (
				<Button
					color={color}
					content={_x('Book', 'Button that triggers booking, displayed in the calendar for selected or predefined time slots', 'booking-weir')}
					icon={<AcceptIcon />}
					iconPosition='after'
					onClick={() => dispatch({type: 'SELECT_SERVICE', value: isSelected ? undefined : id})}
				/>
			)}
		</ServiceSegments>
	</>;
};
