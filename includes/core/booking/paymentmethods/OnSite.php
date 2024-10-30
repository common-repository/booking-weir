<?php

namespace wsd\bw\core\booking\paymentmethods;

use wsd\bw\core\events\Event;
use wsd\bw\core\booking\PaymentMethod;
use wsd\bw\core\booking\IPaymentMethod;

/**
 * OnSite payment method class.
 */
class OnSite extends PaymentMethod implements IPaymentMethod {

	public function get_id() {
		return 'onsite';
	}

	public function get_name() {
		return esc_attr_x('On-site', 'Payment method name', 'booking-weir');
	}

	public function get_data() {
		return [
			'description' => esc_html__('On-site payment by the client', 'booking-weir'),
		];
	}

	public function get_instructions() {
		return [
			'full' => esc_html__('An invoice has been attached to this e-mail. You can pay for it on-site.', 'booking-weir'),
			'escrow' => esc_html__('An invoice for %3$s of the total amount has been attached to this e-mail. You can pay for it on-site.', 'booking-weir'),
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

		/**
		 * Set status from default "Awaiting payment" to "Awaiting" since payment should be done on-site.
		 */
		if($event->get_status() === 'pending') {
			$event->set_status('awaiting');
		}

		$sent = $this->context->get('payment')->send_invoice($event);

		$message = $sent
			? esc_html_x('%1$sInvoice%2$s has been sent to your e-mail.', 'On-site: Payment message content.', 'booking-weir')
			: esc_html_x('Failed to send invoice to your e-mail, please open it from %1$shere%2$s.', 'On-site: Payment message content.', 'booking-weir');

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
 * Register payment method class.
 */
add_filter('bw_payment_method_classes', function($classes) {
	$classes[] = OnSite::class;
	return $classes;
});
