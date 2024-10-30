import {__} from '@wordpress/i18n';
import {useViewportMatch} from '@wordpress/compose';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Button,
	Label,
	Checkbox,
	Form,
	Dropdown,
} from 'semantic-ui-react';

import cx from 'classnames';

import EVENT_STATUSES from 'config/EVENT_STATUSES';

import {
	useCurrentCalendar,
} from 'hooks';

let EventsQuerySelect;
export default EventsQuerySelect = () => {
	const {events} = useCurrentCalendar();
	const dispatch = useDispatch();
	const isFetching = useSelector(state => state.ui.isFetchingEvents);
	const isWide = useViewportMatch('wide');
	const query = useSelector(state => state.query);
	const {
		status,
		['bw_filter[meta_query][0][key]']: pastQuery,
	} = query;
	const past = !pastQuery;

	return (
		<Form as='div'>
			<Form.Group
				inline
				className={cx('marginless', {'stacked': !isWide})}
				style={{
					...!isWide && ({
						flexDirection: 'column',
						alignItems: 'flex-start',
					}),
				}}
			>
				<Form.Field style={{paddingRight: 0}}>
					<Button labelPosition='left' as='div' style={{pointerEvents: 'none'}}>
						<Label
							basic
							color='green'
							pointing='above'
							content={events.length}
							icon={{
								name: 'calendar plus outline',
								style: {marginRight: '0.33em'},
							}}
						/>
						<Button
							positive
							icon='refresh'
							labelPosition='right'
							content={__('Reload', 'booking-weir')}
							loading={isFetching}
							onClick={() => dispatch({type: 'RELOAD_EVENTS'})}
							style={{pointerEvents: 'initial'}}
						/>
					</Button>
				</Form.Field>
				<Form.Field>
					<Dropdown
						multiple
						selection
						fluid
						selectOnBlur={false}
						closeOnChange={true}
						placeholder={EVENT_STATUSES[0].text}
						options={EVENT_STATUSES}
						value={status}
						onChange={(e, {value}) => dispatch({type: 'SET_QUERY_STATUS', value})}
					/>
				</Form.Field>
				<Form.Field>
					<Checkbox
						label={__('Include events older than a week', 'booking-weir')}
						checked={past}
						onChange={() => dispatch({type: 'SET_QUERY_PAST_EVENTS_FILTER', value: !past})}
					/>
				</Form.Field>
			</Form.Group>
		</Form>
	);
};
