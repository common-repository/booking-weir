import {
	List as FUIList,
} from '@fluentui/react-northstar';

export const LIST_THEME = {
	componentVariables: {
		ListItem: v => ({
			rootPadding: 0,
			minHeight: 'auto',
		}),
		ListItemHeader: v => ({
			headerFontSize: v.fontSizes.medium,
		}),
		ListItemContent: v => ({
			contentFontSize: v.fontSizes.medium,
		}),
	},
	componentStyles: {
		ListItem: {
			root: {
				margin: 0,
			},
		},
		ListItemHeader: {
			root: ({theme}) => ({
				fontWeight: theme.siteVariables.fontWeightBold,
			}),
		},
	},
};

const List = ({
	compact = false,
	relaxed = false,
	horizontal = false,
	size,
	variables = {},
	styles = {},
	...props
}) => {
	return (
		<FUIList
			{...props}
			variables={v => ({
				...variables,
				// ...(size && {
				// 	headerFontSize: v.fontSizes[size],
				// 	contentFontSize: v.fontSizes[size],
				// }),
			})}
			styles={({theme, variables}) => ({
				...(horizontal && {
					display: 'flex',
					alignItems: 'flex-start',
					justifyContent: 'space-around',
					mobile: {
						display: 'block',
					},
				}),
				'& [class*="headerWrapper"]': {
					marginBottom: relaxed ? '0.33em' : 0,
					...(size && {
						fontSize: theme.siteVariables.fontSizes[size],
					}),
				},
				'& [class*="contentWrapper"]': {
					...(size && {
						fontSize: theme.siteVariables.fontSizes[size],
					}),
				},
				'& li:not(:last-child) [class*="contentWrapper"]': {
					marginBottom: compact ? 0 : '0.42em',
				},
			})}
		/>
	);
};

List.Item = FUIList.Item;

export default List;
