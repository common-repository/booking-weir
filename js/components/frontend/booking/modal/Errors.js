import {__} from '@wordpress/i18n';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Tooltip,
} from '@fluentui/react-northstar';

import {
	Alert,
} from 'components/ui';

import {
	getIcon,
} from 'components/frontend/notices';

let Errors;
export default Errors = () => {
	const dispatch = useDispatch();
	const message = useSelector(state => state.ui.message);

	const {
		header,
		content,
		icon,
		retry,
	} = message;

	if(!header) {
		return null;
	}

	const actions = [];
	if(retry) {
		actions.push({
			key: 'retry',
			text: true,
			content: __('Retry', 'booking-weir'),
			onClick: () => dispatch(retry),
		});
	}

	return (
		<Tooltip
			pointing
			position='below'
			trigger={(
				<Alert
					compact
					negative
					icon={getIcon(icon)}
					content={header}
					actions={actions}
					styles={{
						position: 'sticky',
						top: 0,
						marginTop: 0,
						marginBottom: '1em',
						zIndex: 10,
					}}
				/>
			)}
			content={content}
		/>
	);
};
