

import Booking from './booking';

import 'components/ui/reset.less';
import 'components/ui/rbc.scss';
import 'components/ui/rbc.less';
import './style.less';

/**
 * Load event validation rules.
 */
import './validation';


let Shortcode;
export default Shortcode = ({atts: {id, calendar, type}}) => {
	return <Booking id={id} calendar={calendar} type={type} />;
};
