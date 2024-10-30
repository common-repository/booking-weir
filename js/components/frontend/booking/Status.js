import {__} from '@wordpress/i18n';

import {
	Segment,
	Header,
	List,
} from 'components/ui';

import {
	getRawHTMLElement,
} from 'utils/html';

const STATUS = window.bw_booking && window.bw_booking.length ? [...window.bw_booking] : [];

let Status;
export default Status = () => {
	if(!STATUS.length) {
		return null;
	}

	return (
		<Segment
			primary
			padded
			styles={{
				borderBottom: 0,
				marginBottom: '1em',
			}}
		>
			<Header
				as='h2'
				align='center'
				mla
				content={__('Booking info', 'booking-weir')}
			/>
			<List
				horizontal
				compact
				size='large'
				items={STATUS.map(({content, ...item}, index) => ({
					key: `item-${index}`,
					...item,
					content: getRawHTMLElement(content),
				}))}
			/>
		</Segment>
	);
};
