import {__, _x, sprintf} from '@wordpress/i18n';

import {
	useDispatch,
} from 'react-redux';

import {
	Table,
	Select,
	Input,
	Button,
	Message,
	Label,
	Form,
	Divider,
} from 'semantic-ui-react';

import {
	TimeInput,
} from 'components/backend/controls/DateTime';

import AddService from './AddService';
import DuplicateService from './DuplicateService';
import DeleteService from './DeleteService';
import CopyServiceLink from './CopyServiceLink';

import Editor from 'components/backend/editor';
import MoveButtons from 'components/backend/controls/MoveButtons';
import ToggleButton from 'components/backend/controls/ToggleButton';

import JsonView from 'utils/JsonView';

import SERVICE_TYPES from 'config/SERVICE_TYPES';

import {
	useCurrentCalendar,
	useCurrencyRenderer,
} from 'hooks';

import {
	updateService,
} from 'actions';

import {
	formatMinutes,
} from 'utils/date';

const SERVICE_AVAILABILITY_TYPES = [
	{
		key: 'default',
		text: _x('Always', 'Service availability type', 'booking-weir'),
		value: 'default',
	},
	{
		key: 'time-range',
		text: __('Time range', 'booking-weir'),
		value: 'time-range',
	},
];

let ServicesEdit;
export default ServicesEdit = () => {
	const dispatch = useDispatch();
	const {
		id: calendarId,
		settings,
		services,
	} = useCurrentCalendar();
	const {
		step,
		openingHour,
		closingHour,
	} = settings;
	const renderCurrency = useCurrencyRenderer();

	return (
		<Table>
			<Table.Header>
				<Table.Row>
					<Table.HeaderCell>{__('Name', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell>{__('Description', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell>{__('Type', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell>{__('Duration', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell>{__('Price', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell>{__('Availability', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell collapsing></Table.HeaderCell>
				</Table.Row>
			</Table.Header>
			<Table.Body>
				{!services.length && (
					<Table.Row>
						<Table.Cell colSpan={7}>
							<Message>{__('There are no services for this calendar.', 'booking-weir')}</Message>
						</Table.Cell>
					</Table.Row>
				)}
				{services.map(service => {
					const {
						id,
						name,
						description,
						type = 'fixed',
						duration,
						price,
						enabled = false,
						availability = SERVICE_AVAILABILITY_TYPES[0].value,
						availableFrom = '00:00',
						availableTo = '00:00',
					} = service;
					return (
						<Table.Row key={`extra-${id}`}>
							<Table.Cell>
								<Input
									placeholder={__('Service name...', 'booking-weir')}
									value={name}
									onChange={(e, {value}) => dispatch(updateService(calendarId, id, 'name', value))}
								/>
							</Table.Cell>
							<Table.Cell>
								<Editor
									label={sprintf(__('Service "%s" description', 'booking-weir'), name)}
									value={description}
									onChange={e => dispatch(updateService(calendarId, id, 'description', e.target.value))}
								/>
							</Table.Cell>
							<Table.Cell collapsing>
								<Select
									placeholder={__('Select a service type', 'booking-weir')}
									options={SERVICE_TYPES}
									value={type || SERVICE_TYPES[0].value}
									onChange={(e, {value}) => dispatch(updateService(calendarId, id, 'type', value))}
								/>
							</Table.Cell>
							<Table.Cell collapsing>
								{type === 'fixed' && <>
									<Input
										type='number'
										labelPosition='right'
										label={`×${step}${_x('min', 'Minutes', 'booking-weir')}`}
										value={duration || 0}
										onChange={(e, {value}) => dispatch(updateService(calendarId, id, 'duration', value))}
										min={1}
										max={9999}
										step={1}
										className='center aligned'
										style={{
											minWidth: 140,
										}}
									/>
									<br />
									<Label
										// content={__('Duration', 'booking-weir')}
										// detail={formatMinutes(duration * step, settings)}
										content={formatMinutes(duration * step, settings)}
										style={{
											margin: '0.25em 0 0 0',
											minWidth: 140,
											width: '100%',
											textAlign: 'center',
										}}
									/>
								</>}
							</Table.Cell>
							<Table.Cell collapsing>
								<Input
									type='number'
									labelPosition='right'
									label={_x('/hr', 'Per hour', 'booking-weir')}
									value={price || 0}
									onChange={(e, {value}) => dispatch(updateService(calendarId, id, 'price', value))}
									min={0}
									max={9999}
									step={1}
									className='center aligned'
									style={{
										minWidth: 140,
									}}
								/>
								{type === 'fixed' && <>
									<br />
									<Label
										content={__('Price', 'booking-weir')}
										detail={renderCurrency(duration * step / 60 * price)}
										// style={{marginLeft: '0.5em'}}
										style={{
											margin: '0.25em 0 0 0',
											minWidth: 140,
											width: '100%',
											textAlign: 'center',
										}}
									/>
								</>}
							</Table.Cell>
							<Table.Cell collapsing>
								<Form as='div'>
									<Form.Field>
										<Select
											compact
											options={SERVICE_AVAILABILITY_TYPES}
											value={availability}
											onChange={(e, {value}) => dispatch(updateService(calendarId, id, 'availability', value))}
											style={{minWidth: 200}}
										/>
									</Form.Field>
									{availability === 'time-range' && (
										<Form.Group widths={2}>
											<TimeInput
												clearable={false}
												value={availableFrom}
												onChange={value => dispatch(updateService(calendarId, id, 'availableFrom', value))}
											/>
											<TimeInput
												clearable={false}
												value={availableTo}
												onChange={value => dispatch(updateService(calendarId, id, 'availableTo', value))}
											/>
										</Form.Group>
									)}
								</Form>
							</Table.Cell>
							<Table.Cell textAlign='right' verticalAlign='top' className='mca'>
								<Button.Group>
									<ToggleButton
										enabled={enabled}
										onClick={() => dispatch(updateService(calendarId, id, 'enabled', !enabled))}
									/>
									<DuplicateService service={service} />
									<CopyServiceLink service={service} />
								</Button.Group>
								<Divider hidden fitted />
								<Button.Group>
									<MoveButtons arrayName='services' element={service} />
									<DeleteService service={service} />
								</Button.Group>
							</Table.Cell>
						</Table.Row>
					);
				})}
			</Table.Body>
			<Table.Footer>
				<Table.Row>
					<Table.HeaderCell colSpan={6}>
						<AddService />
					</Table.HeaderCell>
					<Table.HeaderCell textAlign='right'>
						<JsonView id='services' obj={services} />
					</Table.HeaderCell>
				</Table.Row>
			</Table.Footer>
		</Table>
	);
};
