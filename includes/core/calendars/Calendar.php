<?php

namespace wsd\bw\core\calendars;

use  wsd\bw\Context ;
use  wsd\bw\core\booking\Field ;
use  wsd\bw\core\booking\PriceCalculator ;
use  wsd\bw\core\events\Event ;
use  wsd\bw\util\datetime ;
use  wsd\bw\util\helpers ;
use  DateTimeImmutable ;
use  DateTimeZone ;
use  DateInterval ;
use  DatePeriod ;
use  WP_Query ;
use  WP_REST_Request ;
/**
 * Calendar class.
 */
final class Calendar
{
    /** @var Context */
    protected  $context ;
    /** @var Calendars */
    protected  $calendars ;
    /**
     * Calendar ID.
     *
     * @var string
     */
    protected  $id ;
    /**
     * Calendar object.
     *
     * @var array
     */
    protected  $calendar ;
    /**
     * Calendar events are loaded.
     *
     * @var boolean
     */
    protected  $events_loaded = false ;
    /**
     * Events in the calendar.
     *
     * @var array [id => Event]
     */
    protected  $events ;
    /**
     * GET variable name for default-selecting a service on the front end.
     */
    const  SELECTED_SERVICE = 'bw-service' ;
    public function __construct(
        Context $context,
        Calendars $calendars,
        $id,
        $calendar
    )
    {
        $this->context = $context;
        $this->calendars = $calendars;
        $this->id = $id;
        $this->calendar = $calendar;
        $this->parse_settings();
    }
    
    /**
     * Add calendar settings to class variable with defaults and type checks.
     */
    protected function parse_settings()
    {
        $schema = $this->get_settings_schema();
        $settings = ( isset( $this->calendar['settings'] ) && is_array( $this->calendar['settings'] ) ? $this->calendar['settings'] : [] );
        foreach ( $schema as $setting ) {
            $id = $setting['id'];
            $default = $setting['default'];
            $value = ( isset( $settings[$id] ) ? $settings[$id] : $default );
            switch ( $setting['type'] ) {
                case 'number':
                    $value = (int) $value;
                    break;
            }
            $settings[$id] = $value;
        }
        $this->calendar['settings'] = $settings;
    }
    
    /**
     * Get WP_Query args for calendar's events.
     * The query is for public events only and the resulting events
     * are used for public display and validation. Private and Trashed events
     * are not returned here and can therefore be booked over.
     * For retrieving events of all statuses the `bw/v1/events` REST endpoint can be used.
     *
     * @param array $query Additional args.
     * @return array
     */
    protected function get_query( $query = [] )
    {
        return array_merge( [
            'post_type'      => $this->context->get( 'event-post-type' )::SLUG,
            'posts_per_page' => -1,
            'meta_key'       => 'bw_calendar_id',
            'meta_value'     => $this->get_id(),
            'post_status'    => 'publish',
        ], $query );
    }
    
    /**
     * Collect events that are in this calendar into an array.
     * Doesn't include events older than a week, unless they're repeating events.
     *
     * @return array [id => Event]
     */
    public function get_self_events()
    {
        $events = [];
        /**
         * Load events.
         */
        $wp_query = new WP_Query( $this->get_query( [
            'meta_query' => [ [
            'key'     => 'bw_start_timestamp',
            'value'   => (int) (datetime\utcstrtotime( 'now' ) - WEEK_IN_SECONDS),
            'type'    => 'NUMERIC',
            'compare' => '>=',
        ], 'relation' => 'OR', [
            'key'   => 'bw_repeat',
            'value' => '1',
        ] ],
        ] ) );
        
        if ( $wp_query->have_posts() ) {
            while ( $wp_query->have_posts() ) {
                $wp_query->the_post();
                $id = get_the_ID();
                $events[$id] = $this->context->get( 'event-factory' )->create( $id );
            }
            wp_reset_postdata();
        }
        
        $this->events_loaded = true;
        return $events;
    }
    
    /**
     * Assign calendar's events to class variable.
     */
    public function load_events()
    {
        $this->events = $this->get_self_events();
    }
    
    /**
     * Load events if they haven't been loaded yet.
     */
    public function lazyload_events()
    {
        if ( !$this->events_loaded ) {
            $this->load_events();
        }
    }
    
    /**
     * Get the calendar's ID.
     *
     * @return string
     */
    public function get_id()
    {
        return $this->id;
    }
    
