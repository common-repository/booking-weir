import {__, _x} from '@wordpress/i18n';

import {
	useDispatch,
} from 'react-redux';

import {
	Table,
	Input,
	Button,
} from 'semantic-ui-react';

import AddPaymentType from './AddPaymentType';
import DeletePaymentType from './DeletePaymentType';

import MoveButtons from 'components/backend/controls/MoveButtons';
import ToggleButton from 'components/backend/controls/ToggleButton';

import JsonView from 'utils/JsonView';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	updatePaymentType,
} from 'actions';

let PaymentTypeEdit;
export default PaymentTypeEdit = () => {
	const {
		id: calendarId,
		paymentTypes,
	} = useCurrentCalendar();
	const dispatch = useDispatch();

	return (
		<Table>
			<Table.Header>
				<Table.Row>
					<Table.HeaderCell>{__('Name', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell>{__('Amount', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell collapsing></Table.HeaderCell>
				</Table.Row>
			</Table.Header>
			<Table.Body>
				{!paymentTypes.length && (
					<Table.Row key='default'>
						<Table.Cell>
							{_x('Default', 'Default payment type name', 'booking-weir')}
						</Table.Cell>
						<Table.Cell colSpan={2}>
							{'100%'}
						</Table.Cell>
					</Table.Row>
				)}
				{paymentTypes.map(paymentType => {
					const {
						id,
						name,
						amount,
						enabled,
					} = paymentType;
					return (
						<Table.Row key={id}>
							<Table.Cell>
								<Input
									placeholder={__('Payment type name...', 'booking-weir')}
									value={name}
									onChange={(e, {value}) => dispatch(updatePaymentType(calendarId, id, 'name', value))}
								/>
							</Table.Cell>
							<Table.Cell>
								<Input
									type='number'
									min='0'
									max='100'
									step='1'
									labelPosition='right'
									label={_x('%', 'Payment type amount value unit', 'booking-weir')}
									value={amount || 0}
									onChange={(e, {value}) => parseInt(value) >= 0 && parseInt(value) <= 100 && dispatch(updatePaymentType(calendarId, id, 'amount', parseInt(value)))}
								/>
							</Table.Cell>
							<Table.Cell>
								<Button.Group>
									<ToggleButton
										enabled={enabled}
										onClick={() => dispatch(updatePaymentType(calendarId, id, 'enabled', !enabled))}
									/>
									<MoveButtons arrayName='paymentTypes' element={paymentType} />
									<DeletePaymentType id={id} />
								</Button.Group>
							</Table.Cell>
						</Table.Row>
					);
				})}
			</Table.Body>
			<Table.Footer>
				<Table.Row>
					<Table.HeaderCell colSpan={2}>
						<AddPaymentType />
					</Table.HeaderCell>
					<Table.HeaderCell textAlign='right'>
						<JsonView id='paymentTypes' obj={paymentTypes} />
					</Table.HeaderCell>
				</Table.Row>
			</Table.Footer>
		</Table>
	);
};
