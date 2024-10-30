import {
	Label as FUILabel,
} from '@fluentui/react-northstar';

export const LABEL_THEME = {
	componentVariables: {
		Label: {
			padding: '0.5833em 0.833em',
		},
	},
	componentStyles: {
		Label: {
			root: ({theme}) => ({
				fontSize: theme.siteVariables.fontSizes.smaller,
				fontWeight: theme.siteVariables.fontWeightBold,
				borderRadius: theme.siteVariables.borderRadius,
				whiteSpace: 'nowrap',
				overflow: 'initial',
			}),
		},
	},
};

const SIZES = {
	small: '0.785715em',
};

let Label;
export default Label = ({
	size,
	color,
	link,
	attached,
	transparent,
	inverted = false,
	horizontal,
	variables = {},
	styles = {},
	...props
}) => {
	return (
		<FUILabel
			{...props}
			variables={{
				...variables,
				...(horizontal && {
					padding: '0.4em 0.833em',
				}),
			}}
			styles={({theme, variables}) => ({
				...styles,
				...(size && {
					fontSize: SIZES[size],
				}),
				...(color && {
					color: '#fff',
					backgroundColor: theme.siteVariables.colors.sui[color].base,
				}),
				...(link && {
					':hover': {
						color: theme.siteVariables.colors.sui.text.hovered,
						cursor: 'pointer',
					},
				}),
				...(inverted && {
					color: theme.siteVariables.colors.sui.text.inverted,
					...(link && {
						color: theme.siteVariables.colors.sui.text.invertedMuted,
						':hover': {
							color: theme.siteVariables.colors.sui.text.invertedHovered,
						},
					}),
				}),
				...(transparent && {
					backgroundColor: 'transparent',
				}),
				...(attached && {
					position: 'absolute',
					whiteSpace: 'nowrap',
					margin: 0,
				}),
				...(attached === 'top' && {
					top: '-2em',
					bottom: 'auto',
					borderBottomLeftRadius: 0,
					borderBottomRightRadius: 0,
				}),
				...(attached === 'bottom' && {
					top: 'auto',
					bottom: '-2em',
					borderTopLeftRadius: 0,
					borderTopRightRadius: 0,
				}),
				...(attached === 'top left' && {
					top: '-1px',
					left: 0,
					borderTopRightRadius: 0,
					borderBottomLeftRadius: 0,
				}),
				...(attached === 'top right' && {
					top: '-1px',
					right: 0,
					borderTopLeftRadius: 0,
					borderBottomRightRadius: 0,
				}),
				...(attached === 'bottom left' && {
					bottom: '-1px',
					left: 0,
					borderTopLeftRadius: 0,
					borderBottomRightRadius: 0,
				}),
				...(attached === 'bottom right' && {
					bottom: '-1px',
					right: 0,
					borderTopRightRadius: 0,
					borderBottomLeftRadius: 0,
				}),
			})}
		/>
	);
};
