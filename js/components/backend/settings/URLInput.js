import {__} from '@wordpress/i18n';
import {URLInput as WPURLInput} from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import {decodeEntities} from '@wordpress/html-entities';

import {
	Segment,
} from 'semantic-ui-react';

let URLInput;
export default URLInput = ({value, onChange}) => {
	return (
		<Segment basic compact className='paddingless'>
			<WPURLInput
				className='ui deep input'
				__experimentalFetchLinkSuggestions={fetchLinkSuggestions}
				value={value}
				onChange={value => onChange(value)}
				autoFocus={false}
			/>
		</Segment>
	);
};

const fetchLinkSuggestions = async (search, {perPage = 20} = {}) => {
	const posts = await apiFetch({
		path: addQueryArgs('/wp/v2/search', {
			search,
			per_page: perPage,
			type: 'post',
			subtype: 'page',
		}),
	});

	return posts.map(post => ({
		id: post.id,
		url: post.url,
		title: decodeEntities(post.title) || __('(no title)', 'booking-weir'),
		type: post.subtype || post.type,
	}));
};
