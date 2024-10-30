<?php

namespace wsd\bw\core\booking\paymentmethods;

use wsd\bw\core\events\Event;
use wsd\bw\core\calendars\Calendar;
use wsd\bw\core\booking\PaymentMethod;
use wsd\bw\core\booking\IPaymentMethod;
use wsd\bw\core\booking\IPaymentMethodReturnHandler;
use wsd\bw\core\booking\IPaymentMethodTransactionData;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Exception;

/**
 * Stripe payment method class.
 */
class StripePaymentMethod extends PaymentMethod implements IPaymentMethod, IPaymentMethodReturnHandler, IPaymentMethodTransactionData {

	const SESSION_ID_QUERY_VAR = 'bw_stripe_session_id';

	public function get_id() {
		return 'stripe';
	}

	public function get_name() {
		return esc_attr_x('Stripe', 'Payment method name', 'booking-weir');
	}

	public function get_data() {
		return [
			'description' => esc_html__('Pay with Stripe', 'booking-weir'),
			'options' => [
				[
					'id' => 'apiKey',
					'label' => __('API secret key', 'booking-weir'),
					'description' => __('Available from Stripe dashboard.', 'booking-weir'),
					'type' => 'string',
					'default' => '',
					'required' => true,
				],
				[
					'id' => 'testApiKey',
					'label' => __('Test API secret key', 'booking-weir'),
					'description' => __('Used when Sandbox mode is enabled.', 'booking-weir'),
					'type' => 'string',
					'default' => '',
					'required' => false,
				],
				[
					'id' => 'currencyCode',
					'label' => __('Currency code', 'booking-weir'),
					'description' => __('Three-letter ISO code for the currency with which to perform the payment.', 'booking-weir'),
					'type' => 'string',
					'default' => 'EUR',
					'required' => true,
				],
				[
					'id' => 'sandbox',
					'label' => __('Sandbox', 'booking-weir'),
					'description' => __('Enable to perform the payments in a test environment.', 'booking-weir'),
					'type' => 'toggle',
					'default' => false,
					'required' => false,
				],
			],
		];
	}

	public function get_instructions() {
		return [
			'full' => esc_html__('You can pay for your booking by using the Stripe link %1$shere%2$s.', 'booking-weir'),
			'escrow' => esc_html__('You can pay %3$s of the total amount in advance for your booking by using the Stripe link %1$shere%2$s.', 'booking-weir'),
		];
	}

	/**
	 * @param Event $event
	 */
	public function handle_payment(Event $event) {
		if(
			!$event->get_invoice_email_sent()
			&& $event->get_email()
			&& $event->get_calendar()->get_setting('invoiceEmailEnabled')
		) {
			$this->context->get('payment')->send_invoice($event);
			$event->set_invoice_email_sent(true);
		}

		$calendar = $event->get_calendar();
		$stripe = $this->get_stripe($calendar);
		$options = $calendar->get_payment_method_options('stripe');

		$this->stripe_call(function() use ($stripe, $event, $options) {
			$session_data = [
				'mode' => 'payment',
				'client_reference_id' => $event->get_billing_key(),
				'customer_email' => $event->get_email(),
				'payment_method_types' => apply_filters('bw_stripe_payment_method_types', ['card']),
				'line_items' => [
					[
						'price_data' => [
							'product_data' => [
								'name' => $event->get_title(),
								'description' => $event->get_date_formatted(),
								'metadata' => [
									'bw_event_id' => $event->get_id(),
								],
							],
							'unit_amount' => (int)($event->get_payment_amount() * 100),
							/**
							 * @see https://www.iso.org/iso-4217-currency-codes.html
							 * @see https://stripe.com/docs/currencies
							 */
							'currency' => $this->sanitizer->sanitize_id($options['currencyCode']),
						],
						'quantity' => 1,
					],
				],
				'success_url' => add_query_arg(self::SESSION_ID_QUERY_VAR, '{CHECKOUT_SESSION_ID}', $event->get_return_url()),
				'cancel_url' => $event->get_booking_link(),
			];
			$this->context->get('logger')->log(['Create session' => $session_data], 'stripe');
			$checkout_session = $stripe->checkout->sessions->create($session_data);

			$event->set_transaction_id($checkout_session->id);

			$this->context->get('notices')->add_info(
				esc_html__('Pay with Stripe', 'booking-weir'),
				sprintf(
					wp_kses_post(__('%1$sClick here to pay with Stripe.%2$s', 'booking-weir')),
					'<a href="' . esc_url($checkout_session->url) . '" rel="noopener noreferrer">',
					'</a>'
				)
			);
		});
	}

