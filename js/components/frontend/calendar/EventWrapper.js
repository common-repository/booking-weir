import {__, _x} from '@wordpress/i18n';

import {
	useState,
	useEffect,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	InfoIcon,
} from '@fluentui/react-icons-northstar';

import {
	Label,
	Segment,
	Button,
	Dialog,
	Loader,
	Text,
} from 'components/ui';

import {
	fetchEventContent,
} from 'api';

import {
	setToast,
	clearToast,
} from 'components/frontend/toast';

import {
	toDate,
} from 'utils/date';

import {
	RawHTML,
} from 'utils/html';

import {
	differenceInMinutes,
	getHours,
} from 'date-fns';

import BookButton from 'components/frontend/booking/modal/BookButton';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	getBookableEventPriceText,
} from 'utils/bookable';

import {
	isSelected as isEventSelected,
} from 'utils/event';

import EVENT_TYPES from 'config/EVENT_TYPES';

const TimeLabels = ({text, event, color}) => {
	const {settings} = useCurrentCalendar();
	const start = toDate(event.start);
	const end = toDate(event.end);
	const duration = differenceInMinutes(end, start);

	if(duration > settings.step) {
		const [startText, endText] = text.split(' – ');
		return <>
			<Label
				size='small'
				color={color}
				horizontal
				attached='top left'
				content={startText}
			/>
			<Label
				size='small'
				color={color}
				horizontal
				attached='bottom right'
				content={endText}
			/>
		</>;
	}

	return (
		<Label
			horizontal
			size='small'
			color={color}
			attached={getHours(start) <= settings.openingHour ? 'bottom' : 'top'}
			content={text}
		/>
	);
};

const PriceLabel = ({event, color}) => {
	const {settings} = useCurrentCalendar();
	return (
		<Label
			size='small'
			color={color}
			horizontal
			attached='bottom left'
			content={getBookableEventPriceText(event.start, event.end, event, settings)}
		/>
	);
};

const Title = ({text, inverted, ...props}) => {
	return (
		<Text
			content={text}
			align='center'
			inverted={inverted}
			className='bw-event-title'
			{...props}
		/>
	);
};

const Excerpt = ({event, isOpen, close, inverted = false, footerContent = null}) => {
	const dispatch = useDispatch();
	const [content, setContent] = useState('');
	const {id, start, end} = event;

	useEffect(() => {
		if(isOpen && !content) {
			dispatch(clearToast());
			fetchEventContent(id, start, end)
				.then(response => setContent(response))
				.catch(e => {
					console.error(e);
					dispatch(setToast(`${__('Failed fetching event info', 'booking-weir')}.`));
					close();
				});
		}
	}, [isOpen, content, id, start, end, dispatch, close]);

	return <>
		<Label
			size='small'
			transparent
			horizontal
			link
			attached='top right'
			inverted={inverted}
			icon={<InfoIcon outline />}
		/>
		<Dialog
			size='small'
			open={isOpen}
			header={event.title}
			content={content ? <RawHTML>{content}</RawHTML> : <Loader styles={{padding: '3em'}} />}
			footer={<>
				<Button secondary content={__('Close', 'booking-weir')} onClick={close} />
				{footerContent}
			</>}
			closeIcon
			onClose={close}
		/>
	</>;
};

let EventWrapper;
export default EventWrapper = props => {
	const [isOpen, setIsOpen] = useState((isEventSelected(props?.event) && !!props?.event?.data?.viewContent) || false);
	const close = e => {
		e?.stopPropagation();
		setIsOpen(false);
	};

	if(props.type !== 'time') {
		return props.children;
	}

	const {
		event,
		label,
	} = props;

	const {
		id,
		type,
		title,
		bookable: isBookableEvent,
	} = event;

	const isDraft = !id;
	const isSlot = type === 'slot';
	const isRelated = !!event?.data?.isRelated;
	const isSelected = isEventSelected(event);
	const hasContent = !!event?.data?.hasContent;
	const isBookable = (isDraft || isSlot || isBookableEvent) && !isRelated;
	const color = event?.data?.color || EVENT_TYPES.find(t => t.value === 'draft').color;

	return (
		<Segment
			raised={isDraft}
			inverted={isSelected}
			color={color}
			className='bw-event-wrapper'
			style={{
				top: `${Number.parseFloat(props.style.top).toPrecision(7)}%`,
				left: `${Number.parseFloat(props.style.xOffset).toPrecision(7)}%`,
				width: `calc(${Number.parseFloat(props.style.width).toPrecision(7)}% - 2px)`,
				height: `calc(${Number.parseFloat(props.style.height).toPrecision(7)}% - 3px)`,
			}}
			styles={{
				position: 'absolute',
				margin: '1px',
				padding: '0.4em',
				display: 'flex',
				flexDirection: 'column',
				alignItems: 'center',
				justifyContent: 'center',
				cursor: hasContent ? 'pointer' : 'default',
				':hover': {zIndex: 1},
			}}
			onClick={e => {
				e.preventDefault();
				e.stopPropagation();
				hasContent && setIsOpen(true);
				return false;
			}}
		>
			{(
				!isBookable
				|| isBookableEvent
				|| (isSlot && title !== _x('Slot', 'Slot event public title', 'booking-weir'))
			) && (
				<Title
					text={title}
					inverted={isSelected}
					style={{
						...(isBookable && {
							marginBottom: '0.5em',
						}),
					}}
				/>
			)}
			{isBookable && <BookButton event={event} color={color} />}
			{hasContent && (
				<Excerpt
					event={event}
					inverted={isSelected}
					isOpen={isOpen}
					close={close}
					footerContent={isBookable && (
						<BookButton
							context='dialog'
							event={event}
							color='primary'
							compact={false}
							onClick={close}
							styles={{
								marginLeft: '0.5em',
							}}
						/>
					)}
				/>
			)}
			<TimeLabels text={label} event={event} color={color} />
			{isBookableEvent && <PriceLabel event={event} color={color} />}
		</Segment>
	);
};
