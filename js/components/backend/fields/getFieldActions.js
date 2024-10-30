import {
	applyFilters,
} from '@wordpress/hooks';


import ToggleButton from 'components/backend/controls/ToggleButton';

export const NO_DUPLICATE_TYPES = ['terms'];

const getFieldActions = args => {
	const {
		field: {
			enabled,
		},
		onToggle,
	} = args;

	return applyFilters('bw_field_actions', [
		<ToggleButton
			key='toggle'
			enabled={enabled}
			onClick={onToggle}
		/>
	], args);
};



export default getFieldActions;
