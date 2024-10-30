import {render} from 'react-dom';

import Provider from './provider';
import BWAdmin from 'components/backend';

document.addEventListener('DOMContentLoaded', function() {
	document.getElementById('bw-sui-root') && render(
		<Provider withRouter>
			<BWAdmin />
		</Provider>,
		document.getElementById('bw-sui-root')
	);
});
