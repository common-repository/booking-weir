import {
	Button as FUIButton,
	ButtonGroup as FUIButtonGroup,
} from '@fluentui/react-northstar';

export const BUTTON_THEME = {
	componentVariables: {
		Button: v => ({
			color: 'rgba(0, 0, 0, 0.6)',
			textColor: v.colorScheme.default.foreground1, // Button with text=true prop
			textColorHover: v.colors.sui.primary.hover,
			textColorIconOnlyHover: v.colors.sui.primary.hover,
			backgroundColor: '#e0e1e2',
			backgroundColorActive: '#babbbc',
			backgroundColorHover: '#cacbcd',
			backgroundColorDisabled: '#f1f2f2',
			padding: `0.78571em 1.5em`,
			primaryBoxShadow: 'none',
			boxShadow: '0 0 0 1px transparent inset, 0 0 0 0 rgba(34, 36, 38, 0.15) inset',
			contentLineHeight: '1em',
			height: 'auto',
			minWidth: 'auto',
		}),
		ButtonContent: v => ({
			contentLineHeight: '1em',
		}),
	},
	componentStyles: {
		Button: {
			root: ({props, theme}) => ({
				fontSize: theme.siteVariables.fontSizes.medium,
				borderWidth: '0',
				...(props.iconOnly && {
					minWidth: '2.75em',
				}),
				'-webkit-appearance': 'initial',
			}),
			content: {
				overflow: 'visible',
			},
			icon: {
				marginTop: '-2em',
				marginBottom: '-2em',
			},
		},
		ButtonContent: {
			root: ({props, theme}) => ({
				overflow: 'visible',
			}),
		},
	},
};

const Button = ({
	primary = false,
	secondary = false,
	positive = false,
	negative = false,
	color,
	inverted = false,
	disabled = false,
	compact = false,
	variables = {},
	styles = {},
	...props
}) => {
	const colorKey = primary && 'primary' || secondary && 'secondary' || positive && 'positive' || negative && 'negative' || color;
	return (
		<FUIButton
			as='div'
			{...props}
			variables={{
				...variables,
				...(colorKey && {
					color: '#fff',
					colorHover: '#fff',
					colorActive: '#fff',
					textColorIconOnlyHover: '#fff',
				}),
				...(compact && {
					padding: '0.58929em 1.125em',
				}),
			}}
			styles={({variables, theme}) => ({
				...styles,
				...(colorKey && {
					backgroundColor: theme.siteVariables.colors.sui[colorKey].base,
					':hover': {
						backgroundColor: theme.siteVariables.colors.sui[colorKey].hover,
					},
					':focus': {
						backgroundColor: theme.siteVariables.colors.sui[colorKey].focus,
					},
					':active': {
						backgroundColor: theme.siteVariables.colors.sui[colorKey].down,
					},
				}),
				...(inverted && {
					backgroundColor: 'transparent',
					boxShadow: `0 0 0 2px ${theme.siteVariables.colors.sui[colorKey].light} inset`,
					color: theme.siteVariables.colors.sui[colorKey].light,
					':hover': {
						color: theme.siteVariables.colors.sui.text.inverted,
						boxShadow: 'none',
						backgroundColor: theme.siteVariables.colors.sui[colorKey].light,
					},
					':focus': {
						color: theme.siteVariables.colors.sui.text.inverted,
						boxShadow: 'none',
						backgroundColor: theme.siteVariables.colors.sui[colorKey].light,
					},
					':active': {
						color: theme.siteVariables.colors.sui.text.inverted,
						boxShadow: 'none',
						backgroundColor: theme.siteVariables.colors.sui[colorKey].light,
					},
				}),
				...(disabled && {
					opacity: '0.5',
					pointerEvents: 'none',
				}),
			})}
		/>
	);
};

const ButtonGroup = ({...props}) => {
	return (
		<FUIButtonGroup {...props} />
	);
};

Button.Group = ButtonGroup;

export default Button;
