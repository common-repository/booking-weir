<?php

namespace wsd\bw\config;

/**
 * Interface ConfigInterface.
 *
 * Config data abstraction that can be used to inject arbitrary Config values
 * into other classes.
 *
 * @see https://github.com/schlessera/better-settings-v1
 */
interface ConfigInterface {

	/**
	 * Check whether the Config has a specific key.
	 *
	 * @param string $key The key to check the existence for.
	 *
	 * @return bool Whether the specified key exists.
	 */
	public function has_key($key);

	/**
	 * Get the value of a specific key.
	 *
	 * @param string $key The key to get the value for.
	 *
	 * @return mixed Value of the requested key.
	 */
	public function get_key($key);

	/**
	 * Get the config array.
	 *
	 * @return array The config array.
	 */
	public function get();

	/**
	 * Get an array with all the keys.
	 *
	 * @return array Array of config keys.
	 */
	public function get_keys();
}
