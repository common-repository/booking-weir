import {__} from '@wordpress/i18n';

import {
	useState,
	useEffect,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
	Table,
	Label,
} from 'semantic-ui-react';

import {ActionCreators} from 'redux-undo';

import {
	importCalendar,
} from 'actions';

let ImportCalendars;
export default ImportCalendars = ({data, onImport}) => {
	const dispatch = useDispatch();
	const [importedCalendars, setImportedCalendars] = useState([]);

	useEffect(() => {
		onImport && onImport(importedCalendars);
	}, [importedCalendars, onImport]);

	return (
		<Table>
			<Table.Header>
				<Table.Row>
					<Table.HeaderCell collapsing>{__('ID', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell>{__('Name', 'booking-weir')}</Table.HeaderCell>
					<Table.HeaderCell></Table.HeaderCell>
				</Table.Row>
			</Table.Header>
			<Table.Body>
				{Object.keys(data).map(calendarId => {
					if(!isNaN(calendarId)) {
						return null; // ID should be string.
					}
					const calendar = data[calendarId];
					const {name} = calendar;
					const exists = Object.keys(data).includes(calendarId);
					const imported = importedCalendars.includes(calendarId);
					return (
						<Table.Row key={`import-${calendarId}`}>
							<Table.Cell><code>{calendarId}</code></Table.Cell>
							<Table.Cell>
								{(exists && !imported) && (
									<Label
										horizontal
										size='small'
										color='yellow'
										content={__('Exists', 'booking-weir')}
									/>
								)}
								{imported && (
									<Label
										horizontal
										size='small'
										color='green'
										content={__('Imported', 'booking-weir')}
									/>
								)}
								{name}
							</Table.Cell>
							<Table.Cell textAlign='right'>
								{(!imported && exists) && <>
									<Button
										primary
										icon='download'
										content={__('Import a copy', 'booking-weir')}
										data-tooltip={__(`Calendar with this ID already exists, create a new calendar with the imported data.`, 'booking-weir')}
										data-position='top right'
										onClick={() => {
											dispatch(importCalendar(calendarId, calendar));
											setImportedCalendars([...importedCalendars, calendarId]);
										}}
									/>
									<Button
										color='orange'
										icon='exchange'
										content={__('Overwrite', 'booking-weir')}
										data-tooltip={__(`Calendar with this ID already exists, overwrite it's settings with the imported data.`, 'booking-weir')}
										data-position='top right'
										onClick={() => {
											dispatch(importCalendar(calendarId, calendar, true));
											setImportedCalendars([...importedCalendars, calendarId]);
										}}
									/>
								</>}
								{(!imported && !exists) && (
									<Button
										primary
										icon='download'
										content={__('Import', 'booking-weir')}
										onClick={() => {
											dispatch(importCalendar(calendarId, calendar, true));
											setImportedCalendars([...importedCalendars, calendarId]);
										}}
									/>
								)}
								{imported && (
									<Button
										icon='undo'
										content={__('Undo', 'booking-weir')}
										disabled={importedCalendars[importedCalendars.length - 1] !== calendarId}
										onClick={() => {
											dispatch(ActionCreators.undo());
											setImportedCalendars(importedCalendars.filter(id => id !== calendarId));
										}}
									/>
								)}
							</Table.Cell>
						</Table.Row>
					);
				})}
			</Table.Body>
		</Table>
	);
};
