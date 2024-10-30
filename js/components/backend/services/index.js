import {__} from '@wordpress/i18n';

import {
	Icon,
	Header,
	Message,
	Grid,
	List,
} from 'semantic-ui-react';

import ServicesEdit from './ServicesEdit';

import SERVICE_TYPES from 'config/SERVICE_TYPES';

let Prices;
export default Prices = () => {
	return (
		<Grid columns={2} stackable>
			<Grid.Column>
				<Message info>
					<Icon name='info' />
					{__(`Services allow the user to book a time with a predefined duration and price.`, 'booking-weir')}
				</Message>
			</Grid.Column>
			<Grid.Column>
				<Message>
					<Header>{__('Service types', 'booking-weir')}</Header>
					<List bulleted>
						{SERVICE_TYPES.map(({key, text, desc = ''}) => <List.Item key={key} header={text} description={desc} />)}
					</List>
				</Message>
			</Grid.Column>
			<Grid.Row columns={1}>
				<Grid.Column>
					<ServicesEdit />
				</Grid.Column>
			</Grid.Row>
		</Grid>
	);
};
