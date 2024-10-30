import {render} from 'react-dom';

import Provider from './provider';
import Shortcode from 'components/frontend/Shortcode';

import {
	Provider as ThemeProvider,
} from '@fluentui/react-northstar';

import {
	createFelaRenderer,
} from '@fluentui/react-northstar-fela-renderer';

import {
	RendererContext,
} from '@fluentui/react-bindings';

import {
	RendererProvider,
} from 'react-fela';

import namedKeys from 'fela-plugin-named-keys';
import important from 'fela-plugin-important';

import THEME from 'config/THEME';

/**
 * @wordpress/compose/useViewportMatch():
 * huge:  1440
 * wide:  1280
 * large:  960
 * medium: 782
 * small:  600
 * mobile: 480
 */
const breakpoints = {
	mobile: '320px',
	largestMobile: '767px',
	tablet: '768px',
	largestTablet: '991px',
	computer: '992px',
	largestSmallMonitor: '1199px',
	largeMonitor: '1200px',
	largestLargeMonitor: '1919px',
	widescreen: '1920px',
};

const namedKeysPlugin = namedKeys({
	mobile: `@media only screen and (min-width: ${breakpoints.mobile}) and (max-width: ${breakpoints.largestMobile})`,
	tablet: `@media only screen and (min-width: ${breakpoints.tablet}) and (max-width: ${breakpoints.largestTablet})`,
	desktop: `@media only screen and (min-width: ${breakpoints.computer})`,
	large: `@media only screen and (min-width: ${breakpoints.largeMonitor}) and (max-width: ${breakpoints.largestLargeMonitor})`,
	widescreen: `@media only screen and (min-width: ${breakpoints.widescreen})`,
});

const customFelaRenderer = target => {
	const renderer = createFelaRenderer();
	const felaRenderer = renderer.Provider({}).props.renderer;

	felaRenderer.plugins.push(namedKeysPlugin);
	felaRenderer.plugins.push(important());

	felaRenderer.selectorPrefix = 'bw-';

	renderer.Provider = props => (
		<RendererProvider renderer={felaRenderer} {...{rehydrate: false, targetDocument: target}}>
			{props.children}
		</RendererProvider>
	);

	return renderer;
};

document.addEventListener('DOMContentLoaded', function() {
	const containers = document.querySelectorAll('.bw-booking');
	for(let i = 0; i < containers.length; ++i) {
		const id = containers[i].getAttribute('data-bw-id');
		render(
			<div className='bw-root'>
				<Provider>
					<RendererContext.Provider value={customFelaRenderer}>
						<ThemeProvider theme={THEME}>
							<Shortcode atts={window[id]} />
						</ThemeProvider>
					</RendererContext.Provider>
				</Provider>
			</div>,
			containers[i]
		);
	}
});
