import {
	Form as FUIForm,
	FormField,
} from '@fluentui/react-northstar';

export const FORM_THEME = {
	componentVariables: {
		Form: v => ({
			fieldsMarginBottom: '1em',
			lastChildMarginTop: '0',
		}),
	},
	componentStyles: {
		FormField: {
			label: ({props, theme}) => ({
				// fontSize: theme.siteVariables.fontSizes.small,
				fontWeight: theme.siteVariables.fontWeightBold,
				...(props.required && {
					'::after': {
						content: '"*"',
						color: theme.siteVariables.colors.sui.negative.base,
						margin: '-0.2em 0 0 .2em',
					},
				}),
			}),
			message: ({props, theme}) => ({
				color: theme.siteVariables.colors.sui.negative.base,
			}),
		},
	},
};

const Form = ({...props}) => {
	return (
		<FUIForm {...props} />
	);
};

Form.Field = FormField;

export default Form;