	public function handle_return() {
		if(!isset($_GET[self::SESSION_ID_QUERY_VAR])) {
			return;
		}

		$session_id = $this->sanitizer->sanitize_id(wp_unslash($_GET[self::SESSION_ID_QUERY_VAR])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$this->context->get('logger')->log(['Return handler' => $session_id], 'stripe');

		if(!$event = $this->context->get('payment')->get_event($session_id)) {
			$this->context->get('logger')->log(['Event not found with specified session ID' => $session_id], 'stripe');
			$this->context->get('notices')->add_error(
				$this->get_name(),
				esc_html__("Couldn't find the booking you paid for.", 'booking-weir')
			);
			return;
		}

		if($event->get_status() !== 'pending') {
			$this->context->get('notices')->add_error(
				$this->get_name(),
				esc_html__('Booking is no longer pending payment.', 'booking-weir')
			);
			$this->context->get('notices')->save();
			wp_safe_redirect($event->get_booking_link());
			exit;
		}

		$stripe = $this->get_stripe($event->get_calendar());

		$this->stripe_call(function() use ($stripe, $session_id) {
			$checkout_session = $stripe->checkout->sessions->retrieve($session_id);
			$this->context->get('logger')->log(['Retrieved session' => [$session_id => [
				'client_reference_id' => $checkout_session['client_reference_id'],
			]]], 'stripe');
			$billing_key = $checkout_session['client_reference_id'];
			$booking = $this->context->get('booking');
			if($booking->find_booking($billing_key)) {
				$event = $booking->get_event();
				$this->context->get('logger')->log(['Payment received' => [
					'billing_key' => $event->get_billing_key(),
					'amount' => $checkout_session['amount_total'],
					'amount_total' => $checkout_session['amount_total'] / 100,
					'event_payment_amount' => $event->get_payment_amount(),
					'event_full_price' => $event->get_price(),
				]], 'stripe');
				$status = $checkout_session['amount_total'] / 100 < $event->get_price() ? 'escrow' : 'paid';
				$event->set_status($status);
				$this->context->get('notices')->add_success(
					__('Transaction completed', 'booking-weir'),
					__('Thank you for your payment.', 'booking-weir')
				);
				$this->context->get('notices')->save();
				wp_safe_redirect($event->get_booking_link());
				exit;
			} else {
				$this->context->get('logger')->log(['Booking not found with specified billing key' => $billing_key], 'stripe');
				$this->context->get('notices')->add_error(
					$this->get_name(),
					esc_html__("Couldn't find the booking you paid for.", 'booking-weir')
				);
			}
		});
	}

	/**
	 * @param string $transaction_id
	 * @return array|string
	 */
	public function get_transaction($transaction_id) {
		if(!$event = $this->context->get('payment')->get_event($transaction_id)) {
			return sprintf(
				esc_html__("Couldn't find an event with the transaction ID: %s.", 'booking-weir'),
				$transaction_id
			);
		}

		$stripe = $this->get_stripe($event->get_calendar());
		return (array)$stripe->checkout->sessions->retrieve($transaction_id);
	}

	/**
	 * @see https://github.com/stripe/stripe-php
	 *
	 * @param Calendar $calendar
	 * @return StripeClient
	 */
	private function get_stripe(Calendar $calendar) {
		Stripe::setEnableTelemetry(false);
		Stripe::setAppInfo('Booking Weir', BOOKING_WEIR_VER, 'https://wordpress.org/plugins/booking-weir');

		$options = $calendar->get_payment_method_options('stripe');

		if($this->sanitizer->sanitize_boolean($options['sandbox'])) {
			$api_key = $this->sanitizer->sanitize_id($options['testApiKey']);
			$this->context->get('notices')->add_warning(
				$this->get_name(),
				esc_html__('Using sandbox environment, payments are not real.', 'booking-weir')
			);
		} else {
			$api_key = $this->sanitizer->sanitize_id($options['apiKey']);
		}

		return new StripeClient($api_key);
	}

	/**
	 * @param mixed $e
	 * @param boolean $public
	 */
	private function handle_exception($e, $public = false) {
		$message = $e->getMessage();
		$this->context->get('logger')->log(['Exception' => $message], 'stripe');
		if(!$public && !$this->context->is_elevated()) {
			$this->context->get('notices')->add_error(
				$this->get_name(),
				esc_html__('There was an error with the payment processor.', 'booking-weir')
			);
			return;
		}
		$this->context->get('notices')->add_error(
			$this->get_name(),
			esc_html($message)
		);
	}

	/**
	 * Call function with Strip exception handling.
	 *
	 * @param callable $function
	 */
	private function stripe_call($function) {
		try {
			call_user_func($function);
		} catch(CardException $e) {
			// Card declined
			$this->handle_exception($e, true);
		} catch(RateLimitException $e) {
			// Too many requests made to the API too quickly
			$this->handle_exception($e, true);
		} catch(InvalidRequestException $e) {
			// Invalid parameters were supplied to Stripe's API
			$this->handle_exception($e);
		} catch(AuthenticationException $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			$this->handle_exception($e);
		} catch(ApiConnectionException $e) {
			// Network communication with Stripe failed
			$this->handle_exception($e, true);
		} catch(ApiErrorException $e) {
			// Generic error
			$this->handle_exception($e);
		} catch(Exception $e) {
			$this->handle_exception($e);
		}
	}
}

/**
 * Register payment method class.
 */
add_filter('bw_payment_method_classes', function($classes) {
	$classes[] = StripePaymentMethod::class;
	return $classes;
});
