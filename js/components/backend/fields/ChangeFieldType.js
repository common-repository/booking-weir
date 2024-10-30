import {__, _x} from '@wordpress/i18n';

import {
	Select,
	Header,
} from 'semantic-ui-react';

import {
	useCurrentCalendar,
} from 'hooks';

import FIELD_TYPES from 'config/FIELD_TYPES';

import {
	DEFAULT_FIELD_IDS,
	DEFAULT_FIELDS_FLAT,
} from 'utils/field';

let ChangeFieldType;
export default ChangeFieldType = ({fieldId, value, onChange, ...props}) => {
	const {settings} = useCurrentCalendar();
	const isProduct = settings?.product > 0;

	if(DEFAULT_FIELD_IDS.includes(fieldId)) {
		return (
			<Header
				sub
				color={isProduct ? 'red' : undefined}
				style={{display: 'inline'}}
			>
				{DEFAULT_FIELDS_FLAT.find(f => f.id === fieldId).label}
			</Header>
		);
	}

	if(value === 'grid') {
		return (
			<Header sub style={{display: 'inline'}}>
				{FIELD_TYPES.find(f => f.value === value).text}
			</Header>
		);
	}

	const OPTIONS = FIELD_TYPES
		.filter(f => f.insertable !== false)
		.map(({key, text, value}) => ({key, text, value}));

	return (
		<Select
			selectOnBlur={false}
			options={OPTIONS}
			value={value || OPTIONS[0].value}
			onChange={(e, {value}) => onChange(value)}
			{...props}
		/>
	);
}
