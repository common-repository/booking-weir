<?php

namespace wsd\bw\core;

use wsd\bw\Context;
use wsd\bw\core\events\Event;
use wsd\bw\core\calendars\Calendar;
use Mpdf\Mpdf;

/**
 * Pdf class.
 */
final class Pdf {

	/** @var Context */
	protected $context;

	/**
	 * Filesystem paths are set up.
	 *
	 * @var boolean
	 */
	protected $loaded = false;

	/**
	 * `Mpdf` settings.
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_action('before_delete_post', [$this, 'delete_invoice']);
	}

	/**
	 * Prepare filesystem for PDF generation.
	 *
	 * @return bool|string
	 */
	public function load() {
		if(!function_exists('WP_Filesystem')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		global $wp_filesystem;
		if(!isset($wp_filesystem)) {
			return __('Failed to initialize WP Filesystem.', 'booking-weir');
		}

		if(!$dir = $this->context->upload_dir()) {
			return __('Unable to retrieve upload dir.', 'booking-weir');
		}
		if(!$url = $this->context->upload_url()) {
			return __('Unable to retrieve upload URL.', 'booking-weir');
		}

		$temp_dir = $dir . '/temp';
		if(!file_exists($temp_dir)) {
			if(!wp_mkdir_p($temp_dir)) {
				return __('Unable to create PDF output dir.', 'booking-weir');
			}
		} elseif(!is_writable($temp_dir)) {
			return __('PDF output dir not writable.', 'booking-weir');
		}

		$this->settings['tempDir'] = $temp_dir; // Camelcase, consumed by Mpdf.
		$this->settings['output_dir'] = $dir;
		$this->settings['output_url'] = $url;
		$this->loaded = true;
		return $this->loaded;
	}

	public function lazyload() {
		if(!$this->loaded) {
			$this->load();
		}
	}

	/**
	 * Test PDF generation.
	 *
	 * @param Calendar $calendar
	 * @return array|string
	 */
	public function test(Calendar $calendar) {
		$loaded = $this->load();
		if($loaded !== true) {
			return $loaded;
		}

		$event = $this->context->get('event-factory')->mock($calendar);
		$args = wp_parse_args($this->settings, [
			'mode' => 'c',
			'margin_left' => 20,
			'margin_right' => 15,
			'margin_top' => 48,
			'margin_bottom' => 25,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);
		$mpdf = new Mpdf(apply_filters('bw_mpdf_args', $args, $event));
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->SetTitle($calendar->get_setting('invoicePdfTitle'));
		$mpdf->SetAuthor($calendar->get_setting('invoicePdfAuthor'));
		$mpdf->SetWatermarkText('Test');
		$mpdf->showWatermarkText = true;
		$mpdf->watermarkTextAlpha = 0.1;
		$output_path = $this->settings['output_dir'] . '/test.pdf';
		$mpdf->WriteHTML($this->get_html($event));
		$mpdf->Output($output_path, 'F');

		if(file_exists($output_path)) {
			return [
				'path' => $output_path,
				'url' => esc_url($this->settings['output_url'] . '/test.pdf'),
			];
		}

		return __("Generated file doesn't exist.", 'booking-weir');
	}

	/**
	 * Generate PDF invoice.
	 *
	 * @param Event $event
	 * @return array|string
	 */
	public function generate_invoice(Event $event) {
		if(!$event->exists()) {
			return __('Event not found.', 'booking-weir');
		}
		if(!$calendar = $event->get_calendar()) {
			return __('Calendar not found.', 'booking-weir');
		}
		if(!$calendar->get_setting('invoicePdfEnabled')) {
			return __('PDF generation is not enabled.', 'booking-weir');
		}
		if($calendar->is_product()) {
			return __('Billing is handled by WooCommerce.', 'booking-weir');
		}
		if($event->get_status() === 'archived') {
			return __('Event is archived.', 'booking-weir');
		}

		$this->lazyload();

		if(!isset($this->settings['output_dir']) || !isset($this->settings['tempDir'])) {
			return __('Invalid settings.', 'booking-weir');
		}

		/**
		 * Initialize `Mpdf`.
		 */
		$args = wp_parse_args($this->settings, [
			'mode' => 'c',
			'margin_left' => 20,
			'margin_right' => 15,
			'margin_top' => 48,
			'margin_bottom' => 25,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);
		$mpdf = new Mpdf(apply_filters('bw_mpdf_args', $args, $event));

		/**
		 * Configure `Mpdf`.
		 */
		$mpdf->SetTitle($calendar->get_setting('invoicePdfTitle'));
		$mpdf->SetAuthor($calendar->get_setting('invoicePdfAuthor'));
		$mpdf->SetDisplayMode('fullpage');

		/**
		 * Allows to modify the `Mpdf` instance.
		 *
		 * @see https://mpdf.github.io
		 * @example add_filter('bw_mpdf_invoice', function($mpdf, $event) {
		 * 				$mpdf->SetWatermarkText((string)$event->get_id());
		 * 				$mpdf->showWatermarkText = true;
		 * 				$mpdf->watermarkTextAlpha = 0.1;
		 * 				return $mpdf;
		 * 			}, 10, 2);
		 */
		$mpdf = apply_filters('bw_mpdf_invoice', $mpdf, $event);

		/**
		 * Generate.
		 */
		$output_path = $this->get_invoice_path($event);
		$mpdf->WriteHTML($this->get_html($event));
		$mpdf->Output($output_path, 'F');

		if(file_exists($output_path)) {
			return [
				'path' => $output_path,
				'url' => $this->get_invoice_url($event),
			];
		}
		return __("Generated file doesn't exist.", 'booking-weir');
	}

	/**
	 * Delete generated PDF invoice.
	 * Called when an event is deleted permanently or from private REST API.
	 *
	 * @param int $id ID of the event who's invoice should be deleted.
	 * @param bool $force When not enabled the PDF will only be deleted if the option to delete them automatically is enabled.
	 *
	 * @return bool|string
	 */
	public function delete_invoice($id, $force = false) {
		if(get_post_type($id) !== $this->context->get('event-post-type')::SLUG) {
			return sprintf(
				__('Expected post type "%1$s", got "%2$s".', 'booking-weir'),
				esc_html($this->context->get('event-post-type')::SLUG),
				esc_html(get_post_type($id))
			);
		}
		$event = $this->context->get('event-factory')->create($id);
		if(!$event->exists()) {
			return __('Event not found.', 'booking-weir');
		}
		if($event->get_type() !== 'booking') {
			return __('Event is not a booking.', 'booking-weir');
		}
		if(!$calendar = $event->get_calendar()) {
			return __('Calendar not found.', 'booking-weir');
		}
		if(!$calendar->get_setting('invoicePdfDelete') && !$force) {
			return __('PDF deletion is not enabled.', 'booking-weir');
		}
		if(!$path = $event->get_invoice_path()) {
			return __('Unable to retrieve invoice path.', 'booking-weir');
		}
		if(!file_exists($path)) {
			return true;
		}
		if(validate_file($path) !== 0) {
			return __('File validation failed.', 'booking-weir');
		}
		if(substr($path, -4) !== '.pdf') {
			return __('File is not a PDF.', 'booking-weir');
		}
		/**
		 * Delete.
		 */
		unlink($path);
		if(!file_exists($path)) {
			return true;
		}
		return __('Failed deleting invoice.', 'booking-weir');
	}

	/**
	 * @param Event $event
	 * @return string
	 */
	public function get_invoice_file_name($event) {
		return 'invoice-' . $event->get_billing_key() . '.pdf';
	}

	/**
	 * @param Event $event
	 * @return string
	 */
	public function get_invoice_path($event) {
		$this->lazyload();
		$path = implode(DIRECTORY_SEPARATOR, [
			$this->settings['output_dir'],
			$this->get_invoice_file_name($event),
		]);
		return validate_file($path) === 0 ? $path : '';
	}

	/**
	 * @param Event $event
	 * @return string
	 */
	public function get_invoice_url($event) {
		$this->lazyload();
		return esc_url(implode('/', [
			$this->settings['output_url'],
			$this->get_invoice_file_name($event),
		]));
	}

	/**
	 * @param Event $event
	 * @return string
	 */
	public function get_html(Event $event) {
		ob_start();
		include $this->context->file('templates', 'invoice.php');
		return strtr(ob_get_clean(), $event->get_template_strings());
	}

	/**
	 * Convert Gutenberg fixed layout table to use fixed columns in a PDF.
	 *
	 * @param string $table
	 * @return string
	 */
	public function fixed_table($table) {
		if(strpos($table, 'has-fixed-layout') === false) {
			return $table;
		}
		$columns = count(explode('<td', $table)) - 1;
		if($columns < 2) {
			return $table;
		}
		$width = number_format(100 / $columns, 1);
		return str_replace('<td', '<td width="' . $width . '%"', $table);
	}
}
