import {__} from '@wordpress/i18n';

import {
	useState,
	useCallback,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
	Modal,
} from 'semantic-ui-react';

import {useDropzone} from 'react-dropzone';

import {
	useCurrentCalendar,
} from 'hooks';

let ImportExport;
export default ImportExport = ({id, data, Importer}) => {
	const dispatch = useDispatch();
	const calendar = useCurrentCalendar();
	const calendarName = calendar?.name;
	const [importData, setImportData] = useState({});
	const [imported, setImported] = useState([]);
	const [isOpen, setIsOpen] = useState(false);

	const onDrop = useCallback(acceptedFiles => {
		acceptedFiles[0].text().then(importedData => {
			dispatch({type: 'CLEAR_MESSAGE'});
			try {
				const parsedData = JSON.parse(importedData);
				setImportData(parsedData);
				setIsOpen(true);
			} catch(e) {
				dispatch({
					type: 'SET_MESSAGE',
					value: {
						negative: true,
						content: __('Unable to parse file.', 'booking-weir'),
					},
				});
			}
		});
	}, [dispatch])
	const {getRootProps, getInputProps} = useDropzone({noDrag: true, onDrop});

	const onExport = useCallback(() => {
		const exportData = encodeURIComponent(JSON.stringify(data, null, 2));
		const date = new Date().toISOString();
		const a = document.createElement('a');
		a.id = 'bw-export';
		a.href = `data:text/plain;charset=utf-8,${exportData}`;
		const name = calendarName ? `${calendarName}-${id}` : `${id}`;
		a.download = `bw-${booking_weir_data.blog_name}-${name}-${date}.txt`;
		document.getElementById('bw-no-sui').appendChild(a);
		document.getElementById('bw-export').click();
		document.getElementById('bw-export').remove();
	}, [calendarName, id, data]);

	return <>
		<Button.Group>
			<Button
				icon='upload'
				content={__('Export', 'booking-weir')}
				onClick={onExport}
			/>
			<Button
				icon='download'
				content={<>
					{__('Import', 'booking-weir')}
					<input {...getInputProps()} />
				</>}
				{...getRootProps({className: 'dropzone'})}
			/>
		</Button.Group>
		<Modal
			mountNode={document.getElementById('bw-no-sui')}
			open={isOpen}
			closeIcon
			onClose={() => {
				setIsOpen(false);
				if(imported.length) {
					dispatch({type: 'IMPORTED_CALENDARS'});
				}
			}}
		>
			<Modal.Header style={{margin: 0}}>{__('Import', 'booking-weir')}</Modal.Header>
			<Modal.Content scrolling className='sui-root'>
				<Importer data={importData} onImport={imported => setImported(imported)} />
			</Modal.Content>
		</Modal>
	</>;
};
