import {__} from '@wordpress/i18n';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Segment,
	Message as SUIMessage,
	Icon,
	Label,
} from 'semantic-ui-react';

let Message;
export default Message = () => {
	const dispatch = useDispatch();
	const message = useSelector(state => state.ui.message);
	const {
		icon,
		header,
		content: text,
		retry,
		...props
	} = message;

	const close = () => dispatch({type: 'CLEAR_MESSAGE'});

	const content = <>
		{text}
		{retry && (
			<Label
				as='a'
				horizontal
				size='small'
				color='green'
				attached='bottom right'
				icon={{name: 'refresh', className: 'marginless'}}
				onClick={() => dispatch(retry)}
				className='marginless'
				data-tooltip={__('Retry', 'booking-weir')}
			/>
		)}
	</>;

	return (
		<div style={{
			position: 'fixed',
			bottom: '16px',
			left: '50%',
			transform: 'translateX(-50%)',
			zIndex: '99999',
		}}>
			<Segment
				raised
				className='paddingless borderless'
				style={{
					minWidth: '288px',
					maxWidth: '568px',
					transitionProperty: 'transform',
					transitionDuration: '220ms',
					transitionTimingFunction: 'cubic-bezier(0.2, 0, 0, 1)',
					transformOrigin: 'center bottom',
					transform: `translate3d(0px, 0px, 0px) scale(${text ? 1 : 0})`,
				}}
			>
				{header && text && (
					<SUIMessage
						{...props}
						icon={icon}
						header={header}
						content={content}
						onDismiss={close}
						className='toast'
					/>
				)}
				{!header && text && (
					<SUIMessage {...props} onDismiss={close} className='toast'>
						{icon && <Icon name={icon} />}
						{content}
					</SUIMessage>
				)}
			</Segment>
		</div>
	);
};
