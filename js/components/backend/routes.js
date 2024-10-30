import {
	applyFilters,
} from '@wordpress/hooks';

import {
	Route,
} from 'react-router-dom';

import Events from './events';
import Fields from './fields';
import Payment from './payment';
import Settings from './settings';


const routes = [
	<Route path='events/*' key='events' element={<Events />} />,
	<Route path='fields' key='fields' element={<Fields />} />,
	<Route path='payment' key='payment' element={<Payment />} />,
	<Route path='settings/*' key='settings' element={<Settings />} />,
];


export default applyFilters('bw_admin_routes', routes);
