<?php

namespace wsd\bw\integrations\woocommerce;

use wsd\bw\Context;
use wsd\bw\core\events\Event;
use WC_Product;
use WC_Order_Item_Product;

class Product extends WC_Product {

	/** @var Context */
	protected $bw_context;

	/**
	 * Initialize product.
	 *
	 * @param WC_Product|int $product Product instance or ID.
	 */
	public function __construct($product = 0) {
		$this->data['virtual'] = true; // No shipping.
		$this->data['sold_individually'] = true; // Can't buy multiples.
		$this->data['manage_stock'] = false; // Managed by available time slots in calendar.

		$this->bw_context = apply_filters('bw_context', null);

		parent::__construct($product);
	}

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'bw_booking';
	}

	/**
	 * Returns false if the product cannot be bought.
	 *
	 * @return bool
	 */
	public function is_purchasable() {
		return apply_filters('bw_booking_product_is_purchasable', true);
	}

	/**
	 * Check if a product is sold individually (no quantities).
	 *
	 * @return bool
	 */
	public function is_sold_individually() {
		return apply_filters('bw_booking_product_is_sold_individually', true);
	}

	/**
	 * Returns whether or not the product can be purchased.
	 * This returns true for 'instock' and 'onbackorder' stock statuses.
	 *
	 * @return bool
	 */
	public function is_in_stock() {
		/**
		 * Prevent displaying "Undo?" link when removed from cart by faking lack of stock.
		 * Can't undo since the event associated with the product is deleted when removing from cart.
		 * @see woocommerce/includes/class-wc-form-handler.php `update_cart_action()`
		 */
		if(isset($_GET['remove_item'])) {
			$nonce_value = wc_get_var($_REQUEST['woocommerce-cart-nonce'], wc_get_var($_REQUEST['_wpnonce'], '')); // @codingStandardsIgnoreLine
			if(wp_verify_nonce($nonce_value, 'woocommerce-cart')) {
				return false;
			}
		}
		return parent::is_in_stock();
	}

	/**
	 * Returns the product's active price.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 * @return string price
	 */
	public function get_price($context = 'view') {
		$price = $this->get_prop('price', $context);
		if($price === '') {
			/**
			 * Fix: `Warning: A non-numeric value encountered in class-wc-discounts.php` when applying a coupon.
			 * `woocommerce_before_calculate_totals` that applies correct price won't be called yet, not sure if WC bug.
			 * Even though it returns `0` the coupon applying code will run a second time, applying the correct price.
			 */
			return '0';
		}
		return $price;
	}

	/**
	 * Returns the price in html format.
	 *
	 * @param string $price Price (default: '').
	 * @return string
	 */
	public function get_price_html($price = '') {
		if($calendar = $this->bw_context->get('calendars')->get_calendar_for_product($this->get_id())) {
			if($custom_price_text = $calendar->get_setting('productPriceText')) {
				$price = wp_kses_post($custom_price_text);
			} else {
				$price = sprintf(
					wp_kses_post(_x('%s/hour', 'Booking product price.', 'booking-weir')),
					wc_price($calendar->get_setting('price'))
				);
			}
		}
		$price = apply_filters('bw_booking_product_get_price_html', $price, $this);
		return apply_filters('woocommerce_get_price_html', $price, $this);
	}

	/**
	 * Get the add to cart button text for the single page.
	 *
	 * @return string
	 */
	public function single_add_to_cart_text() {
		return apply_filters('bw_booking_product_single_add_to_cart_text', _x('Choose a time', '', 'booking-weir'), $this);
	}

	/**
	 * Get the add to cart button text.
	 *
	 * @return string
	 */
	public function add_to_cart_text() {
		return apply_filters('bw_booking_product_add_to_cart_text', _x('Book', 'Booking product add to cart text.', 'booking-weir'), $this);
	}

	/**
	 * Get `Event` from `WC_Order_Item_Product` or cart item array.
	 *
	 * @param WC_Order_Item_Product|array $input
	 * @return Event|bool
	 */
	public function get_event_from($input) {
		if(is_array($input)) {
			if(isset($input['variation']) && isset($input['variation']['bw_event_id'])) {
				$event = $this->bw_context->get('event-factory')->create($input['variation']['bw_event_id']);
				if($event->exists()) {
					return $event;
				}
			}
			return false;
		}
		if($input instanceof WC_Order_Item_Product) {
			foreach($input->get_meta_data() as $meta) {
				if($meta->key === 'bw_event_id') {
					$event = $this->bw_context->get('event-factory')->create($meta->value);
					if($event->exists()) {
						return $event;
					}
					return false;
				}
			}
		}
		return false;
	}
}
