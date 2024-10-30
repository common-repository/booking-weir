import {
	Slider,
} from '@fluentui/react-northstar';

export const SLIDER_THEME = {
	componentVariables: {
		Slider: v => ({
			railColor: v.colors.sui.primary.light,
			trackColor: v.colors.sui.primary.base,
			thumbColor: v.colors.sui.primary.base,
			activeThumbColor: v.colors.sui.primary.hover,
		}),
	},
	componentStyles: {},
};

export default Slider;
