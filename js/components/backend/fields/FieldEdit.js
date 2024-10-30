import {__, _x} from '@wordpress/i18n';

import {
	useState,
	useRef,
} from 'react';

import {
	Input,
	Form,
	Checkbox,
	List,
	Select,
} from 'semantic-ui-react';

import {
	ARRAY_UNIQUE,
} from 'utils/array';

import {
	supports,
} from 'utils/field';

const OptionList = ({value, onChange, ...props}) => {
	const [add, setAdd] = useState('');
	const formRef = useRef();
	const options = value.split(',').map(v => v.trim()).filter(v => v.length > 0).filter(ARRAY_UNIQUE);
	const onSubmit = e => {
		onChange([...options, add].join(','));
		setAdd('');
	};

	return <>
		<List style={{marginTop: 0}}>
			{options.length > 0 && options.map(option => (
				<List.Item
					key={option}
					content={option}
					icon={{
						name: 'close',
						link: true,
						onClick: () => onChange([...options.filter(v => v !== option)].join(',')),
					}}
				/>
			))}
		</List>
		<Form ref={formRef} onSubmit={onSubmit}>
			<Form.Field>
				<Input
					type='text'
					value={add}
					onChange={e => setAdd(e.target.value)}
					placeholder={__('Value...', 'booking-weir')}
					action={{
						type: 'submit',
						primary: true,
						icon: {
							name: 'plus',
						},
						content: __('Add option', 'booking-weir'),
					}}
					{...props}
				/>
			</Form.Field>
		</Form>
	</>;
};

let FieldEdit;
export default FieldEdit = ({field, onChange}) => {
	const {
		id,
		type = 'text',
		label = '',
		options = '',
		placeholder = '',
		defaultValue = '',
		defaultChecked = false,
		defaultOption = 'none',
		required = false,
		horizontal = false,
		min,
		max,
		step = 1,
		link = '',
		accept = '',
		maxFileSize = 0,
	} = field;

	return (
		<Form as='div'>
			{supports(type, 'label') && (
				<Form.Field>
					<label htmlFor={`${id}-label`}>{__('Label', 'booking-weir')}</label>
					<Input
						id={`${id}-label`}
						fluid
						placeholder={__('Field label...', 'booking-weir')}
						value={label}
						onChange={(e, {value}) => onChange('label', value)}
					/>
				</Form.Field>
			)}
			{supports(type, 'placeholder') && (
				<Form.Field>
					<label htmlFor={`${id}-placeholder`}>{__('Placeholder', 'booking-weir')}</label>
					<Input
						id={`${id}-placeholder`}
						fluid
						placeholder={__('Field placeholder...', 'booking-weir')}
						value={placeholder}
						onChange={(e, {value}) => onChange('placeholder', value)}
					/>
				</Form.Field>
			)}
			{supports(type, 'defaultValue') && (
				<Form.Field>
					<label htmlFor={`${id}-defaultValue`}>{__('Default', 'booking-weir')}</label>
					<Input
						id={`${id}-defaultValue`}
						fluid
						placeholder={__('Default value...', 'booking-weir')}
						value={defaultValue}
						onChange={(e, {value}) => onChange('defaultValue', value)}
					/>
				</Form.Field>
			)}
			{supports(type, 'defaultChecked') && (
				<Form.Field>
					<Checkbox
						label={__('Default checked', 'booking-weir')}
						checked={defaultChecked}
						onChange={() => onChange('defaultChecked', !defaultChecked)}
					/>
				</Form.Field>
			)}
			{supports(type, 'options') && (
				<Form.Field>
					<label htmlFor={`${id}-options`}>{__('Options', 'booking-weir')}</label>
					<OptionList
						id={`${id}-options`}
						value={options}
						onChange={value => {
							onChange('options', value);
							if(defaultOption && !value.split(',').includes(defaultOption)) {
								onChange('defaultOption', '');
							}
						}}
					/>
				</Form.Field>
			)}
			{supports(type, 'defaultOption') && (
				<Form.Field>
					<label htmlFor={`${id}-defaultOption`}>{__('Default', 'booking-weir')}</label>
					<Select
						id={`${id}-defaultOption`}
						fluid
						placeholder={__('Default value...', 'booking-weir')}
						clearable={true}
						options={options.split(',').map(v => ({
							key: v,
							text: v,
							value: v,
						}))}
						value={defaultOption}
						onChange={(e, {value}) => onChange('defaultOption', value)}
					/>
				</Form.Field>
			)}
			{supports(type, 'min-max-step') && (
				<Form.Group inline>
					<Form.Field>
						<label htmlFor={`${id}-min`}>{_x('Min', 'Minimum', 'booking-weir')}</label>
						<Input
							id={`${id}-min`}
							type='number'
							placeholder='-'
							value={min}
							onChange={(e, {value}) => onChange('min', value)}
						/>
					</Form.Field>
					<Form.Field>
						<label htmlFor={`${id}-max`}>{_x('Max', 'Maximum', 'booking-weir')}</label>
						<Input
							id={`${id}-max`}
							type='number'
							placeholder='-'
							value={max}
							onChange={(e, {value}) => onChange('max', value)}
						/>
					</Form.Field>
					<Form.Field>
						<label htmlFor={`${id}-step`}>{__('Step', 'booking-weir')}</label>
						<Input
							id={`${id}-step`}
							type='number'
							placeholder='-'
							value={step}
							onChange={(e, {value}) => onChange('step', value)}
						/>
					</Form.Field>
				</Form.Group>
			)}
			{supports(type, 'required') && (
				<Form.Field>
					<Checkbox
						label={__('Required', 'booking-weir')}
						checked={required}
						onChange={() => onChange('required', !required)}
					/>
				</Form.Field>
			)}
			{supports(type, 'accept') && (
				<Form.Field>
					<label htmlFor={`${id}-accept`}>{__('Accepted mime types', 'booking-weir')}</label>
					<Input
						id={`${id}-accept`}
						fluid
						placeholder={`image/*, video/*, audio/*...`}
						value={accept}
						onChange={(e, {value}) => onChange('accept', value)}
					/>
				</Form.Field>
			)}
			{supports(type, 'maxFileSize') && (
				<Form.Field>
					<label htmlFor={`${id}-max-file-size`}>{__('Maximum file size', 'booking-weir')}</label>
					<Input
						id={`${id}-max-file-size`}
						type='number'
						min={0}
						placeholder='0'
						value={maxFileSize}
						onChange={(e, {value}) => onChange('maxFileSize', value)}
						label={_x('MB', 'Megabytes', 'booking-weir')}
						labelPosition='right'
					/>
				</Form.Field>
			)}
			{supports(type, 'horizontal') && (
				<Form.Field>
					<Checkbox
						label={__('Horizontal', 'booking-weir')}
						checked={horizontal}
						onChange={() => onChange('horizontal', !horizontal)}
					/>
				</Form.Field>
			)}
			{supports(type, 'link') && (
				<Form.Field>
					<label htmlFor={`${id}-link`}>{__('Link', 'booking-weir')}</label>
					<Input
						id={`${id}-link`}
						fluid
						placeholder={__('Terms and conditions link...', 'booking-weir')}
						value={link}
						onChange={(e, {value}) => onChange('link', value)}
					/>
				</Form.Field>
			)}
		</Form>
	);
};
