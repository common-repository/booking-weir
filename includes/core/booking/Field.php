<?php

namespace wsd\bw\core\booking;

use wsd\bw\Context;
use wsd\bw\core\Sanitizer;

use function wsd\bw\util\helpers\antiscript_file_name;
use function wsd\bw\util\helpers\canonicalize;
use function wsd\bw\util\helpers\get_unique_id;

/**
 * Booking booker info field.
 */
class Field {

	/** @var Context */
	protected $context;

	/**
	 * Field from calendar settings.
	 *
	 * @var array
	 */
	protected $field;

	/** @var Sanitizer */
	protected $sanitizer;

	/**
	 * Field error.
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * Assign default values to the provided field.
	 *
	 * @param Context $context
	 * @param array $field
	 */
	public function __construct(Context $context, $field) {
		$this->field = wp_parse_args($field, [
			'id' => '',
			'type' => 'text',
			'enabled' => true,
			'required' => false,
			'label' => '',
			'placeholder' => '',
			'defaultValue' => '',
			'defaultOption' => '',
			'defaultChecked' => false,
			'min' => '',
			'max' => '',
			'step' => 1,
			'options' => '',
			'link' => '',
			'columns' => 2,
			'fields' => [],
			'accept' => '',
			'maxFileSize' => 0,
		]);
		$this->context = $context;
		$this->sanitizer = $this->context->get('sanitizer');
	}

	/**
	 * Field ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->field['id'];
	}

	/**
	 * Field type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->field['type'];
	}

	/**
	 * Field label.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->field['label'] ?: $this->field['id'];
	}

	/**
	 * Is field enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return (bool)$this->field['enabled'];
	}

	/**
	 * Set field enabled state.
	 *
	 * @param bool $value
	 */
	public function set_enabled($value) {
		$this->field['enabled'] = (bool)$value;
	}

	/**
	 * Is field required.
	 *
	 * @return boolean
	 */
	public function is_required() {
		return (bool)$this->field['required'];
	}

	/**
	 * Get field options.
	 *
	 * @return array
	 */
	public function get_options() {
		return explode(',', $this->field['options']);
	}

	/**
	 * Get minimum value.
	 *
	 * @return int|false
	 */
	public function get_min() {
		return is_numeric($this->field['min']) ? (int)$this->field['min'] : false;
	}

	/**
	 * Get maximum value.
	 *
	 * @return int|false
	 */
	public function get_max() {
		return is_numeric($this->field['max']) ? (int)$this->field['max'] : false;
	}

	/**
	 * Get maximum file size allowed in file input field.
	 *
	 * @return int MB
	 */
	public function get_max_file_size() {
		return is_numeric($this->field['maxFileSize']) ? (int)$this->field['maxFileSize'] : 0;
	}

	/**
	 * Get file input field accepted file mime types/file extensions.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/accept
	 * @return string
	 */
	public function get_accept() {
		return $this->field['accept'];
	}

	/**
	 * Get field error.
	 *
	 * @return string
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Set field error if there already isn't one.
	 *
	 * @param string $error
	 */
	protected function set_error($error) {
		if(!$this->get_error()) {
			$this->error = $error;
		}
	}

