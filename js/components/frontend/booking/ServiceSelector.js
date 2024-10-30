import {
	useEffect,
	useState,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	useCurrentCalendar,
} from 'hooks';

let ServiceSelector;
export default ServiceSelector = () => {
	const currentCalendar = useCurrentCalendar();
	const [selected, setSelected] = useState(false);
	const dispatch = useDispatch();

	useEffect(() => {
		if(selected) {
			return;
		}
		const initialSelectedService = currentCalendar?.data?.initial_selected_service;
		if(!initialSelectedService) {
			return;
		}
		dispatch({type: 'SELECT_SERVICE', value: initialSelectedService});
		setSelected(true);
	}, [selected, currentCalendar?.data?.initial_selected_service]);

	return null;
};
