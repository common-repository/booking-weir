<?php

namespace wsd\bw\core\admin;

use wsd\bw\Context;
use wsd\bw\core\events\EventFactory;
use wsd\bw\core\events\EventPostType;
use WP_Query;

class EventList {

	/** @var Context */
	protected $context;

	/** @var EventPostType */
	protected $event_post_type;

	/** @var EventFactory */
	protected $event_factory;

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context, EventPostType $event_post_type) {
		$this->context = $context;
		$this->event_post_type = $event_post_type;
		$this->event_factory = $this->context->get('event-factory');
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_filter('manage_edit-' . $this->event_post_type::SLUG . '_columns', [$this, 'get_columns']);
		add_filter('manage_edit-' . $this->event_post_type::SLUG . '_sortable_columns', [$this, 'get_sortable_columns']);
		add_filter('manage_' . $this->event_post_type::SLUG . '_posts_custom_column', [$this, 'render_cell'], 10, 2);
		add_filter('bulk_actions-edit-' . $this->event_post_type::SLUG, [$this, 'add_bulk_actions']);
		add_filter('handle_bulk_actions-edit-' . $this->event_post_type::SLUG, [$this, 'handle_bulk_actions'], 10, 3);
		add_action('pre_get_posts', [$this, 'apply_order']);
		add_action('restrict_manage_posts', [$this, 'show_filters']);
		add_filter('parse_query', [$this, 'apply_filters']);
		add_action('admin_footer', [$this, 'add_inline_code']);
	}

	public function get_columns() {
		return [
			'cb' => 'cb',
			'bw_column_calendar_id' => esc_html__('Calendar', 'booking-weir'),
			'bw_column_type' => esc_html__('Type', 'booking-weir'),
			'bw_column_name' => esc_html__('Name', 'booking-weir'),
			'bw_column_status' => esc_html__('Status', 'booking-weir'),
			'bw_column_event_date' => esc_html_x('Date', 'Title (should start with capital letter)', 'booking-weir'),
			'bw_column_price' => esc_html__('Price', 'booking-weir'),
			'actions' => esc_html__('Actions', 'booking-weir'),
		];
	}

	public function get_sortable_columns() {
		return [
			'bw_column_calendar_id' => ['bw_column_calendar_id', true],
			'bw_column_type' => ['bw_column_type', true],
			'bw_column_name' => ['bw_column_name', true],
			'bw_column_status' => ['bw_column_status', true],
			'bw_column_event_date' => ['bw_column_event_date', true],
			'bw_column_price' => ['bw_column_price', true],
		];
	}

	public function render_cell($column_id, $event_id) {
		$event = $this->event_factory->create($event_id);
		$calendar = $event->get_calendar();
		switch($column_id) {
			case 'bw_column_calendar_id':
				if($calendar) {
					echo esc_html($calendar->get_name());
				} else {
					printf(
						'%s<br><span style="color:red">%s</span>',
						esc_html($event->get_calendar_id()),
						esc_html__('Calendar not found.', 'booking-weir')
					);
					/**
					 * Add link to filter events by missing calendar.
					 */
					$calendar_id = isset($_GET['bw_calendar_id'])
						? $this->context->get('sanitizer')->sanitize_id(wp_unslash($_GET['bw_calendar_id'])) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						: false;
					if($calendar_id !== $event->get_calendar_id()) {
						printf(
							'<br><a href="%s" title="%s">%s</a>',
							esc_url(add_query_arg('bw_calendar_id', $event->get_calendar_id())),
							esc_html__('Apply a filter showing all events from missing calendar', 'booking-weir'),
							esc_html__('Show all', 'booking-weir')
						);
					}
				}
			break;
			case 'bw_column_type':
				echo esc_html($event->get_type_name());
			break;
			case 'bw_column_name':
				switch($event->get_type()) {
					case 'default':
						echo esc_html(get_the_title());
					break;
					default:
						echo esc_html($event->get_name());
				}
			break;
			case 'bw_column_status':
				echo esc_html($event->get_status_text());
			break;
			case 'bw_column_event_date':
				echo esc_html($event->get_date_formatted());
			break;
			case 'bw_column_price':
				echo esc_html($event->get_price_formatted());
			break;
			case 'actions':
				$actions = [];
				if(get_post_status($event_id) === 'publish' && $calendar) {
					$actions[] = sprintf(
						'<span class="edit"><a href="%1$s" aria-label="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a></span>',
						esc_url($event->get_admin_url()),
						esc_html__('View in admin calendar', 'booking-weir'),
						esc_html__('View', 'booking-weir')
					);
					foreach($event->get_actions() as $action => $label) {
						$actions[] = sprintf(
							'<span class="edit"><a href="%1$s" aria-label="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a></span>',
							esc_url($event->get_action_url($action)),
							esc_html($label),
							esc_html($label)
						);
					}
				}
				if(current_user_can('edit_post', $event_id) && get_post_status($event_id) !== 'trash') {
					$actions[] = sprintf(
						'<span class="edit"><a href="%s" aria-label="%s">%s</a></span>',
						get_edit_post_link($event_id),
						/* translators: %s: post title */
						esc_attr(sprintf(__('Edit &#8220;%s&#8221;', 'booking-weir'), get_the_title($event_id))),
						__('Edit', 'booking-weir')
					);
				}
				if(current_user_can('delete_post', $event_id)) {
					$post_type_object = get_post_type_object(get_post_type($event_id));
					if(get_post_status($event_id) === 'trash') {
						$actions[] = sprintf(
							'<span class="trash"><a href="%s" aria-label="%s">%s</a></span>',
							wp_nonce_url(admin_url(sprintf($post_type_object->_edit_link . '&amp;action=untrash', $event_id)), 'untrash-post_' . $event_id),
							/* translators: %s: post title */
							esc_attr(sprintf(__('Restore &#8220;%s&#8221; from the Trash', 'booking-weir'), get_the_title($event_id))),
							__('Restore', 'booking-weir')
						);
					} elseif(EMPTY_TRASH_DAYS) {
						$actions[] = sprintf(
							'<span class="trash"><a href="%s" class="submitdelete" aria-label="%s">%s</a></span>',
							get_delete_post_link($event_id),
							/* translators: %s: post title */
							esc_attr(sprintf(__('Move &#8220;%s&#8221; to the Trash', 'booking-weir'), get_the_title($event_id))),
							_x('Trash', 'verb', 'booking-weir')
						);
					}
				}
				printf(
					'<div class="row-actions" style="left:0">%s</div>',
					implode(' | ', $actions) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			break;
			default:
				echo esc_html(get_post_meta($event_id, $column_id, true));
		}
	}

	public function add_bulk_actions($actions) {
		return array_merge([
			'bw_action_move' => esc_html__('Move to calendar', 'booking-weir'),
		], $actions);
	}

	public function handle_bulk_actions($redirect_to, $action, $ids) {
		switch($action) {
			case 'bw_action_move':
				$target = isset($_REQUEST['bw_calendar_select']) && !empty($_REQUEST['bw_calendar_select']) ? $this->context->get('sanitizer')->sanitize_id(wp_unslash($_REQUEST['bw_calendar_select'])) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$calendars = $this->context->get('calendars');
				if(!$calendars->calendar_exists($target)) {
					break;
				}
				$calendar = $calendars->get_calendar($target);
				foreach($ids as $id) {
					$event = $this->context->get('event-factory')->create((int)$id);
					if($event->exists()) {
						$event->set_calendar_id($calendar->get_id());
					}
				}
			break;
		}
		return $redirect_to;
	}

	/**
	 * Apply order for sortable columns.
	 *
	 * @param WP_Query $query
	 */
	public function apply_order(WP_Query $query) {
		if(!$this->is_event_list_page()) {
			return;
		}
		if(!isset($_GET['orderby'])) {
			return;
		}
		foreach([
			'bw_column_calendar_id' => 'bw_calendar_id',
			'bw_column_type' => 'bw_type',
			'bw_column_name' => 'bw_last_name',
			'bw_column_status' => 'bw_status',
			'bw_column_event_date' => 'bw_start_timestamp',
			'bw_column_price' => 'bw_price',
		] as $column => $meta) {
			if($_GET['orderby'] === $column) {
				$query->set('meta_key', $meta);
				$query->set('orderby', 'meta_value');
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$query->set('order', isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC');
			}
		}
	}

	/**
	 * Show filters on event list page.
	 */
	public function show_filters() {
		if(!$this->is_event_list_page()) {
			return;
		}

		$sanitizer = $this->context->get('sanitizer');

		$calendar_id = isset($_GET['bw_calendar_id']) ? $sanitizer->sanitize_id(wp_unslash($_GET['bw_calendar_id'])) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		printf(
			'<select name="bw_calendar_id">%s%s</select>',
			'<option value="">' . esc_html__('All calendars', 'booking-weir') . '</option>',
			implode('', array_map(function($calendar) use ($calendar_id) { // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				return sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr($calendar->get_id()),
					selected($calendar->get_id(), $calendar_id, false),
					esc_html($calendar->get_name())
				);
			}, $this->context->get('calendars')->get_calendars()))
		);

		$type = isset($_GET['bw_type']) ? $sanitizer->sanitize_event_type(wp_unslash($_GET['bw_type'])) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		printf(
			'<select name="bw_type">%s%s</select>',
			'<option value="">' . esc_html__('All types', 'booking-weir') . '</option>',
			implode('', array_map(function($event_type) use ($type) { // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				if(!$event_type['creatable']) {
					return;
				}
				return sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr($event_type['value']),
					selected($event_type['value'], $type, false),
					esc_html($event_type['text'])
				);
			}, $this->context->get('event-types')->get()))
		);

		$status = isset($_GET['bw_status']) ? $sanitizer->sanitize_booking_status(wp_unslash($_GET['bw_status'])) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		printf(
			'<select name="bw_status">%s%s</select>',
			'<option value="">' . esc_html__('All statuses', 'booking-weir') . '</option>',
			implode('', array_map(function($booking_status) use ($status) { // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				if(isset($booking_status['wc']) && $booking_status['wc'] && !class_exists('WooCommerce')) {
					return '';
				}
				return sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr($booking_status['value']),
					selected($booking_status['value'], $status, false),
					esc_html($booking_status['text'])
				);
			}, $this->context->get('booking-statuses')->get()))
		);
	}

	/**
	 * Apply filters on event list page.
	 *
	 * @param WP_Query $query
	 */
	public function apply_filters(WP_Query $query) {
		if(!$this->is_event_list_page()) {
			return;
		}
		$filters = [];
		foreach(['bw_calendar_id', 'bw_type', 'bw_status'] as $filter) {
			if(isset($_GET[$filter]) && !empty($_GET[$filter])) {
				if(!$value = sanitize_meta($filter, wp_unslash($_GET[$filter]), $this->event_post_type::SLUG)) {
					continue;
				}
				$filters[] = [
					[
						'key' => $filter,
						'compare' => '=',
						'value' => $value,
					],
				];
			}
		}
		if(count($filters) > 1) {
			$filters['relation'] = 'AND';
		}
		if(count($filters) > 0) {
			$query->query_vars['meta_query'] = $filters;
		}
		return $query;
	}

	/**
	 * Additional styles and/or scripts for event list page.
	 */
	public function add_inline_code() {
		if(!$this->is_event_list_page()) {
			return;
		}

		$placeholder = esc_attr__('Select a calendar...', 'booking-weir');
		$js = <<<JS
jQuery(function($) {
	$('select[id*="bulk-action-selector"]').on('change', function() {
		var val = $(this).val();
		if(val === 'bw_action_move') {
			var select = $('select[name="bw_calendar_id"]').clone();
			select.attr('name', 'bw_calendar_select');
			select.find('option:first').html('{$placeholder}');
			$(this).after(select);
		} else {
			$('select[name="bw_calendar_select"]').remove();
		}
	});
});
JS;
		echo '<script>' . $js . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	protected function is_event_list_page() {
		global $pagenow;
		global $typenow;
		return is_admin() && $pagenow === 'edit.php' && $typenow === $this->event_post_type::SLUG;
	}
}
