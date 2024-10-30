import {__, _x} from '@wordpress/i18n';

import {
	useRef,
	useLayoutEffect,
} from 'react';

import {
	useDispatch,
} from 'react-redux';

const FILE_INPUT_CONTAINER_ID = 'bw-file-input-container';

const FileInputContainer = () => {
	return <div id={FILE_INPUT_CONTAINER_ID} style={{display: 'none'}}></div>;
};

const FileInput = ({name: id, setFieldValue, ...props}) => {
	const inputRef = useRef(null);
	const dispatch = useDispatch();

	useLayoutEffect(() => {
		const input = inputRef.current;
		const previous = document.querySelector(`#${FILE_INPUT_CONTAINER_ID} [name="${id}"]`);
		const resetField = () => dispatch({type: 'RESET_BOOKING_FIELD', id});
		if(previous) {
			previous.remove();
			resetField();
			setFieldValue(id, null);
		}
		return () => {
			document.getElementById(FILE_INPUT_CONTAINER_ID).appendChild(input);
			resetField();
		};
	}, [id, setFieldValue, dispatch]);

	return <input type='file' name={id} ref={inputRef} {...props} />;
};

export {
	FILE_INPUT_CONTAINER_ID,
	FileInputContainer,
	FileInput,
};
