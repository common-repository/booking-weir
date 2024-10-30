<?php

namespace wsd\bw\config;

use wsd\bw\Context;
use ArrayObject;
use Exception;
use RuntimeException;

/**
 * Class Config.
 *
 * This is a very basic Config class that can be used to abstract away the
 * loading of a PHP array from a file.
 *
 * @see https://github.com/schlessera/better-settings-v1
 */
class Config extends ArrayObject implements ConfigInterface {

	/** @var Context */
	protected $context;

	/**
	 * Instantiate the Config object.
	 *
	 * @param array|string $config Array with settings or path to Config file.
	 */
	public function __construct(Context $context, $config) {
		$this->context = $context;

		/**
		 * If a string was passed to the constructor, assume it is the path to a PHP Config file.
		 */
		if(is_string($config)) {
			$config = $this->load_uri($config) ?: [];
		}

		/**
		 * Make sure the config entries can be accessed as properties.
		 */
		parent::__construct($config, ArrayObject::ARRAY_AS_PROPS);
	}

	/**
	 * Check whether the Config has a specific key.
	 *
	 * @param string $key The key to check the existence for.
	 * @return bool Whether the specified key exists.
	 */
	public function has_key($key) {
		return array_key_exists($key, (array)$this);
	}

	/**
	 * Get the value of a specific key.
	 *
	 * @param string $key The key to get the value for.
	 * @return mixed Value of the requested key.
	 */
	public function get_key($key) {
		return $this[$key];
	}

	/**
	 * Get an array with all the keys.
	 *
	 * @return array Array of config keys.
	 */
	public function get_keys() {
		return array_keys((array)$this);
	}

	/**
	 * Get the config array.
	 *
	 * @return array The config array.
	 */
	public function get() {
		return (array)$this;
	}

	/**
	 * Load the contents of a resource identified by an URI.
	 *
	 * @param string $uri URI of the resource.
	 * @return array|null Raw data loaded from the resource. Null if no data found.
	 * @throws RuntimeException If the resource could not be loaded.
	 */
	protected function load_uri($uri) {
		try {
			/**
			 * Try to load the file through PHP's include().
			 * Make sure we don't accidentally create output.
			 */
			ob_start();
			$data = include $uri;
			ob_end_clean();
			return (array)$data;
		} catch(Exception $exception) {
			throw new RuntimeException(
				sprintf(
					'Could not include PHP config file "%1$s". Reason: "%2$s".',
					$uri,
					$exception->getMessage()
				),
				$exception->getCode(),
				$exception
			);
		}
	}
}
