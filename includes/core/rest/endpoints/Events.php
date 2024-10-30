<?php

namespace wsd\bw\core\rest\endpoints;

use wsd\bw\Context;
use WP_REST_Posts_Controller;
use WP_REST_Post_Meta_Fields;
use WP_REST_Request;
use WP_Error;

/**
 * Fork `WP_REST_Posts_Controller` for events REST functionality.
 *
 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php
 */
class Events extends WP_REST_Posts_Controller {

	/** @var Context */
	protected $context;

	/**
	 * REST api base.
	 */
	const REST_BASE = 'events';

	/**
	 * Constructor.
	 *
	 * @param string $post_type Post type.
	 */
	public function __construct($post_type) {
		$this->context = apply_filters('bw_context', null);
		$this->post_type = $post_type;
		$this->namespace = $this->context->get('rest')->namespace;
		$obj = get_post_type_object($this->post_type);
		$this->rest_base = !empty($obj->rest_base) ? $obj->rest_base : $obj->name;
		$this->meta = new WP_REST_Post_Meta_Fields($this->post_type);
	}

	/**
	 * Checks if a given request has access to create a post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to create items, WP_Error object otherwise.
	 */
	public function create_item_permissions_check($request) {
		if($this->context->is_elevated()) {
			return true;
		}

		/**
		 * Allow anyone to create, so that bookings can be made.
		 */
		return $this->check_nonce($request);
	}

	/**
	 * Checks if a given request has access to read posts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check($request) {
		$permissions = $this->check_permissions($request);
		if(is_wp_error($permissions)) {
			return $permissions;
		}
		return parent::get_items_permissions_check($request);
	}

	/**
	 * Checks if a given request has access to read a post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has read access for the item, WP_Error object otherwise.
	 */
	public function get_item_permissions_check($request) {
		$permissions = $this->check_permissions($request);
		if(is_wp_error($permissions)) {
			return $permissions;
		}
		return parent::get_item_permissions_check($request);
	}

	/**
	 * Checks if a given request has access to update a post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
	 */
	public function update_item_permissions_check($request) {
		$permissions = $this->check_permissions($request);
		if(is_wp_error($permissions)) {
			return $permissions;
		}
		return parent::update_item_permissions_check($request);
	}

	/**
	 * Checks if a given request has access to delete a post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check($request) {
		$permissions = $this->check_permissions($request);
		if(is_wp_error($permissions)) {
			return $permissions;
		}
		return parent::delete_item_permissions_check($request);
	}

	/**
	 * Determines validity and normalizes the given status parameter.
	 *
	 * @param string $post_status Post status.
	 * @param object $post_type Post type.
	 * @return string|WP_Error Post status or WP_Error if lacking the proper permission.
	 */
	protected function handle_status_param($post_status, $post_type) {
		/**
		 * Allow anyone to publish, so that bookings can be made.
		 */
		if($post_type->name === $this->post_type && $post_status === 'publish') {
			return $post_status;
		}
		return parent::handle_status_param($post_status, $post_type);
	}

	/**
	 * Permissions for admin operations.
	 *
	 * @param WP_REST_Request $request
	 * @return true|WP_Error
	 */
	public function check_permissions($request) {
		if(!$this->context->is_elevated()) {
			return new WP_Error('bw_not_elevated', __('User not elevated.', 'booking-weir'), ['status' => 403]);
		}
		return true;
	}

	/**
	 * Nonce check.
	 *
	 * @param WP_REST_Request $request
	 * @return true|WP_Error
	 */
	public function check_nonce($request) {
		if(!$nonce = $request->get_param('bw_nonce')) {
			return new WP_Error('bw_no_nonce', __('No nonce.', 'booking-weir'), ['status' => 400]);
		}
		if(!$calendar_id = $request->get_param('bw_calendar_id')) {
			return new WP_Error('bw_calendar_id_missing', __('Calendar ID missing.', 'booking-weir'), ['status' => 400]);
		}
		if(!wp_verify_nonce($nonce, $calendar_id)) {
			return new WP_Error('bw_invalid_nonce', __('Invalid session.', 'booking-weir'), ['status' => 400]);
		}
		return true;
	}
}
