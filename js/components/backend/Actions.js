import {__} from '@wordpress/i18n';

import {
	useCallback,
	useEffect,
} from 'react';

import {
	useDispatch,
	useSelector,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	ActionCreators,
} from 'redux-undo';

import {
	saveCalendars,
} from 'actions';

let Actions;
export default Actions = () => {
	const {isSaving, isDirty, undo, redo} = useSelector(state => ({
		isSaving: state.ui.isSavingCalendars,
		isDirty: state.ui.calendarsDirty,
		undo: state.ui.undo,
		redo: state.ui.redo,
	}));
	const dispatch = useDispatch();

	const save = useCallback(() => {
		if(isDirty) {
			dispatch(saveCalendars());
		}
	}, [dispatch, isDirty]);

	useEffect(() => {
		const hotkey = e => {
			if(e.ctrlKey && e.key === 's') {
				e.preventDefault();
				save();
			}
		};
		document.addEventListener('keydown', hotkey);
		return () => {
			document.removeEventListener('keydown', hotkey);
		};
	}, [save]);

	return (
		<Button.Group size='large'>
			<Button
				icon='undo'
				data-tooltip={__('Undo', 'booking-weir') + ' ' + undo}
				data-position='bottom right'
				disabled={!isDirty || undo < 1}
				onClick={() => dispatch(ActionCreators.undo())}
			/>
			<Button
				icon='redo'
				data-tooltip={__('Redo', 'booking-weir') + ' ' + redo}
				data-position='bottom right'
				disabled={redo < 1}
				onClick={() => dispatch(ActionCreators.redo())}
			/>
			<Button
				primary
				labelPosition='right'
				icon='save'
				content={__('Save', 'booking-weir')}
				disabled={!isDirty}
				onClick={save}
				loading={isSaving}
			/>
		</Button.Group>
	);
};
