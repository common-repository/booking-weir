<?php

namespace wsd\bw\core\booking;

use wsd\bw\Context;
use wsd\bw\core\events\Event;
use wsd\bw\core\Sanitizer;

/**
 * Payment method interface.
 */
interface IPaymentMethod {

	/**
	 * Payment method ID.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Payment method name.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Payment method data.
	 *
	 * @return array
	 */
	public function get_data();

	/**
	 * Payment method instructions.
	 *
	 * @return array
	 */
	public function get_instructions();

	/**
	 * Handle payment.
	 *
	 * @param Event $event
	 * @return void
	 */
	public function handle_payment(Event $event);
}

/**
 * Interface for payment methods that use a return handler.
 */
interface IPaymentMethodReturnHandler {

	/**
	 * Return handler.
	 *
	 * @return void
	 */
	public function handle_return();
}

/**
 * Interface for payment methods that provide transaction data.
 */
interface IPaymentMethodTransactionData {

	/**
	 * Provide transaction data when possible.
	 *
	 * @param string $transaction_id
	 * @return array|string
	 */
	public function get_transaction($transaction_id);
}

/**
 * Payment methods should extend this class.
 */
abstract class PaymentMethod {

	/** @var Context */
	protected $context;

	/** @var Sanitizer */
	protected $sanitizer;

	public function __construct(Context $context) {
		$this->context = $context;
		$this->sanitizer = $context->get('sanitizer');
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		/**
		 * Register payment method.
		 */
		add_filter('bw_payment_methods', function($methods) {
			$methods[$this->get_id()] = $this->get_name(); // @phpstan-ignore-line
			return $methods;
		});

		/**
		 * Add payment method data.
		 */
		add_filter('bw_payment_method_data', function($methods) {
			$methods[] = array_merge([ // @phpstan-ignore-next-line
				'id' => $this->get_id(),
			], $this->get_data()); // @phpstan-ignore-line
			return $methods;
		});

		/**
		 * Add payment method instructions.
		 */
		add_filter('bw_payment_method_instructions', function($methods) {
			$methods[$this->get_id()] = $this->get_instructions(); // @phpstan-ignore-line
			return $methods;
		});

		/**
		 * Add payment handler.
		 */
		add_action('bw_handle_payment', [$this, '_handle_payment']);

		/**
		 * Add return handler.
		 */
		if($this->implements(IPaymentMethodReturnHandler::class)) {
			add_action('bw_booking', [$this, 'handle_return']);
		}

		/**
		 * Add transaction data provider.
		 */
		if($this->implements(IPaymentMethodTransactionData::class)) {
			add_filter('bw_get_transaction', [$this, '_get_transaction'], 10, 3);
		}
	}

	/**
	 * Trigger payment handler for this payment method.
	 *
	 * @param Event $event
	 * @return void
	 */
	public function _handle_payment(Event $event) {
		if($event->get_payment_method() !== $this->get_id()) { // @phpstan-ignore-line
			return;
		}
		$this->handle_payment($event); // @phpstan-ignore-line
	}

	/**
	 * Trigger transaction info provider for this payment method.
	 *
	 * @param mixed $transaction
	 * @param string $method
	 * @param string $transaction_id
	 * @return array|string
	 */
	public function _get_transaction($transaction, $method, $transaction_id) {
		if($method !== $this->get_id()) { // @phpstan-ignore-line
			return $transaction;
		}
		return $this->get_transaction($transaction_id); // @phpstan-ignore-line
	}

	/**
	 * Check if this class implements an interface.
	 *
	 * @param string $interface
	 * @return bool
	 */
	private function implements($interface) {
		return in_array($interface, class_implements($this));
	}
}
