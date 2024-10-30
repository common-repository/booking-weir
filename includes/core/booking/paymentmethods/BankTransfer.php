<?php

namespace wsd\bw\core\booking\paymentmethods;

use wsd\bw\core\events\Event;
use wsd\bw\core\booking\PaymentMethod;
use wsd\bw\core\booking\IPaymentMethod;

/**
 * Bank transfer payment method class.
 */
class BankTransfer extends PaymentMethod implements IPaymentMethod {

	public function get_id() {
		return 'bankTransfer';
	}

	public function get_name() {
		return esc_attr_x('Bank transfer', 'Payment method name', 'booking-weir');
	}

	public function get_data() {
		return [
			'description' => esc_html__('Manual payment by the client', 'booking-weir'),
		];
	}

	public function get_instructions() {
		return [
			'full' => esc_html__('An invoice has been attached to this e-mail.', 'booking-weir'),
			'escrow' => esc_html__('An invoice for %3$s of the total amount has been attached to this e-mail.', 'booking-weir'),
		];
	}

	public function handle_payment(Event $event) {
		if(
			$event->get_invoice_email_sent()
			|| !$event->get_email()
			|| !$event->get_calendar()->get_setting('invoiceEmailEnabled')
		) {
			return;
		}

		$sent = $this->context->get('payment')->send_invoice($event);

		$message = $sent
			? esc_html_x('%1$sInvoice%2$s has been sent to your e-mail.', 'Bank transfer: Payment message content.', 'booking-weir')
			: esc_html_x('Failed to send invoice to your e-mail, please open it from %1$shere%2$s.', 'Bank transfer: Payment message content.', 'booking-weir');

		$this->context->get('notices')->add(
			esc_html_x('Payment', 'Payment notice header', 'booking-weir'),
			sprintf(
				$message,
				'<strong><a href="' . esc_url($event->get_invoice_url()) . '" target="_blank" rel="noopener noreferrer">',
				'</a></strong>'
			),
			$sent ? 'info' : 'exclamation-triangle',
			$sent ? 'info' : 'warning'
		);

		$event->set_invoice_email_sent(true);
	}
}

/**
 * Register Bank transfer payment method class.
 */
add_filter('bw_payment_method_classes', function($classes) {
	$classes[] = BankTransfer::class;
	return $classes;
});
