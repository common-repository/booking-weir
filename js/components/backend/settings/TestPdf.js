import {__} from '@wordpress/i18n';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Button,
} from 'semantic-ui-react';

import SettingsSegment from './SettingsSegment';

import {
	useCurrentCalendarId,
} from 'hooks';

let TestPdf;
export default TestPdf = () => {
	const dispatch = useDispatch();
	const calendarId = useCurrentCalendarId();
	const isRegeneratingPdf = useSelector(state => state.ui.isRegeneratingPdf);
	const pdf = useSelector(state => state.ui.testPdf);

	return (
		<SettingsSegment
			title={__('Test PDF generation', 'booking-weir')}
			description={__('Generate a test PDF file to ensure the server is capable.', 'booking-weir')}
		>
			<Button
				icon='file pdf'
				labelPosition='right'
				floated='left'
				content={__('Generate test PDF', 'booking-weir')}
				onClick={() => dispatch({type: 'TEST_PDF', calendarId})}
				loading={isRegeneratingPdf}
			/>
			{pdf && (
				<Button
					as='a'
					href={pdf}
					target='_blank'
					rel='noopener noreferrer'
					primary
					icon='eye'
					labelPosition='right'
					floated='left'
					content={__('View', 'booking-weir')}
				/>
			)}
		</SettingsSegment>
	);
};
