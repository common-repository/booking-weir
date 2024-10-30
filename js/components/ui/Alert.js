import {
	Alert as FUIAlert,
} from '@fluentui/react-northstar';

export const ALERT_THEME = {
	componentVariables: {
		Alert: v => ({
			color: v.colors.sui.text.dark,
			borderRadius: v.borderRadius,
			backgroundColor: '#f8f8f9',
			borderColor: '#c9caca',
			padding: '1em 1.5em',
			iconMargin: '0 2em 0 1em',
		}),
	},
	componentStyles: {
		Alert: {
			root: ({props, theme}) => ({
				marginBottom: '1em',
			}),
			body: ({props, theme}) => ({
				flexDirection: 'column',
				marginLeft: '0.5em',
				'& ul': {
					margin: 0,
					padding: '0 0 0 1em',
				},
			}),
			header: ({props, theme}) => ({
				fontSize: theme.siteVariables.fontSizes.large,
				marginBottom: '0.25em',
			}),
			content: ({props, theme}) => ({
				opacity: '0.85',
			}),
			icon: ({props, theme}) => ({
				transform: 'scale(2.5)',
				opacity: '0.8',
			}),
		},
	},
};

let Alert;
export default Alert = ({
	color,
	info,
	warning,
	positive,
	negative,
	compact = false,
	variables = {},
	styles = {},
	...props
}) => {
	const colorType = color
		|| info && 'info'
		|| warning && 'warning'
		|| positive && 'positive'
		|| negative && 'negative';
	return (
		<FUIAlert
			{...props}
			variables={v => ({
				...variables,
				...(colorType && {
					color: v.colors.sui[colorType].text,
					backgroundColor: v.colors.sui[colorType].background,
					borderColor: v.colors.sui[colorType].border,
				}),
				...(compact && {
					padding: 'calc(0.78571em - 2px) 0.75em', // Button padding with adjustment for border
				}),
			})}
			styles={() => ({
				...styles,
				...(compact && {
					'& .ui-alert__icon': {
						transform: 'none',
						marginLeft: 0,
						marginRight: 0,
					},
					'& .ui-alert__actions': {
						display: 'inherit',
						margin: '0 0 0 0.5em',
					},
				}),
				mobile: {
					flexDirection: 'column',
					'& .ui-alert__icon': {
						margin: '1em auto',
					},
					'& .ui-alert__body': {
						marginLeft: 0,
					},
				},
			})}
		/>
	);
};
