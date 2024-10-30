import {__} from '@wordpress/i18n';

import {
	useState,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Button,
	Modal,
} from 'semantic-ui-react';

import {
	pick,
} from 'lodash';

import SafeSrcDocIframe from 'react-safe-src-doc-iframe';

import {
	fetchTestEmail,
} from 'api';

import {
	useCurrentCalendar,
} from 'hooks';

let EmailPreview;
export default EmailPreview = ({label, template}) => {
	const dispatch = useDispatch();
	const {
		id: calendarId,
		settings,
	} = useCurrentCalendar();
	const [isOpen, setIsOpen] = useState(false);
	const [emailHTML, setEmailHTML] = useState('');

	const emailSettings = pick(settings, [
		'templateEmailHeader',
		'templateEmailFooter',
		'templateInvoiceEmailContent',
		'templateStatusConfirmedEmailContent',
		'templateReminderEmailContent',
	]);

	const open = emailType => {
		setIsOpen(true);
		fetchTestEmail(calendarId, emailSettings, emailType)
			.then(response => setEmailHTML(response))
			.catch(e => {
				dispatch({
					type: 'SET_MESSAGE',
					value: {
						negative: true,
						icon: 'warning circle',
						header: __('Failed fetching e-mail preview', 'booking-weir'),
						content: e.message,
					},
				});
				setIsOpen(false);
			});
	};

	return <>
		<Button
			primary
			icon='mail'
			labelPosition='right'
			content={__('Preview', 'booking-weir')}
			onClick={() => open(template)}
		/>
		<Modal
			mountNode={document.getElementById('bw-no-sui')}
			open={isOpen}
			closeIcon={true}
			onClose={() => setIsOpen(false)}
		>
			<Modal.Header style={{margin: 0}}>
				{`${__('E-mail preview', 'booking-weir')}: ${label}`}
			</Modal.Header>
			<Modal.Content scrolling style={{padding: 0}}>
				<SafeSrcDocIframe
					title={__('E-mail preview', 'booking-weir')}
					srcDoc={emailHTML}
					style={{
						width: '100%',
						height: '100vh',
					}}
				/>
			</Modal.Content>
		</Modal>
	</>;
};
