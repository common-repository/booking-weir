import {
	useState,
	useEffect,
} from 'react';

import Alert from 'components/ui/Alert';

import {
	getRawHTMLElement,
} from 'utils/html';

import {
	AcceptIcon,
	InfoIcon,
	ExclamationTriangleIcon,
	ExclamationCircleIcon,
} from '@fluentui/react-icons-northstar';

export const getIcon = name => {
	switch(name) {
		case 'accept':
			return <AcceptIcon />;
		case 'info':
			return <InfoIcon />;
		case 'exclamation-triangle':
			return <ExclamationTriangleIcon />;
		case 'warning circle':
		case 'exclamation-circle':
			return <ExclamationCircleIcon />;
	}
	return null;
}

let Notices;
export default Notices = () => {
	const [notices, setNotices] = useState([]);

	useEffect(() => {
		setNotices(window.bw_notices && window.bw_notices.length ? [...window.bw_notices] : []);
	}, []);

	return notices.map(({header, text, icon, type}, index) => {
		const typeProp = type ? {[type]: true} : {};
		return (
			<Alert
				key={`bw-message-${index}`}
				{...typeProp}
				icon={getIcon(icon)}
				header={getRawHTMLElement(header)}
				content={getRawHTMLElement(text)}
			/>
		);
	});
};
