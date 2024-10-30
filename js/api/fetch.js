import fetch from 'wp-api-fetch'; // @wordpress/api-fetch alias

const {
	api_url: apiRoot,
	api_nonce: nonce,
	nonce_endpoint: nonceEndpoint,
} = window.booking_weir_data || {};

fetch.nonceMiddleware = fetch.createNonceMiddleware(nonce);
if(nonceEndpoint) {
	fetch.nonceEndpoint = nonceEndpoint;
}
fetch.rootURLMiddleware = fetch.createRootURLMiddleware(apiRoot);

fetch.use(fetch.nonceMiddleware);
fetch.use(fetch.rootURLMiddleware);

export default fetch;
