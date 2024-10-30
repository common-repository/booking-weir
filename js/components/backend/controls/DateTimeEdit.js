import {__} from '@wordpress/i18n';

import {
	Input,
	Button,
	Popup,
	Header,
} from 'semantic-ui-react';

import {
	DateInput,
} from 'semantic-ui-calendar-react';

import {
	useDispatch,
} from 'react-redux';

import {
	getHours,
	getMinutes,
	addMinutes,
	subMinutes,
} from 'date-fns';

import {
	updateEvent,
} from 'actions';

import {
	useSelectedEvent,
} from 'hooks';

import {
	toString,
	toDate,
} from 'utils/date';

let DateTimeEdit;
export default DateTimeEdit = ({eventId, calendarId, value, onChange, step, ...props}) => {
	const event = useSelectedEvent();
	const dispatch = useDispatch();
	const date = toDate(value);
	const hours = getHours(date);
	const minutes = getMinutes(date);
	const isStart = hours === 0 && minutes === 0;
	const isEnd = hours === 23 && minutes === 59;

	return (
		<Input
			labelPosition='right'
			className='has-action-icon-buttons has-2-action-icon-buttons'
			{...props}
		>
			<Input
				input={(
					<input
						type='text'
						value={value.split('T').reverse().join(' ')}
						style={{
							pointerEvents: 'none',
							borderTopRightRadius: 0,
							borderBottomRightRadius: 0,
						}}
					/>
				)}
				style={{cursor: 'no-drop'}}
			/>
			{eventId !== 'draft' && (
				<Popup
					on='click'
					content={(
						<div className='sui-root'>
							<Header sub textAlign='center' style={{marginBottom: '0.5em'}}>
								{__('Move event to a different date', 'booking-weir')}
							</Header>
							<div>
								<DateInput
									dateFormat='YYYY-MM-DD'
									inline
									value={value}
									onChange={(e, {value}) => {
										const {start, end} = event;
										dispatch(updateEvent(calendarId, eventId, {
											start: `${value}T${start.split('T')[1]}`,
											end: `${value}T${end.split('T')[1]}`,
										}));
										dispatch({type: 'SET_EVENT_DIRTY', value: true});
										dispatch({type: 'NAVIGATE_TO', value});
									}}
								/>
							</div>
						</div>
					)}
					trigger={
						<Button
							basic
							attached
							icon='calendar'
							className='transparent'
						/>
					}
					position='bottom right'
				/>
			)}
			<Button
				icon='chevron up'
				attached={true}
				disabled={isStart}
				onClick={e => {
					let next = subMinutes(date, step);
					if(isEnd) {
						next = addMinutes(next, 1);
					}
					onChange(e, {value: toString(next)});
				}}
			/>
			<Button
				icon='chevron down'
				attached='right'
				disabled={isEnd}
				onClick={e => {
					let next = addMinutes(date, step);
					if(getHours(next) === 0 && getMinutes(next) === 0) {
						next = subMinutes(next, 1);
					}
					onChange(e, {value: toString(next)});
				}}
			/>
		</Input>
	);
};
