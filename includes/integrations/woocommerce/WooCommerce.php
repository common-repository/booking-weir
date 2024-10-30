<?php

namespace wsd\bw\integrations\woocommerce;

use wsd\bw\Context;
use wsd\bw\core\calendars\Calendars;
use wsd\bw\core\events\Event;
use WC_Cart;
use WC_Order_Item_Product;

/**
 * WooCommerce class.
 */
final class WooCommerce {

	/** @var Context */
	protected $context;

	/** @var Calendars */
	protected $calendars;

	/**
	 * @param Context $context
	 * @param Calendars $calendars
	 */
	public function __construct(Context $context, Calendars $calendars) {
		$this->context = $context;
		$this->calendars = $calendars;
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_filter('woocommerce_product_class', [$this, 'add_product_class'], 10, 2);
		add_filter('product_type_selector', [$this, 'add_product']);
		add_filter('woocommerce_product_data_tabs', [$this, 'filter_data_tabs']);
		add_action('admin_footer', [$this, 'show_panels']);
		add_filter('woocommerce_product_tabs', [$this, 'add_calendar_tab']);
		add_action('woocommerce_single_product_summary', [$this, 'add_calendar_button'], 25);
		add_action('woocommerce_check_cart_items', [$this, 'check_cart_items']);
		add_filter('woocommerce_get_item_data', [$this, 'set_item_data'], 10, 2);
		add_filter('woocommerce_order_item_get_formatted_meta_data', [$this, 'format_meta_data'], 10, 2);
		add_action('woocommerce_before_calculate_totals', [$this, 'set_price']);
		add_action('woocommerce_remove_cart_item', [$this, 'delete_cart_item_event'], 10, 2);
		add_action('woocommerce_remove_cart_item_from_session', [$this, 'delete_expired_cart_item_event'], 10, 2);
		add_action('woocommerce_before_cart_emptied', [$this, 'delete_events_in_cart']);
		add_action('woocommerce_before_delete_order_item', [$this, 'detach_event']);
		add_action('woocommerce_checkout_update_order_meta', [$this, 'update_event_meta'], 10, 2);
		add_action('updated_postmeta', [$this, 'sync_event_meta'], 10, 4);
		add_action('edit_post_shop_order', [$this, 'sync_order_comments'], 10, 2);
		add_action('woocommerce_before_save_order_item', [$this, 'update_price']);
		add_filter('woocommerce_cart_item_price', [$this, 'use_price'], 10, 3);
	}

	/**
	 * Add `bw_booking` product class.
	 */
	public function add_product_class($classname, $product_type) {
		if($product_type == 'bw_booking') {
			$classname = Product::class;
		}
		return $classname;
	}

	/**
	 * Add `bw_booking` product to product type select.
	 */
	public function add_product($types) {
		$types['bw_booking'] = _x('Booking product', 'WooCommerce product type name', 'booking-weir');
		return $types;
	}

	/**
	 * Remove tabs that are not applicable for `bw_booking` product metabox.
	 */
	public function filter_data_tabs($tabs) {
		if(isset($tabs['shipping']) && isset($tabs['shipping']['class']) && is_array($tabs['shipping']['class'])) {
			$tabs['shipping']['class'][] = 'hide_if_bw_booking';
		}
		if(isset($tabs['attribute']) && isset($tabs['attribute']['class']) && is_array($tabs['attribute']['class'])) {
			$tabs['attribute']['class'][] = 'hide_if_bw_booking';
		}
		return $tabs;
	}

