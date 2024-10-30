import {__, _x} from '@wordpress/i18n';
import {useMediaQuery} from '@wordpress/compose';

import {
	applyFilters,
} from '@wordpress/hooks';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Icon,
	Select,
	Checkbox,
	Segment,
	Menu,
	Grid,
	Header,
	Label,
	Input,
	TextArea,
	Form,
} from 'semantic-ui-react';

import {
	StickyContainer,
	Sticky,
} from 'react-sticky';

import {
	Routes,
	Route,
	Navigate,
	NavLink,
} from 'react-router-dom';

import {
	groupBy,
} from 'lodash';

import SettingsSegment from './SettingsSegment';
import EmailPreview from './EmailPreview';
import LocaleInfo from './LocaleInfo';
import TestPdf from './TestPdf';
import CalendarSelect from './CalendarSelect';
import EmailList from './EmailList';
import URLInput from './URLInput';
import ProductSelect from './ProductSelect';

import NumberInput from 'components/backend/controls/NumberInput';
import Editor from 'components/backend/editor';

import LOCALES from 'config/LOCALES';
import TIMEZONES from 'config/TIMEZONES';

import JsonView from 'utils/JsonView';

import {
	useCurrentCalendar,
} from 'hooks';

import {
	updateCalendarName,
	updateSetting,
} from 'actions';

import {
	getAdminbarHeight,
} from 'utils/html';

const SETTING_CATEGORIES = [
	{
		id: 'calendar',
		title: _x('Calendar', 'Setting category title', 'booking-weir'),
	},
	{
		id: 'pricing',
		title: _x('Pricing', 'Setting category title', 'booking-weir'),
	},
	{
		id: 'locale',
		title: _x('Locale', 'Setting category title', 'booking-weir'),
	},
	{
		id: 'booking',
		title: _x('Booking', 'Setting category title', 'booking-weir'),
	},
	{
		id: 'email',
		title: _x('E-mail', 'Setting category title', 'booking-weir'),
	},
	{
		id: 'pdf',
		title: _x('PDF', 'Setting category title', 'booking-weir'),
	},
	{
		id: undefined,
		title: _x('Miscellaneous', 'Setting category title', 'booking-weir'),
	},
];

const ICONS = {
	calendar: 'calendar',
	pricing: 'dollar',
	locale: 'world',
	booking: 'book',
	email: 'mail',
	pdf: 'pdf file',
};

const getValueOrDefault = (type, value, defaultValue) => {
	switch(type) {
		case 'toggle':
			return typeof value === 'boolean' ? value : defaultValue;
		case 'number':
			return typeof value === 'number' ? value : defaultValue;
		case 'text':
			return typeof value === 'string' ? value : defaultValue;
		default:
			return value || defaultValue;
	}
};

const RenderDefaultValue = ({type, defaultValue}) => {
	switch(type) {
		case 'text':
			if(!defaultValue.length) {
				return <Icon disabled name='minus' className='marginless' />;
			}
		break;
		case 'html':
		case 'html-email':
			return <Icon disabled name='html5' className='marginless' />;
	}
	return defaultValue.toString();
};

const SettingControl = ({id, label, type, value, onChange, props}) => {
	switch(type) {
		case 'html':
			return (
				<Editor
					label={label}
					value={value}
					onChange={e => onChange(e.target.value)}
					buttonProps={{floated: 'left'}}
				/>
			);
		case 'html-email':
			return <>
				<Editor
					label={label}
					value={value}
					onChange={e => onChange(e.target.value)}
					buttonProps={{floated: 'left'}}
				/>
				<EmailPreview label={label} template={id} />
			</>;
		case 'locale':
			return (
				<Select
					search
					options={LOCALES}
					value={value}
					onChange={(e, {value}) => onChange(value)}
				/>
			);
		case 'timezone':
			return (
				<Select
					search
					options={TIMEZONES}
					value={value}
					onChange={(e, {value}) => onChange(value)}
				/>
			);
		case 'calendar':
			return <CalendarSelect value={value} onChange={onChange} />;
		case 'toggle':
			return (
				<Checkbox
					toggle
					checked={!!value}
					onChange={(e, {checked}) => onChange(checked)}
				/>
			);
		case 'number':
			return (
				<NumberInput
					type='number'
					value={value}
					onChange={e => onChange(parseInt(e.target.value))}
					{...props}
				/>
			);
		case 'emails':
			return <EmailList value={value} onChange={onChange} {...props} />;
		case 'email':
			return (
				<Input
					type='email'
					value={value}
					onChange={e => onChange(e.target.value)}
					{...props}
				/>
			);
		case 'text':
			return (
				<Input
					type='text'
					value={value}
					onChange={e => onChange(e.target.value)}
					{...props}
				/>
			);
		case 'textarea':
			return (
				<Form as='div'>
					<TextArea
						value={value}
						onChange={e => onChange(e.target.value)}
						{...props}
					/>
				</Form>
			);
		case 'url':
			return <URLInput value={value} onChange={onChange} />;
		case 'product':
			return <ProductSelect value={value} onChange={onChange} />;
	}
	return null;
};

