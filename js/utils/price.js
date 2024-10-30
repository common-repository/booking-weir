export const rounded = price => {
	return parseFloat(price).toFixed(2).replace('.00', '');
}
