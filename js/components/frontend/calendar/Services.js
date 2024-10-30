import {__, _x} from '@wordpress/i18n';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'components/ui';

import ServiceSegments from 'components/frontend/services/ServiceSegments';

import {
	clearToast,
} from 'components/frontend/toast';

import {
	AddIcon,
	AcceptIcon,
} from '@fluentui/react-icons-northstar';

/**
 * Display selectable services above the calendar.
 */
let Services;
export default Services = ({services: allServices, isWide}) => {
	const dispatch = useDispatch();

	const services = allServices.filter(({enabled}) => typeof enabled === 'undefined' || enabled);
	if(!services.length) {
		return null;
	}

	return (
		<ServiceSegments columns={isWide ? 4 : 1}>
			{({service: {id}, color, isSelected}) => (
				<Button
					fluid
					compact
					color={color}
					content={
						isSelected
						? _x('Selected', 'Button text when this service is selected, displayed above the calendar', 'booking-weir')
						: _x('Select', 'Button that selects a service to book, displayed above the calendar', 'booking-weir')
					}
					icon={isSelected ? <AcceptIcon /> : <AddIcon />}
					iconPosition='after'
					onClick={() => {
						dispatch(clearToast());
						dispatch({type: 'SELECT_SERVICE', value: isSelected ? undefined : id});
					}}
					styles={{
						margin: 0,
						maxWidth: '140px',
					}}
				/>
			)}
		</ServiceSegments>
	);
};
