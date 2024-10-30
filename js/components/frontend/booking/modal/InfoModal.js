import {__, _x, sprintf} from '@wordpress/i18n';
import {applyFilters} from '@wordpress/hooks';

import {
	useCallback,
	useRef,
	useMemo,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Flex,
} from '@fluentui/react-northstar';

import {
	ChevronEndMediumIcon,
	AddIcon,
} from '@fluentui/react-icons-northstar';

import {
	Dialog,
	Dropdown,
	Button,
	Form,
	Input,
	TextArea,
	Checkbox,
	Grid,
	RadioGroup,
	Alert,
} from 'components/ui';

import {
	Formik,
} from 'formik';

import * as Yup from 'yup';

import postBooking from './postBooking';
import {FileInput} from './FileInput';

import {
	useCurrentCalendar,
	useBooking,
} from 'hooks';

import {
	updateBooking,
} from 'actions';

import {
	RawHTML,
} from 'utils/html';

import {
	groupValues,
	flattenFields,
	schemaReducer,
	defaultValueReducer,
} from 'utils/field';

const AUTOFILL_VALUES = applyFilters('bw_test_autofill_values', {
	firstName: 'First',
	lastName: 'Last',
	email: booking_weir_data?.debug_email || '',
	phone: '+546 54 64 58',
	additionalInfo: 'Additional info',
	terms: true,
});

const RenderFields = ({
	fields,
	settings,
	values,
	setFieldValue,
	setFieldTouched,
	touched,
	errors,
	handleChange,
	handleBlur,
}) => {
	const checkboxRef = useRef(null);

	if(!fields.length) {
		return (
			<Alert
				content={__('No additional info is required, please proceed.', 'booking-weir')}
			/>
		);
	}

	return fields.map(field => {
		if(!field.enabled) {
			return null;
		}
		switch(field.type) {
			case 'text':
			case 'email':
				return (
					<Form.Field
						key={field.id}
						required={field.required}
						label={field.label}
						control={(
							<Input
								fluid
								type={field.type}
								name={field.id}
								placeholder={field.placeholder}
								value={values[field.id]}
								onChange={handleChange}
								onBlur={handleBlur}
								error={errors[field.id] && touched[field.id]}
							/>
						)}
					/>
				);
			case 'file':
				return (
					<Form.Field
						key={field.id}
						required={field.required}
						label={field.label}
						errorMessage={touched[field.id] ? errors[field.id] : undefined}
						control={(
							<FileInput
								name={field.id}
								accept={field.accept}
								onChange={e => {
									e.preventDefault();
									setFieldValue(field.id, e.target.files[0] || null);
								}}
								onBlur={handleBlur}
								setFieldValue={setFieldValue}
							/>
						)}
					/>
				);
			case 'number': {
				const {min = '', max = '', step = 1} = field;
				return (
					<Form.Field
						key={field.id}
						required={field.required}
						label={field.label}
						control={(
							<Input
								type='number'
								name={field.id}
								value={values[field.id].toString()}
								min={isNaN(parseInt(min)) ? undefined : parseInt(min)}
								max={isNaN(parseInt(max)) ? undefined : parseInt(max)}
								step={step}
								onChange={handleChange}
								onBlur={handleBlur}
								error={errors[field.id] && touched[field.id]}
							/>
						)}
					/>
				);
			}
			case 'textarea':
				return (
					<Form.Field
						key={field.id}
						required={field.required}
						label={field.label}
						control={(
							<TextArea
								fluid='true'
								resize='both'
								name={field.id}
								placeholder={field.placeholder}
								value={values[field.id]}
								onChange={handleChange}
								onBlur={handleBlur}
								error={errors[field.id] && touched[field.id]}
							/>
						)}
						styles={{width: '100%'}}
					/>
				);
			case 'select':
				return (
					<Form.Field
						key={field.id}
						required={field.required}
						label={field.label}
						control={(
							<Dropdown
								fluid
								checkable
								clearable={!field.required && !!values[field.id]}
								name={field.id}
								placeholder={field.placeholder || __('Select...', 'booking-weir')}
								items={field.options.split(',')}
								multiple={field.multiple}
								value={values[field.id]}
								onChange={(e, {value}) => setFieldValue(field.id, value || '')}
								onBlur={() => setFieldTouched(field.id, true)}
								error={errors[field.id] && touched[field.id]}
								getA11ySelectionMessage={{
									onAdd: item => sprintf(__('%s has been selected.', 'booking-weir'), item),
								}}
							/>
						)}
					/>
				);
			case 'radio': {
				const options = field.options.split(',');
				return (
					<Form.Field
						key={field.id}
						required={field.required}
						label={field.label}
						control={(
							<RadioGroup
								vertical={!field.horizontal}
								name={field.id}
								defaultCheckedValue={options[0]}
								checkedValue={values[field.id]}
								items={options.map(value => ({
									key: value,
									value,
									label: value,
									name: field.id,
								}))}
								onCheckedValueChange={(e, {value}) => setFieldValue(field.id, value)}
							/>
						)}
					/>
				);
			}
			case 'checkbox':
				return (
					<Form.Field
						key={field.id}
						control={(
							<Checkbox
								name={field.id}
								label={field.label}
								required={field.required}
								checked={values[field.id]}
								onChange={(e, {checked}) => {
									setFieldValue(field.id, checked);
									setFieldTouched(field.id, true);
								}}
								error={errors[field.id] && touched[field.id]}
							/>
						)}
					/>
				);
			case 'terms':
				return (
					<Form.Field
						key={field.id}
						control={(
							<Checkbox
								required={field.required}
								label={<>
									<RawHTML>
										{sprintf(
											_x('I agree with the %1$sterms and conditions%2$s', 'Booking modal terms and conditions checkbox label', 'booking-weir'),
											field.link ? '<a href="' + field.link + '" target="_blank" rel="noopener noreferrer">' : '',
											field.link ? '</a>' : '',
										)}
									</RawHTML>
									<input
										ref={checkboxRef}
										aria-hidden='true'
										type='checkbox'
										id={field.id}
										name={field.id}
										checked={values[field.id]}
										onChange={handleChange}
										onBlur={handleBlur}
										style={{display: 'none'}}
									/>
								</>}
								checked={values[field.id]}
								onChange={e => {
									if(e?.target.tagName !== 'A') {
										checkboxRef.current.click();
									}
								}}
								error={errors[field.id] && touched[field.id]}
							/>
						)}
					/>
				);
			case 'grid':
				return (
					<Grid
						key={field.id}
						stackable
						fluid
						columns={field.columns || 2}
					>
						<RenderFields
							fields={field.fields}
							settings={settings}
							values={values}
							touched={touched}
							errors={errors}
							handleChange={handleChange}
							handleBlur={handleBlur}
						/>
					</Grid>
				);
			default:
				return null;
		}
	});
};

