<?php

namespace wsd\bw\core\booking;

use wsd\bw\Context;
use wsd\bw\core\calendars\Calendars;
use wsd\bw\core\calendars\Calendar;
use wsd\bw\core\events\Event;
use wsd\bw\core\Email;
use wsd\bw\core\Notices;
use wsd\bw\core\Sanitizer;
use WP_Query;

use function wsd\bw\util\helpers\array_whitelist;

/**
 * Booking class.
 */
final class Booking {

	/** @var Context */
	protected $context;

	/** @var Calendars */
	protected $calendars;

	/** @var Payment */
	protected $payment;

	/** @var Notices */
	protected $notices;

	/** @var Email */
	protected $email;

	/** @var Sanitizer */
	protected $sanitizer;

	/**
	 * POST variable name for submitting a booking.
	 */
	const SUBMIT = 'bw_booking';

	/**
	 * GET variable name for viewing a booking.
	 */
	const VIEW = 'bw';

	/**
	 * Submitted booking from `$_POST`.
	 *
	 * @var array
	 */
	public $booking;

	/**
	 * The event created as a result of the booking.
	 *
	 * @var Event
	 */
	public $event;

	/**
	 * The calendar containing the booking.
	 *
	 * @var Calendar
	 */
	public $calendar;

	/**
	 * Does the booking have errors and should be aborted.
	 *
	 * @var bool
	 */
	protected $has_errors = false;

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context, Calendars $calendars, Payment $payment, Notices $notices, Email $email, Sanitizer $sanitizer) {
		$this->context = $context;
		$this->calendars = $calendars;
		$this->payment = $payment;
		$this->notices = $notices;
		$this->email = $email;
		$this->sanitizer = $sanitizer;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		/**
		 * Booking creation may redirect to a subsequent page,
		 * `template_redirect` is the latest action that can be utilized.
		 */
		add_action('template_redirect', [$this, 'run']);

		/**
		 * Don't index when booking status is displayed.
		 */
		add_action('wp_head', [$this, 'noindex']);

		/**
		 * Add selected booking to calendar's public events.
		 */
		add_filter('bw_public_events', [$this, 'include_selected_booking']);
	}

	/**
	 * Perform booking or display it's info and handle payment.
	 * Shouldn't be called directly unless testing.
	 */
	public function run() {
		if(did_action('bw_booking')) {
			_doing_it_wrong(__CLASS__, 'Booking logic should not be executed multiple times.', '1.0.0');
		}
		do_action('bw_booking');

		if($this->is_booking_submitted()) {
			if(
				$this->parse_booking()
				&& $this->validate_booking()
				&& $this->sanitize_booking()
				&& $this->validate_fields()
				&& $this->validate_extras()
				&& $this->validate_price()
				&& $this->create_booking()
			) {
				$this->maybe_redirect();
			}
		} elseif($this->is_booking_selected()) {
			if($this->find_booking($this->get_selected_booking())) {
				if($this->event->get_status() === 'pending') {
					if($this->event->get_price() > 0) {
						$this->handle_payment();
					} else {
						$this->handle_confirmation();
					}
				}
				if($this->event->get_status() === 'awaiting') {
					$this->handle_confirmation();
				}
				$this->display_status();
			} else {
				$this->notices->add_error(
					esc_html__('Booking', 'booking-weir'),
					esc_html__('Booking not found.', 'booking-weir')
				);
			}
		}
	}

	/**
	 * Take a booking from `$_POST` for processing.
	 *
	 * @return bool
	 */
	protected function parse_booking() {
		$this->has_errors = false;
		$booking = [];
		foreach($this->get_submitted_booking() as $key => $value) {
			if(!is_scalar($value)) {
				continue;
			}
			$key = $this->sanitizer->sanitize_id($key);
			$value = urldecode($value);
			if(in_array($key, ['extras', 'breakdown', 'fields'])) {
				$value = json_decode($value, true);
			}
			if($key === 'price') {
				$value = (float)$value;
			}
			$booking[$key] = $value;
		}

		$booking = array_merge([
			'calendarId' => '',
			'start' => '',
			'end' => '',
			'serviceId' => '',
			'bookableEventId' => 0,
			'price' => (float)0,
			'breakdown' => [],
			'fields' => [],
			'extras' => [],
			'paymentMethod' => '',
			'nonce' => '',
		], $booking);

		$this->booking = array_whitelist(
			$booking,
			apply_filters('bw_booking_post_whitelist', [
				'calendarId',
				'start',
				'end',
				'serviceId',
				'bookableEventId',
				'slotId',
				'price',
				'extras',
				'breakdown',
				'fields',
				'paymentMethod',
				'paymentType',
				'utcOffset',
				'coupon',
				'nonce',
			])
		);

		$diff = array_diff(array_keys($booking), array_keys($this->booking));
		if(count($diff) > 0) {
			$this->add_booking_errors([sprintf(
				__('Booking contains invalid keys: "%s".', 'booking-weir'),
				esc_html(implode(', ', $diff))
			)]);
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	protected function validate_booking() {
		$errors = [];

		/**
		 * Load calendar.
		 */
		if(!isset($this->booking['calendarId']) || empty($this->booking['calendarId'])) {
			$errors[] = __('Calendar not chosen.', 'booking-weir');
		} else {
			$calendar = $this->calendars->get_calendar(
				$this->sanitizer->sanitize_id($this->booking['calendarId'])
			);
			if($calendar instanceof Calendar) {
				$this->calendar = $calendar;
			} else {
				$errors[] = __('Calendar not found.', 'booking-weir');
			}
		}

		/**
		 * Don't proceed without a calendar.
		 */
		if(count($errors) !== 0) {
			$this->add_booking_errors($errors);
			return false;
		}

		foreach($this->booking as $key => $value) {
			$is_valid = false;
			switch($key) {
				case 'start':
				case 'end':
					$is_valid = $this->sanitizer->validate_datetime($value);
				break;
				case 'bookableEventId':
					if(!is_numeric($value)) {
						$errors[] = __('Invalid bookable event ID.', 'booking-weir');
					}
					if((int)$value > 0 && !$this->calendar->has_event((int)$value)) {
						$errors[] = __('Bookable event not found.', 'booking-weir');
					}
				break;
				case 'serviceId':
					if(empty($value) && $this->calendar->get_setting('services')) {
						$errors[] = __('Service ID is required.', 'booking-weir');
					}
					if(strlen($value) > 0 && !$this->calendar->get_service($value)) {
						$errors[] = __('Invalid service ID.', 'booking-weir');
					}
				break;
				case 'price':
					if($value === (float)0) {
						break;
					}
					$is_valid = $this->sanitizer->validate_price($value);
				break;
				case 'paymentMethod':
					if($this->calendar->is_product()) {
						/**
						 * Payment handled by WooCommerce.
						 */
						break;
					}
					if(!$value && $this->calendar->get_default_payment_method() !== false && $this->booking['price'] > 0) {
						/**
						 * Add error when calendar has payment methods enabled, but one wasn't selected.
						 */
						$errors[] = __('Payment method not selected.', 'booking-weir');
					}
				break;
				case 'paymentType':
					if($value && !$this->calendar->has_payment_type($this->sanitizer->sanitize_id($value))) {
						$errors[] = __('Invalid payment type.', 'booking-weir');
					}
				break;
				case 'extras':
					$is_valid = $this->sanitizer->validate_extras($value);
				break;
				case 'breakdown':
					$is_valid = $this->sanitizer->validate_breakdown($value);
				break;
				case 'fields':
					$is_valid = $this->sanitizer->validate_fields($value);
				break;
				case 'email':
					$is_valid = $this->sanitizer->validate_email($value);
				break;
				case 'nonce':
					if(!isset($value) || empty($value)) {
						$errors[] = __('No nonce.', 'booking-weir');
						break;
					}
					if(!wp_verify_nonce($value, $this->calendar->get_id())) {
						$errors[] = __('Invalid session.', 'booking-weir');
					}
				break;
			}
			if(is_wp_error($is_valid)) {
				$errors[] = $is_valid->get_error_message();
			}
		}

		if(count($errors) === 0) {
			return true;
		}

		$this->add_booking_errors($errors);
		return false;
	}

	/**
	 * Ensure every accepted value is sanitized.
	 * Keys are sanitized in `parse_booking`.
	 *
	 * @return bool
	 */
	protected function sanitize_booking() {
		foreach($this->booking as $key => $value) {
			switch($key) {
				case 'calendarId':
				case 'paymentMethod':
				case 'paymentType':
				case 'serviceId':
					$this->booking[$key] = $this->sanitizer->sanitize_id($value);
				break;
				case 'start':
				case 'end':
					$this->booking[$key] = $this->sanitizer->sanitize_datetime($value);
				break;
				case 'price':
					$this->booking[$key] = $this->sanitizer->sanitize_float($value);
				break;
				case 'extras':
					$this->booking[$key] = $this->sanitizer->sanitize_extras($value);
				break;
				case 'breakdown':
					$this->booking[$key] = $this->sanitizer->sanitize_breakdown($value);
				break;
				case 'fields':
					$this->booking[$key] = $this->sanitizer->sanitize_fields($value);
				break;
				case 'firstName':
				case 'lastName':
				case 'phone':
				case 'coupon':
					$this->booking[$key] = $this->sanitizer->sanitize_string($value);
				break;
				case 'additionalInfo':
					$this->booking[$key] = $this->sanitizer->sanitize_textarea($value);
				break;
				case 'email':
					$this->booking[$key] = $this->sanitizer->sanitize_email($value);
				break;
				case 'terms':
					$this->booking[$key] = $this->sanitizer->sanitize_boolean($value);
				break;
				case 'bookableEventId':
				case 'slotId':
				case 'utcOffset':
					$this->booking[$key] = $this->sanitizer->sanitize_integer($value);
				break;
				case 'nonce':
					$this->booking[$key] = $this->sanitizer->sanitize_key($value);
				break;
				default:
					$this->add_booking_errors([sprintf(__('Unsanitary input: "%s".', 'booking-weir'), esc_html($key))]);
					return false;
			}
		}
		return true;
	}

	/**
	 * @return bool
	 */
	protected function validate_fields() {
		$errors = [];

		/**
		 * Check that submitted fields belong.
		 */
		$field_values = array_merge($this->get_default_fields_values(), $this->booking['fields']);
		foreach($field_values as $id => $value) {
			if(!$field = $this->calendar->get_field($id)) {
				$errors[] = sprintf(
					__('This calendar does not contain a submitted field "%s".', 'booking-weir'),
					esc_html($id)
				);
				continue;
			}
			if(!$field->is_enabled()) {
				$errors[] = sprintf(
					__('Submitted field "%s" is disabled.', 'booking-weir'),
					esc_html($field->get_label())
				);
				continue;
			}
		}

		/**
		 * Unset default fields for WC calendar because they aren't validated.
		 */
		if($this->calendar->is_product()) {
			foreach($this->calendar->get_default_fields() as $id => $field) {
				if(isset($this->booking[$field->get_id()])) {
					unset($this->booking[$field->get_id()]);
				}
			}
		}

		/**
		 * Validate all fields of the calendar.
		 */
		foreach($this->calendar->get_fields() as $field) {
			if(!$field->is_enabled()) {
				continue;
			}
			if(!$field->validate($field_values[$field->get_id()] ?? '')) {
				$errors[] = $field->get_error();
				continue;
			}
		}

		if(count($errors) > 0) {
			$this->add_booking_errors($errors);
			return false;
		}
		return true;
	}

	/**
	 * Get default fields for validation.
	 *
	 * @return array [fieldId => value]
	 */
	protected function get_default_fields_values() {
		if($this->calendar->is_product()) {
			return [];
		}
		$default_fields = [];
		foreach($this->calendar->get_default_fields() as $field) {
			if($field->is_enabled() && isset($this->booking[$field->get_id()])) {
				$default_fields[$field->get_id()] = $this->booking[$field->get_id()];
			}
		}
		return $default_fields;
	}

	/**
	 * Validate extras.
	 *
	 * @return bool
	 */
	protected function validate_extras() {
		$selected_extras = isset($this->booking['extras']) ? $this->booking['extras'] : [];
		if(count($selected_extras) < 1) {
			return true;
		}

		foreach($selected_extras as $id => $value) {
			$extra = $this->calendar->get_extra($id);
			if($extra === false) {
				$this->add_booking_errors([__('This calendar does not contain a chosen extra.', 'booking-weir')]);
				return false;
			}
			if(isset($extra['enabled']) && $extra['enabled'] === false) {
				$this->add_booking_errors([__('This booking contains an extra that is disabled.', 'booking-weir')]);
				return false;
			}
		}

		return true;
	}

	/**
	 * Verify that submitted price is what the server says.
	 *
	 * @return bool
	 */
	protected function validate_price() {
		$start = $this->booking['start'];
		$end = $this->booking['end'];
		$extras = isset($this->booking['extras']) ? $this->booking['extras'] : [];
		$coupon = isset($this->booking['coupon']) ? $this->booking['coupon'] : '';
		$service_id = isset($this->booking['serviceId']) ? $this->booking['serviceId'] : '';
		$bookable_event_id = isset($this->booking['bookableEventId']) ? $this->booking['bookableEventId'] : 0;
		$price = $this->calendar->get_event_price($start, $end, $extras, $coupon, $service_id, $bookable_event_id);

		/**
		 * Validate that the price that was sent with the booking is the
		 * same when calculated server side using the same event values.
		 */
		if($price['value'] !== $this->booking['price']) {
			$this->add_booking_errors([
				sprintf(
					_x('Price mismatch (%1$g - %2$g).', 'Error: submitted price - calculated price is different', 'booking-weir'),
					$this->booking['price'],
					$price['value']
				)
			]);
			return false;
		}

		/**
		 * Validate that the breakdown sent with
		 * price is the same that was calculated.
		 */
		if(json_encode($this->booking['breakdown']) !== json_encode($this->sanitizer->sanitize_breakdown($price['breakdown']))) {
			$this->add_booking_errors([__('Breakdown mismatch.', 'booking-weir')]);
			$this->context->get('logger')->log(['Breakdown mismatch' => [
				'Booking breakdown' => $this->booking['breakdown'],
				'Price breakdown' => $price['breakdown'],
			]], $this->calendar->get_id());
			return false;
		}

		return true;
	}

	/**
	 * Create a booking.
	 *
	 * @return bool Created.
	 */
	protected function create_booking() {
		do_action('bw_before_create_booking', $this);

		if($this->has_errors) {
			return false;
		}

		$result = $this->calendar->add_booking($this->booking);
		if(!$result instanceof Event) {
			$this->add_booking_errors([$result]);
			return false;
		}
		$this->event = $result;

		/**
		 * Use the URL the calendar is on for the return URL.
		 */
		$this->event->set_return_url(get_the_permalink());

		do_action('bw_after_create_booking', $this);

		/**
		 * Add the booking to cart.
		 */
		if($this->calendar->is_product()) {
			if(WC()->cart->add_to_cart($this->calendar->get_product_id(), 1, 0, [
				'bw_event_id' => $this->event->get_id(),
			])) {
				wc_add_to_cart_message($this->calendar->get_product_id());
				do_action('bw_after_add_booking_to_cart', $this);
				return true;
			} else {
				/**
				 * Delete the reserved event since it couldn't be added to cart.
				 */
				$this->event->delete_permanently();
			}
			$this->add_booking_errors([esc_html__('Booking could not be added to cart.', 'booking-weir')]);
			return false;
		}

		/**
		 * Notify admin(s) of new booking.
		 */
		$this->email->send([
			'email' => $this->calendar->get_setting($this->event->get_email() === $this->email::DEBUG_EMAIL_PLACEHOLDER ? 'debugEmail' : 'noticeEmails'),
			'title' => sprintf(
				esc_html_x('New booking: %s', 'New booking admin notification e-mail title (formatted date)', 'booking-weir'),
				$this->event->get_date_formatted()
			),
			'content' => wp_kses_post($this->get_admin_info()),
		], $this->calendar);

		$this->notices->add_success(
			esc_html__('Booked', 'booking-weir'),
			esc_html__('Your chosen time has been booked.', 'booking-weir')
		);

		return true;
	}

	/**
	 * Redirect to booking view.
	 */
	protected function maybe_redirect() {
		if(defined('BOOKING_WEIR_TEST')) {
			return;
		}
		if($this->calendar->is_product()) {
			return;
		}
		$this->notices->save();
		wp_safe_redirect($this->event->get_booking_link());
		exit;
	}

	/**
	 * Trigger the appropriate payment method to handle payment for the event.
	 */
	public function handle_payment() {
		/**
		 * Trigger payment methods.
		 */
		do_action('bw_handle_payment', $this->event);
	}

	/**
	 * Send e-mail that confirms the booking.
	 * Strip the PDF invoice from the e-mail if the `invoicePdfNoPayment` option is not enabled.
	 */
	public function handle_confirmation() {
		if(!$this->event->get_email()) {
			return;
		}
		if(!$this->calendar->get_setting('invoiceEmailEnabled')) {
			return;
		}
		if(!$this->event->get_invoice_email_sent()) {
			add_filter('bw_invoice_email', function($mail_data) {
				$mail_data['title'] = esc_html_x('Booking received', 'Booking confirmation e-mail title', 'booking-weir');
				return $mail_data;
			}, 5);

			if(!$this->payment->send_invoice($this->event)) {
				$includes_invoice = $this->calendar->get_setting('invoicePdfEnabled') && $this->calendar->get_setting('invoicePdfNoPayment');
				$message = $includes_invoice
					? esc_html__('Failed to send a confirmation with an invoice to your e-mail, please open it from %1$shere%2$s.', 'booking-weir')
					: esc_html__('Failed to send a confirmation to your e-mail, but your booking is received.', 'booking-weir');
				$this->notices->add_warning(
					esc_html_x('Confirmation', 'Booking received confirmation e-mail failed to send notice title', 'booking-weir'),
					sprintf(
						$message,
						'<strong><a href="' . esc_url($this->event->get_invoice_url()) . '" target="_blank" rel="noopener noreferrer">',
						'</a></strong>'
					)
				);
			}

			$this->event->set_invoice_email_sent(true);
		}
	}

	/**
	 * The event created as a result of the booking.
	 *
	 * @return Event
	 */
	public function get_event() {
		return $this->event;
	}

	/**
	 * Display booking status above the calendar.
	 */
	protected function display_status() {
		$status = [];

		if($name = $this->event->get_name()) {
			$status[] = [
				'header' => esc_html__('Name', 'booking-weir'),
				'content' => esc_attr($name),
			];
		}

		$status[] = [
			'header' => esc_html__('Booking', 'booking-weir'),
			'content' => esc_attr($this->event->get_date_formatted()),
		];

		if($this->event->is_child_of_bookable_event()) {
			$status[] = [
				'header' => esc_html__('Event', 'booking-weir'),
				'content' => esc_attr($this->event->get_bookable_event_title()),
			];
		}

		if($this->event->is_service()) {
			$status[] = [
				'header' => esc_html__('Service', 'booking-weir'),
				'content' => esc_attr($this->event->get_service_name()),
			];
		}

		if($this->event->is_in_named_slot()) {
			$status[] = [
				'header' => esc_html__('Slot', 'booking-weir'),
				'content' => esc_attr($this->event->get_slot_title()),
			];
		}

		$status[] = [
			'header' => esc_html__('Status', 'booking-weir'),
			'content' => esc_attr($this->event->get_status_text()),
		];

		$status[] = [
			'header' => esc_html__('Starts in', 'booking-weir'),
			'content' => esc_attr($this->event->starts_in_formatted()),
		];

		if($this->context->is_elevated()) {
			$status[] = [
				'header' => esc_html__('Admin link', 'booking-weir'),
				'content' => esc_attr($this->get_admin_link()),
			];
		}

		$status = apply_filters('bw_booking_status_display', $status, $this);

		add_action('bw_shortcode_ready', function() use ($status) {
			wp_localize_script(
				$this->context->get_script_handle('shortcode'),
				'bw_booking',
				$status
			);
		});
	}

	public function get_admin_link() {
		return sprintf(
			'<a href="%1$s" rel="nofollow">%2$s</a>',
			esc_url($this->event->get_admin_url()),
			esc_html('#' . $this->event->get_id())
		);
	}

	public function get_admin_button() {
		return sprintf(
			'<a href="%1$s" class="ui primary button btn btn-primary" target="_blank" rel="noopener noreferrer nofollow">%2$s</a>',
			esc_url($this->event->get_admin_url()),
			esc_html__('View in admin calendar', 'booking-weir')
		);
	}

	protected function get_admin_info() {
		$info = array_merge(
			[
				esc_html__('Start', 'booking-weir') => $this->event->get_start_formatted(),
				esc_html__('End', 'booking-weir') => $this->event->get_end_formatted(),
				esc_html__('Price', 'booking-weir') => $this->event->get_price_formatted(),
				esc_html__('Breakdown', 'booking-weir') => $this->event->get_breakdown_formatted(),
				esc_html__('Extras', 'booking-weir') => $this->event->get_extras_formatted(),
				esc_html__('Payment', 'booking-weir') => sprintf(
					'%1$s (%2$s: %3$s)',
					$this->event->get_payment_method_name(),
					$this->event->get_payment_type_name(),
					$this->event->get_payment_amount_formatted()
				),
			],
			$this->event->get_fields_formatted()
		);
		$content = '<ul class="ui list">';
		foreach($info as $title => $value) {
			if(!$value) {
				continue;
			}
			$content .= sprintf(
				'<li>' .
					'<strong>%1$s</strong>: <span>%2$s</span>' .
				'</li>',
				esc_html($title),
				wp_kses_post($value)
			);
		}
		$content .= '</ul>';
		$content .= $this->get_admin_button();
		foreach($this->event->get_actions() as $action => $label) {
			$content .= sprintf(
				'<br><a href="%1$s" class="ui button btn" target="_blank" rel="noopener noreferrer nofollow">%2$s</a>',
				esc_url($this->event->get_action_url($action)),
				esc_html($label)
			);
		}
		return $content;
	}

	public function find_booking($billing_key) {
		$wp_query = new WP_Query([
			'post_type' => $this->context->get('event-post-type')::SLUG,
			'posts_per_page' => 1,
			'meta_key' => 'bw_billing_key',
			'meta_value' => $this->sanitizer->sanitize_id($billing_key),
			'post_status' => 'publish',
		]);
		if($wp_query->have_posts()) {
			while($wp_query->have_posts()) {
				$wp_query->the_post();
				$id = get_the_ID();
				$calendar_id = get_post_meta($id, 'bw_calendar_id', true);
				if($calendar = $this->calendars->get_calendar($calendar_id)) {
					if($event = $calendar->get_event($id)) {
						$this->calendar = $calendar;
						$this->event = $event;
						wp_reset_postdata();
						return true;
					}
				}
			}
			wp_reset_postdata();
		}
		return false;
	}

	/**
	 * Output errors in a message as well as log file.
	 *
	 * @param array $errors
	 */
	public function add_booking_errors($errors) {
		$this->has_errors = true;

		/**
		 * Log if the booking had a working calendar ID attached.
		 */
		if($this->calendar instanceof Calendar) {
			$this->context->get('logger')->log([
				'Booking errors' => $errors,
				'Booking' => $this->booking,
			], $this->calendar->get_id());
		}
		/**
		 * When it's a product, output the errors with WC notices.
		 */
		if($this->calendar instanceof Calendar && $this->calendar->is_product()) {
			foreach($errors as $error) {
				wc_add_notice($error, 'error');
			}
			return;
		}
		/**
		 * Add plugin notices.
		 */
		$this->notices->add_errors(esc_html__('Booking errors', 'booking-weir'), $errors);
	}

	/**
	 * Add noindex meta tag to head to prevent indexing booking status "pages".
	 */
	public function noindex() {
		if(isset($_GET[self::VIEW])) {
			printf('<meta name="robots" content="noindex" />' . "\n");
		}
	}

	/**
	 * Whether a booking is submitted via post.
	 *
	 * @return boolean
	 */
	public function is_booking_submitted() {
		return isset($_POST[self::SUBMIT]) && is_array($_POST[self::SUBMIT]); // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Get the submitted booking.
	 * This class handles the validation and sanitation.
	 *
	 * @return array
	 */
	private function get_submitted_booking() {
		// phpcs:disable WordPress.Security.NonceVerification
		if(!isset($_POST[self::SUBMIT])) {
			return [];
		}
		return (array)wp_unslash($_POST[self::SUBMIT]); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// phpcs:enable WordPress.Security.NonceVerification
	}

	/**
	 * Whether a booking is selected via query var.
	 *
	 * @return boolean
	 */
	public function is_booking_selected() {
		return isset($_GET[self::VIEW]) && is_string($_GET[self::VIEW]);
	}

	/**
	 * @return string
	 */
	private function get_selected_booking() {
		if(!isset($_GET[self::VIEW])) {
			return '';
		}
		return $this->sanitizer->sanitize_id(wp_unslash($_GET[self::VIEW])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * Ensure selected booking is included in calendar's public events.
	 */
	public function include_selected_booking($events) {
		if(!$this->is_booking_selected()) {
			return $events;
		}
		if($this->event instanceof Event) {
			if(!in_array($this->event->get_id(), array_keys($events))) {
				$events[$this->event->get_id()] = $this->event;
			}
		}
		return $events;
	}
}
