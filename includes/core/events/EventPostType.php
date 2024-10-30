<?php

namespace wsd\bw\core\events;

use  wsd\bw\Context ;
use  wsd\bw\core\rest\endpoints\Events ;
use  wsd\bw\config\Config ;
use  wsd\bw\util\helpers ;
use  wsd\bw\util\datetime ;
use  DateTimeImmutable ;
use  DateTimeZone ;
use  DateInterval ;
use  WP_Error ;
use  WP_REST_Request ;
use  WP_Post ;
use  stdClass ;
/**
 * EventPostType class.
 */
final class EventPostType
{
    /** @var Context */
    protected  $context ;
    /** @var Config */
    protected  $event_meta ;
    /** @var EventFactory */
    protected  $event_factory ;
    /**
     * Post type slug.
     */
    const  SLUG = 'bw_event' ;
    /**
     * GET variables for link API.
     */
    const  ID = 'bw_event_id' ;
    const  ACTION = 'bw_event_action' ;
    const  NONCE = 'bw_nonce' ;
    /**
     * @param Context $context
     */
    public function __construct( Context $context, Config $event_meta, EventFactory $event_factory )
    {
        $this->context = $context;
        $this->event_meta = $event_meta;
        $this->event_factory = $event_factory;
    }
    
    /**
     * Registers functionality through WordPress hooks.
     */
    public function register()
    {
        add_action( 'init', [ $this, 'register_event_post_type' ] );
        /**
         * REST API.
         */
        require_once $this->context->path( 'includes/core/rest/endpoints/Events.php' );
        add_filter( 'rest_bw_event_item_schema', [ $this, 'rest_schema' ] );
        add_filter(
            'rest_pre_insert_bw_event',
            [ $this, 'rest_pre_insert' ],
            10,
            2
        );
        add_filter(
            'rest_insert_bw_event',
            [ $this, 'rest_insert' ],
            10,
            3
        );
        add_filter(
            'rest_after_insert_bw_event',
            [ $this, 'rest_after_insert' ],
            10,
            3
        );
        add_filter(
            'rest_prepare_bw_event',
            [ $this, 'rest_prepare' ],
            10,
            3
        );
        add_filter(
            'rest_bw_event_query',
            [ $this, 'rest_query' ],
            10,
            2
        );
        add_filter(
            'rest_bw_event_query',
            [ $this, 'rest_query_filter' ],
            10,
            2
        );
        add_filter( 'rest_post_search_query', [ $this, 'rest_exclude_from_search' ] );
        /**
         * Link API.
         */
        add_action( 'admin_init', [ $this, 'link_api' ] );
        /**
         * Post meta.
         */
        add_action(
            'updated_postmeta',
            [ $this, 'updated_postmeta' ],
            10,
            4
        );
    }
    
