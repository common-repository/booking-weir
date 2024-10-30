import {__} from '@wordpress/i18n';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

let ToggleEditMode;
export default ToggleEditMode = () => {
	const editMode = useSelector(state => state.ui.editMode);
	const dirty = useSelector(state => state.ui.eventDirty);
	const dispatch = useDispatch();

	return (
		<Button
			basic
			title={editMode ? __('Stop editing', 'booking-weir') : __('Edit event', 'booking-weir')}
			icon='edit'
			disabled={dirty}
			loading={dirty}
			onClick={() => dispatch({type: 'SET_EDIT_MODE', value: !editMode})}
			active={editMode}
			className='shadowless bw-edit-mode-toggle'
			style={{
				borderTopLeftRadius: 0,
				borderBottomRightRadius: 0,
				position: 'absolute',
				top: 0,
				right: -3,
				zIndex: 1,
			}}
		/>
	);
};
