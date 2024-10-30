/**
 * Locales supported by date-fns.
 */
export default [...window.booking_weir_data.locales].map(locale => ({
	key: locale,
	text: locale,
	value: locale,
}));
