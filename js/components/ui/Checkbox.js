import {
	Checkbox as FUICheckbox,
} from '@fluentui/react-northstar';

export const CHECKBOX_THEME = {
	componentVariables: {
		Checkbox: v => ({
			textColor: v.colors.sui.text.dark,
			textColorHover: v.colors.sui.text.hovered,
			borderColor: v.colors.sui.border.solid,
			borderColorHover: v.colors.sui.border.solidSelected,
			borderWidth: '1px',
			borderRadius: v.borderRadius,
			margin: 0,
			padding: '1px',
			rootPadding: '3px 5px 3px 0',
			checkedIndicatorColor: v.colors.sui.text.dark,
			checkedBackground: 'transparent',
			checkedBorderColor: v.colors.sui.border.solidSelected,
			checkedBackgroundHover: 'transparent',
		}),
	},
	componentStyles: {
		Checkbox: {
			root: ({props: p, variables: v}) => ({
				alignItems: 'center',
				...(p.checked && {
					':hover': {
						'& .ui-checkbox__indicator': {
							borderColor: v.borderColorHover,
						},
					},
				}),
			}),
			label: {
				lineHeight: '1.4em',
			},
			checkbox: {
				transition: 'border .1s ease',
				':active': {
					backgroundColor: '#f9fafb',
					borderColor: 'rgba(34, 36, 38, 0.35)',
				},
				'& svg': {
					width: '13px',
					height: '13px',
				},
			},
		},
	},
};

let Checkbox;
export default Checkbox = ({
	error = false,
	required = false,
	variables = {},
	styles = {},
	...props
}) => {
	return (
		<FUICheckbox
			{...props}
			variables={v => ({
				...variables,
				...(error && {
					textColor: v.colors.sui.negative.text,
					borderColor: v.colors.sui.negative.border,
					background: v.colors.sui.negative.background,
				}),
			})}
			styles={({theme}) => ({
				...styles,
				...(required && {
					'::after': {
						content: '"*"',
						color: theme.siteVariables.colors.sui.negative.base,
						margin: '-0.2em 0 0 0.2em',
						gridColumn: 4,
					},
				}),
			})}
		/>
	);
};
