import {__} from '@wordpress/i18n';

import {
	Dialog as FUIDialog,
	Text,
} from '@fluentui/react-northstar';

import {
	CloseIcon,
} from '@fluentui/react-icons-northstar';

import Button from './Button';

export const DIALOG_THEME = {
	componentVariables: {
		Dialog: v => ({
			rootWidth: 'auto',
			rootPadding: 0,
			rootBorderRadius: v.borderRadius,
			headerMargin: 0,
			headerFontSize: '1.42857em',
			contentMargin: 0,
			overlayBackground: 'rgba(0, 0, 0, 0.85)',
		}),
	},
	componentStyles: {
		Dialog: {
			root: ({theme}) => ({
				fontSize: theme.siteVariables.fontSizes.base,
			}),
			header: () => ({
				padding: '0.75em 0 0.75em 1em',
				lineHeight: '1.28571em',
				borderBottom: '1px solid rgba(34, 36, 38, 0.15)',
				display: 'flex',
				justifyContent: 'space-between',
				justifySelf: 'initial',
			}),
			content: () => ({
				padding: '1.5em',
				maxHeight: 'calc(80vh - 10em)', // SUI modal .scrolling.content.
				overflow: 'auto', // SUI modal .scrolling.content.
			}),
			footer: ({theme}) => ({
				padding: '1.5em',
				backgroundColor: '#f9fafb',
				borderTop: '1px solid rgba(34, 36, 38, 0.15)',
				borderRadius: `0 0 ${theme.siteVariables.borderRadius} ${theme.siteVariables.borderRadius}`,
			}),
			overlay: () => ({
				animationFillMode: 'both',
				animationDuration: '.5s',
				transition: 'backgroundColor .5s linear',
			}),
		},
	},
};

const DIALOG_SIZES = {
	mini: {
		mobile: '95vw',
		tablet: '35vw',
		desktop: '340px',
		large: '360px',
		widescreen: '380px',
	},
	tiny: {
		mobile: '95vw',
		tablet: '53vw',
		desktop: '510px',
		large: '540px',
		widescreen: '570px',
	},
	small: {
		mobile: '95vw',
		tablet: '70vw',
		desktop: '680px',
		large: '720px',
		widescreen: '760px',
	},
	medium: {
		mobile: '95vw',
		tablet: '88vw',
		desktop: '850px',
		large: '900px',
		widescreen: '950px',
	},
	large: {
		mobile: '95vw',
		tablet: '88vw',
		desktop: '1020px',
		large: '1080px',
		widescreen: '1140px',
	},
	fullscreen: {
		mobile: '95vw',
		tablet: '95vw',
		desktop: '95vw',
		large: '95vw',
		widescreen: '95vw',
	},
};

let Dialog;
export default Dialog = ({
	size = 'medium',
	closeIcon = false,
	onClose,
	styles = {},
	...props
}) => {
	if(closeIcon) {
		props.header = {
			as: Text,
			content: <>
				{props.header}
				<Button
					text
					iconOnly
					icon={<CloseIcon />}
					title={__('Close', 'booking-weir')}
					onClick={onClose}
				/>
			</>,
			size: 'larger',
		};
	}

	return (
		<FUIDialog
			className='bw-root'
			{...props}
			styles={{
				...styles,
				...(size && {
					mobile: {width: DIALOG_SIZES[size].mobile},
					tablet: {width: DIALOG_SIZES[size].tablet},
					desktop: {width: DIALOG_SIZES[size].desktop},
					large: {width: DIALOG_SIZES[size].large},
					widescreen: {width: DIALOG_SIZES[size].widescreen},
				}),
			}}
		/>
	);
};
