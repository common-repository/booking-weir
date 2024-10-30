import {
	RadioGroup as FUIRadioGroup,
} from '@fluentui/react-northstar';

export const RADIOGROUP_THEME = {
	componentVariables: {
		RadioGroupItem: v => ({
			textColorDefault: v.colors.sui.text.dark,
			textColorDefaultHoverFocus: v.colors.sui.text.hovered,
			textColorChecked: v.colors.sui.text.selected,
			indicatorBorderColorDefault: v.colors.sui.border.solid,
			indicatorBorderColorDefaultHover: v.colors.sui.border.solidSelected,
			indicatorBorderColorChecked: v.colors.sui.border.solidSelected,
			indicatorBackgroundColorChecked: `radial-gradient(circle, ${v.colors.sui.text.dark} 30%, rgba(0, 0, 0, 0) 45%)`,
			padding: 0,
		}),
	},
	componentStyles: {
		RadioGroupItem: {
			root: ({props: p, variables: v}) => ({
				marginBottom: '0.25em',
				...(!p.vertical && {
					marginRight: '1em',
				}),
			}),
			indicator: ({props: p, variables: v}) => ({
				width: '1.072em',
				height: '1.072em',
				...(p.checked && {
					borderColor: v.indicatorBorderColorChecked,
				}),
			}),
		},
	},
};

let RadioGroup;
export default RadioGroup = ({...props}) => {
	return (
		<FUIRadioGroup {...props} />
	);
};
