import {__} from '@wordpress/i18n';

import {
	Flex,
	Text,
} from '@fluentui/react-northstar';

import {
	ChevronStartIcon,
	ChevronEndIcon,
	SettingsIcon,
} from '@fluentui/react-icons-northstar';

import {
	Button,
} from 'components/ui';

import {
	DateViewUpdater,
	RangeUpdater,
	Navigator,
} from 'utils/calendarState';

import {
	useCurrentCalendar,
} from 'hooks';

const navigate = {
	PREVIOUS: 'PREV',
	NEXT: 'NEXT',
	TODAY: 'TODAY',
	DATE: 'DATE',
};

let Toolbar;
export default Toolbar = ({localizer, label, date, view, views, onNavigate, onView}) =>  {
	const {messages} = localizer;
	const {data} = useCurrentCalendar();
	const isWide = views.includes('week') || views.includes('work_week');

	const getNavButtons = () => {
		return [
			{
				key: 'previous',
				as: 'div',
				title: messages.previous,
				onClick: () => onNavigate(navigate.PREVIOUS),
				iconOnly: true,
				text: true,
				icon: <ChevronStartIcon />,
			},
			{
				key: 'next',
				as: 'div',
				title: messages.next,
				onClick: () => onNavigate(navigate.NEXT),
				iconOnly: true,
				text: true,
				icon: <ChevronEndIcon />,
			},
			{
				key: 'today',
				as: 'div',
				text: true,
				content: messages.today,
				onClick: () => onNavigate(navigate.TODAY),
			},
		];
	};

	const getViewButtons = () => {
		let buttons = [];

		if(views.length > 1) {
			buttons = views.map(name => ({
				key: name,
				as: 'div',
				content: messages[name],
				disabled: view === name,
				onClick: () => onView(name),
				text: true,
			}));
		}

		if(data.admin_url) {
			buttons.push({
				key: 'manage',
				as: 'a',
				title: __('Manage calendar', 'booking-weir'),
				text: true,
				icon: <SettingsIcon />,
				iconOnly: true,
				href: data.admin_url,
			});
		}

		return buttons;
	};

	return <>
		<Flex
			gap='gap.small'
			space='between'
			vAlign='center'
			hAlign={isWide ? undefined: 'center'}
			column={!isWide}
			styles={{marginBottom: '1em'}}
		>
			<Button.Group buttons={getNavButtons()} styles={{whiteSpace: 'nowrap'}} />
			<Text
				size='larger'
				weight='bold'
				styles={{...(!isWide && {order: -1})}}
				content={label}
			/>
			<Button.Group buttons={getViewButtons()} styles={{whiteSpace: 'nowrap'}} />
		</Flex>
		<DateViewUpdater date={date} view={view} />
		<RangeUpdater date={date} view={view} localizer={localizer} />
		<Navigator date={date} navigate={onNavigate} />
	</>;
}
