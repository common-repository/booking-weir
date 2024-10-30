import {
	Dropdown as FUIDropdown,
} from '@fluentui/react-northstar';

export const DROPDOWN_THEME = {
	componentVariables: {
		Dropdown: v => ({
			backgroundColor: '#fff',
			backgroundColorHover: '#fff',
			borderWidth: '1px',
			borderColor: v.colors.sui.border.color,
			borderColorHover: v.colors.sui.border.selected,
			borderColorFocus: v.colorScheme.brand.foregroundFocus,
			openBorderColorHover: v.colorScheme.brand.foregroundFocus,
			containerBorderRadius: v.borderRadius,
			openAboveContainerBorderRadius: v.borderRadius,
			openBelowContainerBorderRadius: v.borderRadius,
			comboboxPaddingButton: '0.78571em 1em',
			listItemSelectedFontWeight: 'normal',
		}),
	},
	componentStyles: {
		Dropdown: {
			container: ({props, theme}) => ({
				':focus-within': {
					borderColor: theme.siteVariables.colorScheme.brand.foregroundFocus,
				},
			}),
			triggerButton: ({props, theme}) => ({
				'& .ui-button__content': {
					fontWeight: 'normal',
					lineHeight: '1.1em',
					...(props.value && {
						color: theme.siteVariables.colors.sui.text.dark,
					}),
				},
			}),
		},
	},
};

let Dropdown;
export default Dropdown = ({
	error = false,
	variables = {},
	...props
}) => {
	return (
		<FUIDropdown
			{...props}
			variables={v => ({
				...variables,
				...(error && {
					borderColor: v.colors.sui.negative.border,
					backgroundColor: v.colors.sui.negative.background,
					backgroundColorHover: v.colors.sui.negative.background,
				}),
			})}
		/>
	);
};