    /**
     * Event post type settings for `register_post_type`.
     *
     * @return array
     */
    protected function get_post_type_config()
    {
        return apply_filters( 'bw_event_post_type', [
            'description'           => esc_html__( 'Booking Weir events.', 'booking-weir' ),
            'labels'                => [
            'name'                  => _x( 'Events', 'Event post type', 'booking-weir' ),
            'singular_name'         => _x( 'Event', 'Event post type', 'booking-weir' ),
            'menu_name'             => _x( 'Events', 'Event post type', 'booking-weir' ),
            'name_admin_bar'        => _x( 'Event', 'Event post type', 'booking-weir' ),
            'add_new'               => _x( 'Add New', 'Event post type', 'booking-weir' ),
            'add_new_item'          => _x( 'Add New Event', 'Event post type', 'booking-weir' ),
            'edit_item'             => _x( 'Edit Event', 'Event post type', 'booking-weir' ),
            'new_item'              => _x( 'New Event', 'Event post type', 'booking-weir' ),
            'view_item'             => _x( 'View Event', 'Event post type', 'booking-weir' ),
            'search_items'          => _x( 'Search Events', 'Event post type', 'booking-weir' ),
            'not_found'             => _x( 'No Events found', 'Event post type', 'booking-weir' ),
            'not_found_in_trash'    => _x( 'No Events found in trash', 'Event post type', 'booking-weir' ),
            'all_items'             => _x( 'Events', 'Event post type', 'booking-weir' ),
            'featured_image'        => _x( 'Event Image', 'Event post type', 'booking-weir' ),
            'set_featured_image'    => _x( 'Set Event image', 'Event post type', 'booking-weir' ),
            'remove_featured_image' => _x( 'Remove Event image', 'Event post type', 'booking-weir' ),
            'use_featured_image'    => _x( 'Use as Event image', 'Event post type', 'booking-weir' ),
            'insert_into_item'      => _x( 'Insert into Event', 'Event post type', 'booking-weir' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this Event', 'Event post type', 'booking-weir' ),
            'views'                 => _x( 'Filter Events list', 'Event post type', 'booking-weir' ),
            'pagination'            => _x( 'Events list navigation', 'Event post type', 'booking-weir' ),
            'list'                  => _x( 'Events list', 'Event post type', 'booking-weir' ),
        ],
            'show_in_menu'          => $this->context->get( 'admin' )->get_menu_slug(),
            'public'                => $this->context->is_elevated(),
            'show_in_rest'          => true,
            'publicly_queryable'    => false,
            'menu_icon'             => 'dashicons-calendar-alt',
            'capability_type'       => 'post',
            'hierarchical'          => false,
            'has_archive'           => false,
            'query_var'             => false,
            'exclude_from_search'   => true,
            'show_in_nav_menus'     => false,
            'rewrite'               => false,
            'can_export'            => true,
            'supports'              => [
            'title',
            'excerpt',
            'custom-fields',
            'revisions'
        ],
            'rest_controller_class' => Events::class,
            'rest_base'             => Events::REST_BASE,
        ] );
    }
    
    /**
     * Register post type that stores all the events and associated meta.
     */
    public function register_event_post_type()
    {
        register_post_type( self::SLUG, $this->get_post_type_config() );
        foreach ( $this->get_meta_schema() as $meta_key => $args ) {
            register_meta( self::SLUG, $meta_key, $args );
            register_rest_field( self::SLUG, $meta_key, [
                'get_callback'    => function ( $event ) use( $meta_key, $args ) {
                return get_post_meta( $event['id'], $meta_key, $args['single'] );
            },
                'update_callback' => function ( $value, $event_obj ) use( $meta_key, $args ) {
                /**
                 * Validate value.
                 */
                
                if ( isset( $args['validate_callback'] ) && is_callable( $args['validate_callback'] ) ) {
                    $is_valid = $args['validate_callback']( $value );
                    if ( is_wp_error( $is_valid ) ) {
                        return $is_valid;
                    }
                    if ( !$is_valid ) {
                        return new WP_Error( 'bw_rest_update_event_meta_failed', sprintf( __( 'Invalid value for event meta: %s.', 'booking-weir' ), $meta_key ), [
                            'status' => 500,
                        ] );
                    }
                }
                
                /**
                 * Sanitize value.
                 */
                
                if ( isset( $args['sanitize_callback'] ) && is_callable( $args['sanitize_callback'] ) ) {
                    $value = $args['sanitize_callback']( $value );
                } else {
                    return new WP_Error( 'bw_rest_update_event_meta_failed', sprintf( __( 'Sanitize callback is required for: %s.', 'booking-weir' ), $meta_key ), [
                        'status' => 500,
                    ] );
                }
                
                /**
                 * Update value.
                 */
                $next_value = apply_filters( 'bw_event_meta_value', $value, $meta_key );
                
                if ( !update_post_meta( $event_obj->ID, $meta_key, $next_value ) ) {
                    /**
                     * `update_post_meta()` returns `false` when updating to same value,
                     * don't throw error unless something else went wrong.
                     */
                    $current_value = get_post_meta( $event_obj->ID, $meta_key, $args['single'] );
                    $is_same = ( $args['single'] ? $current_value == $next_value : count( array_diff( $current_value, $next_value ) ) === 0 );
                    if ( !$is_same ) {
                        return new WP_Error( 'bw_rest_update_event_meta_failed', sprintf( __( 'Failed updating event meta: %s.', 'booking-weir' ), $meta_key ), [
                            'status' => 500,
                        ] );
                    }
                }
                
                return true;
            },
                'schema'          => [
                'type'        => $args['type'],
                'description' => $args['description'],
            ],
            ] );
            /**
             * Keep revisions of meta changes.
             *
             * Currently needs a plugin active, but in a future
             * WordPress version it may work out of the box.
             *
             * @see https://wordpress.org/plugins/wp-post-meta-revisions
             * @see https://core.trac.wordpress.org/ticket/20564
             */
            add_filter( 'wp_post_revision_meta_keys', function ( $keys ) use( $meta_key ) {
                $keys[] = $meta_key;
                return $keys;
            } );
        }
    }
    
    /**
     * Event post type meta fields schema.
     *
     * @return array
     */
    public function get_meta_schema()
    {
        return $this->event_meta->get();
    }
    
    /**
     * Event post type schema.
     *
     * @param boolean $public
     * @return array
     */
    public function get_schema( $public = true )
    {
        $schema = array_merge( [
            'id'      => [
            'name'         => 'id',
            'type'         => 'integer',
            'show_in_rest' => false,
            'public'       => true,
        ],
            'title'   => [
            'name'         => 'title',
            'type'         => 'string',
            'show_in_rest' => true,
            'public'       => true,
        ],
            'excerpt' => [
            'name'         => 'excerpt',
            'type'         => 'string',
            'show_in_rest' => true,
            'public'       => true,
        ],
            'status'  => [
            'name'         => 'post_status',
            'type'         => 'string',
            'show_in_rest' => true,
            'public'       => false,
        ],
        ], $this->get_meta_schema() );
        $event_schema = [];
        foreach ( $schema as $key => $val ) {
            if ( $public && (!isset( $val['public'] ) || !$val['public']) ) {
                continue;
            }
            $event_schema[] = [
                'key'          => esc_attr( $key ),
                'name'         => esc_attr( $val['name'] ),
                'type'         => esc_attr( $val['type'] ),
                'show_in_rest' => esc_attr( $val['show_in_rest'] ),
            ];
        }
        return $event_schema;
    }
    
    /**
     * Filter REST API schema.
     *
     * @param array $schema
     * @return array
     */
    public function rest_schema( array $schema )
    {
        /**
         * Enable raw excerpt in `edit` context.
         */
        if ( isset( $schema['properties']['excerpt']['properties']['raw'] ) ) {
            $schema['properties']['excerpt']['properties']['raw']['context'] = [ 'view', 'edit' ];
        }
        return $schema;
    }
    
    /**
     * Filters a post before it is inserted via the REST API.
     *
     * @param stdClass $prepared_post An object representing a single post prepared for inserting or updating the database.
     * @param WP_REST_Request $request Request object.
     * @return stdClass|WP_Error
     */
    public function rest_pre_insert( $prepared_post, $request )
    {
        /**
         * Updating.
         */
        
        if ( $request->get_param( 'id' ) ) {
            $event = $this->event_factory->create( $request->get_param( 'id' ) );
            if ( !$event->exists() ) {
                return new WP_Error( 'bw_event_validation', __( 'Event not found.', 'booking-weir' ), [
                    'status' => 400,
                ] );
            }
            /**
             * Attach order.
             */
            
            if ( $request->get_param( 'bw_order_id' ) ) {
                $order_id = (int) $request->get_param( 'bw_order_id' );
                
                if ( $order_id === -1 ) {
                    $detached = $event->detach_order( $order_id );
                    if ( $detached !== true ) {
                        return new WP_Error( 'bw_event', esc_html( $detached ), [
                            'status' => 400,
                        ] );
                    }
                    $request->set_param( 'bw_order_id', '' );
                } else {
                    $attached = $event->attach_order( $order_id );
                    if ( $attached !== true ) {
                        return new WP_Error( 'bw_event', esc_html( $attached ), [
                            'status' => 400,
                        ] );
                    }
                }
            
            }
            
            return $prepared_post;
        }
        
        /**
         * Set event type.
         */
        if ( !$request->get_param( 'bw_type' ) ) {
            $request->set_param( 'bw_type', 'default' );
        }
        $event_type = $request->get_param( 'bw_type' );
        /**
         * Validate that event has a start time, end time and a destination calendar.
         */
        if ( !($calendar_id = $request->get_param( 'bw_calendar_id' )) ) {
            return new WP_Error( 'bw_event_validation', __( 'Calendar ID missing.', 'booking-weir' ), [
                'status' => 400,
            ] );
        }
        if ( !($start = $request->get_param( 'bw_start' )) ) {
            return new WP_Error( 'bw_event_validation', __( 'Start time missing.', 'booking-weir' ), [
                'status' => 400,
            ] );
        }
        if ( is_wp_error( $this->context->get( 'sanitizer' )->validate_datetime( $start ) ) ) {
            return $this->context->get( 'sanitizer' )->validate_datetime( $start );
        }
        if ( !($end = $request->get_param( 'bw_end' )) ) {
            return new WP_Error( 'bw_event_validation', __( 'End time missing.', 'booking-weir' ), [
                'status' => 400,
            ] );
        }
        if ( is_wp_error( $this->context->get( 'sanitizer' )->validate_datetime( $end ) ) ) {
            return $this->context->get( 'sanitizer' )->validate_datetime( $end );
        }
        if ( !($calendar = $this->context->get( 'calendars' )->get_calendar( $calendar_id )) ) {
            return new WP_Error( 'bw_event_validation', __( 'Calendar not found.', 'booking-weir' ), [
                'status' => 400,
            ] );
        }
        $utc_offset = $request->get_param( 'bw_utc_offset' );
        if ( !isset( $utc_offset ) || !is_numeric( $utc_offset ) ) {
            return new WP_Error( 'bw_event_validation', __( 'UTC offset missing.', 'booking-weir' ), [
                'status' => 400,
            ] );
        }
        if ( $utc_offset !== $calendar->get_utc_offset() ) {
            return new WP_Error( 'bw_event_validation', sprintf( __( 'UTC offset mismatch: %1$d - %2$d.', 'booking-weir' ), $utc_offset, $calendar->get_utc_offset() ), [
                'status' => 400,
            ] );
        }
        /**
         * Ensure default values are set based on type.
         */
        switch ( $event_type ) {
            case 'booking':
                $prepared_post->post_title = esc_html_x( 'Booking', 'Event type="booking" name', 'booking-weir' );
                if ( !$request->get_param( 'bw_status' ) ) {
                    $request->set_param( 'bw_status', 'awaiting' );
                }
                if ( $calendar->is_product() && $request->get_param( 'bw_status' ) !== 'cart' ) {
                    $request->set_param( 'bw_status', 'detached' );
                }
                if ( !in_array( $request->get_param( 'bw_status' ), [
                    'pending',
                    'awaiting',
                    'cart',
                    'detached'
                ] ) ) {
                    return new WP_Error( 'bw_event_validation', __( 'Invalid initial event status.', 'booking-weir' ), [
                        'status' => 400,
                    ] );
                }
                
                if ( !$request->get_param( 'bw_payment_type' ) ) {
                    $request->set_param( 'bw_payment_type', $calendar->get_default_payment_type_id() );
                } elseif ( !$calendar->has_payment_type( $request->get_param( 'bw_payment_type' ) ) ) {
                    return new WP_Error( 'bw_event_validation', __( 'Invalid payment type.', 'booking-weir' ), [
                        'status' => 400,
                    ] );
                }
                
                
                if ( !$request->get_param( 'bw_payment_method' ) ) {
                    $request->set_param( 'bw_payment_method', $calendar->get_default_payment_method() );
                } elseif ( !$calendar->has_payment_method( $request->get_param( 'bw_payment_method' ) ) ) {
                    return new WP_Error( 'bw_event_validation', __( 'Invalid payment method.', 'booking-weir' ), [
                        'status' => 400,
                    ] );
                }
                
                break;
        }
        /**
         * Add event metadata.
         */
        $start_timestamp = (int) datetime\utcstrtotime( $start );
        $end_timestamp = (int) datetime\utcstrtotime( $end );
        $created_timestamp = (int) datetime\utcstrtotime( 'now' );
        $request->set_param( 'bw_version', BOOKING_WEIR_VER );
        $request->set_param( 'bw_userip', helpers\get_ip_address() );
        $request->set_param( 'bw_useragent', helpers\get_user_agent() );
        $request->set_param( 'bw_created_timestamp', $created_timestamp );
        /**
         * Validate that event can be inserted into the calendar.
         */
        if ( $start_timestamp === $end_timestamp ) {
            return new WP_Error( 'bw_event_validation', __( 'Start and end times are equal.', 'booking-weir' ), [
                'status' => 400,
            ] );
        }
        if ( $start_timestamp > $end_timestamp ) {
            return new WP_Error( 'bw_event_validation', __( 'Start time is later than end time.', 'booking-weir' ), [
                'status' => 400,
            ] );
        }
        if ( $end_timestamp - $start_timestamp > DAY_IN_SECONDS ) {
            return new WP_Error( 'bw_event_validation', __( 'Event duration can not be longer than a day.', 'booking-weir' ), [
                'status' => 400,
            ] );
        }
        /**
         * Validate based on event type.
         */
        switch ( $event_type ) {
            case 'booking':
                /**
                 * Don't restrict booking creation for admins.
                 */
                if ( $this->context->is_elevated() ) {
                    break;
                }
                /**
                 * Booking validations.
                 */
                $tz = new DateTimeZone( $calendar->get_setting( 'timezone' ) );
                $now = new DateTimeImmutable( 'now', $tz );
                $start_date = new DateTimeImmutable( $start, $tz );
                $end_date = new DateTimeImmutable( $end, $tz );
                $start_hour = (int) $start_date->format( 'H' );
                $end_hour = (int) $end_date->format( 'H' );
                $end_minutes = (int) $end_date->format( 'i' );
                $duration = ($end_timestamp - $start_timestamp) / 60;
                $opening_hour = $calendar->get_opening_hour();
                $closing_hour = $calendar->get_closing_hour();
                $events = $calendar->get_events();
                $space = $calendar->get_setting( 'space' );
                $space_minutes = new DateInterval( 'PT' . $space . 'M' );
                if ( $now > $start_date ) {
                    return new WP_Error( 'bw_event_validation', __( 'Start time is earlier than current time.', 'booking-weir' ), [
                        'status' => 400,
                    ] );
                }
                if ( $start_hour < $opening_hour ) {
                    return new WP_Error( 'bw_event_validation', __( 'Start time is earlier than opening hours.', 'booking-weir' ), [
                        'status' => 400,
                    ] );
                }
                if ( $end_hour > $closing_hour || $end_hour === $closing_hour && $end_minutes !== 0 ) {
                    return new WP_Error( 'bw_event_validation', __( 'End time is later than closing hours.', 'booking-weir' ), [
                        'status' => 400,
                    ] );
                }
                /**
                 * Ensure event is at least 1 step long.
                 */
                $step = (int) $calendar->get_setting( 'step' );
                if ( $duration < $step ) {
                    
                    if ( $end_hour === 23 && $end_minutes === 59 && $duration + 1 === $step ) {
                        /**
                         * When booking to the end of the day add 1 minute allowance because the booking has to end at 23:59.
                         */
                    } else {
                        return new WP_Error( 'bw_event_validation', __( 'Duration is shorter than minimum.', 'booking-weir' ), [
                            'status' => 400,
                        ] );
                    }
                
                }
                /**
                 * Prevent creating too short bookings.
                 */
                $min_duration = (int) $calendar->get_setting( 'minDuration' );
                if ( $min_duration > 0 && $duration < $min_duration ) {
                    
                    if ( $end_hour === 23 && $end_minutes === 59 && $duration + 1 === $min_duration ) {
                        /**
                         * When booking to the end of the day add 1 minute allowance because the booking has to end at 23:59.
                         */
                    } else {
                        return new WP_Error( 'bw_event_validation', sprintf( __( 'Bookings must be at least %s minutes long.', 'booking-weir' ), $min_duration ), [
                            'status' => 400,
                        ] );
                    }
                
                }
                /**
                 * Prevent creating too long bookings.
                 */
                $max_duration = (int) $calendar->get_setting( 'maxDuration' );
                if ( $max_duration > 0 && $duration > $max_duration ) {
                    return new WP_Error( 'bw_event_validation', sprintf( __( 'Bookings must not be longer than %s minutes.', 'booking-weir' ), $max_duration ), [
                        'status' => 400,
                    ] );
                }
                /**
                 * Prevent booking too far into the future.
                 */
                $future = (int) $calendar->get_setting( 'future' );
                
                if ( $future > 0 ) {
                    $days = DateInterval::createFromDateString( $future . ' day' );
                    if ( $start_date > $now->setTime( 0, 0 )->add( $days ) ) {
                        return new WP_Error( 'bw_event_validation', sprintf( __( 'Booking is only allowed less than %s in advance.', 'booking-weir' ), $future . ' ' . _n(
                            'day',
                            'days',
                            $future,
                            'booking-weir'
                        ) ), [
                            'status' => 400,
                        ] );
                    }
                }
                
                /**
                 * Validate service.
                 */
                
                if ( $service_id = $request->get_param( 'bw_service_id' ) ) {
                    $service = $calendar->get_service( $service_id );
                    switch ( $service['type'] ) {
                        case 'fixed':
                            $service_duration = $service['duration'] * $step;
                            if ( $duration !== $service_duration ) {
                                return new WP_Error( 'bw_event_validation', sprintf( __( 'Booking for this service must be %d minutes long.', 'booking-weir' ), $service_duration ), [
                                    'status' => 400,
                                ] );
                            }
                            break;
                    }
                }
                
                /**
                 * Check for overlapping events.
                 */
                $bookable_event_id = (int) $request->get_param( 'bw_bookable_event_id' );
                foreach ( $events as $event ) {
                    $event_start_date = new DateTimeImmutable( $event->get_start(), $tz );
                    $event_end_date = new DateTimeImmutable( $event->get_end(), $tz );
                    
                    if ( $event_start_date < $end_date && $start_date < $event_end_date ) {
                        $is_slot = $event->get_type() === 'slot';
                        $is_bookable = $event->is_bookable() && $event->get_id() === $bookable_event_id;
                        $is_child_of_bookable = $bookable_event_id > 0 && $event->get_bookable_event_id() === $bookable_event_id;
                        
                        if ( ($is_slot || $is_bookable || $is_child_of_bookable) && $event_start_date == $start_date && $event_end_date == $end_date ) {
                            if ( $is_bookable ) {
                                if ( $event->is_at_capacity() ) {
                                    return new WP_Error( 'bw_event_validation', __( 'Event has reached capacity.', 'booking-weir' ), [
                                        'status' => 400,
                                    ] );
                                }
                            }
                            /**
                             * Allow booking in a bookable event or slot.
                             */
                            continue;
                        }
                        
                        return new WP_Error( 'bw_event_validation', __( 'Overlapping with another event.', 'booking-weir' ), [
                            'status' => 400,
                            'id'     => $event->get_id(),
                            'start'  => $event->get_start(),
                            'end'    => $event->get_end(),
                        ] );
                    }
                
                }
                /**
                 * Check space between events.
                 */
                foreach ( $events as $event ) {
                    if ( $event->get_type() === 'unavailable' ) {
                        /**
                         * Allow booking close to `unavailable` events.
                         */
                        continue;
                    }
                    $event_start_date = new DateTimeImmutable( $event->get_start(), $tz );
                    $event_end_date = new DateTimeImmutable( $event->get_end(), $tz );
                    if ( $event_start_date < $start_date && $event_end_date->add( $space_minutes ) > $start_date ) {
                        return new WP_Error( 'bw_event_validation', sprintf( __( 'Please leave at least %s minutes after the previous event.', 'booking-weir' ), $space ), [
                            'status' => 400,
                        ] );
                    }
                    if ( $start_date < $event_start_date && $end_date->add( $space_minutes ) > $event_start_date ) {
                        return new WP_Error( 'bw_event_validation', sprintf( __( 'Please leave at least %s minutes before the next event.', 'booking-weir' ), $space ), [
                            'status' => 400,
                        ] );
                    }
                }
                break;
        }
        return $prepared_post;
    }
    
    /**
     * Fires after a single event is created or updated via the REST API.
     *
     * Note: can't throw errors from here.
     *
     * @param WP_Post $post Inserted or updated post object.
     * @param WP_REST_Request $request Request object.
     * @param bool $creating True when creating a post, false when updating.
     */
    public function rest_insert( $post, $request, $creating )
    {
        $params = $request->get_params();
        $event_id = $post->ID;
        /**
         * Setup event data.
         */
        
        if ( $creating ) {
            /**
             * Calendar ID is ensured present when creating.
             */
            $calendar = $this->context->get( 'calendars' )->get_calendar( $request->get_param( 'bw_calendar_id' ) );
            $type = $request->get_param( 'bw_type' );
            $status = $request->get_param( 'bw_status' );
            $start = $request->get_param( 'bw_start' );
            $end = $request->get_param( 'bw_end' );
            $extras = $request->get_param( 'bw_extras' );
            $coupon = $request->get_param( 'bw_coupon' );
            $service_id = $request->get_param( 'bw_service_id' );
            $bookable_event_id = $request->get_param( 'bw_bookable_event_id' );
        } else {
            /**
             * When updating use params from request or values from existing event.
             */
            $event = $this->event_factory->create( $event_id );
            $calendar = $event->get_calendar();
            $type = ( isset( $params['bw_type'] ) ? $request->get_param( 'bw_type' ) : $event->get_type() );
            $status = ( isset( $params['bw_status'] ) ? $request->get_param( 'bw_status' ) : $event->get_status() );
            $start = ( isset( $params['bw_start'] ) ? $request->get_param( 'bw_start' ) : $event->get_start() );
            $end = ( isset( $params['bw_end'] ) ? $request->get_param( 'bw_end' ) : $event->get_end() );
            $extras = ( isset( $params['bw_extras'] ) ? $request->get_param( 'bw_extras' ) : $event->get_extras() );
            $coupon = ( isset( $params['bw_coupon'] ) ? $request->get_param( 'bw_coupon' ) : $event->get_coupon() );
            $service_id = ( isset( $params['bw_service_id'] ) ? $request->get_param( 'bw_service_id' ) : $event->get_service_id() );
            $bookable_event_id = ( isset( $params['bw_bookable_event_id'] ) ? $request->get_param( 'bw_bookable_event_id' ) : $event->get_bookable_event_id() );
        }
        
        
        if ( $type === 'booking' ) {
            /**
             * Apply price.
             */
            $price = $calendar->get_event_price(
                $start,
                $end,
                $extras,
                $coupon,
                $service_id,
                $bookable_event_id
            );
            $request->set_param( 'bw_price', $price['value'] );
            $request->set_param( 'bw_breakdown', $price['breakdown'] );
            
            if ( $creating ) {
                /**
                 * Set status.
                 */
                
                if ( $status === 'awaiting' ) {
                    $requires_payment = $calendar->get_default_payment_method() !== false && $price['value'] > 0;
                    if ( $requires_payment ) {
                        $request->set_param( 'bw_status', 'pending' );
                    }
                }
                
                /**
                 * Setup billing data.
                 */
                $request->set_param( 'bw_billing_id', apply_filters(
                    'bw_billing_id',
                    date( 'y' ) . $event_id,
                    $post,
                    $request
                ) );
                $request->set_param( 'bw_billing_key', helpers\get_unique_id() );
            }
        
        }
        
        /**
         * Update timestamps in case times changed.
         */
        $start_timestamp = (int) datetime\utcstrtotime( $start );
        $end_timestamp = (int) datetime\utcstrtotime( $end );
        $request->set_param( 'bw_start_timestamp', $start_timestamp );
        $request->set_param( 'bw_end_timestamp', $end_timestamp );
    }
    
    /**
     * Fires after a single event is completely created or updated via the REST API.
     *
     * @param WP_Post $post Inserted or updated post object.
     * @param WP_REST_Request $request Request object.
     * @param bool $creating True when creating a post, false when updating.
     */
    public function rest_after_insert( $post, $request, $creating )
    {
        $event_id = $post->ID;
        $event = $this->event_factory->create( $event_id );
        if ( $event->get_type() === 'booking' ) {
            if ( !$event->is_WC() ) {
                if ( $creating || $event->get_calendar()->get_setting( 'invoicePdfRegenerate' ) ) {
                    $this->context->get( 'pdf' )->generate_invoice( $event );
                }
            }
        }
    }
    
    /**
     * Add dynamic data to the event (backend).
     */
    public function rest_prepare( $response, $post, $request )
    {
        $data = $response->get_data();
        $event = $this->event_factory->create( $post->ID );
        if ( !$event->exists() ) {
            return $response;
        }
        $response->set_data( array_merge( $data, [
            'bw_data' => $event->get_data(),
        ] ) );
        return $response;
    }
    
    /**
     * Unlimited events per GET request.
     */
    public function rest_query( $args, $request )
    {
        if ( strtolower( $request->get_method() ) === 'get' ) {
            $args['posts_per_page'] = PHP_INT_MAX;
        }
        return $args;
    }
    
    /**
     * Add `bw_filter` arg.
     */
    public function rest_query_filter( $args, $request )
    {
        if ( empty($request['bw_filter']) || !is_array( $request['bw_filter'] ) ) {
            return $args;
        }
        $filter = $request['bw_filter'];
        $vars = array_merge( apply_filters( 'rest_query_vars', $GLOBALS['wp']->public_query_vars ), [
            'meta_query',
            'meta_key',
            'meta_value',
            'meta_compare'
        ] );
        foreach ( $vars as $var ) {
            if ( isset( $filter[$var] ) ) {
                $args[$var] = $filter[$var];
            }
        }
        return $args;
    }
    
    /**
     * Prevent showing events in `/wp/v2/search` results.
     *
     * By default the search endpoint includes post types that are `public` and `show_in_rest`.
     * This post type needs these attributes, but shouldn't be searchable as events are not public facing.
     *
     * @param array $query_args
     * @return array
     */
    public function rest_exclude_from_search( $query_args )
    {
        if ( isset( $query_args['post_type'] ) ) {
            
            if ( is_array( $query_args['post_type'] ) ) {
                $query_args['post_type'] = array_diff( $query_args['post_type'], [ self::SLUG ] );
            } elseif ( strtolower( $query_args['post_type'] ) === self::SLUG ) {
                unset( $query_args['post_type'] );
            }
        
        }
        return $query_args;
    }
    
    /**
     * Handle Link API actions.
     */
    public function link_api()
    {
        if ( !$this->context->is_elevated() ) {
            return;
        }
        if ( !isset( $_GET[self::ID] ) || !isset( $_GET[self::ACTION] ) || !isset( $_GET[self::NONCE] ) ) {
            return;
        }
        $id = (int) $_GET[self::ID];
        /**
         * TODO: can't do nonce check as it is user-specific and only lasts 24h.
         */
        // $action = $this->context->get('sanitizer')->sanitize_key(wp_unslash($_GET[self::ACTION])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        // $nonce = $this->context->get('sanitizer')->sanitize_key(wp_unslash($_GET[self::NONCE])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        // if(!wp_verify_nonce($nonce, $action)) {
        // 	helpers\admin_notice(__('Invalid session.', 'booking-weir'), 'error');
        // 	return;
        // }
        
        if ( !($event = $this->event_factory->create( $id )) ) {
            helpers\admin_notice( __( 'Event not found.', 'booking-weir' ), 'error' );
            return;
        }
        
        switch ( $_GET[self::ACTION] ) {
            case 'confirm':
                if ( $event->get_status() === 'awaiting' ) {
                    $event->set_status( 'confirmed' );
                }
                break;
            default:
                helpers\admin_notice( __( 'Invalid action.', 'booking-weir' ), 'error' );
                return;
        }
        wp_safe_redirect( $event->get_admin_url() );
        exit;
    }
    
    /**
     * Trigger action when `bw_*` meta changes.
     */
    public function updated_postmeta(
        $meta_id,
        $object_id,
        $meta_key,
        $meta_value
    )
    {
        if ( strpos( $meta_key, 'bw_' ) !== 0 ) {
            return;
        }
        do_action(
            'bw_event_meta_changed',
            (int) $object_id,
            $meta_key,
            $meta_value
        );
    }

}