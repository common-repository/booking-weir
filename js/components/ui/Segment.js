import {
	Segment as FUISegment,
} from '@fluentui/react-northstar';

export const SEGMENT_THEME = {
	componentVariables: {
		Segment: v => ({
			borderRadius: v.borderRadius,
			borderWidth: '2px 0 2px 0',
			colorScheme: {
				primary: {
					foreground: '#2185d0',
				},
				secondary: {
					background: '#f3f4f5',
				},
				tertiary: {
					background: '#dcddde',
				},
			},
			shadow: {
				raised: `0px 2px 4px 0px rgba(34, 36, 38, 0.12), 0px 2px 10px 0px rgba(34, 36, 38, 0.15)`,
			},
		}),
	},
};

let Segment;
export default Segment = ({
	color,
	primary = false,
	secondary = false,
	tertiary = false,
	inverted = false,
	raised = false,
	compact = false,
	padded = false,
	styles = {},
	...props
}) => {
	const colorType = color
		|| primary && 'primary'
		|| secondary && 'secondary'
		|| tertiary && 'tertiary'
		|| inverted && 'inverted';
	const colorProp = inverted ? 'backgroundColor' : 'borderColor';
	return (
		<FUISegment
			{...props}
			styles={({theme, variables}) => {
				return {
					...styles,
					...(colorType && {
						[colorProp]: theme.siteVariables.colors.sui[colorType].base,
					}),
					...(raised && {
						boxShadow: variables.shadow.raised,
					}),
					...(compact && {
						display: 'table',
					}),
					...(padded && {
						padding: '2em',
					}),
				};
			}}
		/>
	);
};
