import {
	useViewportMatch,
	useMediaQuery,
} from '@wordpress/compose';

import {
	Component,
} from 'react';

import {
	Grid,
	Button,
	Header,
	Dropdown,
} from 'semantic-ui-react';

import PropTypes from 'prop-types';

import {
	DateViewUpdater,
	RangeUpdater,
	Navigator,
} from 'utils/calendarState';

const navigate = {
	PREVIOUS: 'PREV',
	NEXT: 'NEXT',
	TODAY: 'TODAY',
	DATE: 'DATE',
};

const ViewButtons = ({views}) => {
	const collapseButtons = useMediaQuery('(max-width: 1680px) and (min-width: 1280px)');
	if(collapseButtons) {
		return (
			<Dropdown
				button
				floating
				className='right labeled icon'
				text={views.find(v => v.active).children}
				value={views.findIndex(v => v.active)}
				options={views.map(({key, children}, index) => ({
					key,
					text: children,
					value: index,
				}))}
				onChange={(e, {value}) => views[value].onClick()}
			/>
		);
	}
	return views.map(view => <Button key={view.key} {...view} />);
};

const ToolbarGrid = ({nav, title, views}) => {
	const isWide = useViewportMatch('wide');
	const columns = isWide ? 3 : 1;
	const navAlign = isWide ? 'left' : 'center';
	const viewsAlign = isWide ? 'right' : 'center';
	return (
		<Grid columns={columns} verticalAlign='middle' style={{marginBottom: 0}}>
			<Grid.Column textAlign={navAlign}>{nav}</Grid.Column>
			<Grid.Column textAlign='center' className='toolbar-label'>{title}</Grid.Column>
			<Grid.Column textAlign={viewsAlign}>{views}</Grid.Column>
		</Grid>
	);
};

class Toolbar extends Component {
	navigate = action => {
		this.props.onNavigate(action);
	};

	view = view => {
		this.props.onView(view);
	};

	viewNamesGroup(messages) {
		let viewNames = this.props.views;
		const view = this.props.view;

		if(viewNames.length > 1) {
			const views = viewNames.map(name => ({
				key: name,
				active: view === name,
				onClick: this.view.bind(null, name),
				children: messages[name],
			}));
			return <ViewButtons views={views} />;
		}
	}

	render() {
		let {
			localizer,
			label,
			view,
			date,
			onNavigate,
		} = this.props;
		let {messages} = localizer;

		return <>
			<ToolbarGrid
				nav={(
					<Button.Group compact className='marginless'>
						<Button
							onClick={this.navigate.bind(null, navigate.PREVIOUS)}
							icon='left chevron'
							aria-label={messages.previous}
						/>
						<Button
							onClick={this.navigate.bind(null, navigate.NEXT)}
							icon='right chevron'
							aria-label={messages.next}
						/>
						<Button
							onClick={this.navigate.bind(null, navigate.TODAY)}
							content={messages.today}
						/>
					</Button.Group>
				)}
				title={<Header style={{whiteSpace: 'nowrap'}}>{label}</Header>}
				views={<Button.Group compact className='marginless'>{this.viewNamesGroup(messages)}</Button.Group>}
			/>
			<DateViewUpdater date={date} view={view} />
			<RangeUpdater date={date} view={view} localizer={localizer} />
			<Navigator date={date} navigate={onNavigate} />
		</>;
	}
}

Toolbar.propTypes = {
	view: PropTypes.string.isRequired,
	views: PropTypes.arrayOf(PropTypes.string).isRequired,
	label: PropTypes.node.isRequired,
	localizer: PropTypes.object,
	onNavigate: PropTypes.func.isRequired,
	onView: PropTypes.func.isRequired,
};

export default Toolbar;
