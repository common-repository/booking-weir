import {__, _x} from '@wordpress/i18n';
import {useViewportMatch} from '@wordpress/compose';

import {
	Fragment,
	useCallback,
	useState,
	useEffect,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Segment,
	Label,
	Modal,
} from 'semantic-ui-react';

import {
	push,
} from 'redux-first-history';

import cx from 'classnames';

import StatusLabel from 'components/backend/events/booking/StatusLabel';

import {
	setSelectedEvent,
} from 'actions';

import {
	fetchEventContent,
} from 'api';

import {
	RawHTML,
} from 'utils/html';

import {
	useCurrency,
	useCurrentCalendarId,
	useSelectedEventId,
} from 'hooks';

const useCalendarEventState = props => {
	const {event} = props;
	const currentCalendarId = useCurrentCalendarId();
	const isWide = useViewportMatch('wide');
	const selectedEventId = useSelectedEventId();
	const dispatch = useDispatch();

	const isSelected = event.id === selectedEventId;
	const isDraft = event.id === 'draft';
	const isBooking = event.type === 'booking';
	const isRepeat = !!event.isRepeat;
	const isRelated = (currentCalendarId !== event.calendarId) && !isDraft;
	const isWC = event.data.isWC;
	const isDraggable = !!props.isDraggable && isWide && !isRepeat && !isRelated;
	const isDragged = !!props.isDragged;
	const hasContent = !!event.excerpt;

	const select = useCallback(() => {
		if(isRelated) {
			if(confirm(__('Event is in another calendar. Change calendars to view event?', 'booking-weir'))) {
				dispatch(push(`/${event.calendarId}/events/${event.id}`))
			}
		} else {
			dispatch(setSelectedEvent(event.id));
		}
	}, [isRelated, dispatch, event.id, event.calendarId]);

	return {
		isSelected,
		isDraft,
		isBooking,
		isRepeat,
		isRelated,
		isWC,
		isDraggable,
		isDragged,
		hasContent,
		select,
	};
};

let EventSegment;
export default EventSegment = props => {
	const state = useCalendarEventState(props);
	switch(props.type) {
		case 'time':
			return <TimeGridEventSegment {...props} state={state} />;
		case 'date':
			return <MiniEventSegment {...props} state={state} />;
		case 'agenda':
			return <AgendaEventSegment {...props} state={state} />;
		default:
			return props.children;
	}
};

/**
 * Render event in Week and Day views.
 */
const TimeGridEventSegment = ({
	event,
	state: {
		isSelected,
		isBooking,
		isRelated,
		isDraggable,
		isDragged,
		hasContent,
		select,
	},
	...props
}) => {
	return (
		<Segment
			key={event.id}
			color={event.data.color}
			raised={isSelected}
			inverted={isSelected}
			disabled={isRelated}
			className={cx(
				'event',
				'rbc-event',
				{
					// 'shadowless': !isSelected,
					'raised': isSelected,
					'rbc-addons-dnd-dragged-event': isDragged,
				},
			)}
			style={{
				top: `${Number.parseFloat(props.style.top).toPrecision(7)}%`,
				left: `${Number.parseFloat(props.style.xOffset).toPrecision(7)}%`,
				width: `calc(${Number.parseFloat(props.style.width).toPrecision(7)}% - 2px)`,
				height: `calc(${Number.parseFloat(props.style.height).toPrecision(7)}% - 3px)`,
			}}
			onClick={select}
			{...(isDraggable ? props.dragProps : {})}
		>
			<div className={cx('bw-event-content', {'rbc-addons-dnd-resizable': isDraggable})}>
				{isDraggable && props.dragStartAnchor}
				<Title event={event} wrap />
				{isDraggable && props.dragEndAnchor}
			</div>
			<TimeLabels text={props.label} event={event} />
			{isBooking && <BookingLabels event={event} />}
			{hasContent && <Excerpt event={event} isSelected={isSelected} />}
		</Segment>
	);
};

/**
 * Render event in Month view.
 */
const MiniEventSegment = ({
	event,
	state: {
		isSelected,
		isRelated,
		isDraggable,
		isDragged,
		select,
	},
	...props
}) => {
	return (
		<Segment
			key={event.id}
			color={event.data.color}
			raised={isSelected}
			inverted={isSelected}
			disabled={isRelated}
			className={cx('mini-event', {'rbc-addons-dnd-dragged-event': isDragged})}
			onClick={select}
			{...(isDraggable ? props.dragProps : {})}
		>
			<Title event={event} />
		</Segment>
	);
};

