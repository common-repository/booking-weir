import {__} from '@wordpress/i18n';

import {
	Input,
	TextArea,
	Checkbox,
	List,
	Select,
	Radio,
} from 'semantic-ui-react';

import EditFile from './EditFile';

let EditField;
export default EditField = ({id, field, value, onChange}) => {
	const {
		type,
		label,
		placeholder,
	} = field;

	const handleChange = (e, {value}) => onChange(value);

	switch(type) {
		case 'text':
		case 'email':
			return (
				<Input
					id={id}
					placeholder={placeholder}
					value={value || ''}
					type={type}
					onChange={handleChange}
				/>
			);
		case 'number':
			return (
				<Input
					id={id}
					placeholder={placeholder}
					value={value || 0}
					type={type}
					onChange={handleChange}
				/>
			);
		case 'textarea':
			return (
				<TextArea
					id={id}
					placeholder={placeholder}
					value={value || ''}
					onChange={handleChange}
				/>
			);
		case 'select':
			return (
				<Select
					id={id}
					placeholder={placeholder}
					clearable={true}
					options={field.options.split(',').map(v => ({
						key: v,
						text: v,
						value: v,
					}))}
					value={value}
					onChange={handleChange}
				/>
			);
		case 'radio': {
			return (
				<List style={{marginTop: 0}}>
					{field.options.split(',').map(opt => {
						return (
							<List.Item key={opt}>
								<Radio
									label={opt}
									value={opt}
									checked={value === opt}
									onChange={e => handleChange(e, {value: opt})}
								/>
							</List.Item>
						);
					})}
				</List>
			);
		}
		case 'checkbox':
			return (
				<Checkbox
					id={id}
					label={label}
					checked={!!value}
					onChange={(e, {checked}) => handleChange(e, {value: checked})}
				/>
			);
		case 'file':
			return (
				<EditFile
					id={id}
					value={value}
					onChange={handleChange}
				/>
			);
	}
	return null;
};
