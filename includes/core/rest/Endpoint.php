<?php

namespace wsd\bw\core\rest;

use wsd\bw\Context;
use WP_REST_Response;

/**
* Class for reducing chrome when registering endpoints.
*/
abstract class Endpoint {

	/** @var Context */
	protected $context;

	/** @var API */
	protected $API;

	/**
	 * Endpoint name.
	 *
	 * @var string
	 */
	protected $endpoint;

	public function __construct(Context $context, API $API, $endpoint) {
		$this->context = $context;
		$this->API = $API;
		$this->endpoint = $endpoint;
		add_action('rest_api_init', [$this, 'register_routes']);
	}

	abstract public function register_routes();

	protected function register_public_route($name = '', $args = []) {
		if(!isset($args['permission_callback'])) {
			/**
			 * Required since WP 5.5.
			 */
			$args['permission_callback'] = '__return_true';
		}
		register_rest_route(
			$this->API->namespace,
			'/' . $this->endpoint . '/' . $name,
			$args
		);
	}

	protected function register_private_route($name = '', $args = []) {
		register_rest_route(
			$this->API->namespace,
			'/' . $this->endpoint . '/' . $name,
			array_merge(
				$args,
				['permission_callback' => [$this, 'check_permissions']]
			)
		);
	}

	/**
	 * @param mixed $value
	 * @return WP_REST_Response
	 */
	protected function success($value = true) {
		return new WP_REST_Response([
			'success' => true,
			'value' => $value,
		], 200);
	}

	/**
	 * @param string $message
	 * @return WP_REST_Response
	 */
	protected function error($message = '') {
		if(empty($message)) {
			$message = esc_html__('Request failed.', 'booking-weir');
		}
		return new WP_REST_Response([
			'success' => false,
			'message' => $message,
		], 400);
	}

	public function check_permissions($request) {
		return $this->context->is_elevated();
	}
}
