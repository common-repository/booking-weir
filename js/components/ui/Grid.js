import {
	Grid as FUIGrid,
} from '@fluentui/react-northstar';

let Grid;
export default Grid = ({
	fluid = false,
	stackable = false,
	variables = {},
	styles = {},
	...props
}) => {
	return (
		<FUIGrid
			variables={{
				...variables,
				gridGap: '1em',
			}}
			styles={{
				...styles,
				...(fluid && {
					width: '100%',
				}),
				...(stackable && {
					mobile: {
						gridTemplateColumns: 'repeat(1, 1fr)',
					},
				}),
			}}
			{...props}
		/>
	);
};