	/**
	 * Check if given value is valid for this field.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate($value) {
		if(!$this->check_required($value)) {
			$this->set_error(sprintf(
				__('Field "%s" is required.', 'booking-weir'),
				esc_html($this->get_label())
			));
			return false;
		}
		if(!$this->check_value($value)) {
			$this->set_error(sprintf(
				__('Invalid value "%1$s" for field "%2$s".', 'booking-weir'),
				esc_html($value),
				esc_html($this->get_label())
			));
			return false;
		}
		return true;
	}

	/**
	 * Check that required value is appropriate.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	protected function check_required($value) {
		if(!$this->is_required()) {
			return true;
		}
		switch($this->get_type()) {
			case 'number':
				if($value && !is_numeric($value)) {
					$this->set_error(sprintf(__('"%s" must be a numeric value.', 'booking-weir'), esc_html($this->get_label())));
					return false;
				}
				return is_numeric($value);
			case 'email':
				return !empty($this->sanitizer->sanitize_email($value));
			case 'checkbox':
				return $this->sanitizer->sanitize_boolean($value) === true;
			case 'terms':
				if($this->sanitizer->sanitize_boolean($value) !== true) {
					$this->set_error(__('You must agree to terms and conditions.', 'booking-weir'));
					return false;
				}
				return true;
			case 'file':
				return isset($_FILES['bw_booking']['tmp_name'][$this->get_id()]) && !empty($_FILES['bw_booking']['tmp_name'][$this->get_id()]);
			default:
				return !!$value;
		}
	}

	/**
	 * Check if field value is appropriate.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	protected function check_value($value) {
		switch($this->get_type()) {
			case 'number':
				$value = (int)$value;
				if($this->get_min() !== false && $value < $this->get_min()) {
					$this->set_error(sprintf(
						__('"%1$s" must equal or greater than %2$d.', 'booking-weir'),
						esc_html($this->get_label()),
						$this->get_min()
					));
					return false;
				}
				if($this->get_max() !== false && $value > $this->get_max()) {
					$this->set_error(sprintf(
						__('"%1$s" must equal or less than %2$d.', 'booking-weir'),
						esc_html($this->get_label()),
						$this->get_max()
					));
					return false;
				}
			break;
			case 'select':
			case 'radio':
				if($value && !in_array($value, $this->get_options())) {
					return false;
				}
			break;
			case 'email':
				if($value) {
					$is_valid = $this->sanitizer->validate_email($value);
					if(is_wp_error($is_valid)) {
						$this->set_error($is_valid->get_error_message());
						return false;
					}
				}
			break;
			case 'file':
				if(!isset($_FILES['bw_booking']['error'][$this->get_id()]) || $_FILES['bw_booking']['error'][$this->get_id()] === 4) {
					return true; // No file uploaded.
				}
				if($_FILES['bw_booking']['error'][$this->get_id()] !== 0) {
					$this->set_error(sprintf(
						__('File upload error (%1$s) for "%2$s".', 'booking-weir'),
						(int)$_FILES['bw_booking']['error'][$this->get_id()],
						esc_html($this->get_label())
					));
					return false;
				}
				return $this->validate_file_type() && $this->validate_file_size() && $this->queue_file_upload();
		}
		return true;
	}

	/**
	 * Format field value for display.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function format_value($value) {
		switch($this->get_type()) {
			case 'checkbox':
				$value = $value ? __('Yes', 'booking-weir') : __('No', 'booking-weir');
			break;
			case 'file':
				$value = wp_basename($value);
			break;
		}
		return (string)$value;
	}

	/**
	 * @return bool Is valid type.
	 */
	protected function validate_file_type() {
		$accept = strtolower(trim($this->get_accept()));
		if(empty($accept)) {
			return true;
		}

		if(
			!isset($_FILES['bw_booking']['type'][$this->get_id()])
			|| empty($_FILES['bw_booking']['type'][$this->get_id()])
			|| !$type = sanitize_mime_type(wp_unslash($_FILES['bw_booking']['type'][$this->get_id()]))
		) {
			$this->set_error(sprintf(
				__('File upload error (%1$s) for "%2$s".', 'booking-weir'),
				'file_type_missing',
				esc_html($this->get_label())
			));
			return false;
		}

		/**
		 * Custom validation hook.
		 */
		if(apply_filters('bw_validate_mime_type', false, $type, $this)) {
			return true;
		}

		$mimes = array_map('sanitize_mime_type', array_map('trim', explode(',', $accept)));
		$mimes = apply_filters('bw_accepted_mime_types', $mimes, $this);
		foreach($mimes as $mime) {
			if(strpos($mime, '/*') !== false) {
				if(strpos($type, str_replace('/*', '/', $mime)) === 0) {
					return true;
				}
			}
		}
		if(!in_array($type, $mimes)) {
			$this->set_error(sprintf(
				__('File upload error (%1$s) for "%2$s".', 'booking-weir'),
				sprintf(
					__('mime type "%s" is not allowed', 'booking-weir'),
					esc_html($type)
				),
				esc_html($this->get_label())
			));
			$this->context->get('logger')->log(['Rejected file upload with mime type' => esc_html($type)]);
			return false;
		}
		return true;
	}

	/**
	 * @return bool Is file size acceptable.
	 */
	protected function validate_file_size() {
		$max = $this->get_max_file_size();
		if($max <= 0) {
			return true;
		}
		if(!isset($_FILES['bw_booking']['size'][$this->get_id()])) {
			return false;
		}
		$size = (int)$_FILES['bw_booking']['size'][$this->get_id()];
		if($size > $max * MB_IN_BYTES) {
			$this->set_error(sprintf(
				__('Uploaded file for "%1$s" exceeds maximum allowed file size of %2$dMB.', 'booking-weir'),
				esc_html($this->get_label()),
				$this->get_max_file_size()
			));
			return false;
		}
		return true;
	}

	/**
	 * Upload attached file when booking is created.
	 *
	 * @return bool
	 */
	protected function queue_file_upload() {
		$id = $this->get_id();
		if(!isset($_FILES['bw_booking']['name'][$id])) {
			return false;
		}
		$name = sanitize_file_name(wp_unslash($_FILES['bw_booking']['name'][$id]));
		$name = canonicalize($name);
		$name = antiscript_file_name($name);
		$name = apply_filters('bw_uploaded_file_name', $name, $this);

		if(!isset($_FILES['bw_booking']['tmp_name'][$id])) {
			return false;
		}
		$tmp_name = wp_unslash($_FILES['bw_booking']['tmp_name'][$id]); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if(validate_file($tmp_name) !== 0) {
			return false;
		}

		if(!is_uploaded_file($tmp_name)) {
			$this->set_error(sprintf(
				__('File upload error (%1$s) for "%2$s".', 'booking-weir'),
				'file_not_uploaded',
				esc_html($this->get_label())
			));
			return false;
		}

		$dir = $this->context->upload_dir() . '/files';
		if(!file_exists($dir) && !wp_mkdir_p($dir)) {
			$this->set_error(sprintf(
				__('File upload error (%1$s) for "%2$s".', 'booking-weir'),
				'no_upload_dir',
				esc_html($this->get_label())
			));
			return false;
		}

		add_action('bw_before_create_booking', function(Booking $booking) use ($id, $name, $tmp_name) {
			$dir = $this->context->upload_dir() . '/files';
			$file = get_unique_id() . '-' . $name;
			$dest = $dir . '/' . $file;
			if(@move_uploaded_file($tmp_name, $dest)) {
				@chmod($dest, 0400);
				add_action('bw_after_create_booking', function(Booking $booking) use ($id, $file) {
					$booking->get_event()->set_field($id, $file);
				});
			} else {
				$booking->add_booking_errors([sprintf(
					__('File upload error (%1$s) for "%2$s".', 'booking-weir'),
					'move_uploaded_file_failed',
					esc_html($this->get_label())
				)]);
			}
		});

		return true;
	}
}
