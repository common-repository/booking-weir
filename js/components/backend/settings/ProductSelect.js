import {__} from '@wordpress/i18n';
import {addQueryArgs} from '@wordpress/url';
import {URLInput} from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import {decodeEntities} from '@wordpress/html-entities';

import {
	useState,
} from 'react';

import {
	Button,
	Label,
	Divider,
	Segment,
} from 'semantic-ui-react';

let ProductSelect;
export default ProductSelect = ({value, onChange}) => {
	const [search, setSearch] = useState('');
	const hasValue = (!!value && value > 0);
	return <>
		{hasValue && <>
			<Button labelPosition='left' as='div'>
				<Label basic pointing='right' content={value} style={{alignSelf: 'stretch'}} />
				<Button content={__('Clear', 'booking-weir')} onClick={() => onChange(0)} />
			</Button>
			<Divider hidden fitted />
		</>}
		{!hasValue && (
			<Segment basic compact className='paddingless'>
				<URLInput
					className='ui deep input'
					placeholder={__('Type product name to search...', 'booking-weir')}
					__experimentalFetchLinkSuggestions={fetchProductSuggestions}
					value={search}
					onChange={(value, post) => {
						setSearch(value);
						if(post && post.id) {
							setSearch('');
							onChange(post.id);
						}
					}}
					autoFocus={false}
				/>
			</Segment>
		)}
	</>;
};

const fetchProductSuggestions = async (search, {perPage = 20} = {}) => {
	const posts = await apiFetch({
		path: addQueryArgs('/wp/v2/search', {
			search,
			per_page: perPage,
			type: 'post',
			subtype: 'product',
		}),
	});

	return posts.map(post => ({
		id: post.id,
		url: post.url,
		title: decodeEntities(post.title) || __('(no title)', 'booking-weir'),
		type: post.subtype || post.type,
	}));
};
