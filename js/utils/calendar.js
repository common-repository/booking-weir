export const getUrl = calendar => {
	const {
		settings: {
			url: calendarUrl,
		},
	} = calendar;

	const homeUrl = booking_weir_data.home_url;

	if(!calendarUrl.length) {
		return `${homeUrl}/`;
	}

	if(calendarUrl.includes('http') || calendarUrl.includes('//')) {
		return calendarUrl;
	}

	if(calendarUrl[0] === '/') {
		return `${homeUrl}${calendarUrl}`
	}

	return `${homeUrl}/${calendarUrl}`
};