const CategoryContentBefore = ({category, settings}) => {
	const dispatch = useDispatch();
	const {id, name} = useCurrentCalendar();

	switch(category) {
		case 'calendar': {
			return (
				<Segment padded className='settings'>
					<Header content={__('Name', 'booking-weir')} subheader={__('The name of this calendar.', 'booking-weir')} />
					<Input
						placeholder={__('Calendar name...', 'booking-weir')}
						value={name}
						onChange={(e, {value}) => dispatch(updateCalendarName(id, value))}
					/>
				</Segment>
			);
		}
		default:
			return null;
	}
};

const CategoryContentAfter = ({category, settings}) => {
	switch(category) {
		case 'locale':
			return <LocaleInfo settings={settings} />;
		case 'pdf':
			return <TestPdf />;
		default:
			return null;
	}
};

const RenderCategory = ({calendarId, id, category, settings}) => {
	return <>
		<CategoryContentBefore category={id} settings={settings} />
		{category.map(setting => <RenderSetting key={setting.id} calendarId={calendarId} setting={setting} settings={settings} />)}
		<CategoryContentAfter category={id} settings={settings} />
	</>;
};

const RenderSetting = ({calendarId, setting, settings}) => {
	const dispatch = useDispatch();
	const {
		id,
		label,
		description,
		default: defaultValue,
		type,
		premium = false,
		props = {},
		wc = false,
	} = setting;
	const value = getValueOrDefault(type, settings[id], defaultValue);
	const setValue = value => dispatch(updateSetting(calendarId, id, value));

	/**
	 * Don't render WC settings when the calendar is not associated with a product.
	 */
	if(wc && !settings.product) {
		return null;
	}

	if(premium && !applyFilters('bw_is_premium', false)) {
		return null;
	}

	return (
		<SettingsSegment
			title={label}
			description={description}
		>
			{!!defaultValue && (
				<Label
					as='a'
					role='button'
					title={__('Default value', 'booking-weir')}
					attached='top right'
					data-tooltip={__('Reset to default', 'booking-weir')}
					data-position='left center'
					onClick={() => setValue(defaultValue)}
				>
					<RenderDefaultValue type={type} defaultValue={defaultValue} />
				</Label>
			)}
			<SettingControl
				id={id}
				label={label}
				type={type}
				value={value}
				props={props}
				onChange={setValue}
			/>
		</SettingsSegment>
	);
};

let SettingsEdit;
export default SettingsEdit = () => {
	const calendar = useCurrentCalendar();
	const settingsSchema = useSelector(state => state.calendar.settingsSchemas.get(calendar?.id));
	const isStacked = useMediaQuery('(max-width: 767px)');

	if(!calendar || !settingsSchema) {
		return <Segment loading basic padded='very' />;
	}

	const categories = groupBy(settingsSchema, 'category');
	const {settings} = calendar;

	return (
		<StickyContainer>
			<Grid>
				<Grid.Column mobile={16} tablet={4} computer={3}>
					<Sticky disableCompensation={isStacked}>
						{({isSticky, style}) => (
							<Menu
								vertical
								pointing={!isStacked}
								fluid={isStacked || !isSticky}
								style={{
									marginTop: isSticky && !isStacked ? getAdminbarHeight() + 16 : 0,
									paddingTop : 0,
									maxWidth: '100%',
									...(!isStacked && style),
								}}
							>
								{Object.keys(categories).map(category => (
									<Menu.Item
										key={category}
										as={NavLink}
										to={`/${calendar.id}/settings/${category}`}
										// isActive={(match, {pathname}) => pathname.includes(`/${category}`)}
									>
										<Icon name={ICONS[category]} />
										{SETTING_CATEGORIES.find(cat => cat.id === category)?.title || category}
									</Menu.Item>
								))}
							</Menu>
						)}
					</Sticky>
					<JsonView
						id='settings'
						obj={settings}
						style={{
							...(!isStacked && {
								position: 'absolute',
								left: '1em',
								bottom: '1em',
							}),
							...(isStacked && {
								marginTop: '1em',
								marginBottom: '-1em',
							}),
						}}
					/>
				</Grid.Column>
				<Grid.Column stretched mobile={16} tablet={12} computer={13}>
					<Routes>
						<Route index element={<Navigate to='calendar' />} />
						{Object.keys(categories).map(category => (
							<Route
								key={category}
								path={category}
								element={
									<RenderCategory
										calendarId={calendar.id}
										id={category}
										category={categories[category]}
										settings={settings}
									/>
								}
							/>
						))}
					</Routes>
				</Grid.Column>
			</Grid>
		</StickyContainer>
	);
};