let InfoModal;
export default InfoModal = () => {
	const isOpen = useSelector(state => state.ui.bookingModalStep === 2);
	const dispatch = useDispatch();
	const booking = useBooking();
	const {
		fields,
		settings,
		data,
	} = useCurrentCalendar();
	const isProduct = !!parseInt(data?.product);
	const addToCart = isProduct;

	const back = useCallback(e => {
		e.preventDefault();
		dispatch({type: 'BOOKING_MODAL_PREV_STEP'});
		return false;
	}, [dispatch]);

	// eslint-disable-next-line react-hooks/exhaustive-deps
	const SCHEMA = useMemo(() => Yup.object().shape(fields.reduce(schemaReducer, {})), [JSON.stringify(fields)]);
	// eslint-disable-next-line react-hooks/exhaustive-deps
	const DEFAULT_VALUES = useMemo(() => fields.reduce(defaultValueReducer, {}), [JSON.stringify(fields)]);

	return (
		<Formik
			validationSchema={SCHEMA}
			initialValues={DEFAULT_VALUES}
			onSubmit={(values, actions) => {
				const nextBooking = {...booking, ...groupValues(values)};
				dispatch(updateBooking(nextBooking));
				if(addToCart) {
					postBooking(nextBooking);
				} else {
					dispatch({type: 'BOOKING_MODAL_NEXT_STEP'});
					actions.setSubmitting(false);
				}
			}}
		>
			{({
				values,
				setValues,
				setFieldValue,
				setFieldTouched,
				errors,
				touched,
				handleChange,
				handleBlur,
				handleSubmit,
				isSubmitting,
			}) => (
				<Dialog
					className='bw-info-modal'
					as='form'
					size='tiny'
					open={isOpen}
					header={_x('Info', 'Booking modal info collection title', 'booking-weir')}
					content={(
						<Form as='div' styles={{height: 'initial'}}>
							<RenderFields
								fields={fields}
								settings={settings}
								values={values}
								setFieldValue={setFieldValue}
								setFieldTouched={setFieldTouched}
								touched={touched}
								errors={errors}
								handleChange={handleChange}
								handleBlur={handleBlur}
							/>
						</Form>
					)}
					footer={(
						<Flex gap='gap.small' hAlign='end'>
							<Button
								secondary
								content={_x('Back', 'Booking modal back button', 'booking-weir')}
								onClick={back}
							/>
							{!!window.booking_weir_data?.log && (
								<Button
									type='button'
									color='orange'
									content={__('Test', 'booking-weir')}
									onClick={() => {
										const availableFields = flattenFields(fields);
										const fill = Object.keys(AUTOFILL_VALUES).reduce((acc, cur) => {
											const index = availableFields.findIndex(f => f.id === cur);
											if(index > -1) {
												const field = availableFields[index];
												if(field.enabled !== false) {
													acc[cur] = AUTOFILL_VALUES[cur];
												}
											}
											return acc;
										}, {});
										setValues({...values, ...fill});
									}}
								/>
							)}
							<Button
								positive
								content={addToCart ? _x('Add to cart', 'Booking modal add to cart button', 'booking-weir') : _x('Proceed', 'Booking modal forward button', 'booking-weir')}
								icon={addToCart ? <AddIcon /> : <ChevronEndMediumIcon />}
								iconPosition='after'
								onClick={handleSubmit}
								disabled={isSubmitting}
							/>
						</Flex>
					)}
					onSubmit={handleSubmit}
					closeIcon
					onClose={() => dispatch({type: 'SET_BOOKING_MODAL_STEP', step: 0})}
				/>
			)}
		</Formik>
	);
};
