<?php

namespace wsd\bw\core\rest;

use wsd\bw\Context;

/**
 * REST API class
 *
 * Handles initializing all the endpoints from `endpoints` directory.
 * Endpoints should implement the `Endpoint` class.
 */
class API {

	/** @var Context */
	protected $context;

	/**
	 * API version.
	 *
	 * @var string
	 */
	public static $version = '1';

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	public $namespace;

	/**
	 * Endpoint for refreshing REST API nonce.
	 */
	const NONCE_ENDPOINT = 'bw-rest-nonce';

	public function __construct(Context $context) {
		$this->context = $context;
		$this->namespace = $this->context->plugin_slug() . '/v' . self::$version;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		$this->load_endpoints();
		add_action('wp_ajax_nopriv_' . self::NONCE_ENDPOINT, [$this, 'rest_nonce']);
		add_action('wp_ajax_' . self::NONCE_ENDPOINT, [$this, 'rest_nonce']);
	}

	/**
	 * Load API endpoints.
	 */
	public function load_endpoints() {
		foreach($this->context->files('includes/core/rest/endpoints') as $endpoint) {
			require_once $endpoint;
			$name = str_replace('.php', '', basename($endpoint));
			if($name === 'Events') {
				/**
				 * Events is loaded with `register_post_type`.
				 */
				continue;
			}
			$class = __NAMESPACE__ . '\\endpoints\\' . $name;
			new $class($this->context, $this, strtolower($name));
		}
	}

	/**
	 * Get `wp_rest` nonce.
	 */
	public function rest_nonce() {
		exit(sanitize_key(wp_create_nonce('wp_rest')));
	}
}
