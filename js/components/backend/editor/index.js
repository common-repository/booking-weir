import {__} from '@wordpress/i18n';
import {serialize} from '@wordpress/blocks';
import {useViewportMatch} from '@wordpress/compose';

import {
	useState,
	useCallback,
	useLayoutEffect,
} from 'react';

import {
	Button,
	Modal,
} from 'semantic-ui-react';

import Gutenberg from './Gutenberg';
import HelpModal from './HelpModal';

import {
	getAdminbarHeight,
} from 'utils/html';

let Editor;
export default Editor = ({label = '', value = '', onChange, buttonProps = {}}) => {
	const [isOpen, setIsOpen] = useState(false);
	const isWide = useViewportMatch('wide');
	const [visual, setVisual] = useState(true); // Editor mode, true = visual, false = text
	const [editorValue, setEditorValue] = useState([]);
	const [adminbarHeight, setAdminbarHeight] = useState(getAdminbarHeight());

	const toggle = useCallback(() => {
		setIsOpen(!isOpen);
	}, [isOpen]);

	const close = useCallback(() => {
		setIsOpen(false);
	}, [setIsOpen]);

	const onEditorChange = useCallback(value => {
		setEditorValue(value);
	}, [setEditorValue]);

	const onSave = useCallback(() => {
		if(onChange) {
			onChange({
				target: {
					value: serialize(editorValue),
				},
			});
		}
		close();
	}, [onChange, close, editorValue]);

	useLayoutEffect(() => {
		const updateAdminbarHeight = () => {
			setAdminbarHeight(getAdminbarHeight());
		};
		window.addEventListener('resize', updateAdminbarHeight);
		return () => {
			window.removeEventListener('resize', updateAdminbarHeight);
		};
	}, [setAdminbarHeight]);

	return (
		<>
			<Button
				content={__('Edit', 'booking-weir')}
				icon='edit'
				labelPosition='right'
				onClick={toggle}
				active={isOpen}
				{...buttonProps}
			/>
			<Modal
				mountNode={document.getElementById('bw-no-sui')}
				open={isOpen}
				size={isWide ? 'large' : 'fullscreen'}
				style={{marginTop: adminbarHeight}}
			>
				<Modal.Header style={{margin: 0}}>{__('Edit', 'booking-weir')}{': '}{label}</Modal.Header>
				<Modal.Content style={{padding: 0}}>
					<Gutenberg
						initialValue={value}
						visual={visual}
						onChange={onEditorChange}
					/>
				</Modal.Content>
				<Modal.Actions className='sui-root'>
					<HelpModal />
					<Button
						floated='left'
						content={visual ? __('Switch to text mode', 'booking-weir') : __('Switch to visual mode', 'booking-weir')}
						onClick={() => setVisual(!visual)}
					/>
					<Button
						inverted
						color='red'
						content={__('Cancel', 'booking-weir')}
						onClick={close}
					/>
					<Button
						positive
						icon='check'
						content={__('Update', 'booking-weir')}
						labelPosition='right'
						disabled={!visual}
						onClick={onSave}
					/>
				</Modal.Actions>
			</Modal>
		</>
	);
};
