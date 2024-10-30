import {
	Text as FUIText,
} from '@fluentui/react-northstar';

let Text;
export default Text = ({
	inverted = false,
	styles = {},
	...props
}) => {
	return (
		<FUIText
			{...props}
			styles={({theme, variables}) => {
				return {
					...styles,
					...(inverted && {
						color: theme.siteVariables.colors.sui.text.inverted,
					}),
				};
			}}
		/>
	);
};
