import {__} from '@wordpress/i18n';

import {
	useSelector,
} from 'react-redux';

import {
	Icon,
	Header,
	Menu,
} from 'semantic-ui-react';

import {
	Routes,
	Route,
	Navigate,
	Link,
} from 'react-router-dom';

import getMenuItems from './getMenuItems';
import routes from './routes';

import {
	useCurrentCalendar,
} from 'hooks';

let CalendarEdit;
export default CalendarEdit = () => {
	const calendar = useCurrentCalendar();
	const eventsLoaded = useSelector(state => state.calendar.eventsLoaded.get(calendar.id));
	const menuItems = getMenuItems({
		calendar,
		eventsLoaded,
	});

	if(!calendar) {
		return null;
	}

	const url = calendar?.settings?.url;

	return <>
		<Link
			to='/'
			title={__('Return to calendars list', 'booking-weir')}
			style={{float: 'right'}}
		>
			<Icon
				link
				color='black'
				size='big'
				name='share'
				flipped='horizontally'
			/>
		</Link>
		<Header size='large' style={{marginTop: 0}}>
			<a
				href={url || '#'}
				title={__('Visit calendar', 'booking-weir')}
				onClick={url ? undefined : e => e.preventDefault()}
			>
				<Icon name='calendar' color='black' link />
			</a>
			<Header.Content>
				{calendar.name || __('Calendar', 'booking-weir')}
				<Header.Subheader>{__('Manage calendar', 'booking-weir')}</Header.Subheader>
			</Header.Content>
		</Header>
		<Menu
			size='large'
			stackable
			pointing
			widths={menuItems.length}
			items={menuItems}
			style={{paddingTop: 0, marginBottom: '2em'}}
		/>
		<Routes>
			<Route index element={<Navigate to='events' />} />
			{routes.map(route => route)}
		</Routes>
	</>;
};
