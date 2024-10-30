<?php

namespace wsd\bw\core\booking\paymentmethods;

use wsd\bw\core\events\Event;
use wsd\bw\core\calendars\Calendar;
use wsd\bw\core\booking\PaymentMethod;
use wsd\bw\core\booking\IPaymentMethod;
use wsd\bw\core\booking\IPaymentMethodReturnHandler;
use wsd\bw\core\booking\IPaymentMethodTransactionData;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use HttpException;

/**
 * PayPal payment method class.
 */
class PayPal extends PaymentMethod implements IPaymentMethod, IPaymentMethodReturnHandler, IPaymentMethodTransactionData {

	public function get_id() {
		return 'paypal';
	}

	public function get_name() {
		return esc_attr_x('PayPal', 'Payment method name', 'booking-weir');
	}

	public function get_data() {
		return [
			'description' => esc_html__('Pay with PayPal', 'booking-weir'),
			'options' => [
				[
					'id' => 'clientId',
					'label' => __('Client ID', 'booking-weir'),
					'description' => __('Available from PayPal developer dashboard.', 'booking-weir'),
					'type' => 'string',
					'default' => '',
					'required' => true,
				],
				[
					'id' => 'clientSecret',
					'label' => __('Client secret', 'booking-weir'),
					'description' => __('Available from PayPal developer dashboard.', 'booking-weir'),
					'type' => 'string',
					'default' => '',
					'required' => true,
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
			'full' => esc_html__('You can pay for your booking by using the PayPal link %1$shere%2$s.', 'booking-weir'),
			'escrow' => esc_html__('You can pay %3$s of the total amount in advance for your booking by using the Paypal link %1$shere%2$s.', 'booking-weir'),
		];
	}

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
		$paypal = $this->get_paypal($calendar);
		$options = $calendar->get_payment_method_options('paypal');

		$request = new OrdersCreateRequest();
		$request->prefer('return=representation');
		$request->body = [
			'intent' => 'CAPTURE',
			'purchase_units' => [[
				'reference_id' => $event->get_billing_key(),
				'amount' => [
					'value' => $event->get_payment_amount(),
					'currency_code' => $this->sanitizer->sanitize_id($options['currencyCode']),
				],
			]],
			'application_context' => [
				'cancel_url' => $event->get_return_url(),
				'return_url' => $event->get_return_url(),
			],
		];

		try {
			$response = $paypal['client']->execute($request);
			if($response->statusCode === 201) {
				foreach($response->result->links as $link) {
					if($link->rel === 'approve') {
						$event->set_transaction_id($this->sanitizer->sanitize_id($response->result->id));
						$this->context->get('notices')->add_info(
							esc_html__('Pay with PayPal', 'booking-weir'),
							sprintf(
								wp_kses_post(__('%1$sClick here to pay with PayPal.%2$s', 'booking-weir')),
								'<a href="' . esc_url($link->href) . '" rel="' . esc_attr($link->rel) . '">',
								'</a>'
							)
						);
						break;
					}
				}
			}
		} catch(HttpException $e) {
			$this->context->get('notices')->add_error(
				$this->get_name(),
				esc_html($e->getMessage())
			);
		}
	}

	public function handle_return() {
		if(!isset($_GET['token']) || !isset($_GET['PayerID'])) {
			return;
		}

		$token = $this->sanitizer->sanitize_id(wp_unslash($_GET['token'])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// $payerID = $this->sanitizer->sanitize_id(wp_unslash($_GET['PayerID'])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if(!$event = $this->context->get('payment')->get_event($token)) {
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

		$paypal = $this->get_paypal($event->get_calendar());
		$request = new OrdersCaptureRequest($token);
		$request->prefer('return=representation');
		try {
			$response = $paypal['client']->execute($request);
			if($response->statusCode === 201) {
				switch($response->result->status) {
					case 'COMPLETED':
						$purchase = $response->result->purchase_units[0];
						$billing_key = $this->sanitizer->sanitize_id($purchase->reference_id);
						$booking = $this->context->get('booking');
						if($booking->find_booking($billing_key)) {
							$event = $booking->get_event();
							$status = (float)$purchase->amount->value < $event->get_price() ? 'escrow' : 'paid';
							$event->set_status($status);
							$event->set_transaction_id($this->sanitizer->sanitize_id($response->result->id));
							$this->context->get('notices')->add_success(
								__('Transaction completed', 'booking-weir'),
								__('Thank you for your payment.', 'booking-weir')
							);
							$this->context->get('notices')->save();
							wp_safe_redirect($event->get_booking_link());
							exit;
						} else {
							$this->context->get('notices')->add_error(
								$this->get_name(),
								esc_html__("Couldn't find the booking you paid for.", 'booking-weir')
							);
						}
					break;
					default:
						$this->context->get('logger')->log(['Unhandled status' => $response], 'paypal');
				}
			} else {
				$this->context->get('logger')->log(['Unhandled response code' => $response], 'paypal');
			}
		} catch(HttpException $ex) {
			$this->context->get('notices')->add_error(
				$this->get_name(),
				esc_html($ex->getMessage())
			);
		}
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

		$paypal = $this->get_paypal($event->get_calendar());

		try {
			$response = $paypal['client']->execute(new OrdersGetRequest($transaction_id));
			return (array)$response;
		} catch(HttpException $ex) {
			return $ex->getMessage();
		}
	}

	/**
	 * @see https://github.com/paypal/Checkout-PHP-SDK
	 *
	 * @param Calendar $calendar
	 * @return array
	 */
	private function get_paypal(Calendar $calendar) {
		$options = $calendar->get_payment_method_options('paypal');

		$clientId = $this->sanitizer->sanitize_id($options['clientId']);
		$clientSecret = $this->sanitizer->sanitize_id($options['clientSecret']);

		if($this->sanitizer->sanitize_boolean($options['sandbox'])) {
			$environment = new SandboxEnvironment($clientId, $clientSecret);
			$this->context->get('notices')->add_warning(
				esc_attr_x('PayPal', 'Payment method name', 'booking-weir'),
				esc_html__('Using sandbox environment, payments are not real.', 'booking-weir')
			);
		} else {
			$environment = new ProductionEnvironment($clientId, $clientSecret);
		}

		return [
			'env' => $environment,
			'client' => new PayPalHttpClient($environment),
		];
	}
}

/**
 * Register payment method class.
 */
add_filter('bw_payment_method_classes', function($classes) {
	$classes[] = PayPal::class;
	return $classes;
});
