<?php

namespace wsd\bw\util\datetime;

use DateTimeZone;

if(!function_exists(__NAMESPACE__ . '\\timerange_in_timerange')):
	/**
	 * @param array $timerange ['H:i', 'H:i'] The timerange to check.
	 * @param array $window ['H:i', 'H:i'] The timerange to check against.
	 * @return boolean
	 */
	function timerange_in_timerange($timerange, $window) {
		return (
			// Window starts before or during start time
			(strtotime($window[0]) <= strtotime($timerange[0]))
			&&
			// Window ends after or during end time
			(strtotime($window[1]) >= strtotime($timerange[1]))
		);
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\daterange_in_daterange')):
	/**
	 * @param array $daterange ['Y-m-d', 'Y-m-d'] The daterange to check.
	 * @param array $window ['Y-m-d', 'Y-m-d'] The daterange to check against.
	 * @return boolean
	 */
	function daterange_in_daterange($daterange, $window) {
		return (
			// Window starts before or during start time
			(strtotime($window[0]) <= strtotime($daterange[0]))
			&&
			// Window ends after or during end time
			(strtotime($window[1]) >= strtotime($daterange[1]))
		);
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\today_in_daterange')):
	/**
	 * @param array $daterange ['Y-m-d', 'Y-m-d'] The daterange to check.
	 * @return boolean
	 */
	function today_in_daterange($daterange) {
		return (
			// Window starts before or during start time
			(strtotime($daterange[0]) <= time())
			&&
			// Window ends after or during end time
			(strtotime($daterange[1]) >= strtotime(date('Y-m-d', time())))
		);
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\utcstrtotime')):
	/**
	 * Converts string to time in UTC timezone.
	 *
	 * WordPress default timezone should be UTC,
	 * if it isn't, override this function: store
	 * current timezone, set default timezone to
	 * UTC, run `strtotime` and restore timezone
	 * before returning the value.
	 *
	 * @param string $str String.
	 * @return int
	 */
	function utcstrtotime($str) {
		$time = strtotime($str);
		return $time;
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\wpdatetime')):
	/**
	 * Display date and time in localized WP formats.
	 *
	 * @param int|string $time Timestamp or a date string.
	 * @return string
	 */
	function wpdatetime($time) {
		$time = is_int($time) ? $time : strtotime($time);
		return date_i18n(sprintf(
			'%s %s',
			get_option('date_format'),
			get_option('time_format')
		), $time);
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\get_supported_locales')):
	/**
	 * Get locales supported by date-fns.
	 *
	 * @see https://github.com/date-fns/date-fns/tree/master/src/locale
	 * @return array
	 */
	function get_supported_locales() {
		return [
			'af',
			'ar-DZ',
			'ar-MA',
			'ar-SA',
			'az',
			'be',
			'bg',
			'bn',
			'ca',
			'cs',
			'cy',
			'da',
			'de',
			'el',
			'en-AU',
			'en-CA',
			'en-GB',
			'en-US',
			'eo',
			'es',
			'et',
			'fa-IR',
			'fi',
			'fr',
			'fr-CA',
			'gl',
			'gu',
			'he',
			'hi',
			'hr',
			'hu',
			'hy',
			'id',
			'is',
			'it',
			'ja',
			'ka',
			'kk',
			'ko',
			'lt',
			'lv',
			'ms',
			'nb',
			'nl',
			'nn',
			'pl',
			'pt',
			'pt-BR',
			'ro',
			'ru',
			'sk',
			'sl',
			'sr',
			'sr-Latn',
			'sv',
			'ta',
			'te',
			'th',
			'tr',
			'ug',
			'uk',
			'vi',
			'zh-CN',
			'zh-TW',
		];
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\get_default_locale')):
	/**
	 * Get default value for calendar locale.
	 *
	 * @return string
	 */
	function get_default_locale() {
		$locale = str_replace('_', '-', get_locale());
		if(in_array($locale, get_supported_locales())) {
			return $locale;
		}
		return 'en-GB';
	}
endif;

if(!function_exists(__NAMESPACE__ . '\\get_default_timezone')):
	/**
	 * Get default value for calendar timezone.
	 * WordPress `timezone_string` option is used if it's not set to a manual offset.
	 *
	 * @return string
	 */
	function get_default_timezone() {
		if(function_exists('wp_timezone')) {
			$timezone = wp_timezone();
			if($timezone instanceof DateTimeZone) {
				$name = $timezone->getName();
				if(strpos($name, '/') !== false) {
					return $name;
				}
			}
		}
		return 'Europe/London';
	}
endif;
