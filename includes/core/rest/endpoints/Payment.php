<?php

namespace wsd\bw\core\rest\endpoints;

use wsd\bw\core\rest\Endpoint;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class Payment extends Endpoint {

	public function register_routes() {

		$this->register_private_route('transaction', [
			'methods' => WP_REST_Server::CREATABLE,
			'callback' => [$this, 'get_transaction'],
			'args' => [
				'paymentMethod' => [
					'type' => 'string',
					'required' => true,
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_string'],
				],
				'transactionId' => [
					'type' => 'string',
					'required' => true,
					'sanitize_callback' => [$this->context->get('sanitizer'), 'sanitize_string'],
				],
			],
		]);
	}

	/**
	 * Get transaction info.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_transaction(WP_REST_Request $request) {
		$payment_method = $request->get_param('paymentMethod');
		$transaction_id = $request->get_param('transactionId');
		$payment = $this->context->get('payment');
		$transaction = $payment->get_transaction($payment_method, $transaction_id);
		if(!is_array($transaction)) {
			return $this->error(__('Error retrieving transaction.', 'booking-weir'));
		}
		return $this->success($transaction);
	}
}
