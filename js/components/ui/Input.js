import {
	Input as FUIInput,
} from '@fluentui/react-northstar';

export const INPUT_THEME = {
	componentVariables: {
		Input: v => ({
			borderColor: v.colors.sui.border.color,
			borderWidth: '1px',
			backgroundColor: '#fff',
			borderRadius: v.borderRadius,
			inputPadding: `0.67857em 1em`,
			fontColor: v.colors.sui.text.dark,
			placeholderColor: v.colors.sui.text.light,

			iconRight: '1em',
			iconLeft: '1em',
			inputPaddingWithIconAtStart: `0.67857em 1em 0.67857em 2.67143em`,
			inputPaddingWithIconAtEnd: `0.67857em 2.67143em 0.67857em 1em`,

			inputFocusBorderColor: v.colorScheme.brand.foregroundFocus,
			inputFocusBorderRadius: v.borderRadius,
		}),
	},
	componentStyles: {
		Input: {
			input: {
				transition: 'border-color .1s ease',
				minWidth: '70px',
				':focus + .ui-icon': {
					opacity: '1',
				},
			},
			icon: {
				opacity: '0.5',
				transition: 'opacity .3s ease',
			},
		},
	},
};

let Input;
export default Input = ({error = false, variables = {}, ...props}) => {
	return (
		<FUIInput
			{...props}
			variables={v => ({
				...variables,
				...(error && {
					fontColor: v.colors.sui.negative.text,
					placeholderColor: v.colors.sui.negative.placeholder,
					borderColor: v.colors.sui.negative.border,
					backgroundColor: v.colors.sui.negative.background,
				}),
			})}
		/>
	);
};
