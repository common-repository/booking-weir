import {
	TimeInput as SUITimeInput,
} from 'semantic-ui-calendar-react';

import {
	getPopupOffset,
} from 'utils/html';

import {
	useCurrentCalendarLocale,
} from 'hooks';

const CalendarProps = {
	mountNode: document.getElementById('bw-sui-root'),
	clearable: true,
	popupPosition: 'bottom right',
	animation: 'none',
	closable: true,
	closeOnMouseLeave: false,
	closeOnScroll: false,
	popupProps: {
		className: 'bw-calendar-picker',
		style: {
			...getPopupOffset(),
			padding: 0,
		},
	},
	// closePopup: () => null, // debug
};

const DateCalendarProps = {
	...CalendarProps,
	dateFormat: 'YYYY-MM-DD',
};

const DatesRangeCalendarProps = {
	...CalendarProps,
	...DateCalendarProps,
	allowSameEndDate: true,
};

export const TimeInput = ({value, onChange, ...props}) => {
	const locale = useCurrentCalendarLocale();

	const handleChange = (e, {value}) => {
		if(
			value.length !== 5
			|| value.indexOf(':') !== 2
		) {
			console.error('Invalid time input value', value);
			return;
		}
		onChange(value);
	};

	return (
		<SUITimeInput
			{...CalendarProps}
			value={value}
			onChange={handleChange}
			localization={locale}
			{...props}
		/>
	);
};
