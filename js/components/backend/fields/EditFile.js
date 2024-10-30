import {__, sprintf} from '@wordpress/i18n';

import {
	List,
	Icon,
} from 'semantic-ui-react';

import {
	useDispatch,
} from 'react-redux';

import {
	useSelectedEventId,
} from 'hooks';

import {withoutPrefix} from 'utils/file';

import {deleteFile} from 'api';

let EditField;
export default EditField = ({id, value, onChange}) => {
	const dispatch = useDispatch();
	const eventId = useSelectedEventId();

	if(!value) {
		return <Icon disabled name='minus' />;
	}

	return (
		<List className='marginless'>
			<List.Item
				icon={
					<Icon
						link
						name='close'
						onClick={() => {
							if(confirm(sprintf(
								__(`Delete "%s" file?`, 'booking-weir'),
								`${booking_weir_data.upload_url}/files/${value}`)
							)) {
								dispatch({type: 'CLEAR_MESSAGE'});
								deleteFile(eventId, id, value)
									.then(response => {
										onChange({}, {value: ''});
									})
									.catch(e => {
										console.error(e);
										dispatch({
											type: 'SET_MESSAGE',
											value: {
												negative: true,
												icon: 'warning circle',
												header: __('Failed deleting file', 'booking-weir'),
												content: e.message,
											},
										});
									});
							}
						}}
					/>
				}
				content={withoutPrefix(value)}
			/>
		</List>
	);
};
