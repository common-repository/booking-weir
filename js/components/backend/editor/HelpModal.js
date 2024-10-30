import {__, _x} from '@wordpress/i18n';

import {
	useState,
	useEffect,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Button,
	Modal,
	Header,
	Table,
} from 'semantic-ui-react';

import {
	useCurrentCalendarId,
} from 'hooks';

import {
	copy,
} from 'utils/clipboard';

let HelpModal;
export default HelpModal = () => {
	const calendarId = useCurrentCalendarId();
	const dispatch = useDispatch();
	const templateStrings = useSelector(state => state.ui.templateStrings);
	const [isOpen, setIsOpen] = useState(false);

	const fetched = Object.keys(templateStrings).length > 0;
	useEffect(() => {
		if(!isOpen || fetched) {
			return;
		}
		dispatch({type: 'FETCH_TEMPLATE_STRINGS', calendarId});
	}, [isOpen, dispatch, calendarId, fetched]);

	const onClose = () => {
		dispatch({type: 'CLEAR_MESSAGE'});
		setIsOpen(false);
	};

	return (
		<>
			<Button
				primary
				floated='left'
				icon='question circle outline'
				content={__('Help', 'booking-weir')}
				onClick={() => setIsOpen(true)}
			/>
			<Modal
				mountNode={document.getElementById('bw-no-sui')}
				open={isOpen}
				onClose={onClose}
			>
				<Modal.Header style={{margin: 0}}>{__('Help', 'booking-weir')}</Modal.Header>
				<Modal.Content scrolling className='sui-root'>
					<Header>{_x('Notice', 'Title for info about templates with the block editor', 'booking-weir')}</Header>
					<p>{__(`While the WordPress block editor can be used to edit template settings and event content, all of its features are not fully supported and the outcome depends on the context where the content is used. Treat it as a basic textarea with some benefits, don't expect WYSIWYG results from this editor, test and verify after making changes.`, 'booking-weir')}</p>
					<p>{__(`E-mail templates include a subset of the WordPress core block styles so simple blocks like header, button, image should work well. Most e-mail clients don't support CSS flex, so tables should be used instead of columns.`, 'booking-weir')}</p>
					<p>{__(`PDF templates do not support most of the block styles, so it's best to use basic paragraphs or an HTML block.`, 'booking-weir')}</p>
					{fetched && <>
						<Header>{__('Available template strings', 'booking-weir')}</Header>
						<Table>
							<Table.Header>
								<Table.Row>
									<Table.HeaderCell collapsing>{__('Template string', 'booking-weir')}</Table.HeaderCell>
									<Table.HeaderCell>{__('Example value', 'booking-weir')}</Table.HeaderCell>
								</Table.Row>
							</Table.Header>
							<Table.Body>
								{Object.keys(templateStrings).map(tag => (
									<Table.Row key={tag}>
										<Table.Cell>
											<code
												onClick={() => {
													copy(tag);
													dispatch({
														type: 'SET_MESSAGE',
														value: {
															positive: true,
															content: __('Copied to clipboard', 'booking-weir'),
														},
													});
												}}
												style={{cursor: 'pointer'}}
											>
												{tag}
											</code>
										</Table.Cell>
										<Table.Cell style={{whiteSpace: 'pre'}}>{templateStrings[tag]}</Table.Cell>
									</Table.Row>
								))}
							</Table.Body>
						</Table>
					</>}
				</Modal.Content>
				<Modal.Actions className='sui-root'>
					<Button
						inverted
						color='red'
						content={__('Close', 'booking-weir')}
						onClick={onClose}
					/>
				</Modal.Actions>
			</Modal>
		</>
	);
};