    /**
     * Get the calendar's display name.
     * ID is returned when there is no name set.
     *
     * @return string
     */
    public function get_name()
    {
        return ( isset( $this->calendar['name'] ) ? $this->calendar['name'] : $this->get_id() );
    }
    
    /**
     * Dynamic data for the calendar, that can be used on the back end.
     *
     * @return array
     */
    protected function get_data()
    {
        $data = [
            'ver' => BOOKING_WEIR_VER,
        ];
        return apply_filters( 'bw_calendar_data', $data, $this );
    }
    
    /**
     * Get the calendar value that is stored in database along with dynamic data.
     *
     * @return array
     */
    public function get_calendar()
    {
        return array_merge( $this->calendar, [
            'data' => $this->get_data(),
        ] );
    }
    
    /**
     * Get the calendar settings.
     *
     * @return array
     */
    public function get_settings()
    {
        return $this->calendar['settings'];
    }
    
    /**
     * Get a calendar setting value.
     *
     * @param string $id Setting ID.
     * @return mixed
     */
    public function get_setting( $id )
    {
        $settings = $this->get_settings();
        $value = ( isset( $settings[$id] ) ? $settings[$id] : false );
        if ( strpos( $id, 'template' ) === 0 ) {
            $value = do_blocks( $value );
        }
        return $value;
    }
    
    /**
     * Get the calendar's events.
     *
     * @return array [id => Event]
     */
    public function get_events()
    {
        $this->lazyload_events();
        return $this->events;
    }
    
    /**
     * Get an event from the calendar.
     *
     * @param int $id Event ID.
     * @return Event|false
     */
    public function get_event( $id )
    {
        if ( isset( $this->events[$id] ) ) {
            return $this->events[$id];
        }
        if ( $this->has_event( $id ) ) {
            return $this->context->get( 'event-factory' )->create( $id );
        }
        return false;
    }
    
    /**
     * Get parent calendar.
     *
     * @return Calendar|bool
     */
    public function get_parent()
    {
        $parent = $this->get_setting( 'parent' );
        if ( !empty($parent) ) {
            if ( $this->calendars->calendar_exists( $parent ) ) {
                return $this->calendars->get_calendar( $parent );
            }
        }
        return false;
    }
    
    /**
     * Check if this calendar contains an event.
     *
     * @param int $id Event ID.
     * @return boolean
     */
    public function has_event( $id )
    {
        $wp_query = new WP_Query( $this->get_query( [
            'p' => (int) $id,
        ] ) );
        return $wp_query->post_count === 1;
    }
    
    /**
     * Get the extras of this calendar.
     *
     * @return array
     */
    public function get_extras()
    {
        return ( isset( $this->calendar['extras'] ) && is_array( $this->calendar['extras'] ) ? $this->calendar['extras'] : [] );
    }
    
    /**
     * Get extra by ID.
     *
     * @param string $id Extra ID.
     * @return array|false
     */
    public function get_extra( $id )
    {
        $extras = $this->get_extras();
        $key = array_search( $id, array_column( $extras, 'id' ) );
        return ( is_int( $key ) ? $extras[$key] : false );
    }
    
    /**
     * Get the services of this calendar.
     *
     * @return array
     */
    public function get_services()
    {
        return ( isset( $this->calendar['services'] ) && is_array( $this->calendar['services'] ) ? $this->calendar['services'] : [] );
    }
    
    /**
     * Get service by ID.
     *
     * @param string $id Service ID.
     * @return array|false
     */
    public function get_service( $id = '' )
    {
        if ( empty($id) ) {
            return false;
        }
        $services = $this->get_services();
        $key = array_search( $id, array_column( $services, 'id' ) );
        if ( !is_int( $key ) ) {
            return false;
        }
        return wp_parse_args( $services[$key], [
            'id'            => '',
            'type'          => 'fixed',
            'name'          => '',
            'description'   => '',
            'price'         => 0,
            'duration'      => 0,
            'enabled'       => true,
            'availability'  => 'default',
            'availableFrom' => '00:00',
            'availableTo'   => '00:00',
        ] );
    }
    
    /**
     * Get the fields value from calendar settings.
     *
     * @param boolean $flat Flatten the fields.
     * @return array
     */
    public function get_calendar_fields( $flat = false )
    {
        $fields = ( isset( $this->calendar['fields'] ) && is_array( $this->calendar['fields'] ) ? $this->calendar['fields'] : [] );
        return ( $flat ? helpers\flatten_fields( $fields ) : $fields );
    }
    
