import {__, _x} from '@wordpress/i18n';

import {
	useState,
	useEffect,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

import {
	Text,
	Flex,
} from '@fluentui/react-northstar';

import {
	Grid,
	Segment,
	Label,
	Button,
	Dialog,
	Loader,
} from 'components/ui';

import {
	fetchServiceDescription,
} from 'api';

import {
	setToast,
	clearToast,
} from 'components/frontend/toast';

import {
	RawHTML,
} from 'utils/html';

import {
	InfoIcon,
} from '@fluentui/react-icons-northstar';

import {
	useCurrentCalendar,
	useSelectedService,
} from 'hooks';

import {
	formatMinutes,
} from 'utils/date';

import {
	getServicePriceText,
} from 'utils/services';

const Description = ({service, calendarId, footerContent}) => {
	const dispatch = useDispatch();
	const [isOpen, setIsOpen] = useState(false);
	const close = () => setIsOpen(false);
	const [content, setContent] = useState('');

	const {
		id,
		name,
	} = service;

	useEffect(() => {
		if(isOpen && !content) {
			dispatch(clearToast());
			fetchServiceDescription(calendarId, id)
				.then(response => setContent(response))
				.catch(e => {
					console.error(e);
					dispatch(setToast(__('Failed fetching service description.', 'booking-weir')));
					close();
				});
		}
	}, [isOpen, content, id, calendarId, dispatch]);

	return <>
		<Button
			icon={<InfoIcon />}
			content={_x('Info', 'Button text that displays a modal with service description.', 'booking-weir')}
			onClick={() => setIsOpen(true)}
		/>
		<Dialog
			size='small'
			open={isOpen}
			header={name}
			content={content ? <RawHTML>{content}</RawHTML> : <Loader styles={{padding: '3em'}} />}
			footer={footerContent ? footerContent({service, close}) : <Button secondary content={__('Close', 'booking-weir')} onClick={close} />}
			closeIcon
			onClose={close}
		/>
	</>;
};

let ServiceSegments;
export default ServiceSegments = ({
	children,
	descriptionFooter,
	filter = () => true,
	columns = 3,
	titleSize = 'large',
	titleWeight = 'regular',
}) => {
	const selectedService = useSelectedService();
	const calendar = useCurrentCalendar();
	const {
		settings,
		services: allServices,
	} = calendar;
	const {step} = settings;

	const services = allServices.filter(({enabled}) => typeof enabled === 'undefined' || enabled).filter(filter);
	if(!services.length) {
		return null;
	}

	return (
		<Grid
			columns={Math.min(services.length, columns)}
			styles={{marginBottom: '1em'}}
			stackable
		>
			{services.map((service, index) => {
				const {
					id,
					type = 'fixed',
					name,
					hasDescription,
					duration,
				} = service;
				const isSelected = !!selectedService && selectedService.id === id;
				const color = isSelected ? 'positive' : 'primary';

				return (
					<Segment
						key={id}
						color={color}
						styles={{
							position: 'relative',
							paddingTop: '2em',
							borderBottomWidth: 0,
						}}
					>
						{type === 'fixed' && (
							<Label
								size='small'
								color={color}
								horizontal
								attached='top left'
								content={formatMinutes(duration * step, settings)}
							/>
						)}
						<Label
							size='small'
							color={color}
							horizontal
							attached='top right'
							content={getServicePriceText(service, settings)}
						/>
						<Flex fill column space='between'>
							<Text
								className='bw-service-title'
								size={titleSize}
								weight={titleWeight}
								content={name}
								styles={{
									display: 'block',
									marginBottom: '0.5em',
								}}
							/>
							<Flex gap='gap.small'>
								{children({service, index, color, isSelected})}
								{hasDescription && (
									<Description
										service={service}
										calendarId={calendar.id}
										footerContent={descriptionFooter}
									/>
								)}
							</Flex>
						</Flex>
					</Segment>
				);
			})}
		</Grid>
	);
};
