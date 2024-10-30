import {
	TextArea as FUITextArea,
} from '@fluentui/react-northstar';

export const TEXTAREA_THEME = {
	componentVariables: {
		TextArea: v => ({
			borderColor: 'rgba(34, 36, 38, 0.15)',
			borderColorFocus: v.colorScheme.brand.foregroundFocus,
			borderWidth: '1px',
			backgroundColor: '#fff',
			borderRadius: v.borderRadius,
			padding: '0.67857em 1em',
			fontColor: 'rgba(0, 0, 0, 0.87)',
			placeholderColor: 'rgba(0, 0, 0, 0.4)',
		}),
	},
};

let TextArea;
export default TextArea = ({
	error = false,
	variables = {},
	...props
}) => {
	return (
		<FUITextArea
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
