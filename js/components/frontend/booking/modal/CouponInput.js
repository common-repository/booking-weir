import {__, _x} from '@wordpress/i18n';

import {
	useSelector,
	useDispatch,
} from 'react-redux';

import {
	Text,
} from '@fluentui/react-northstar';

import {
	TagIcon,
	AddIcon,
} from '@fluentui/react-icons-northstar';

import {
	ActionInput,
	Button,
	Form,
} from 'components/ui';

import {Formik} from 'formik';
import * as Yup from 'yup';

import {
	useBooking,
	useCurrentCalendar,
} from 'hooks';

import {
	updateBooking,
} from 'actions';

const COUPON_SCHEMA = Yup.object().shape({
	code: Yup.string().trim().required(),
});

const COUPON_DEFAULT_VALUES = {
	code: '',
};

let CouponInput;
export default CouponInput = () => {
	const {data} = useCurrentCalendar();
	const booking = useBooking();
	const isFetchingPrice = useSelector(state => state.ui.isFetchingPrice);
	const dispatch = useDispatch();

	/**
	 * Don't render coupon input when the calendar doesn't have any coupons.
	 */
	if(!data.has_coupons) {
		return null;
	}

	/**
	 * Info about submitted coupons.
	 */
	const info = booking?.info?.coupons;

	return (
		<Formik
			validationSchema={COUPON_SCHEMA}
			initialValues={COUPON_DEFAULT_VALUES}
			onSubmit={({code}, actions) => {
				dispatch(updateBooking({
					...booking,
					price: false,
					coupon: code,
				}));
				actions.setSubmitting(false);
			}}
		>
			{({
				values,
				errors,
				touched,
				handleChange,
				handleBlur,
				handleSubmit,
				isSubmitting,
			}) => (
				<form onSubmit={handleSubmit}>
					<ActionInput
						icon={<TagIcon />}
						iconPosition='start'
						placeholder={_x('Coupon...', 'coupon input placeholder', 'booking-weir')}
						name='code'
						value={values.code}
						onChange={handleChange}
						onBlur={handleBlur}
						disabled={isSubmitting || isFetchingPrice}
						action={(
							<Button
								primary
								icon={<AddIcon />}
								iconOnly
								onClick={handleSubmit}
								disabled={isSubmitting || isFetchingPrice}
								aria-label={__('Apply coupon', 'booking-weir')}
							/>
						)}
					/>
					{info && (
						<Form.Field
							control={info.map((text, index) => (
								<Text
									key={`coupon-info-${index}`}
									as='p'
									color='grey'
									size='small'
									content={text}
									style={{marginBottom: 0}}
								/>
							))}
						/>
					)}
				</form>
			)}
		</Formik>
	);
};
