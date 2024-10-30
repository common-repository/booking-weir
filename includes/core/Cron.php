<?php

namespace wsd\bw\core;

use wsd\bw\Context;
use WP_Query;

/**
 * Cron class.
 */
final class Cron {

	/** @var Context */
	protected $context;

	/** @var Logger */
	protected $logger;

	/**
	 * CRON tasks.
	 *
	 * @var array
	 */
	protected $tasks;

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context, Logger $logger) {
		$this->context = $context;
		$this->logger = $logger;
		$this->tasks = [];
	}

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		$this->tasks = [
			[
				'id' => 'bw_cron_reminder_email',
				'interval' => 'hourly',
				'callback' => [$this, 'send_reminder_emails'],
			],
			[
				'id' => 'bw_cron_check_cart',
				'interval' => 'bw_half_hour',
				'callback' => [$this, 'check_cart'],
				'condition' => class_exists('WooCommerce'),
			],
		];

		add_filter('cron_schedules', [$this, 'register_schedules']);
		add_action('init', [$this, 'register_tasks']);
		add_action('bw_deactivate', [$this, 'deactivate_tasks']);

		$this->hook_tasks();
	}

	public function register_schedules($schedules) {
		$schedules['bw_half_hour'] = [
			'interval' => 60 * 30,
			'display' => esc_html__('Every 30 minutes', 'booking-weir'),
		];
		return $schedules;
	}

	public function register_tasks() {
		foreach($this->tasks as $task) {
			if((!isset($task['condition']) || $task['condition']) && !wp_next_scheduled($task['id'])) {
				wp_schedule_event(time(), $task['interval'], $task['id']);
				$this->logger->log(['scheduled' => $task['id']], 'cron');
			}
		}
	}

	public function hook_tasks() {
		foreach($this->tasks as $task) {
			if(!isset($task['condition']) || $task['condition']) {
				add_action($task['id'], $task['callback']);
			}
		}
	}

	public function deactivate_tasks() {
		foreach($this->tasks as $task) {
			if($timestamp = wp_next_scheduled($task['id'])) {
				wp_unschedule_event($timestamp, $task['id']);
				$this->logger->log(['deactivated' => $task['id']], 'cron');
			}
		}
	}

	/**
	 * Send reminder emails.
	 */
	public function send_reminder_emails() {
		$sent = $this->context->get('email')->send_reminder_emails();
		if($sent > 0) {
			$this->logger->log([
				'task' => 'send_reminder_emails',
				'output' => sprintf('attempted to send %d emails', $sent),
			], 'cron');
		}
	}

	/**
	 * Delete events from cart after 30min.
	 */
	public function check_cart() {
		$deleted = 0;
		$wp_query = new WP_Query([
			'post_type' => $this->context->get('event-post-type')::SLUG,
			'posts_per_page' => -1,
			'meta_key' => 'bw_status',
			'meta_value' => 'cart',
		]);
		if($wp_query->have_posts()) {
			while($wp_query->have_posts()) {
				$wp_query->the_post();
				$id = get_the_ID();
				$calendar_id = get_post_meta($id, 'bw_calendar_id', true);
				if($calendar = $this->context->get('calendars')->get_calendar($calendar_id)) {
					if($event = $calendar->get_event($id)) {
						if($event->get_created_ago_minutes() > (int)apply_filters('bw_cart_expiration_minutes', 30)) {
							$event->delete_permanently();
							$deleted++;
						}
					}
				}
			}
			wp_reset_postdata();
		}
		if($deleted > 0) {
			$this->logger->log([
				'task' => 'check_cart',
				'output' => sprintf('deleted %d expired events', $deleted),
			], 'cron');
		}
	}
}
