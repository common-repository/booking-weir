import {__} from '@wordpress/i18n';

import {
	useState,
	useEffect,
} from 'react';

import {
	Button,
	Segment,
	Modal,
} from 'semantic-ui-react';

let JsonView;
export default JsonView = ({id = 'default', obj = {}, ...props}) => {
	const [isOpen, setIsOpen] = useState(false);
	const toggle = () => setIsOpen(!isOpen);

	useEffect(() => {
		if(isOpen) {
			console.debug(id, obj);
		}
	}, [isOpen, id, obj]);

	return <>
		<Button
			basic
			icon='code'
			active={isOpen}
			onClick={toggle}
			aria-label={__('View raw data', 'booking-weir')}
			{...props}
		/>
		<Modal
			mountNode={document.getElementById('bw-no-sui')}
			open={isOpen}
			closeIcon={true}
			onClose={() => setIsOpen(false)}
		>
			<Modal.Header style={{margin: 0}}>
				{__('Raw data', 'booking-weir')}{': '}<code style={{fontSize: 'inherit'}}>{id}</code>
			</Modal.Header>
			<Modal.Content scrolling className='sui-root paddingless'>
				<Segment secondary clearing className='shadowless borderless'>
					<pre style={{margin: 0, overflowX: 'auto', whiteSpace: 'pre-wrap'}}>
						{JSON.stringify(obj, null, 2)}
					</pre>
				</Segment>
			</Modal.Content>
		</Modal>
	</>;
};
