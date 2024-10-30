import {
	Label,
} from 'semantic-ui-react';

import BOOKING_STATUSES from 'config/BOOKING_STATUSES';

let StatusLabel;
export default StatusLabel = ({status, ...props}) => {
	if(!status) {
		return null;
	}

	const {
		text,
		color,
	} = BOOKING_STATUSES.filter(option => option.value === status)[0];

	return (
		<Label
			content={text}
			color={color}
			{...props}
		/>
	);
};
