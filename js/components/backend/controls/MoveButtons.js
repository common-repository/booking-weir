import {
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	arrayMoveUp,
	arrayMoveDown,
} from 'actions';

let MoveButtons;
export default MoveButtons = ({arrayName, element, parent = false, parentIndex = 0}) => {
	const calendar = useCurrentCalendar();
	const dispatch = useDispatch();

	const {
		id: calendarId,
	} = calendar;

	const container = parent ? calendar[parent][parentIndex] : calendar;

	const {
		[arrayName]: array,
	} = container;

	const index = element.id
		? array.findIndex(({id}) => id === element.id)
		: array.findIndex((el) => el === element);

	const isFirst = index === 0 || array.length <= 1;
	const isLast = index === array.length - 1 || array.length <= 1;

	const moveUp = () => dispatch(arrayMoveUp(arrayName, calendarId, index, parent, parentIndex));
	const moveDown = () => dispatch(arrayMoveDown(arrayName, calendarId, index, parent, parentIndex));

	return <>
		<Button
			icon='caret up'
			disabled={isFirst}
			onClick={moveUp}
		/>
		<Button
			icon='caret down'
			disabled={isLast}
			onClick={moveDown}
		/>
	</>;
};
