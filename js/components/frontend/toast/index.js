import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Segment,
	Button,
	Flex,
	Text,
	Box,
} from '@fluentui/react-northstar';

import {
	CloseIcon,
} from '@fluentui/react-icons-northstar';

export const setToast = text => {
	return {type: 'SET_TOAST', value: text};
};

export const clearToast = () => {
	return {type: 'CLEAR_TOAST'};
};

let Toast;
export default Toast = () => {
	const dispatch = useDispatch();
	const text = useSelector(state => state.ui.toast);
	const close = () => dispatch(clearToast());

	return (
		<Box styles={{
			position: 'fixed',
			bottom: '16px',
			left: '50%',
			transform: 'translateX(-50%)',
			zIndex: '999',
		}}>
			<Segment
				variables={{
					backgroundColor: 'rgb(49, 49, 49)',
					boxShadow: 'rgba(0, 0, 0, 0.2) 0px 3px 5px -1px, rgba(0, 0, 0, 0.14) 0px 6px 10px 0px, rgba(0, 0, 0, 0.12) 0px 1px 18px 0px',
					color: 'rgb(255, 255, 255)',
					padding: '6px 0 6px 20px',
					borderRadius: '2px',
					borderWidth: '0',
				}}
				styles={{
					minWidth: '288px',
					maxWidth: '568px',
					transitionProperty: 'transform',
					transitionDuration: '220ms',
					transitionTimingFunction: 'cubic-bezier(0.2, 0, 0, 1)',
					transformOrigin: 'center bottom',
					transform: `translate3d(0px, 0px, 0px) scale(${text ? 1 : 0})`,
				}}
			>
				<Flex space='between' vAlign='center'>
					<Text content={text} />
					<Button
						text
						icon={<CloseIcon />}
						iconOnly
						onClick={close}
						variables={v => ({textColor: v.colors.sui.text.invertedLight})}
					/>
				</Flex>
			</Segment>
		</Box>
	);
};
