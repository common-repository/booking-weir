import {sprintf, __} from '@wordpress/i18n';

import * as Yup from 'yup';

import FIELD_TYPES from 'config/FIELD_TYPES';
import DEFAULT_FIELDS from 'config/DEFAULT_FIELDS';

import {withoutPrefix} from 'utils/file';

export const flattenFields = (fields = [], filterDefault = false) => {
	const flat = fields.reduce((acc, cur) => {
		if(cur.type === 'grid') {
			acc = [...acc, ...cur.fields];
		} else {
			acc = [...acc, cur];
		}
		return acc;
	}, []);

	if(filterDefault) {
		return flat.filter(f => !DEFAULT_FIELD_IDS.includes(f.id));
	}

	return flat;
};

export const DEFAULT_FIELDS_FLAT = flattenFields(DEFAULT_FIELDS);
export const DEFAULT_FIELD_IDS = DEFAULT_FIELDS_FLAT.map(f => f.id);

export const supports = (type, feature) => {
	const field = FIELD_TYPES.find(field => field.value === type);
	const supports = field.supports || [];
	return supports.includes(feature);
};

export const getDefaultValue = ({type, defaultValue, defaultChecked, defaultOption, options}) => {
	switch(type) {
		case 'number':
			return isNaN(parseInt(defaultValue)) ? 0 : parseInt(defaultValue);
		case 'terms':
			return false;
	}
	if(supports(type, 'defaultValue') && typeof defaultValue !== 'undefined') {
		return defaultValue;
	}
	if(supports(type, 'defaultChecked')) {
		return !!defaultChecked;
	}
	if(supports(type, 'defaultOption')) {
		switch(type) {
			case 'select':
				return defaultOption || '';
			case 'radio': {
				const first = options.split(',')[0];
				const selectedDefault = options.split(',').includes(defaultOption) ? defaultOption : first;
				return defaultOption ? selectedDefault : first;
			}
		}
	}
	return '';
};

export const groupValues = values => {
	return Object.keys(values).reduce((acc, cur) => {
		if(DEFAULT_FIELD_IDS.includes(cur)) {
			acc[cur] = values[cur];
		} else {
			acc.fields[cur] = values[cur];
		}
		return acc;
	}, {fields: {}});
};

export const getField = (fields, id) => {
	const flatFields = flattenFields(fields);
	const index = flatFields.findIndex(field => field.id === id);
	return flatFields[index];
};

export const hasField = (fields, id) => {
	const flatFields = flattenFields(fields);
	return flatFields.findIndex(field => field.id === id) > -1;
};

export const getFieldProp = (fields, id, prop) => {
	const flatFields = flattenFields(fields);
	const index = flatFields.findIndex(field => field.id === id);
	const value = index > -1 ? flatFields[index][prop] || '' : '';
	if(prop === 'value' && !value) {
		return getDefaultValue(flatFields[index]);
	}
	return value;
};

export const getFieldDisplayValue = (fields, id, currentValue, blank) => {
	blank = blank || '-';
	const flatFields = flattenFields(fields);
	const index = flatFields.findIndex(field => field.id === id);
	if(index < 0) {
		return blank;
	}
	const field = flatFields[index];
	let value = currentValue || getDefaultValue(field);
	switch(field.type) {
		case 'terms':
		case 'checkbox':
			return currentValue ? __('Yes', 'booking-weir') : __('No', 'booking-weir');
		case 'number':
			return parseInt(value);
		case 'file':
			if(value?.name) {
				return value.name; // Front end, `File` object name.
			}
			if(value) {
				return ( // Back end, filename.
					<a
						href={`${booking_weir_data.upload_url}/files/${value}`}
						target='_blank'
						rel='noopener noreferrer'
						title={value}
					>
						{withoutPrefix(value)}
					</a>
				);
			}
			return blank;
	}
	return value || blank;
};

export const countFields = fields => {
	return flattenFields(fields).filter(({enabled}) => enabled !== false).length;
};

export const schemaReducer = (acc, cur) => {
	const {
		id,
		type,
		required,
		enabled,
		maxFileSize,
	} = cur;

	if(!enabled) {
		return acc;
	}

	switch(type) {
		case 'number': {
			const {min = '', max = ''} = cur;
			acc[id] = Yup.number();
			if(!isNaN(parseInt(min))) {
				acc[id] = acc[id].min(parseInt(min));
			}
			if(!isNaN(parseInt(max))) {
				acc[id] = acc[id].max(parseInt(max));
			}
			break;
		}
		case 'radio':
			acc[id] = Yup.string().oneOf(cur.options.split(','));
		break;
		case 'select':
			acc[id] = Yup.string();
		break;
		case 'file':
			acc[id] = Yup.mixed();
		break;
		case 'email':
			acc[id] = Yup.string().email();
		break;
		case 'checkbox':
			acc[id] = Yup.bool();
		break;
		case 'terms':
			acc[id] = Yup.bool().oneOf([true]);
		break;
		case 'grid':
			acc = {...acc, ...cur.fields.reduce(schemaReducer, {})};
		break;
		default:
			acc[id] = Yup.string();
	}

	if(supports(type, 'required') && required) {
		switch(type) {
			case 'checkbox':
				acc[id] = acc[id].oneOf([true]);
			break;
			case 'select':
				acc[id] = acc[id].oneOf(cur.options.split(',')).required();
			break;
			case 'file':
				acc[id] = acc[id].required(__('File is required', 'booking-weir'));
			break;
			default:
				acc[id] = acc[id].required();
		}
	}

	if(supports(type, 'maxFileSize') && maxFileSize) {
		acc[id] = acc[id].test(
			'is-smaller-than-max',
			sprintf(__('File size must me smaller than %dmb', 'booking-weir'), maxFileSize),
			value => {
				if(!value) {
					return true;
				}
				return value.size <= (maxFileSize * 1000000);
			}
		);
	}

	return acc;
};

export const defaultValueReducer = (acc, cur) => {
	const {id, type, enabled} = cur;
	if(!enabled) {
		return acc;
	}
	if(type === 'grid') {
		acc = {...acc, ...cur.fields.reduce(defaultValueReducer, {})};
	} else {
		acc[id] = getDefaultValue(cur);
	}
	return acc;
};
