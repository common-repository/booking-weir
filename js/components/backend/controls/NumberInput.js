import {__} from '@wordpress/i18n';

import {
	Button,
	Input,
} from 'semantic-ui-react';

let NumberInput;
export default NumberInput = ({...props}) => {
	const {
		min,
		max,
		step,
		value,
		onChange,
	} = props;

	const decrement = () => {
		const nextVal = value - step;
		if(nextVal >= min) {
			onChange({target: {value: parseInt(nextVal)}});
		}
	};

	const increment = () => {
		const nextVal = value + step;
		if(nextVal <= max) {
			onChange({target: {value: parseInt(nextVal)}});
		}
	};

	const change = e => {
		const nextVal = parseInt(e.target.value);
		if(nextVal == value || isNaN(nextVal)) {
			return;
		}
		onChange({target: {value: nextVal}});
	};

	return (
		<Input className='number'>
			<Button
				icon='minus'
				attached='left'
				onClick={decrement}
				aria-label={__('Decrement', 'booking-weir')}
			/>
			<input
				value={parseInt(value)}
				type='text'
				pattern={'[0-9]*'}
				style={{
					borderRadius: 0,
					width: Math.max((value + '').length + 2, 4) + 'em',
					textAlign: 'center',
				}}
				onChange={change}
			/>
			<Button
				icon='plus'
				attached='right'
				onClick={increment}
				aria-label={__('Increment', 'booking-weir')}
			/>
		</Input>
	);
};
