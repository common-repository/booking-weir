/**
 * Assign default settings using the settings schema.
 *
 * @param {*} schema
 */
export const getDefaultSettings = schema => {
	let settings = {};
	schema.forEach(setting => {
		settings[setting.id] = setting.default || '';
	});
	return settings;
};
