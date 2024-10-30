/**
 * Get all registered payment methods.
 */
const {payment_methods} = window.booking_weir_data;
export default Object.keys(payment_methods).map(id => ({
	id,
	label: payment_methods[id],
}));
