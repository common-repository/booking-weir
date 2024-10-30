import {
	cloneElement,
} from 'react';

import {
	Flex,
} from '@fluentui/react-northstar';

import Input from './Input';

let ActionInput;
export default ActionInput = ({
	action,
	position = 'after',
	compact = false,
	styles = {},
	...props
}) => {
	const {
		input: inputStyles,
		...rootStyles
	} = styles;

	const {
		props: {
			styles: actionStyles = {},
		},
	} = action;

	const inputBorderRadius = position === 'after' ? {
		borderTopRightRadius: '0 !important',
		borderBottomRightRadius: '0 !important',
	} : {
		borderTopLeftRadius: '0 !important',
		borderBottomLeftRadius: '0 !important',
	};

	const actionBorderRadius = position === 'after' ? {
		borderTopLeftRadius: '0 !important',
		borderBottomLeftRadius: '0 !important',
	} : {
		borderTopRightRadius: '0 !important',
		borderBottomRightRadius: '0 !important',
	};

	const Action = cloneElement(action, {
		styles: {
			...actionStyles,
			height: 'auto',
			...actionBorderRadius
		},
	});

	return (
		<Flex styles={{
			...rootStyles,
			...(compact && {
				maxHeight: '2em',
				'& > .ui-box': {maxHeight: '2em'},
				'& > .ui-box > .ui-box': {maxHeight: '2em'},
			}),
		}}>
			{position === 'before' && Action}
			<Input
				{...props}
				styles={{
					...inputStyles,
					'& input': {
						height: '100%',
						...inputBorderRadius,
					},
				}}
			/>
			{position === 'after' && Action}
		</Flex>
	);
};
