import {
	useState,
	useEffect,
	useRef,
} from 'react';

import {
	useSelector,
} from 'react-redux';

import APP_ID from 'config/APP_ID';

import {
	getCurrentCalendarId,
} from 'utils/params';

export * from 'hooks/events';

export function useCurrentCalendarId() {
	const staticCalendarId = useSelector(state => state.calendar.staticCalendarId);
	const location = useSelector(state => state.router.location);
	if(staticCalendarId) {
		return staticCalendarId;
	}
	return getCurrentCalendarId(location);
}

export function useCurrentCalendar() {
	const id = useCurrentCalendarId();
	const calendar = useSelector(state => state.calendars.present[id]);

	if(!calendar) {
		return false;
	}

	return {
		id,
		...calendar,
	};
}

export function useCurrentCalendarData() {
	const currentCalendarData = useSelector(state => state.calendar);
	return currentCalendarData;
}

export function useCurrentCalendarLocale() {
	const calendar = useCurrentCalendar();
	return calendar?.settings?.locale || 'en';
}

/**
 * Returns the current `booking` that is being attempted.
 */
export function useBooking() {
	const booking = useSelector(state => state.ui.booking);
	return booking;
}

export function useDebounce(value, delay) {
	const [debouncedValue, setDebouncedValue] = useState(value);

	useEffect(() => {
		const handler = setTimeout(() => {
			setDebouncedValue(value);
		}, delay);

		return () => {
			clearTimeout(handler);
		};
	}, [value, delay]);

	return debouncedValue;
}

export function usePersistentState(id, init) {
	const [value, setValue] = useState(
		JSON.parse(localStorage.getItem(`${APP_ID}-${id}`)) || init
	);

	useEffect(() => {
		localStorage.setItem(`${APP_ID}-${id}`, JSON.stringify(value));
	});

	return [value, setValue];
}

export function useWindowWidth() {
	let [windowWidth, setWindowWidth] = useState(window.innerWidth);

	function handleResize() {
		setWindowWidth(window.innerWidth);
	}

	useEffect(() => {
		window.addEventListener('resize', handleResize);
		return () => {
			window.removeEventListener('resize', handleResize);
		};
	}, []);

	return windowWidth;
}

export function useCurrency(value = 0) {
	const {settings} = useCurrentCalendar();
	const {currency, currencySuffix} = settings;
	return isNaN(value) ? '-' : `${currency}${value}${currencySuffix}`;
}

export function useCurrencyRenderer() {
	const {settings} = useCurrentCalendar();
	const {currency, currencySuffix} = settings;
	return value => isNaN(value) ? '-' : `${currency}${value}${currencySuffix}`;
}

/**
 * Calls onChange handler when state changes.
 * Doesn't execute on first render when state is first passed down.
 */
export function useOnChange(state, handler) {
	const firstRender = useRef(true);
	useEffect(() => {
		if(firstRender.current) {
			firstRender.current = false;
			return;
		}
		handler && handler(undefined, {value: state});
	}, [state, handler]);
}

/**
 * Get the service that is selected in the front end calendar.
 */
export function useSelectedService() {
	const selectedServiceId = useSelector(state => state.ui.selectedService);
	const {services} = useCurrentCalendar();
	if(!selectedServiceId) {
		return undefined;
	}
	return services.find(service => service.id === selectedServiceId) || undefined;
}

/**
 * Get service by ID.
 */
export function useService(serviceId) {
	const {services} = useCurrentCalendar();
	if(!serviceId) {
		return undefined;
	}
	return services.find(service => service.id === serviceId) || undefined;
}

/**
 * Get bookable event by ID.
 */
export function useBookableEvent(bookableEventId) {
	const id = useCurrentCalendarId();
	const bookableEvent = useSelector(state => state.calendars.present[id].events.find(({id}) => bookableEventId === id));
	return bookableEvent;
}
