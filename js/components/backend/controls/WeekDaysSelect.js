import {
	Button,
} from 'semantic-ui-react';

import {
	getWeekdaysData,
} from 'utils/date';

import {
	useCurrentCalendar,
} from 'hooks';

let WeekDaysSelect;
export default WeekDaysSelect = ({value = [], onChange}) => {
	const {settings} = useCurrentCalendar();
	const weekdays = getWeekdaysData(settings);

	const toggle = day => {
		let nextDays = value.includes(day) ? value.filter(d => d !== day) : value.concat(day);
		onChange(nextDays);
	};

	return (
		<Button.Group compact size='tiny'>
			{weekdays.enInLocalOrder.map((day, index) => (
				<Button
					key={day}
					data-tooltip={weekdays.localInLocalOrder[index]}
					primary={value.includes(day)}
					onClick={() => toggle(day)}
				>
					{weekdays.labels[index]}
				</Button>
			))}
		</Button.Group>
	);
};