	/**
	 * Show tax settings in General tab for Booking product.
	 */
	public function show_panels() {
		global $pagenow;
		global $typenow;
		if($pagenow !== 'post.php' || $typenow !== 'product') {
			return;
		}
		$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
	jQuery && jQuery(function($) {
		$('#general_product_data ._tax_class_field').parent().addClass('show_if_bw_booking');
		$('select#product-type').trigger('change'); // Calls `show_and_hide_panels()`.
	});
});
JS;
		printf('<script>%s</script>', $js); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render a calendar tab for `bw_booking` products.
	 */
	public function add_calendar_tab($tabs) {
		global $product;
		if($product && $product->get_type() === 'bw_booking') {
			$tabs['bw_booking'] = [
				'title' => esc_html(_x('Booking', 'Booking product calendar tab title.', 'booking-weir')),
				'priority' => 5,
				'callback' => function() {
					global $product;
					if($calendar = $this->calendars->get_calendar_for_product($product->get_id())) {
						echo do_shortcode(sprintf(
							'[%s id="%s"]',
							$this->context->get('shortcode')::TAG,
							esc_attr($calendar->get_id())
						));
					} else {
						if($this->context->is_elevated()) {
							esc_html_e('Calendar not found.', 'booking-weir');
						} else {
							esc_html_e('Booking unavailable.', 'booking-weir');
						}
					}
				},
			];
		}
		return $tabs;
	}

	/**
	 * Render button on single product page that directs the user to the calendar.
	 */
	public function add_calendar_button() {
		global $product;
		if($product->get_type() !== 'bw_booking') {
			return;
		}
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				document.addEventListener('click', function(e) {
					if(!e.target.matches('[href="#bw_wc_calendar"]')) {
						return;
					}
					e.preventDefault();
					var tab = document.getElementById('tab-bw_booking');
					if(!tab) {
						return;
					}
					if(tab.style.display === 'none') {
						var tabs = document.querySelectorAll('[href="#tab-bw_booking"]');
						if(tabs.length > 0) {
							tabs[0].click();
							setTimeout(function() {
								tab.scrollIntoView({behavior: 'smooth'});
							}, 250);
						}
					} else {
						tab.scrollIntoView({behavior: 'smooth'});
					}
				});
			});
		</script>
		<a href="#bw_wc_calendar" class="button alt"><?php echo esc_html($product->single_add_to_cart_text()); ?></a>
		<?php
	}

	/**
	 * Remove `bw_booking` products from cart if their associated `bw_event` doesn't exist.
	 */
	public function check_cart_items() {
		$cart = WC()->cart;
		foreach($cart->get_cart() as $cart_item_key => $item) {
			if($item['data']->get_type() === 'bw_booking' && !$item['data']->get_event_from($item)) {
				$cart->set_quantity($cart_item_key, 0);
				wc_add_notice(__('An expired booking was removed from the cart.', 'booking-weir'), 'error');
			}
		}
	}

	/**
	 * Display event info for `bw_booking` products on Cart and Checkout pages.
	 */
	public function set_item_data(array $item_data, array $cart_item) {
		if($cart_item['data']->get_type() !== 'bw_booking' || !$event = $cart_item['data']->get_event_from($cart_item)) {
			return $item_data;
		}
		$item_data[] = [
			'key' => $event->get_calendar()->get_name(),
			'value' => $event->get_date_formatted(),
		];
		if($event->is_child_of_bookable_event()) {
			$item_data[] = [
				'key' => __('Event', 'booking-weir'),
				'value' => $event->get_bookable_event_title(),
			];
		}
		if($event->is_service()) {
			$item_data[] = [
				'key' => __('Service', 'booking-weir'),
				'value' => $event->get_service_name(),
			];
		}
		if($event->is_in_named_slot()) {
			$item_data[] = [
				'key' => __('Slot', 'booking-weir'),
				'value' => $event->get_slot_title(),
			];
		}
		if($event->has_extras()) {
			$item_data[] = [
				'key' => __('Extras', 'booking-weir'),
				'value' => $event->get_extras_formatted('flat'),
			];
		}
		return apply_filters('bw_wc_get_item_data', $item_data, $event, $cart_item);
	}

	/**
	 * Display event info for `bw_booking` products on:
	 * - Edit order page
	 * - Order received page
	 */
	public function format_meta_data(array $formatted_meta, $item) {
		if(!$item instanceof WC_Order_Item_Product) {
			return $formatted_meta;
		}
		$product = $item->get_product();
		if(!$product instanceof Product) {
			return $formatted_meta;
		}
		if(!$event = $product->get_event_from($item)) {
			foreach($formatted_meta as $index => $meta) {
				if($meta->key === 'bw_event_id') {
					$formatted_meta[$index]->display_key = __('Event', 'booking-weir');
					$formatted_meta[$index]->display_value = sprintf('<strong style="color:red">%s</strong>', __('Deleted', 'booking-weir'));
				}
			}
			return $formatted_meta;
		}
		$order = wc_get_order($item->get_order_id());
		/**
		 * Sync with order with event when it's been attached manually
		 * by adding `bw_event_id` meta to a `bw_booking` order item.
		 */
		if(!$event->get_order_id()) {
			$event->attach_order($item->get_order_id(), false);
		}
		/**
		 * Display event info in meta.
		 */
		foreach($formatted_meta as $index => $meta) {
			if($meta->key === 'bw_event_id') {
				if(is_admin()) {
					$formatted_meta[$index]->display_key = __('Event', 'booking-weir');
					$formatted_meta[$index]->display_value = '<a href="' . esc_url($event->get_admin_url()) . '" target="_blank" rel="noopener noreferrer">#' . esc_html($meta->value) . '</a> (' . wc_price($event->get_price()) . ')';
					if(wc_format_decimal($item->get_subtotal()) === '0' && $event->get_price() > 0) {
						if($order->is_editable()) {
							$formatted_meta[$index]->display_value .= ' ' . sprintf(
								esc_html__('Click "%s" to apply event price.', 'booking-weir'),
								__('Recalculate', 'woocommerce')
							);
						}
					}
					// if($event->get_price() != $item->get_subtotal()) {
					// 	$formatted_meta[$index]->display_value .= '<br>' . sprintf(
					// 		esc_html__('Event price has changed from %1$s to %2$s in the calendar.', 'booking-weir'),
					// 		wc_price((float)$item->get_subtotal()),
					// 		wc_price($event->get_price())
					// 	);
					// 	if($order->is_editable()) {
					// 		$formatted_meta[$index]->display_value .= ' ' . sprintf(
					// 			esc_html__('Click "%s" to apply the new price.', 'booking-weir'),
					// 			__('Recalculate', 'woocommerce')
					// 		);
					// 	}
					// }
				} else {
					unset($formatted_meta[$index]);
					$formatted_meta[] = (object)[
						'key' => 'bw_booking_info',
						'value' => $event->get_id(),
						'display_key' => $event->get_calendar()->get_name(),
						'display_value' => $event->get_date_formatted(),
					];
					if($event->is_child_of_bookable_event()) {
						$formatted_meta[] = (object)[
							'key' => 'bw_bookable_event_title',
							'value' => $event->get_id(),
							'display_key' => __('Event', 'booking-weir'),
							'display_value' => $event->get_bookable_event_title(),
						];
					}
					if($event->is_service()) {
						$formatted_meta[] = (object)[
							'key' => 'bw_service_name',
							'value' => $event->get_id(),
							'display_key' => __('Service', 'booking-weir'),
							'display_value' => $event->get_service_name(),
						];
					}
					if($event->is_in_named_slot()) {
						$formatted_meta[] = (object)[
							'key' => 'bw_slot_name',
							'value' => $event->get_id(),
							'display_key' => __('Slot', 'booking-weir'),
							'display_value' => $event->get_slot_title(),
						];
					}
					if($event->has_extras()) {
						$formatted_meta[] = (object)[
							'key' => 'bw_booking_extras',
							'value' => $event->get_id(),
							'display_key' => __('Extras', 'booking-weir'),
							'display_value' => $event->get_extras_formatted('flat'),
						];
					}
				}
			}
		}
		return $formatted_meta;
	}

	/**
	 * Assign event's price to `bw_booking` products in cart.
	 */
	public function set_price(WC_Cart $cart) {
		if(is_admin() && !wp_doing_ajax()) {
			return;
		}

		foreach($cart->get_cart() as $item) {
			if($item['data']->get_type() !== 'bw_booking') {
				continue;
			}
			if($event = $item['data']->get_event_from($item)) {
				$price = wc_format_decimal($event->get_price());
				if($item['data']->get_price() !== $price) {
					$item['data']->set_price($price);
				}
			}
		}
	}

	/**
	 * Update order item price if the event price has changed when hitting `Recalculate` on WC edit order page.
	 */
	public function update_price(WC_Order_Item_Product $item) {
		$action = isset($_POST['action']) ? sanitize_key(wp_unslash($_POST['action'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if($action !== 'woocommerce_calc_line_taxes') {
			return;
		}
		$product = $item->get_product();
		if(!$product instanceof Product) {
			return;
		}
		if(!$event = $product->get_event_from($item)) {
			return;
		}
		if(!$event instanceof Event) {
			return;
		}

		/**
		 * Set event price when it's 0 in the order (event has been manually attached to the order).
		 */
		if(wc_format_decimal($item->get_subtotal()) === '0' && $event->get_price() > 0) {
			$discount = (float)$item->get_subtotal() - (float)$item->get_total();
			/**
			 * Set price before discounts.
			 */
			$item->set_subtotal((string)$event->get_price());
			/**
			 * Keep previous discount if there was one.
			 */
			$total = (float)$item->get_subtotal() - $discount;
			$item->set_total((string)$total);
		}
		// if(wc_format_decimal($event->get_price()) !== wc_format_decimal($item->get_subtotal())) {
		// 	$discount = (float)$item->get_subtotal() - (float)$item->get_total();
		// 	/**
		// 	 * Set price before discounts.
		// 	 */
		// 	$item->set_subtotal((string)$event->get_price());
		// 	/**
		// 	 * Keep previous discount if there was one.
		// 	 */
		// 	$total = (float)$item->get_subtotal() - $discount;
		// 	$item->set_total((string)$total);
		// }
	}

	/**
	 * Delete event when removing `bw_booking` product from cart.
	 */
	public function delete_cart_item_event(string $cart_item_key, WC_Cart $cart) {
		$item = $cart->get_cart_item($cart_item_key);
		if($item['data']->get_type() !== 'bw_booking') {
			return;
		}
		if($event = $item['data']->get_event_from($item)) {
			if($event->get_status() === 'cart') {
				$event->delete_permanently();
			}
		}
	}

	/**
	 * Delete event when `bw_booking` product was automatically removed from cart.
	 */
	public function delete_expired_cart_item_event($key, $values) {
		$product = wc_get_product($values['product_id']);
		if(!$product || $product->get_type() !== 'bw_booking') {
			return;
		}
		if($event = $product->get_event_from($values)) { // @phpstan-ignore-line
			if($event->get_status() === 'cart') {
				$event->delete_permanently();
			}
		}
	}

	/**
	 * Delete all events in cart.
	 */
	public function delete_events_in_cart() {
		foreach(WC()->cart->get_cart() as $item) {
			if($item['data']->get_type() === 'bw_booking' && $event = $item['data']->get_event_from($item)) {
				if($event->get_status() === 'cart') {
					$event->delete_permanently();
				}
			}
		}
	}

	/**
	 * Detach event when it's deleted from the order.
	 */
	public function detach_event($item_id) {
		$item_id = absint($item_id);
		if(!$item_id) {
			return;
		}

		$event_id = absint(wc_get_order_item_meta($item_id, 'bw_event_id', true));
		if(!$event_id) {
			return;
		}

		$event = $this->context->get('event-factory')->create($event_id);
		if($event->exists()) {
			$event->detach_order(false);
		}
	}

	/**
	 * After an order has been placed update it's events with billing data.
	 */
	public function update_event_meta(int $order_id, array $data) {
		$order = wc_get_order($order_id);
		foreach($order->get_items() as $item) {
			if(!$item instanceof WC_Order_Item_Product) {
				continue;
			}
			if(!$product = $item->get_product()) {
				continue;
			}
			if($product instanceof Product && $event = $product->get_event_from($item)) {
				if($event->get_status() === 'cart') {
					$event->set_order_id($order_id);
					$event->set_status('wc');
					if(isset($data['billing_first_name'])) {
						$event->set_first_name($data['billing_first_name']);
					}
					if(isset($data['billing_last_name'])) {
						$event->set_last_name($data['billing_last_name']);
					}
					if(isset($data['billing_email'])) {
						$event->set_email($data['billing_email']);
					}
					if(isset($data['billing_phone'])) {
						$event->set_phone($data['billing_phone']);
					}
					if(isset($data['order_comments'])) {
						$event->set_additional_info($data['order_comments']);
					}
				}
			}
		}
	}

	/**
	 * Sync order meta to booking events.
	 */
	public function sync_event_meta($meta_id, $object_id, $meta_key, $meta_value) {
		if(!in_array($meta_key, [
			'_billing_first_name',
			'_billing_last_name',
			'_billing_email',
			'_billing_phone',
		]) || !$order = wc_get_order($object_id)) {
			return;
		}
		foreach($order->get_items() as $item) {
			if(!$item instanceof WC_Order_Item_Product) {
				continue;
			}
			$product = $item->get_product();
			if($product instanceof Product && $event = $product->get_event_from($item)) {
				if($event->get_status() !== 'archived') {
					switch($meta_key) {
						case '_billing_first_name':
							$event->set_first_name($meta_value);
						break;
						case '_billing_last_name':
							$event->set_last_name($meta_value);
						break;
						case '_billing_email':
							$event->set_email($meta_value);
						break;
						case '_billing_phone':
							$event->set_phone($meta_value);
						break;
					}
				}
			}
		}
	}

	/**
	 * Sync order comments (post excerpt) to booking events' additional info.
	 */
	public function sync_order_comments($id, $post) {
		if(!$order = wc_get_order($id)) {
			return;
		}
		foreach($order->get_items() as $item) {
			if(!$item instanceof WC_Order_Item_Product) {
				continue;
			}
			$product = $item->get_product();
			if($product instanceof Product && $event = $product->get_event_from($item)) {
				if($event->get_status() !== 'archived') {
					$event->set_additional_info($post->post_excerpt);
				}
			}
		}
	}

	/**
	 * Use event price for cart item.
	 */
	public function use_price($price, $cart_item, $cart_item_key) {
		if($cart_item['data']->get_type() === 'bw_booking' && $event = $cart_item['data']->get_event_from($cart_item)) {
			return wc_price($event->get_price());
		}
		return $price;
	}
}
