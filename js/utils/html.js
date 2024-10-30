import {
	createElement,
} from 'react';

/**
 * Component used as equivalent of Fragment with unescaped HTML, in cases where
 * it is desirable to render dangerous HTML without needing a wrapper element.
 *
 * @param {string} props.children HTML to render.
 *
 * @return {Element} Dangerously-rendering element.
 */
export const RawHTML = ({children, ...props}) => {
	return createElement('div', {
		dangerouslySetInnerHTML: {__html: children},
		...props,
	});
}

/**
 * Convert attribute-escaped (esc_attr) string to raw HTML.
 *
 * @param {string}
 *
 * @return {Element} Dangerously-rendering element.
 */
export const getRawHTMLElement = string => {
	let parser = new DOMParser;
	let dom = parser.parseFromString(`<!doctype html><body>${string}</body>`, 'text/html');
	return <span dangerouslySetInnerHTML={{__html: dom.body.textContent}} />;
};

/**
 * Get the height of WP admin bar.
 */
export const getAdminbarHeight = () => {
	const adminBar = document.getElementById('wpadminbar');
	if(!adminBar) {
		return 0;
	}
	const styles = window.getComputedStyle(adminBar);
	if(styles.position !== 'fixed') {
		return 0;
	}
	return adminBar.clientHeight;
};

/**
 * Get date picker offset needed to compensate for WP admin bar and sidebar.
 */
export const getPopupOffset = () => {
	let top = getAdminbarHeight() * -1;
	let left = document.getElementById('adminmenu')?.clientWidth * -1 || 0;
	return {top, left};
};

/**
 * Returns an array with the provided element and all its child elements.
 */
export const getElementWithChildren = el => {
	const reducer = (acc, cur) => {
		acc.push(cur);
		if(cur.children.length) {
			acc = acc.concat(Array.from(cur.children).reduce(reducer, []));
		}
		return acc;
	};
	const children = Array.from(el.children).reduce(reducer, []);
	return [el].concat(children);
};

export const getElementsWithChildren = els => {
	return Array.from(els).reduce((acc, cur) => {
		return acc.concat(getElementWithChildren(cur));
	}, []);
};
