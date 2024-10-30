import {
	getBlockTypes,
	parse,
	serialize,
} from '@wordpress/blocks';

import {
	registerCoreBlocks,
} from '@wordpress/block-library';

import '@wordpress/format-library';

import {
	BlockEditorProvider,
	BlockEditorKeyboardShortcuts,
	BlockTools,
	WritingFlow,
	ObserveTyping,
	BlockList,
	BlockInspector,
} from '@wordpress/block-editor';

import {
	SlotFillProvider,
	Popover,
} from '@wordpress/components';

import {
	ShortcutProvider,
} from '@wordpress/keyboard-shortcuts';

import {
	useViewportMatch,
} from '@wordpress/compose';

import {
	useState,
	useEffect,
	useCallback,
} from 'react';

import Textarea from 'react-autosize-textarea';

import './editor-styles.less';

let Gutenberg;
export default Gutenberg = ({initialValue = '', visual = true, onChange = null}) => {
	const [blocks, updateBlocks] = useState([]);
	const [text, updateText] = useState('');
	const isSmall = useViewportMatch('medium', '<');

	useEffect(() => {
		if(getBlockTypes().findIndex(({name}) => name === 'core/paragraph') === -1) {
			registerCoreBlocks();
		}
		updateBlocks(parse(initialValue || ''));
	}, [initialValue, updateBlocks]);

	useEffect(() => {
		if(onChange) {
			onChange(blocks);
		}
		updateText(serialize(blocks));
	}, [blocks, onChange, updateText]);

	const textEditorChange = useCallback(e => {
		const value = e.target.value;
		updateText(value);
	}, [updateText]);

	const textEditorBlur = useCallback(() => {
		updateBlocks(parse(text));
	}, [text, updateBlocks]);

	return (
		<div id='editor' className='block-editor__container'>
			<ShortcutProvider>
				<SlotFillProvider>
					<BlockEditorProvider
						value={blocks}
						onInput={updateBlocks}
						onChange={updateBlocks}
						settings={{__experimentalBlockPatterns: []}}
					>
						<div className={`edit-post-layout is-mode-${visual ? 'visual' : 'text'} is-sidebar-opened interface-interface-skeleton`}>
							<div className='interface-interface-skeleton__body'>
								<div className='interface-interface-skeleton__content'>
									<div className='editor-styles-wrapper'>
										<BlockTools />
										<BlockEditorKeyboardShortcuts />
										{visual && (
											<WritingFlow>
												<ObserveTyping>
													<BlockList />
												</ObserveTyping>
											</WritingFlow>
										)}
										{!visual && (
											<div style={{margin: '1em'}}>
												<Textarea
													autoComplete='off'
													dir='auto'
													value={text}
													onChange={textEditorChange}
													onBlur={textEditorBlur}
													className='editor-post-text-editor'
												/>
											</div>
										)}
									</div>
								</div>
								{!isSmall && (
									<div className='interface-interface-skeleton__sidebar'>
										<div className='interface-complementary-area edit-post-sidebar'>
											<BlockInspector />
										</div>
									</div>
								)}
							</div>
						</div>
						<Popover.Slot />
					</BlockEditorProvider>
				</SlotFillProvider>
			</ShortcutProvider>
		</div>
	);
};
