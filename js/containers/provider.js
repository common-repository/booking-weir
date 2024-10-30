import {createStore, applyMiddleware} from 'redux';
import {Provider} from 'react-redux';
import {createLogger} from 'redux-logger';
import createSagaMiddleware from 'redux-saga';
import {createHashHistory} from 'history';
import {createReduxHistoryContext} from 'redux-first-history';
import {HistoryRouter as Router} from "redux-first-history/rr6";

import createRootReducer from 'reducers';
import rootSaga from 'sagas';

const {
	log = false,
} = window.booking_weir_data || {};

/**
 * Create history.
 */
const history = createHashHistory();

const {
	createReduxHistory,
	routerMiddleware,
	routerReducer,
} = createReduxHistoryContext({
	history,
});

/**
 * Middlewares.
 */
const middlewares = [];

/**
 * Router middleware.
 */
middlewares.push(routerMiddleware);

/**
 * Middleware: Logging.
 */
if(log) {
	const loggerMiddleware = createLogger({
		collapsed: true,
	});
	middlewares.push(loggerMiddleware);
}

/**
 * Middleware: Sagas.
 */
const sagaMiddleware = createSagaMiddleware();
middlewares.push(sagaMiddleware);

/**
 * Store.
 */
const store = createStore(
	createRootReducer(routerReducer),
	applyMiddleware(...middlewares)
);

/**
 * Run sagas.
 */
sagaMiddleware.run(rootSaga);

let BWProvider;
export default BWProvider = ({withRouter, children}) => (
	<Provider store={store}>
		{withRouter && (
			<Router history={createReduxHistory(store)}>
				{children}
			</Router>
		)}
		{!withRouter && children}
	</Provider>
);
