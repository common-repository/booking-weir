import {
	Header as FUIHeader,
} from '@fluentui/react-northstar';

export const HEADER_THEME = {
	componentVariables: {
		Header: v => ({
			color: v.colors.sui.text.dark,
		}),
	},
	componentStyles: {
		Header: {
			root: {
				marginBottom: '1em',
			},
		},
	},
};

const Header = ({
	mla = false,
	mca = false,
	mra = false,
	variables = {},
	styles = {},
	...props
}) => {
	return (
		<FUIHeader
			{...props}
			variables={v => ({
				...variables,
			})}
			styles={({theme, variables}) => ({
				...(mla && {
					mobile: {textAlign: 'left'},
				}),
				...(mca && {
					mobile: {textAlign: 'center'},
				}),
				...(mra && {
					mobile: {textAlign: 'right'},
				}),
				...styles,
			})}
		/>
	);
};

export default Header;
