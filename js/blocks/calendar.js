import {__, _x} from '@wordpress/i18n';


import {
	registerBlockType,
} from '@wordpress/blocks';

import {
	useBlockProps,
	InspectorControls,
} from '@wordpress/block-editor';

import {
	useInstanceId,
} from '@wordpress/compose';

import {
	SelectControl,
	RadioControl,
	Notice,
	PanelBody,
} from '@wordpress/components';

import {
	Icon,
	calendar,
} from '@wordpress/icons';

import {
	useEffect,
	useState,
} from 'react';

import {
	getCalendars,
} from 'api';

import './calendar.less';

const BLOCK_TITLE = booking_weir_data.white_label
	? _x('Booking calendar', 'White label block title', 'booking-weir')
	: __('Booking Weir calendar', 'booking-weir');

const TYPES = [
	{
		label: __('Default'),
		value: 'default',
	},
	{
		label: __('Services'),
		value: 'services',
	},
];

const EDIT = ({
	attributes: {
		calendarId,
		type,
	},
	setAttributes,
}) => {
	const blockProps = useBlockProps({
		className: 'components-placeholder',
		style: {
			alignItems: 'center',
		},
	});
	const instanceId = useInstanceId(EDIT);
	const selectId = `bw-calendar-select-${instanceId}`;
	const [calendars, setCalendars] = useState([]);
	const [error, setError] = useState('');
	const [retry, setRetry] = useState(false);

	useEffect(() => {
		setError('');
		getCalendars()
			.then(calendars => {
				setCalendars(Object.keys(calendars).map(id => ({
					label: calendars[id],
					value: id,
				})));
			}).catch(e => {
				setError(__('Failed fetching calendars', 'booking-weir') + (e.message ? `: ${e.message}` : ''));
			});
	}, [retry]);

	return (
		<div {...blockProps}>
			{((typeof IS_PREMIUM !== 'undefined') && IS_PREMIUM) && (
				<InspectorControls>
					<PanelBody title={__('Settings', 'booking-weir')}>
						<RadioControl
							label={__('Type', 'booking-weir')}
							options={TYPES}
							selected={type}
							onChange={value => setAttributes({type: value})}
						/>
					</PanelBody>
				</InspectorControls>
			)}
			<label htmlFor={selectId} className='components-placeholder__label'>
				<Icon icon={calendar} />
				{BLOCK_TITLE}
			</label>
			<SelectControl
				id={selectId}
				options={[{
					label: '-',
					value: '',
				}].concat(calendars)}
				value={calendarId}
				onChange={value => setAttributes({calendarId: value})}
				style={{width: 300}}
			/>
			{error.length > 0 && (
				<Notice
					status='error'
					isDismissible={false}
					actions={[{
						label: __('Retry', 'booking-weir'),
						onClick: () => setRetry(!retry),
					}]}
				>
					{error}
				</Notice>
			)}
		</div>
	);
};

const SAVE = () => {
	return null;
};

registerBlockType('booking-weir/calendar', {
	apiVersion: 2,
	title: BLOCK_TITLE,
	description: __('Display a booking calendar.', 'booking-weir'),
	category: 'booking-weir',
	icon: calendar,
	attributes: {
		calendarId: {
			type: 'string',
			default: '',
		},
		type: {
			type: 'string',
			default: 'default',
		},
	},
	supports: {
		html: false,
		align: ['wide', 'full'],
		// color: {
		// 	text: true,
		// 	background: true,
		// },
		multiple: false,
		reusable: false,
	},
	edit: EDIT,
	save: SAVE,
});
