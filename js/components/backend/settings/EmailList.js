import {__} from '@wordpress/i18n';

import {
	useState,
	useRef,
} from 'react';

import {
	Form,
	Button,
	Input,
	Divider,
} from 'semantic-ui-react';

import {
	ARRAY_UNIQUE,
} from 'utils/array';

let EmailList;
export default EmailList = ({value, onChange, ...props}) => {
	const [add, setAdd] = useState('');
	const formRef = useRef();

	const emails = value
					.split(',')
					.map(v => v.trim())
					.filter(v => v.length > 0)
					.filter(ARRAY_UNIQUE);

	return <>
		{emails.length > 0 && <>
			<Form as='div'>
				{emails.map(email => (
					<Form.Field key={email}>
						{email}{' '}
						<Button
							basic
							aria-label={`${__('Remove', 'booking-weir')} ${email}`}
							icon='close'
							onClick={() => onChange([...emails.filter(v => v !== email)].join(','))}
							style={{boxShadow: 'none', padding: '0.25em'}}
						/>
					</Form.Field>
				))}
			</Form>
			<Divider hidden />
		</>}
		<Form ref={formRef} onSubmit={e => onChange([...emails, add].join(',')) && setAdd('')}>
			<Input
				type='email'
				value={add}
				onChange={e => setAdd(e.target.value)}
				placeholder={__('E-mail...', 'booking-weir')}
				action={{
					type: 'submit',
					primary: true,
					icon: {
						name: 'plus',
					},
					content: __('Add', 'booking-weir'),
				}}
				{...props}
			/>
		</Form>
	</>;
};
