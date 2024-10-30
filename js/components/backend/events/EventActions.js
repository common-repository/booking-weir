import {__} from '@wordpress/i18n';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import {
	useCurrentCalendarId,
} from 'hooks';

import {
	updateEvent,
	deleteEvent,
	deleteEventPermanently,
	setSelectedEvent,
} from 'actions';

let EventActions;
export default EventActions = ({event}) => {
	const dispatch = useDispatch();
	const currentCalendarId = useCurrentCalendarId();
	const statuses = useSelector(state => state.query.status);
	const {
		id,
		status,
		orderId,
		post: {
			status: post_status,
		},
		data: {
			isWC,
		},
	} = event;

	if(status === 'cart') {
		return (
			<Button
				negative
				icon={{
					name: 'cart arrow down',
					style: {
						position: 'initial',
						overflow: 'visible',
					},
				}}
				data-tooltip={__('Delete from cart', 'booking-weir')}
				data-position='top right'
				onClick={() => dispatch(deleteEventPermanently(currentCalendarId, id)) && dispatch(setSelectedEvent(-1))}
			/>
		);
	}

	return <>
		{post_status === 'publish' && (
			<Button
				secondary
				icon='eye slash'
				data-tooltip={__('Make private', 'booking-weir')}
				data-position='top right'
				onClick={() => dispatch(updateEvent(currentCalendarId, id, {post_status: 'draft'}))}
			/>
		)}
		{post_status === 'draft' && (
			<Button
				primary
				icon='eye'
				data-tooltip={__('Make public', 'booking-weir')}
				data-position='top right'
				onClick={() => dispatch(updateEvent(currentCalendarId, id, {post_status: 'publish'}))}
			/>
		)}
		{post_status !== 'trash' && (
			<Button
				negative
				icon='trash'
				data-tooltip={__('Trash', 'booking-weir')}
				data-position='top right'
				onClick={() => {
					dispatch(deleteEvent(currentCalendarId, id));
					if(!statuses.includes('trash')) {
						dispatch(setSelectedEvent(-1));
					}
				}}
			/>
		)}
		{post_status === 'trash' && (
			<Button
				positive
				icon='undo'
				data-tooltip={__('Restore from trash', 'booking-weir')}
				data-position='top right'
				onClick={() => dispatch(updateEvent(currentCalendarId, id, {post_status: 'draft'}))}
			/>
		)}
		{post_status === 'trash' && (
			<Button
				negative
				icon='trash'
				data-tooltip={__('Delete permanently', 'booking-weir')}
				data-position='top right'
				onClick={() => dispatch(deleteEventPermanently(currentCalendarId, id)) && dispatch(setSelectedEvent(-1))}
			/>
		)}
	</>;
};