    /**
     * Get the fields of this calendar.
     *
     * @return array [id => Field]
     */
    public function get_fields()
    {
        $all_fields = $this->get_calendar_fields( true );
        /**
         * Load fields from calendar settings.
         */
        $fields = [];
        foreach ( $all_fields as $field ) {
            $fields[$field['id']] = new Field( $this->context, $field );
        }
        
        if ( $this->is_product() ) {
            /**
             * Filter out default fields as they are synced to WooCommerce order.
             */
            foreach ( $this->get_default_fields() as $default_field ) {
                foreach ( $fields as $id => $field ) {
                    if ( $field->get_id() === $default_field->get_id() ) {
                        unset( $fields[$id] );
                    }
                }
            }
        } else {
            /**
             * Add any missing default field in disabled state.
             */
            foreach ( $this->get_default_fields() as $default_field ) {
                foreach ( $fields as $field ) {
                    if ( $field->get_id() === $default_field->get_id() ) {
                        continue 2;
                    }
                }
                $default_field->set_enabled( false );
                $fields[$default_field->get_id()] = $default_field;
            }
        }
        
        return $fields;
    }
    
    /**
     * Get the default fields of this calendar.
     *
     * @return array [id => Field]
     */
    public function get_default_fields()
    {
        $default_fields = [];
        foreach ( helpers\flatten_fields( $this->context->get( 'default-fields' )->get() ) as $default_field ) {
            $default_fields[$default_field['id']] = new Field( $this->context, $default_field );
        }
        return $default_fields;
    }
    
    /**
     * Get field by ID.
     *
     * @param string $id Field ID.
     * @return Field|false
     */
    public function get_field( $id )
    {
        $fields = $this->get_fields();
        return ( isset( $fields[$id] ) ? $fields[$id] : false );
    }
    
    /**
     * Get the prices of this calendar.
     * Note: this is not the price per hour setting.
     *
     * @return array
     */
    public function get_prices()
    {
        return ( isset( $this->calendar['prices'] ) && is_array( $this->calendar['prices'] ) ? $this->calendar['prices'] : [] );
    }
    
    /**
     * Get the payment types of this calendar.
     *
     * @return array
     */
    public function get_payment_types()
    {
        return ( isset( $this->calendar['paymentTypes'] ) && is_array( $this->calendar['paymentTypes'] ) ? $this->calendar['paymentTypes'] : [] );
    }
    
