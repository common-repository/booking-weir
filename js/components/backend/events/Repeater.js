import {__, _x, _n, sprintf} from '@wordpress/i18n';

import {
	useState,
} from 'react';

import {
	Form,
	Grid,
	Divider,
	List,
	Checkbox,
	Input,
	Select,
	Segment,
} from 'semantic-ui-react';

import {
	DateInput,
} from 'semantic-ui-calendar-react';

import WeekDaysSelect from 'components/backend/controls/WeekDaysSelect';

import {
	useCurrentCalendarLocale,
	useEvent,
	useOnChange,
} from 'hooks';

import {
	compareAsc,
} from 'date-fns';

import {
	toDate,
} from 'utils/date';

import {
	ARRAY_UNIQUE,
} from 'utils/array';

import {
	getPopupOffset,
} from 'utils/html';

const DateInputProps = {
	mountNode: document.getElementById('bw-sui-root'),
	clearable: true,
	popupPosition: 'left center',
	animation: 'none',
	closable: false,
	closeOnMouseLeave: false,
	closeOnScroll: false,
	dateFormat: 'YYYY-MM-DD',
	popupProps: {
		className: 'bw-calendar-picker',
		style: {
			...getPopupOffset(),
			padding: 0,
		},
	},
};

const REPEATER_TYPES = [
	{
		key: 'interval',
		text: __('Interval', 'booking-weir'),
		value: 'interval',
	},
	{
		key: 'week-days',
		text: __('Week days', 'booking-weir'),
		value: 'week-days',
	},
	{
		key: 'dates',
		text: __('Dates', 'booking-weir'),
		value: 'dates',
	},
];

const REPEATER_UNITS = [
	{
		key: 'days',
		text: __('Days', 'booking-weir'),
		value: 'days',
	},
	{
		key: 'business-days',
		text: __('Business days', 'booking-weir'),
		value: 'business-days',
	},
	{
		key: 'weeks',
		text: __('Weeks', 'booking-weir'),
		value: 'weeks',
	},
	{
		key: 'months',
		text: __('Months', 'booking-weir'),
		value: 'months',
	},
	{
		key: 'years',
		text: __('Years', 'booking-weir'),
		value: 'years',
	},
];

