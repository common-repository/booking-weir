import {applyFilters} from '@wordpress/hooks';

import {
	teamsTheme,
	mergeThemes,
} from '@fluentui/react-northstar';

import C from 'tinycolor2';

import {ALERT_THEME} from 'components/ui/Alert';
import {BUTTON_THEME} from 'components/ui/Button';
import {INPUT_THEME} from 'components/ui/Input';
import {LABEL_THEME} from 'components/ui/Label';
import {LIST_THEME} from 'components/ui/List';
import {CHECKBOX_THEME} from 'components/ui/Checkbox';
import {DIALOG_THEME} from 'components/ui/Dialog';
import {TEXTAREA_THEME} from 'components/ui/TextArea';
import {FORM_THEME} from 'components/ui/Form';
import {RADIOGROUP_THEME} from 'components/ui/RadioGroup';
import {SEGMENT_THEME} from 'components/ui/Segment';
import {SLIDER_THEME} from 'components/ui/Slider';
import {DROPDOWN_THEME} from 'components/ui/Dropdown';
import {DATEPICKER_THEME} from 'components/ui/Datepicker';
import {HEADER_THEME} from 'components/ui/Header';

const calculateSuiColorStates = colors => {
	return Object.keys(colors).reduce((acc, cur) => {
		const {
			base,
			light,
			text,
		} = colors[cur];

		const textColor = text || base;
		const lightColor = light || C(base).lighten(20).toString();

		acc[cur] = {
			background: base, // Usually specified.
			text: textColor,
			header: C(textColor).darken(5).toString(),
			border: textColor,

			hover: C(base).darken(5).saturate(10).toString(), // :hover
			focus: C(base).darken(8).saturate(20).toString(), // :focus
			down: C(base).darken(10).toString(), // :active
			active: C(base).darken(5).saturate(15).toString(), // .active

			light: lightColor,
			lightHover: C(lightColor).darken(5).saturate(10).toString(),
			lightFocus: C(lightColor).darken(8).saturate(20).toString(),
			lightDown: C(lightColor).darken(10).toString(),
			lightActive: C(lightColor).darken(5).saturate(15).toString(),

			...colors[cur],
		};
		return acc;
	}, {});
};

/**
 * Color palette.
 */
const colors = {
	brand: {
		50: '#2185d0',
		100: '#2185d0',
		200: '#2185d0',
		300: '#2185d0',
		400: '#2185d0',
		500: '#2185d0',
		600: '#2185d0', // Base
		700: '#1678c2', // Hover
		800: '#1a69a4', // Pressed (SUI active)
		900: '#2185d0',
		1000: '#2185d0',
	},
	sui: {
		text: {
			dark: 'rgba(0, 0, 0, 0.85)',
			muted: 'rgba(0, 0, 0, 0.6)',
			light: 'rgba(0, 0, 0, 0.4)',
			unselected: 'rgba(0, 0, 0, 0.4)',
			hovered: 'rgba(0, 0, 0, 0.8)',
			pressed: 'rgba(0, 0, 0, 0.9)',
			selected: 'rgba(0, 0, 0, 0.95)',
			disabled: 'rgba(0, 0, 0, 0.2)',
			inverted: 'rgba(255, 255, 255, 0.9)',
			invertedMuted: 'rgba(255, 255, 255, 0.8)',
			invertedLight: 'rgba(255, 255, 255, 0.7)',
			invertedUnselected: 'rgba(255, 255, 255, 0.5)',
			invertedHovered: 'rgba(255, 255, 255, 1)',
			invertedPressed: 'rgba(255, 255, 255, 1)',
			invertedSelected: 'rgba(255, 255, 255, 1)',
			invertedDisabled: 'rgba(255, 255, 255, 0.2)',
		},
		border: {
			color: 'rgba(34, 36, 38, 0.15)',
			strong: 'rgba(34, 36, 38, 0.22)',
			internal: 'rgba(34, 36, 38, 0.1)',
			selected: 'rgba(34, 36, 38, 0.35)',
			strongSelected: 'rgba(34, 36, 38, 0.5)',
			disabled: 'rgba(34, 36, 38, 0.5)',
			solid: '#d4d4d5',
			solidInternal: '#fafafa',
			solidSelected: '#bcbdbd',
			white: 'rgba(255, 255, 255, 0.1)',
			selectedWhite: 'rgba(255, 255, 255, 0.8)',
			solidWhite: '#555555',
			selectedSolidWhite: '#999999',
		},
		...calculateSuiColorStates({
			primary: {
				base: '#2185D0',
				light: '#2185d0',
				text: '#fff',
				// background: '#FFE8E6',
			},
			secondary: {
				base: '#1B1C1D',
				light: '#545454',
				text: '#fff',
				border: '#1B1C1D',
				// background: '#FFE8E6',
			},
			positive: {
				base: '#21BA45',
				light: '#2185d0',
				background: '#FCFFF5',
				border: '#A3C293',
				header: '#1A531B',
				text: '#2C662D',
			},
			negative: {
				base: '#DB2828',
				light: '#FF695E',
				background: '#FFF6F6',
				border: '#E0B4B4',
				header: '#912D2B',
				text: '#9F3A38',
				placeholder: C('#9F3A38').lighten(40).toString(),
			},
			info: {
				base: '#31CCEC',
				background: '#F8FFFF',
				border: '#A9D5DE',
				header: '#0E566C',
				text: '#276F86',
			},
			warning: {
				base: '#F2C037',
				background: '#FFFAF3',
				border: '#C9BA9B',
				header: '#794B02',
				text: '#573A08',
			},
			red: {
				base: '#DB2828',
				light: '#FF695E',
				background: '#FFE8E6',
			},
			orange: {
				base: '#F2711C',
				light: '#FF851B',
				background: '#FFEDDE',
			},
			yellow: {
				base: '#FBBD08',
				light: '#FFE21F',
				background: '#FFF8DB',
				text: '#B58105', // Difficult to read, manual override.
			},
			olive: {
				base: '#B5CC18',
				light: '#D9E778',
				background: '#FBFDEF',
				text: '#8ABC1E', // Difficult to read, manual override.
			},
			green: {
				base: '#21BA45',
				light: '#2ECC40',
				background: '#E5F9E7',
				text: '#1EBC30', // Difficult to read, manual override.
			},
			teal: {
				base: '#00B5AD',
				light: '#6DFFFF',
				background: '#E1F7F7',
				text: '#10A3A3', // Difficult to read, manual override.
			},
			blue: {
				base: '#2185D0',
				light: '#54C8FF',
				background: '#DFF0FF',
			},
			violet: {
				base: '#6435C9',
				light: '#A291FB',
				background: '#EAE7FF',
			},
			purple: {
				base: '#A333C8',
				light: '#DC73FF',
				background: '#F6E7FF',
			},
			pink: {
				base: '#E03997',
				light: '#FF8EDF',
				background: '#FFE3FB',
			},
			brown: {
				base: '#A5673F',
				light: '#D67C1C',
				background: '#F1E2D3',
			},
			grey: {
				base: '#767676',
				light: '#DCDDDE',
				background: '#F9FAFB',
			},
			black: {
				base: '#1B1C1D',
				light: '#545454',
				background: '#000000',
			},
		}),
	},
};

