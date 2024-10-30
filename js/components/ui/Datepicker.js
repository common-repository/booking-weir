import {
	// Datepicker as FUIDatepicker,
	DatepickerCalendar as FUIDatepickerCalendar,
} from '@fluentui/react-northstar';

export const DATEPICKER_THEME = {
	componentVariables: {
		DatepickerCalendarCellButton: v => ({
			calendarCellHoverColor: 'inherit',
			calendarCellHoverBackgroundColor: v.colorScheme.default.backgroundHover,
			calendarCellSelectedColor: v.colorScheme.default.foregroundPressed,
			calendarCellSelectedBackgroundColor: v.colorScheme.default.backgroundActive1,
		}),
	},
	componentStyles: {},
};

let Datepicker;
export default Datepicker = ({...props}) => {
	return (
		<FUIDatepickerCalendar {...props} />
	);
};
