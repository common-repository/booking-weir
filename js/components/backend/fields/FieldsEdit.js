import {__, _x} from '@wordpress/i18n';
import {
	applyFilters,
} from '@wordpress/hooks';

import {
	useDispatch,
} from 'react-redux';

import {
	Grid,
	Button,
	Form,
	Table,
	Divider,
	Message,
} from 'semantic-ui-react';

import FieldEdit from './FieldEdit';
import ResetFields from './ResetFields';
import ChangeFieldType from './ChangeFieldType';

import NumberInput from 'components/backend/controls/NumberInput';
import MoveButtons from 'components/backend/controls/MoveButtons';

import getFieldActions from './getFieldActions';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	addField,
	updateField,
	deleteField,
	addGridField,
	updateGridField,
	deleteGridField,
} from 'actions';

import JsonView from 'utils/JsonView';


let FieldsEdit;
export default FieldsEdit = () => {
	const dispatch = useDispatch();
	const calendar = useCurrentCalendar();
	const {fields} = calendar;

	return <>
		<Table className='mobile-divided'>
			<Table.Header>
				<Table.Row>
					<Table.HeaderCell collapsing>{__('Field type', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell>{__('Field settings', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell collapsing></Table.HeaderCell>
				</Table.Row>
			</Table.Header>
			<Table.Body>
				{!fields.length && (
					<Table.Row>
						<Table.Cell colSpan={4}>
							<Message>{__('There are no fields for this calendar.', 'booking-weir')}</Message>
						</Table.Cell>
					</Table.Row>
				)}
				{fields.map((field, index) => {
					const {id, type} = field;
					return (
						<Table.Row key={id}>
							<Table.Cell verticalAlign='top' className='ui form'>
								<ChangeFieldType
									fieldId={id}
									value={type}
									onChange={value => dispatch(updateField(calendar.id, id, 'type', value))}
								/>
								{type === 'grid' && (
									<Form.Field inline style={{marginTop: '0.5em'}}>
										<label htmlFor={`${id}-columns`}>{__('Columns', 'booking-weir')}</label>
										<NumberInput
											id={`${id}-columns`}
											type='number'
											value={field.columns || 2}
											min={1}
											max={4}
											step={1}
											onChange={e => dispatch(updateField(calendar.id, id, 'columns', e.target.value))}
										/>
									</Form.Field>
								)}
							</Table.Cell>
							{type === 'grid' && (
								<Table.Cell verticalAlign='top'>
									<GridEdit grid={field} index={index} />
								</Table.Cell>
							)}
							{type !== 'grid' && (
								<Table.Cell>
									<FieldEdit
										field={field}
										onChange={(setting, value) => dispatch(updateField(calendar.id, id, setting, value))}
									/>
								</Table.Cell>
							)}
							<Table.Cell textAlign='right' verticalAlign='top'>
								<Button.Group>
									{getFieldActions({
										field,
										onToggle: () => dispatch(updateField(calendar.id, id, 'enabled', !field.enabled)),
										onDuplicate: () => {
											const {id, ...duplicateField} = field;
											dispatch(addField(calendar.id, duplicateField));
										},
										onDelete: () => dispatch(deleteField(calendar.id, id)),
									}).map(action => action)}
								</Button.Group>
								<Divider hidden fitted />
								<Button.Group>
									<MoveButtons arrayName='fields' element={field} />
								</Button.Group>
							</Table.Cell>
						</Table.Row>
					);
				})}
			</Table.Body>
			<Table.Footer>
				<Table.Row>
					<Table.HeaderCell colSpan={8}>
						<Grid stackable>
							<Grid.Column width={10} className='mca'>
								{applyFilters('bw_add_field', null, {
									onAdd: field => dispatch(addField(calendar.id, field)),
								})}
							</Grid.Column>
							<Grid.Column width={6} textAlign='right' className='mca'>
								<ResetFields />
								{applyFilters('bw_fields_import_export', null, fields)}
								<JsonView id='fields' obj={fields} floated='right' />
							</Grid.Column>
						</Grid>
					</Table.HeaderCell>
				</Table.Row>
			</Table.Footer>
		</Table>
	</>;
};

const GridEdit = ({grid, index}) => {
	const dispatch = useDispatch();
	const {
		id: calendarId,
	} = useCurrentCalendar();
	const {
		fields,
		columns = 2,
	} = grid;

	return <>
		<Grid columns={columns} stackable>
			{fields.map(field => {
				const {id, type, enabled} = field;
				return (
					<Grid.Column key={id}>
						<Table basic>
							<Table.Header>
								<Table.Row>
									<Table.HeaderCell className='ui normal text form'>
										<Form.Field inline>
											<label htmlFor={`${grid.id}-${id}-type`}>
												{__('Field type', 'booking-weir')}
											</label>
											<ChangeFieldType
												id={`${grid.id}-${id}-type`}
												fieldId={id}
												value={type}
												onChange={value => dispatch(updateGridField(calendarId, grid.id, id, 'type', value))}
											/>
										</Form.Field>
									</Table.HeaderCell>
									<Table.HeaderCell collapsing></Table.HeaderCell>
								</Table.Row>
							</Table.Header>
							<Table.Body>
								<Table.Row>
									<Table.Cell verticalAlign='top' className='ui form'>
										<FieldEdit
											field={field}
											onChange={(setting, value) => dispatch(updateGridField(calendarId, grid.id, id, setting, value))}
										/>
									</Table.Cell>
									<Table.Cell textAlign='right' verticalAlign='top' className='mca'>
										<Button.Group>
											{getFieldActions({
												field,
												onToggle: () => dispatch(updateGridField(calendarId, grid.id, id, 'enabled', !enabled)),
												onDuplicate: () => {
													const {id, ...duplicateField} = field;
													dispatch(addGridField(calendarId, grid.id, duplicateField));
												},
												onDelete: () => dispatch(deleteGridField(calendarId, grid.id, id)),
											}).map(action => action)}
										</Button.Group>
										<Divider hidden fitted />
										<Button.Group>
											<MoveButtons parent='fields' parentIndex={index} arrayName='fields' element={field} />
										</Button.Group>
									</Table.Cell>
								</Table.Row>
							</Table.Body>
						</Table>
					</Grid.Column>
				);
			})}
		</Grid>
		<Divider hidden />
		{applyFilters('bw_add_field', null, {
			onAdd: field => dispatch(addGridField(calendarId, grid.id, field)),
		})}
	</>;
};
