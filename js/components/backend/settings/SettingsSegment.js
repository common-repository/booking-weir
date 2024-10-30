import {
	Segment,
	Header,
} from 'semantic-ui-react';

let SettingsSegment;
export default SettingsSegment = ({title = '', description = '', children}) => {
	return (
		<Segment padded className='settings'>
			{title && <Header content={title} subheader={description}/>}
			{children}
		</Segment>
	);
};
