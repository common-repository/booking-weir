import {__} from '@wordpress/i18n';

import {
	useDispatch,
} from 'react-redux';

import {
	Table,
	Button,
	Header,
	Form,
	Input,
	Checkbox,
	Label,
} from 'semantic-ui-react';

import MoveButtons from 'components/backend/controls/MoveButtons';
import ToggleButton from 'components/backend/controls/ToggleButton';

import JsonView from 'utils/JsonView';

import PAYMENT_METHODS from 'config/PAYMENT_METHODS';
import PAYMENT_METHOD_DATA from 'config/PAYMENT_METHOD_DATA';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	togglePaymentMethod,
} from 'actions';

let PaymentMethodEdit;
export default PaymentMethodEdit = () => {
	const dispatch = useDispatch();
	const {
		id: calendarId,
		paymentMethods: enabledPaymentMethods,
		paymentMethodData,
	} = useCurrentCalendar();
	const availablePaymentMethodIds = PAYMENT_METHODS.map(({id}) => id) || [];

	/**
	 * Find payment method label from `PAYMENT_METHODS`.
	 * Returns `id` when not found.
	 *
	 * @param string id Payment method ID.
	 */
	const getLabel = id => {
		const found = PAYMENT_METHODS.find(({id: ID}) => id === ID);
		return found ? found.label : id;
	};

	/**
	 * Take enabled payment methods for the front,
	 * add all available payment methods to the end,
	 * filter out duplicate (already enabled) payment methods,
	 * add labels to enabled payment methods (not stored in calendar).
	 */
	const paymentMethods = enabledPaymentMethods
		.concat(availablePaymentMethodIds)
		.filter((value, index, self) => self.indexOf(value) === index)
		.map(enabledPaymentMethodId => ({
			id: enabledPaymentMethodId,
			label: getLabel(enabledPaymentMethodId),
		}));

	return (
		<Table>
			<Table.Header>
				<Table.Row>
					<Table.HeaderCell>{__('Payment method', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell collapsing></Table.HeaderCell>
				</Table.Row>
			</Table.Header>
			<Table.Body>
				{paymentMethods.map(({id, label}) => {
					const available = PAYMENT_METHODS.findIndex(({id: ID}) => id === ID) > -1;
					const enabled = enabledPaymentMethods.includes(id);
					const data = PAYMENT_METHOD_DATA.find(({id: paymentMethodId}) => id === paymentMethodId);
					return (
						<Table.Row key={`payment-method-${id}`}>
							<Table.Cell>
								<Header size='small'>
									{label}
									{!available && <Label horizontal color='red'>{__('Unavailable', 'booking-weir')}</Label>}
									{data?.description && <Header.Subheader>{data.description}</Header.Subheader>}
								</Header>
								{enabled && <Options id={id} options={data?.options || []} />}
							</Table.Cell>
							<Table.Cell verticalAlign='top'>
								<Button.Group>
									<ToggleButton
										enabled={enabled}
										onClick={() => dispatch(togglePaymentMethod(calendarId, id))}
									/>
									<MoveButtons arrayName='paymentMethods' element={id} />
								</Button.Group>
							</Table.Cell>
						</Table.Row>
					);
				})}
			</Table.Body>
			<Table.Footer>
				<Table.Row>
					<Table.HeaderCell colSpan={2} textAlign='right'>
						<JsonView id='paymentMethods' obj={{
							availablePaymentMethods: PAYMENT_METHODS,
							enabledPaymentMethods,
							paymentMethodData,
						}} />
					</Table.HeaderCell>
				</Table.Row>
			</Table.Footer>
		</Table>
	);
};

const Options = ({id: paymentMethodId, options}) => {
	const dispatch = useDispatch();
	const {id: calendarId, paymentMethodData} = useCurrentCalendar();
	const data = paymentMethodData.find(({id}) => id === paymentMethodId) || {id: paymentMethodId};

	if(!options.length) {
		return null;
	}

	return (
		<Form as='div'>
			{options.map(option => {
				const {
					required,
					default: defaultValue,
					description,
				} = option;
				return (
					<Form.Field
						id={`${paymentMethodId}-${option.id}`}
						key={option.id}
						required={required}
					>
						<label htmlFor={`${paymentMethodId}-${option.id}`}>{option.label}</label>
						<Option
							{...option}
							value={data[option.id] || defaultValue}
							onChange={(e, {value}) => {
								dispatch({
									type: 'UPDATE_PAYMENT_METHOD_DATA',
									calendarId,
									paymentMethodId,
									optionId: option.id,
									value,
								});
							}}
						/>
						{description && <p className='description'>{description}</p>}
					</Form.Field>
				);
			})}
		</Form>
	);
};

const Option = ({id: optionId, label, type, value, required, onChange}) => {
	switch(type) {
		case 'string':
			return (
				<Input
					type='text'
					value={value}
					onChange={onChange}
				/>
			);
		case 'toggle':
			return (
				<Checkbox
					toggle
					checked={!!value}
					onChange={(e, {checked}) => onChange(e, {value: checked})}
				/>
			);
	}
	return null;
};
