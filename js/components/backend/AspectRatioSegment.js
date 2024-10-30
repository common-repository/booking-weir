import {
	Segment,
} from 'semantic-ui-react';

let AspectRatioSegment;
export default AspectRatioSegment = ({
	flex = false,
	children,
	...props
}) => {
	return (
		<Segment {...props}>
			<div className='bw-ar-container' style={{
				width: '100%',
				paddingTop: '75%',
				position: 'relative',
			}}>
				<div className='bw-ar-content' style={{
					width: '100%',
					position: 'absolute',
					top: '50%',
					transform: 'translateY(-50%)',
					...(flex && {
						display: 'flex',
						flexDirection: 'column',
						justifyContent: 'space-between',
						height: '100%',
					}),
				}}>
					{children}
				</div>
			</div>
		</Segment>
	);
};
