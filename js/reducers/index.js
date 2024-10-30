import {combineReducers} from 'redux';
import undoable from 'redux-undo';

import ui from './ui';
import calendars from './calendars';
import calendar from './calendar';
import query from './query';

const createRootReducer = routerReducer => combineReducers({
	ui,
	calendars: undoable(calendars),
	calendar,
	query,
	router: routerReducer,
});

export default createRootReducer;
