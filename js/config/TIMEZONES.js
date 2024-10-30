import {listTimeZones} from 'timezone-support';

export default listTimeZones().filter(tz => {
	return tz.includes('/') && !tz.includes('Etc/');
}).sort().map(tz => ({
	key: tz,
	text: tz,
	value: tz,
}));
