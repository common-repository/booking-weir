import {FILE_INPUT_CONTAINER_ID} from './FileInput';

/**
 * Send a POST request to the current URL with the booking data.
 */
let postBooking;
export default postBooking = booking => {
	/**
	 * Strip data that's not needed to be sent.
	 */
	const {
		info,
		title,
		bookableEvent,
		slot,
		service,
		...provisionalBookingData
	} = booking;

	const bookingData = {
		...provisionalBookingData,
		...(bookableEvent && {
			bookableEventId: bookableEvent.id,
		}),
		...(slot && {
			slotId: slot.id,
		}),
		...(service && {
			serviceId: service.id,
		}),
	};

	/**
	 * Compose a form and submit it.
	 */
	const form = document.createElement('form');
	form.setAttribute('action', `${document.location.href}`);
	form.setAttribute('method', 'post');
	const files = document.getElementById(FILE_INPUT_CONTAINER_ID);
	if(files && files.childElementCount > 0) {
		form.setAttribute('enctype', 'multipart/form-data');
		document.querySelectorAll(`#${FILE_INPUT_CONTAINER_ID} input[type="file"]`).forEach(fileInput => {
			fileInput.setAttribute('name', `bw_booking[${fileInput.name}]`);
			form.appendChild(fileInput);
		});
	}
	Object.keys(bookingData).forEach(key => {
		let value = bookingData[key];
		if(typeof value === 'object') {
			if(key === 'fields' && value) {
				/**
				 * Filter out field values that are (`File`) objects.
				 */
				value = Object.fromEntries(Object.entries(value).filter(([k, v]) => typeof v !== 'object'));
			}
			value = (value && value.toJSON) ? value.toJSON() : JSON.stringify(value);
		}
		const input = document.createElement('input');
		input.setAttribute('type', 'hidden');
		input.setAttribute('name', `bw_booking[${encodeURIComponent(key)}]`);
		input.setAttribute('value', encodeURIComponent(value));
		form.appendChild(input);
	});
	document.body.appendChild(form);
	form.submit();
};