/**
 * Render event in Agenda view.
 */
const AgendaEventSegment = ({
	event,
	state: {
		isSelected,
		isRelated,
		isBooking,
		select,
	},
}) => {
	return (
		<Segment
			basic
			color={event.data.color}
			inverted={isSelected}
			disabled={isRelated}
			className='mini-event'
			onClick={select}
		>
			<Title event={event} />
			{isBooking && <BookingInfo event={event} />}
		</Segment>
	);
};

/**
 * Render time labels in top left and bottom right corners of the container.
 */
const TimeLabels = ({text, event}) => {
	const [start, end] = text.split(' – ');
	return <>
		<Label
			size='small'
			color={event.data.color}
			horizontal
			attached='top left'
			style={{
				top: '-1px',
				left: '-1px',
				margin: 0,
			}}
			content={start}
		/>
		<Label
			size='small'
			color={event.data.color}
			horizontal
			attached='bottom right'
			style={{
				bottom: '-1px',
				right: '-1px',
				margin: 0,
			}}
			content={end}
		/>
	</>;
};

/**
 * Render booking info labels in top right and bottom left corners.
 */
const BookingLabels = ({event}) => {
	const {id, status, price} = event;
	const bookingPrice = useCurrency(price);

	return (
		<Fragment key={id}>
			<Label
				size='small'
				horizontal
				basic
				attached='top right'
				style={{
					top: '-1px',
					right: '-1px',
					margin: 0,
					borderColor: 'transparent',
				}}
				content={bookingPrice}
			/>
			<StatusLabel
				size='small'
				status={status}
				horizontal
				attached='bottom left'
				style={{
					bottom: '-1px',
					left: '-1px',
					margin: 0,
					maxWidth: '45%',
				}}
			/>
		</Fragment>
	);
};

/**
 * Render booking info in Agenda event.
 */
const BookingInfo = ({event}) => {
	const isWide = useViewportMatch('wide');

	const {
		firstName,
		lastName,
		price,
		status,
	} = event;
	const name = `${firstName} ${lastName}`.trim();
	const bookingPrice = useCurrency(price);

	return (
		<div style={{float: isWide ? 'right' : 'none'}}>
			{`${name} (${bookingPrice}) `}
			<StatusLabel
				size='small'
				horizontal
				status={status}
			/>
		</div>
	);
};

/**
 * Render event title.
 */
export const Title = ({event, wrap = false}) => {
	const {
		isRepeat,
		repeatId,
		data: {
			titlePrefix,
			titleText,
		},
	} = event;

	const TITLE = `${titlePrefix}${titleText}${isRepeat ? ` (${repeatId})` : ``}`;

	if(wrap) {
		return <div className='sub header' style={{textAlign: 'center'}}>{TITLE}</div>;
	}

	return <>{TITLE}</>;
};

/**
 * Render event excerpt.
 */
const Excerpt = ({event, isSelected}) => {
	const id = event.id;
	const {start, end} = event;
	const dispatch = useDispatch();
	const [isOpen, setIsOpen] = useState(false);
	const [content, setContent] = useState('');

	const open = e => {
		e.stopPropagation();
		setIsOpen(true);
	};
	const close = e => {
		e.stopPropagation();
		setIsOpen(false);
	};

	useEffect(() => {
		if(isOpen && !content) {
			dispatch({type: 'CLEAR_MESSAGE'});
			fetchEventContent(id, start, end)
				.then(response => setContent(response))
				.catch(e => {
					console.error(e);
					dispatch({
						type: 'SET_MESSAGE',
						value: {
							negative: true,
							icon: 'warning circle',
							header: __('Failed fetching event info', 'booking-weir'),
							content: e.message,
						},
					});
					setIsOpen(false);
				});
		}
	}, [isOpen, content, id, start, end, dispatch]);

	return <>
		<Label
			basic
			horizontal
			size='small'
			attached='top right'
			icon={{
				name: 'info',
				className: 'marginless link',
			}}
			onClick={open}
			className={cx('marginless', 'transparent', {inverted: isSelected})}
			style={{
				top: '-1px',
				right: '-1px',
			}}
		/>
		<Modal
			mountNode={document.getElementById('bw-no-sui')}
			open={isOpen}
			closeIcon
			onClose={close}
		>
			<Modal.Header style={{margin: 0}}>{event.title}</Modal.Header>
			<Modal.Content className='sui-root'>
				{content ? <RawHTML>{content}</RawHTML> : <Segment basic loading />}
			</Modal.Content>
		</Modal>
	</>;
};