let Repeater;
export default Repeater = ({eventId, onChange}) => {
	const locale = useCurrentCalendarLocale();
	const event = useEvent(eventId);
	const {repeater: currentRepeater} = event;
	const [repeater, setRepeater] = useState(currentRepeater || {});
	useOnChange(repeater, onChange);

	const {
		type = REPEATER_TYPES[0].value,
		interval = 1,
		units = REPEATER_UNITS[0].value,
		days = [],
		dates = [],
		limit = 0,
		until = '',
		preventOverlap = false,
		ignore = [],
	} = repeater;

	return (
		<Segment secondary>
			<Grid stackable>
				<Grid.Row columns={1}>
					<Grid.Column>
						<Form.Field>
							<label htmlFor='repeater-type'>{__('Repeat type', 'booking-weir')}</label>
							<Select
								id='repeater-type'
								fluid
								options={REPEATER_TYPES}
								value={type}
								onChange={(e, {value}) => setRepeater({...repeater, type: value})}
							/>
						</Form.Field>
					</Grid.Column>
				</Grid.Row>
				{type === 'interval' && (
					<Grid.Row columns={2}>
						<Grid.Column>
							<Form.Field>
								<label htmlFor='repeater-interval'>{_x('Every', 'Every n days/weeks/months', 'booking-weir')}</label>
								<Input
									id='repeater-interval'
									type='number'
									fluid
									min='1'
									step='1'
									value={interval}
									onChange={(e, {value}) => setRepeater({...repeater, interval: parseInt(value)})}
								/>
							</Form.Field>
						</Grid.Column>
						<Grid.Column>
							<Form.Field>
								<label htmlFor='repeater-units'>{__('Units', 'booking-weir')}</label>
								<Select
									id='repeater-units'
									fluid
									options={REPEATER_UNITS}
									value={units}
									onChange={(e, {value}) => setRepeater({...repeater, units: value})}
								/>
							</Form.Field>
						</Grid.Column>
					</Grid.Row>
				)}
				{type === 'week-days' && (
					<Grid.Row columns={1}>
						<Grid.Column>
							<Form.Field>
								<label>{__('Repeat days', 'booking-weir')}</label>
								<WeekDaysSelect
									value={days}
									onChange={value => setRepeater({...repeater, days: value})}
								/>
							</Form.Field>
						</Grid.Column>
					</Grid.Row>
				)}
				{type === 'dates' && (
					<Grid.Row columns={2}>
						<Grid.Column>
							<Form.Field>
								<label htmlFor='repeater-dates'>{__('Dates', 'booking-weir')}</label>
								<DateInput
									{...DateInputProps}
									fluid
									value=''
									placeholder={__('Add date...', 'booking-weir')}
									onChange={(e, {value}) => setRepeater({
										...repeater,
										dates: [...dates, value].filter(ARRAY_UNIQUE).sort(
											(dateLeft, dateRight) => compareAsc(toDate(dateLeft), toDate(dateRight))
										),
									})}
									localization={locale}
								/>
							</Form.Field>
						</Grid.Column>
						<Grid.Column>
							<Form.Field>
								{dates.length > 0 && <label htmlFor='repeater-selected-dates'>{__('Selected', 'booking-weir')}</label>}
								<List
									id='repeater-selected-dates'
									items={dates.map(value => {
										const removeSelectedDate = () => setRepeater({...repeater, dates: dates.filter(v => v !== value)});
										return (
											<List.Item
												key={value}
												icon={{
													name: 'close',
													className: 'link',
													onClick: removeSelectedDate,
													onKeyDown: e => {
														if(e.keyCode === 13 || e.keyCode === 32) {
															e.preventDefault();
															removeSelectedDate();
														}
													},
													tabIndex: 0,
													role: 'button',
													'aria-label': sprintf(__('Remove %s from selected dates.', 'booking-weir'), value),
												}}
												content={value}
											/>
										);
									})}
									style={{marginTop: 0}}
								/>
							</Form.Field>
						</Grid.Column>
					</Grid.Row>
				)}
				<Grid.Row columns={1}>
					<Grid.Column>
						<Form.Field>
							<label htmlFor='repeater-overlap'>{__('Prevent overlap', 'booking-weir')}</label>
							<Checkbox
								id='repeater-overlap'
								toggle
								label={__('Hide when overlapping with existing (non-repeating) events', 'booking-weir')}
								checked={preventOverlap}
								onChange={(e, {checked}) => setRepeater({...repeater, preventOverlap: !preventOverlap})}
							/>
						</Form.Field>
					</Grid.Column>
				</Grid.Row>
				{type !== 'dates' && <>
					<Grid.Row>
						<Grid.Column width={7}>
							<Form.Field>
								<label htmlFor='repeater-limit'>{__('Limit', 'booking-weir')}</label>
								<Input
									id='repeater-limit'
									type='number'
									fluid
									min='0'
									step='1'
									label={limit > 0 ? _n('repeat', 'repeats', limit, 'booking-weir') : __('unlimited', 'booking-weir')}
									labelPosition='right'
									value={limit}
									onChange={(e, {value}) => setRepeater({...repeater, limit: parseInt(value)})}
								/>
							</Form.Field>
						</Grid.Column>
						<Grid.Column width={2} className='mobile-invisible'>
							<Divider vertical>{__('OR', 'booking-weir')}</Divider>
						</Grid.Column>
						<Grid.Column width={7}>
							<Form.Field>
								<label htmlFor='repeater-until'>{__('Until', 'booking-weir')}</label>
								<DateInput
									{...DateInputProps}
									fluid
									value={until}
									placeholder={__('Add final date...', 'booking-weir')}
									onChange={(e, {value}) => setRepeater({...repeater, until: value})}
									localization={locale}
									closable={true}
								/>
							</Form.Field>
						</Grid.Column>
					</Grid.Row>
					<Grid.Row columns={2}>
						<Grid.Column>
							<Form.Field>
								<label htmlFor='repeater-ignore'>{__('Ignore', 'booking-weir')}</label>
								<DateInput
									{...DateInputProps}
									fluid
									value=''
									placeholder={__('Add ignored date...', 'booking-weir')}
									onChange={(e, {value}) => setRepeater({...repeater, ignore: [...ignore, value].filter((v, i, a) => a.indexOf(v) === i)})}
									localization={locale}
								/>
							</Form.Field>
						</Grid.Column>
						<Grid.Column>
							<Form.Field>
								{ignore.length > 0 && <label htmlFor='repeater-ignored'>{__('Ignored', 'booking-weir')}</label>}
								<List
									id='repeater-ignored'
									items={ignore.map(value => {
										const removeDate = () => setRepeater({...repeater, ignore: ignore.filter(v => v !== value)});
										return (
											<List.Item
												key={value}
												icon={{
													name: 'close',
													className: 'link',
													onClick: removeDate,
													onKeyDown: e => {
														if(e.keyCode === 13 || e.keyCode === 32) {
															e.preventDefault();
															removeDate();
														}
													},
													tabIndex: 0,
													role: 'button',
													'aria-label': sprintf(__('Remove %s from ignored dates.', 'booking-weir'), value),
												}}
												content={value}
											/>
										);
									})}
									style={{marginTop: 0}}
								/>
							</Form.Field>
						</Grid.Column>
					</Grid.Row>
				</>}
			</Grid>
		</Segment>
	);
};
