import {__} from '@wordpress/i18n';

import {
	useState,
	useEffect,
} from 'react';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Modal,
} from 'semantic-ui-react';

import {
	fetchTransaction,
} from 'api';

let TransactionView;
export default TransactionView = () => {
	const dispatch = useDispatch();
	const view = useSelector(state => state.ui.transactionView);
	const [isOpen, setIsOpen] = useState(false);
	const [transactionId, setTransactionId] = useState('');
	const [transaction, setTransaction] = useState({loading: true});

	useEffect(() => {
		if(view.paymentMethod && view.transactionId) {
			setTransactionId(view.transactionId);
			setIsOpen(true);
			fetchTransaction(view.paymentMethod, view.transactionId)
				.then(response => setTransaction(response))
				.catch(e => {
					dispatch({
						type: 'SET_MESSAGE',
						value: {
							negative: true,
							icon: 'warning circle',
							header: __('Failed fetching transaction data', 'booking-weir'),
							content: e.message,
						},
					});
					setIsOpen(false);
				});
			dispatch({type: 'SET_TRANSACTION_VIEW', value: {}});
		}
	}, [view, dispatch]);

	return (
		<Modal
			mountNode={document.getElementById('bw-no-sui')}
			open={isOpen}
			size='small'
			closeIcon={true}
			onClose={() => setIsOpen(false)}
		>
			<Modal.Header style={{margin: 0}}>
				{__('Transaction', 'booking-weir')}
				{' '}
				<code>{transactionId}</code>
			</Modal.Header>
			<Modal.Content scrolling>
				<pre style={{margin: 0, overflowX: 'auto', whiteSpace: 'pre-wrap'}}>
					{JSON.stringify(transaction, null, 2)}
				</pre>
			</Modal.Content>
		</Modal>
	);
};
