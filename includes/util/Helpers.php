<?php

namespace wsd\bw\util\helpers;

if(!function_exists(__NAMESPACE__ . '\\get_unique_id')):
	/**
	 * PHP equivalent of JS `(Date.now().toString(36) + Math.random().toString(36).substr(2, 5)).toUpperCase()`
	 *
	 * @return string
	 */
	function get_unique_id() {
		return strtoupper(
			base_convert(substr(str_replace('.', '', (string)microtime(true)), 0, 13), 10, 36) .
			substr(base_convert((string)mt_rand(), 10, 36), 0, 5)
		);
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\admin_notice')):
	/**
	 * Add an admin notice.
	 *
	 * @param string $message Message to display.
	 * @param string $type success|info|warning|error
	 */
	function admin_notice($message = '', $type = 'info') {
		if(!is_admin()) {
			return;
		}

		if(empty($message)) {
			return;
		}

		add_action('admin_notices', function() use ($message, $type) {
			?>
			<div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
				<p><?php echo wp_kses_post($message); ?></p>
			</div>
			<?php
		});
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\get_current_url')):
	/**
	 * Get the URL of the current page.
	 *
	 * @param boolean $with_query_vars
	 * @return string
	 */
	function get_current_url($with_query_vars = false) {
		global $wp;
		return $with_query_vars
			? add_query_arg($wp->query_vars, home_url($wp->request))
			: home_url($wp->request);
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\validate_ip')):
	/**
	 * Ensures an IP address is both a valid IP address and does not fall within a private network range.
	 *
	 * @param string $ip
	 * @return bool
	 */
	function validate_ip($ip) {
		if(strtolower($ip) === 'unknown') {
			return false;
		}

		/**
		 * Generate IPv4 network address.
		 */
		if(!$ip = ip2long($ip)) {
			return false;
		}

		/**
		 * If the IP address is set and not equivalent to 255.255.255.255.
		 */
		if($ip !== false && $ip !== -1) {
			/**
			 * Make sure to get unsigned long representation of
			 * IP address due to discrepancies between 32 and 64 bit OSes
			 * and signed numbers (integers default to signed in PHP).
			 */
			$ip = sprintf('%u', $ip);

			/**
			 * Private network range checking.
			 */
			if($ip >= 0 && $ip <= 50331647) {
				return false;
			}
			if($ip >= 167772160 && $ip <= 184549375) {
				return false;
			}
			if($ip >= 2130706432 && $ip <= 2147483647) {
				return false;
			}
			if($ip >= 2851995648 && $ip <= 2852061183) {
				return false;
			}
			if($ip >= 2886729728 && $ip <= 2887778303) {
				return false;
			}
			if($ip >= 3221225984 && $ip <= 3221226239) {
				return false;
			}
			if($ip >= 3232235520 && $ip <= 3232301055) {
				return false;
			}
			if($ip >= 4294967040) {
				return false;
			}
		}
		return true;
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\get_ip_address')):
	function get_ip_address() {
		/**
		 * Check for shared Internet/ISP IP.
		 */
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
			if(validate_ip($ip)) {
				return $ip;
			}
		}

		/**
		 * Check for IP addresses passing through proxies.
		 */
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
			/**
			 * Check if multiple IP addresses exist in var.
			 */
			if(strpos($ip, ',') !== false) {
				$ip_list = explode(',', $ip);
				foreach($ip_list as $_ip) {
					if(validate_ip($_ip)) {
						return $_ip;
					}
				}
			} else {
				if(validate_ip($ip)) {
					return $ip;
				}
			}
		}
		if(!empty($_SERVER['HTTP_X_FORWARDED'])) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED']));
			if(validate_ip($ip)) {
				return $ip;
			}
		}
		if(!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']));
			if(validate_ip($ip)) {
				return $ip;
			}
		}
		if(!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_FORWARDED_FOR']));
			if(validate_ip($ip)) {
				return $ip;
			}
		}
		if(!empty($_SERVER['HTTP_FORWARDED'])) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_FORWARDED']));
			if(validate_ip($ip)) {
				return $ip;
			}
		}

		/**
		 * Return unreliable IP address since all else failed.
		 */
		return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\get_user_agent')):
	function get_user_agent() {
		return isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\array_find')):
	function array_find(iterable $array, callable $callback) {
		foreach($array as $item) {
			if(call_user_func($callback, $item) === true) {
				return $item;
			}
		}
		return null;
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\glob_maybe_brace')):
	/**
	 * When `GLOB_BRACE` is not supported do it manually.
	 *
	 * @param string $selector
	 * @return array
	 */
	function glob_maybe_brace($selector) {
		if(defined('GLOB_BRACE')) {
			return glob($selector, GLOB_BRACE);
		}

		if(strpos($selector, '{') !== false) {
			$before = explode('{', $selector);
			$after = explode('}', $before[1]);
			$globs = explode(',', $after[0]);
			$results = [];
			foreach($globs as $glob) {
				$results = array_merge($results, glob($before[0] . $glob . $after[1]));
			}
			return $results;
		} else {
			return glob($selector);
		}
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\localize_script')):
	/**
	 * Similar to `wp_localize_script` except instead of an array value it accepts string.
	 *
	 * @param string $handle
	 * @param string $name
	 * @param string $value
	 * @return bool
	 */
	function localize_script($handle, $name, $value) {
		return wp_add_inline_script(
			$handle,
			sprintf(
				'var %s = "%s";',
				esc_js($name),
				esc_js($value)
			),
			'before'
		);
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\flatten_fields')):
	/**
	 * Flatten fields.
	 *
	 * @param array $input
	 * @return array
	 */
	function flatten_fields($input) {
		$fields = [];
		foreach($input as $field) {
			if($field['type'] === 'grid') {
				foreach($field['fields'] as $grid_field) {
					/**
					 * Disable nested field if parent is disabled.
					 */
					if(isset($field['enabled']) && (bool)$field['enabled'] === false) {
						$grid_field['enabled'] = false;
					}
					$fields[] = $grid_field;
				}
			} else {
				$fields[] = $field;
			}
		}
		return $fields;
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\array_whitelist')):
	/**
	 * Returns only array entries listed in a whitelist
	 *
	 * @see https://andy-carter.com/blog/simple-php-function-to-whitelist-array-keys
	 * @param array $array Original array to operate on.
	 * @param array $whitelist Whitelisted keys.
	 * @return array
	 */
	function array_whitelist($array, $whitelist) {
		return array_intersect_key(
			$array,
			array_flip($whitelist)
		);
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\canonicalize')):
	/**
	 * Canonicalize string.
	 *
	 * @author https://wordpress.org/plugins/contact-form-7
	 * @param string $text
	 * @param string $strto lower|upper
	 * @return string
	 */
	function canonicalize($text, $strto = 'lower') {
		if(function_exists('mb_convert_kana') && get_option('blog_charset') === 'UTF-8') {
			$text = mb_convert_kana($text, 'asKV', 'UTF-8');
		}

		if($strto === 'lower') {
			$text = strtolower($text);
		} elseif($strto == 'upper') {
			$text = strtoupper($text);
		}

		$text = trim($text);
		return $text;
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\antiscript_file_name')):
	/**
	 * Antiscript file name.
	 *
	 * @author https://wordpress.org/plugins/contact-form-7
	 * @param string $filename
	 * @return string
	 */
	function antiscript_file_name($filename) {
		$filename = wp_basename($filename);
		$filename = preg_replace('/[\r\n\t -]+/', '-', $filename);
		$filename = preg_replace('/[\pC\pZ]+/iu', '', $filename);

		$parts = explode('.', $filename);

		if(count($parts) < 2) {
			return $filename;
		}

		$script_pattern = '/^(php|phtml|pl|py|rb|cgi|asp|aspx)\d?$/i';

		$filename = array_shift($parts);
		$extension = array_pop($parts);

		foreach((array)$parts as $part) {
			if(preg_match($script_pattern, $part)) {
				$filename .= '.' . $part . '_';
			} else {
				$filename .= '.' . $part;
			}
		}

		if(preg_match($script_pattern, $extension)) {
			$filename .= '.' . $extension . '_.txt';
		} else {
			$filename .= '.' . $extension;
		}

		return $filename;
	}
endif;