const theme = mergeThemes(teamsTheme, {
	siteVariables: {
		fontSizes: {
			base: '14px',
			smaller: '0.7143em',
			small: '0.8571em',
			medium: '1em',
			large: '1.2857em',
			larger: '1.7143em',
		},
		borderRadius: '4px',
		colors,
		colorScheme: {
			brand: {
				foreground: colors.brand[600],
				foregroundHover: colors.brand[600],
				foregroundPressed: colors.brand[800],
				foregroundActive: colors.brand[600],
				foregroundFocus: colors.brand[600],
				background: colors.brand[600],
				backgroundHover: colors.brand[700],
				backgroundPressed: colors.brand[800],
				backgroundActive: colors.brand[600],
				backgroundActive1: colors.brand[600],
				backgroundFocus: colors.brand[600],
				borderFocus1: colors.brand[600],
			},
			red: {
				text: colors.sui.red.default,
				foreground: colors.sui.red.default,
			},
			orange: {
				foreground: '#F2711C',
			},
			yellow: {
				foreground: '#FBBD08',
			},
			olive: {
				foreground: '#B5CC18',
			},
			green: {
				foreground: '#21BA45',
			},
			teal: {
				foreground: '#00B5AD',
			},
			blue: {
				foreground: '#2185D0',
			},
			violet: {
				foreground: '#6435C9',
			},
			purple: {
				foreground: '#A333C8',
			},
			pink: {
				foreground: '#E03997',
			},
			brown: {
				foreground: '#A5673F',
			},
			grey: {
				foreground: '#767676',
			},
			black: {
				foreground: '#1B1C1D',
			},
		},
	},
	componentVariables: {
		...ALERT_THEME.componentVariables,
		...BUTTON_THEME.componentVariables,
		...INPUT_THEME.componentVariables,
		...LABEL_THEME.componentVariables,
		...LIST_THEME.componentVariables,
		...CHECKBOX_THEME.componentVariables,
		...DIALOG_THEME.componentVariables,
		...TEXTAREA_THEME.componentVariables,
		...FORM_THEME.componentVariables,
		...RADIOGROUP_THEME.componentVariables,
		...SEGMENT_THEME.componentVariables,
		...SLIDER_THEME.componentVariables,
		...DROPDOWN_THEME.componentVariables,
		...DATEPICKER_THEME.componentVariables,
		...HEADER_THEME.componentVariables,
	},
	componentStyles: {
		...ALERT_THEME.componentStyles,
		...BUTTON_THEME.componentStyles,
		...INPUT_THEME.componentStyles,
		...LABEL_THEME.componentStyles,
		...LIST_THEME.componentStyles,
		...CHECKBOX_THEME.componentStyles,
		...DIALOG_THEME.componentStyles,
		...TEXTAREA_THEME.componentStyles,
		...FORM_THEME.componentStyles,
		...RADIOGROUP_THEME.componentStyles,
		...SEGMENT_THEME.componentStyles,
		...SLIDER_THEME.componentStyles,
		...DROPDOWN_THEME.componentStyles,
		...DATEPICKER_THEME.componentStyles,
		...HEADER_THEME.componentStyles,
	},
});

theme.fontFaces = []; // Remove fonts
theme.staticStyles = [];

// Override completely to get rid of scrollbar styles
// node_modules/@fluentui/react-northstar/dist/es/themes/teams/components/Provider/providerStyles.js
theme.componentStyles.Provider.root = ({variables, theme}) => ({
	background: 'transparent',
	color: variables.color,
	textAlign: 'left',
	fontSize: theme.siteVariables.fontSizes.base,
	/**
	 * Override default RBC border-radius with borderRadius variable.
	 */
	'& .bw-calendar.rbc-calendar .rbc-time-view': {
		borderRadius: theme.siteVariables.borderRadius,
	},
	'& .bw-calendar.rbc-calendar .rbc-agenda-view': {
		borderRadius: theme.siteVariables.borderRadius,
	},
});

export default applyFilters('bw_theme', theme, calculateSuiColorStates);
