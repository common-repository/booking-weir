import {
	useEffect,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import Day from 'react-big-calendar/lib/Day';
import Week from 'react-big-calendar/lib/Week';
import Month from 'react-big-calendar/lib/Month';
import Agenda from 'react-big-calendar/lib/Agenda';

import {
	startOfDay,
	endOfDay,
	isWithinInterval,
} from 'date-fns';

import {
	toDate,
	toString,
} from 'utils/date';

export const DateViewUpdater = ({date, view}) => {
	const dispatch = useDispatch();
	const currentView = useSelector(state => state.ui.view);
	const currentDate = useSelector(state => state.ui.date);

	useEffect(() => {
		const nextDate = toString(date);
		if(currentDate !== nextDate) {
			dispatch({type: 'SET_DATE', value: toString(date)});
		}
	// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [date.getTime(), currentDate, dispatch]);

	useEffect(() => {
		if(currentView !== view) {
			dispatch({type: 'SET_VIEW', value: view});
		}
	}, [view, currentView, dispatch]);

	return null;
};

export const RangeUpdater = ({date, view, localizer}) => {
	const dispatch = useDispatch();

	const getRange = (date, view, localizer) => {
		let range = [];
		switch(view) {
			case 'day':
				range = Day.range(date, {localizer});
			break;
			case 'agenda':
				range = Object.values(Agenda.range(date, {localizer, length: 90}));
			break;
			case 'month':
				range = Object.values(Month.range(date, {localizer}));
			break;
			case 'week':
			default:
				range = Week.range(date, {localizer});
		}
		return {
			start: toString(startOfDay(range[0])),
			end: toString(endOfDay(range[range.length - 1])),
		};
	};

	const range = getRange(date, view, localizer);

	useEffect(() => {
		dispatch({type: 'SET_RANGE', value: range});
	// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [range.start, range.end, dispatch]);

	return null;
};

export const Navigator = ({date, navigate}) => {
	const dispatch = useDispatch();
	const to = useSelector(state => state.ui.navigateTo);

	useEffect(() => {
		if(!to) {
			return;
		}
		navigate('DATE', toDate(to));
	}, [to, navigate]);

	useEffect(() => {
		if(!to) {
			return;
		}
		if(isWithinInterval(toDate(to), {
			start: startOfDay(date),
			end: endOfDay(date),
		})) {
			/**
			 * Delay to let calendar width and view settle.
			 * (Issue with calendar in WC product tabs)
			 */
			const done = setTimeout(() => {
				dispatch({type: 'NAVIGATED_TO', value: to});
			}, 1000);
			return () => clearTimeout(done);
		}
	}, [to, date, dispatch]);

	return null;
};