    /**
     * Check if this calendar has the payment type.
     *
     * @param string $id Payment type ID.
     * @return bool
     */
    public function has_payment_type( $id )
    {
        foreach ( $this->get_payment_types() as $payment_type ) {
            if ( $payment_type['id'] === $id ) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get payment type.
     *
     * @param string $id Payment type ID.
     * @return array
     */
    public function get_payment_type( $id )
    {
        if ( $id ) {
            foreach ( $this->get_payment_types() as $payment_type ) {
                if ( $payment_type['id'] === $id ) {
                    return $payment_type;
                }
            }
        }
        return [
            'id'     => 'default',
            'name'   => esc_html_x( 'Default', 'Default payment type name', 'booking-weir' ),
            'amount' => 100,
        ];
    }
    
    /**
     * @return string
     */
    public function get_default_payment_type_id()
    {
        $types = $this->get_payment_types();
        return ( isset( $types[0] ) ? $types[0]['id'] : 'default' );
    }
    
    /**
     * Get the enabled payment methods for this calendar.
     *
     * @return array
     */
    public function get_payment_methods()
    {
        return ( isset( $this->calendar['paymentMethods'] ) && is_array( $this->calendar['paymentMethods'] ) ? $this->calendar['paymentMethods'] : [] );
    }
    
    /**
     * Check if a given payment method is enabled for this calendar.
     *
     * @param string $id Payment method ID.
     * @return boolean
     */
    public function has_payment_method( $id )
    {
        return in_array( $id, $this->get_payment_methods() );
    }
    
    /**
     * Get the default payment method for this calendar.
     *
     * @return string|false
     */
    public function get_default_payment_method()
    {
        $methods = $this->get_payment_methods();
        return ( count( $methods ) > 0 ? $methods[0] : false );
    }
    
    /**
     * Get the data of a payment method.
     *
     * @param string $method Payment method ID.
     * @return array
     */
    public function get_payment_method_data( $method )
    {
        $data = $this->calendar['paymentMethodData'];
        foreach ( $data as $method_data ) {
            if ( $method_data['id'] === $method ) {
                return $method_data;
            }
        }
        return [];
    }
    
    /**
     * Get payment method's configured options.
     *
     * @param string $method Payment method ID.
     * @return array
     */
    public function get_payment_method_options( $method )
    {
        $default_options = [];
        foreach ( $this->context->get( 'payment' )->get_method_data() as $method_data ) {
            
            if ( $method_data['id'] === $method ) {
                $default_options = $method_data['options'];
                break;
            }
        
        }
        $data = $this->get_payment_method_data( $method );
        $options = [];
        foreach ( $default_options as $default_option ) {
            $options[$default_option['id']] = ( isset( $data[$default_option['id']] ) ? $data[$default_option['id']] : $default_option['default'] );
        }
        return $options;
    }
    
    /**
     * Check if calendar accepts using multiple coupons when creating a booking.
     *
     * @return bool
     */
    public function allows_using_multiple_coupons()
    {
        return false;
    }
    
    /**
     * Get price for event.
     *
     * @param string $start
     * @param string $end
     * @param array  $selected_extras
     * @param string $coupon
     * @param string $service_id
     * @param int $bookable_event_id
     * @return array [(float)value, (array)breakdown]
     */
    public function get_event_price(
        $start,
        $end,
        $selected_extras = [],
        $coupon = '',
        $service_id = '',
        $bookable_event_id = 0
    )
    {
        $calculator = new PriceCalculator(
            $this,
            $start,
            $end,
            $selected_extras,
            $coupon,
            $service_id,
            $bookable_event_id
        );
        return $calculator->get_price();
    }
    
    /**
     * Converts an array of event data to an array that can be submitted to REST controller.
     *
     * @param array $event
     * @return array
     */
    public function event_to_post( $event )
    {
        $post = [];
        if ( isset( $event['id'] ) ) {
            $post['id'] = $event['id'];
        }
        if ( isset( $event['title'] ) ) {
            $post['title'] = $event['title'];
        }
        if ( isset( $event['excerpt'] ) ) {
            $post['excerpt'] = $event['excerpt'];
        }
        foreach ( $this->context->get( 'event-meta-schema' ) as $key => $args ) {
            if ( isset( $event[$args['name']] ) ) {
                $post[$key] = $event[$args['name']];
            }
        }
        return $post;
    }
    
    /**
     * Add event to calendar.
     *
     * @param array $event
     * @return Event|string
     */
    public function add_event( $event )
    {
        if ( !is_array( $event ) ) {
            return __( 'Invalid event.', 'booking-weir' );
        }
        if ( !isset( $event['start'] ) || !isset( $event['end'] ) ) {
            return __( 'Event start or end time was not found.', 'booking-weir' );
        }
        /**
         * Make sure `calendarId` is set and matches this calendar.
         */
        if ( !isset( $event['calendarId'] ) ) {
            $event['calendarId'] = $this->get_id();
        }
        if ( $event['calendarId'] !== $this->get_id() ) {
            return __( 'Calendar mismatch.', 'booking-weir' );
        }
        /**
         * Require nonce.
         */
        if ( !isset( $event['nonce'] ) ) {
            return __( 'No nonce.', 'booking-weir' );
        }
        /**
         * Store WC_Product.
         */
        $product = null;
        if ( isset( $GLOBALS['product'] ) ) {
            $product = $GLOBALS['product'];
        }
        /**
         * Add event via REST API that handles missing fields and validation.
         */
        $this->context->get( 'logger' )->log( [
            'Add event' => $event,
        ], $this->get_id() );
        $request = new WP_REST_Request( 'POST', sprintf( '/%s/events', $this->context->get( 'rest' )->namespace ) );
        $request->set_body_params( array_merge( $this->event_to_post( $event ), [
            'status'   => 'publish',
            'bw_nonce' => sanitize_key( $event['nonce'] ),
        ] ) );
        $response = rest_do_request( $request );
        
        if ( $response->is_error() ) {
            $this->context->get( 'logger' )->log( [
                'Responded with error when adding event' => $response,
            ], $this->get_id() );
            $error = $response->as_error();
            return $error->get_error_message();
        }
        
        $server = rest_get_server();
        $post = $server->response_to_data( $response, false );
        wp_reset_postdata();
        /**
         * Restore WC_Product.
         */
        if ( $product && !isset( $GLOBALS['product'] ) ) {
            $GLOBALS['product'] = $product;
        }
        /**
         * Created event.
         */
        
        if ( isset( $post['id'] ) ) {
            $event = $this->get_event( $post['id'] );
            $this->events[$post['id']] = $event;
            return $event;
        }
        
        $this->context->get( 'logger' )->log( [
            'Invalid response when adding event.' => $post,
        ], $this->get_id() );
        return __( 'Invalid response when adding event.', 'booking-weir' );
    }
    
    /**
     * When an event is deleted it should be removed from the calendar's loaded events.
     *
     * @see Event::delete_permanently
     * @param int $id
     */
    public function remove_event( $id )
    {
        unset( $this->events[$id] );
    }
    
    /**
     * Add booking to calendar.
     *
     * @param array $booking
     * @return Event|string
     */
    public function add_booking( $booking )
    {
        $booking['type'] = 'booking';
        if ( $this->is_product() ) {
            $booking['status'] = 'cart';
        }
        if ( isset( $booking['email'] ) && $booking['email'] === $this->context->get( 'email' )::DEBUG_EMAIL_PLACEHOLDER && $this->context->is_elevated() ) {
            $this->context->get( 'email' )->set_debug( true );
        }
        return $this->add_event( $booking );
    }
    
    /**
     * Does the calendar have coupons.
     *
     * @return boolean
     */
    public function has_coupons()
    {
        $prices = $this->get_prices();
        if ( is_array( $prices ) && count( $prices ) > 0 ) {
            foreach ( $prices as $price ) {
                if ( isset( $price['enabled'] ) && !$price['enabled'] ) {
                    continue;
                }
                if ( $price['type'] === 'coupon' ) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Get the calendar's settings schema.
     *
     * @return array
     */
    public function get_settings_schema()
    {
        return apply_filters( 'bw_calendar_settings_schema', $this->calendars->get_default_settings_schema(), $this->get_id() );
    }
    
    /**
     * Get public calendar data.
     *
     * @return array
     */
    public function get_public_calendar()
    {
        return [
            'events'         => $this->get_public_events(),
            'services'       => $this->get_public_services(),
            'settings'       => $this->get_public_settings(),
            'extras'         => $this->get_extras(),
            'fields'         => $this->get_calendar_fields(),
            'paymentMethods' => $this->get_payment_methods(),
            'paymentTypes'   => $this->get_payment_types(),
            'data'           => $this->get_public_data(),
        ];
    }
    
    /**
     * Events displayed in the calendar shortcode on the front end.
     *
     * @return array
     */
    protected function get_public_events()
    {
        /**
         * Get all events that should be displayed.
         */
        $events = $this->get_events();
        /**
         * Accept filter.
         */
        $events = apply_filters( 'bw_public_events', $events, $this );
        /**
         * Construct public events.
         */
        $public_events = [];
        foreach ( $events as $id => $event ) {
            if ( $event->is_child_of_bookable_event() ) {
                continue;
            }
            $public_event = [
                'id'       => esc_attr( $id ),
                'title'    => esc_attr( $event->get_public_title() ),
                'bw_type'  => esc_attr( $event->get_type() ),
                'bw_start' => esc_attr( $event->get_start() ),
                'bw_end'   => esc_attr( $event->get_end() ),
                'bw_data'  => $event->get_public_data( $this ),
            ];
            
            if ( $event->is_bookable() ) {
                $public_event['bw_bookable'] = '1';
                $public_event['bw_booking'] = $event->get_booking_data();
            }
            
            
            if ( $event->repeats() && !$event->is_repeat() ) {
                $public_event['bw_repeat'] = '1';
                $public_event['bw_repeater'] = $event->get_repeater();
            }
            
            $public_events[] = apply_filters(
                'bw_public_event',
                $public_event,
                $event,
                $this
            );
        }
        return $public_events;
    }
    
    /**
     * Services used by the calendar shortcode on the front end.
     *
     * @return array
     */
    protected function get_public_services()
    {
        $public_services = [];
        foreach ( $this->get_services() as $service ) {
            $service = $this->get_service( $service['id'] );
            if ( !$service['enabled'] ) {
                continue;
            }
            $public_services[] = [
                'id'             => esc_attr( $service['id'] ),
                'type'           => ( isset( $service['type'] ) ? esc_attr( $service['type'] ) : 'fixed' ),
                'name'           => esc_attr( $service['name'] ),
                'hasDescription' => isset( $service['description'] ) && strlen( $service['description'] ) > 0,
                'duration'       => (int) $service['duration'],
                'price'          => (double) $service['price'],
                'availability'   => esc_attr( $service['availability'] ),
                'availableFrom'  => esc_attr( $service['availableFrom'] ),
                'availableTo'    => esc_attr( $service['availableTo'] ),
            ];
        }
        return $public_services;
    }
    
    /**
     * Settings used by the calendar shortcode on the front end.
     *
     * @return array
     */
    protected function get_public_settings()
    {
        $schema = $this->get_settings_schema();
        $settings = ( isset( $this->calendar['settings'] ) ? $this->calendar['settings'] : [] );
        $public_settings = [];
        foreach ( $schema as $setting ) {
            $id = $setting['id'];
            if ( !isset( $settings[$id] ) ) {
                continue;
            }
            $public = ( isset( $setting['public'] ) ? $setting['public'] : false );
            if ( !$public ) {
                continue;
            }
            $public_settings[$id] = $settings[$id];
        }
        return $public_settings;
    }
    
    /**
     * Dynamic data for the calendar, displayed on the front end.
     *
     * @return array
     */
    protected function get_public_data()
    {
        $data = [
            'has_coupons' => $this->has_coupons(),
            'product'     => $this->get_product_id(),
            'nonce'       => wp_create_nonce( $this->get_id() ),
        ];
        if ( $this->context->is_elevated() ) {
            $data['admin_url'] = $this->get_admin_url();
        }
        
        if ( isset( $_GET[self::SELECTED_SERVICE] ) ) {
            $service_id = $this->context->get( 'sanitizer' )->sanitize_id( wp_unslash( $_GET[self::SELECTED_SERVICE] ) );
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            if ( $this->get_service( $service_id ) ) {
                $data['initial_selected_service'] = esc_attr( $service_id );
            }
        }
        
        return $data;
    }
    
    /**
     * How many seconds before event start time should
     * reminder email for an event in this calendar be sent.
     *
     * @return int Offset in seconds.
     */
    public function get_reminder_email_offset()
    {
        $offset = (int) $this->get_setting( 'reminderEmailOffset' );
        // Hours
        if ( $offset < 1 ) {
            return 0;
        }
        return $offset * 60 * 60;
    }
    
    /**
     * Save current calendar state to global calendars state.
     *
     * @return boolean
     */
    protected function save()
    {
        return false;
    }
    
    /**
     * Get the ID of the WooCommerce product that this calendar has been assigned to.
     *
     * @return integer
     */
    public function get_product_id()
    {
        return $this->get_setting( 'product' );
    }
    
    /**
     * Check if the calendar has a WooCommerce product attached for booking purposes.
     *
     * @return boolean
     */
    public function is_product()
    {
        return (int) $this->get_setting( 'product' ) > 0;
    }
    
    /**
     * Link to this calendar's admin screen.
     *
     * @return string
     */
    public function get_admin_url()
    {
        return sprintf( '%1$s#/%2$s', $this->context->get( 'admin' )->get_url(), $this->get_id() );
    }
    
    /**
     * Render a button with a link to this calendar's admin screen.
     *
     * @return string
     */
    public function get_admin_button()
    {
        return sprintf( '<a href="%1$s" class="ui primary button btn btn-primary" target="_blank" rel="noopener noreferrer">%2$s</a>', esc_url( $this->get_admin_url() ), esc_html__( 'Manage calendar', 'booking-weir' ) );
    }
    
    /**
     * The UTC offset of the calendar's timezone.
     *
     * @return int
     */
    public function get_utc_offset()
    {
        $tz = new DateTimeZone( $this->get_setting( 'timezone' ) );
        $off = $tz->getOffset( new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) ) ) / 60;
        return $off;
    }
    
    /**
     * The hour before which events cannot be booked.
     *
     * @return int
     */
    public function get_opening_hour()
    {
        return (int) $this->get_setting( 'openingHour' );
    }
    
    /**
     * The hour after which events cannot be booked.
     * A value smaller than opening hour should default to end of day.
     *
     * @return int
     */
    public function get_closing_hour()
    {
        $opening_hour = $this->get_opening_hour();
        $closing_hour = (int) $this->get_setting( 'closingHour' );
        if ( $closing_hour <= 0 || $closing_hour < $opening_hour ) {
            return 24;
        }
        return $closing_hour;
    }

}