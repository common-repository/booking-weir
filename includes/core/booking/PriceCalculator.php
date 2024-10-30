<?php

namespace wsd\bw\core\booking;

use  wsd\bw\core\calendars\Calendar ;
/**
 * Calculates a price for an event in a calendar using the calendar's hourly price and pricing rules.
 */
class PriceCalculator
{
    /**
     * Calendar that the event is in.
     *
     * @var Calendar
     */
    protected  $calendar ;
    /**
     * Event start time.
     *
     * @var string Y-m-dTH:i
     */
    protected  $start ;
    /**
     * Event end time.
     *
     * @var string Y-m-dTH:i
     */
    protected  $end ;
    /**
     * Extras that are selected for the event.
     *
     * @var array [(string)extra_id => (bool|int)extra_value]
     */
    protected  $extras ;
    /**
     * Array of coupons parsed from a string.
     *
     * @var array
     */
    protected  $coupons ;
    /**
     * Calendar's initial price per hour setting.
     *
     * @var float
     */
    protected  $price = 0 ;
    /**
     * Calendar's pricing rules.
     *
     * @var Ruleset
     */
    protected  $ruleset ;
    /**
     * Event start timestamp.
     * Note: not adjusted to any timezone, purely for comparisons and calculating duration.
     *
     * @var integer
     */
    protected  $start_timestamp = 0 ;
    /**
     * Event end timestamp.
     * Note: not adjusted to any timezone, purely for comparisons and calculating duration.
     *
     * @var integer
     */
    protected  $end_timestamp = 0 ;
    /**
     * Event duration in seconds.
     *
     * @var integer
     */
    protected  $duration = 0 ;
    /**
     * Event duration in minutes.
     *
     * @var integer
     */
    protected  $duration_minutes = 0 ;
    /**
     * Calendars step setting in minutes (fixed time increments that the event can consist of).
     *
     * @var integer
     */
    protected  $step = 0 ;
    /**
     * Amount of steps the event consists of.
     *
     * @var integer
     */
    protected  $steps = 0 ;
    /**
     * Stores instances of `Price`, such as the price for duration and the price for extras.
     *
     * @var array [(string)id => Price]
     */
    protected  $prices ;
    /**
     * Total calculated price.
     *
     * @var Price
     */
    protected  $total ;
    /**
     * Stores all the coupons that were successfully applied.
     *
     * @var array
     */
    protected  $applied_coupons ;
    /**
     * Stores all the coupons that were entered, but don't exist.
     *
     * @var array
     */
    protected  $invalid_coupons ;
    /**
     * Service data, if the booking is for a service.
     * With services, the price is fixed instead of based on duration.
     *
     * @var array|false
     */
    protected  $service = false ;
    /**
     * ID of the event that the booking is for.
     *
     * @var int
     */
    protected  $bookable_event_id = 0 ;
    /**
     * Stores additional info/errors/feedback about the price calculation.
     *
     * @var array
     */
    protected  $info ;
    /**
     * Initializes variables and performs the price calculation.
     *
     * @param Calendar $calendar
     * @param string $start
     * @param string $end
     * @param array $extras
     * @param string $coupon
     * @param string $service_id
     * @param int $bookable_event_id
     */
    public function __construct(
        Calendar $calendar,
        $start,
        $end,
        $extras = array(),
        $coupon = '',
        $service_id = '',
        $bookable_event_id = 0
    )
    {
        /**
         * Input values.
         */
        $this->calendar = $calendar;
        $this->start = $start;
        $this->end = $end;
        $this->extras = $extras;
        $this->coupons = $this->parse_coupons( $coupon );
        $this->service = $calendar->get_service( $service_id );
        $this->bookable_event_id = (int) $bookable_event_id;
        /**
         * Values needed during calculation, derived from input values.
         */
        $this->price = $this->get_initial_price_per_hour();
        $this->ruleset = new Ruleset( $this->calendar->get_prices() );
        $this->start_timestamp = strtotime( $start );
        $this->end_timestamp = strtotime( $end );
        $this->duration = $this->end_timestamp - $this->start_timestamp;
        $this->duration_minutes = $this->duration / 60;
        $this->step = $this->calendar->get_setting( 'step' );
        $this->steps = $this->duration_minutes / $this->step;
        /**
         * Separate prices, later added up.
         */
        $this->prices = [
            'duration' => new Price(),
        ];
        /**
         * Run actions.
         */
        $this->calculate_duration_price();
        $this->calculate_total_price();
        $this->add_coupon_info();
    }
    
    /**
     * Price per hour varies depending on if the booking
     * is regular, for a service or for a bookable event.
     * Price falls back to calendar price per hour.
     *
     * @return float
     */
    protected function get_initial_price_per_hour()
    {
        
        if ( $this->bookable_event_id > 0 ) {
            $event = $this->calendar->get_event( $this->bookable_event_id );
            $data = $event->get_booking_data();
            if ( is_numeric( $data['price'] ) && $data['price'] >= 0 ) {
                return (double) $data['price'];
            }
        }
        
        if ( is_array( $this->service ) && isset( $this->service['price'] ) && is_numeric( $this->service['price'] ) ) {
            return (double) $this->service['price'];
        }
        return (double) $this->calendar->get_setting( 'price' );
    }
    
    /**
     * Returns the result of the price calculation.
     *
     * @return array [(float)value, (array)breakdown]
     */
    public function get_price()
    {
        return [
            'value'     => round( $this->total->get_value(), 2 ),
            'breakdown' => $this->total->get_breakdown(),
            'info'      => $this->get_info(),
        ];
    }
    
    /**
     * Calculates the price for the duration of the event
     * by calculating the price for each event step.
     */
    protected function calculate_duration_price()
    {
        $current = $this->start_timestamp;
        for ( $i = $this->steps ;  $i > 0 ;  $i-- ) {
            $start = $current;
            $current += $this->step * 60;
            $end = $current;
            $this->calculate_step_price( $start, $end );
        }
    }
    
    /**
     * Calculates the price for an event step.
     *
     * @param integer $start Step start timestamp.
     * @param integer $end Step end timestamp.
     */
    protected function calculate_step_price( $start, $end )
    {
        $step = new Step(
            $this,
            $start,
            $end,
            $this->coupons,
            $this->step,
            $this->duration,
            $this->price
        );
        $price = new Price( $step->get_price_per_step() );
        foreach ( $this->ruleset->get_modifiers( $step ) as $modifier ) {
            $price->modify( $modifier );
        }
        $this->prices['duration']->add_price( $price );
    }
    
    /**
     * Adds up all the prices and applies any late modifiers.
     */
    protected function calculate_total_price()
    {
        $this->total = array_reduce( $this->prices, function ( $total, $price ) {
            $price->apply_total_modifiers();
            $total->add_price( $price );
            return $total;
        }, new Price() )->apply_final_modifiers();
    }
    
    /**
     * Add info about coupons.
     */
    protected function add_coupon_info()
    {
        if ( count( $this->applied_coupons ) > 0 ) {
            $this->add_info( 'coupons', sprintf( esc_html( _n(
                'Applied coupon: %s.',
                'Applied coupons: %s.',
                count( $this->applied_coupons ),
                'booking-weir'
            ) ), implode( ', ', $this->applied_coupons ) ) );
        }
        if ( count( $this->invalid_coupons ) > 0 ) {
            $this->add_info( 'coupons', sprintf( esc_html( _n(
                'Invalid coupon: %s.',
                'Invalid coupons: %s.',
                count( $this->invalid_coupons ),
                'booking-weir'
            ) ), implode( ', ', $this->invalid_coupons ) ) );
        }
    }
    
    /**
     * Keep track of coupons that have been used.
     *
     * @param string $code
     */
    public function set_coupon_used( $code )
    {
        if ( !in_array( $code, $this->applied_coupons ) ) {
            $this->applied_coupons[] = $code;
        }
    }
    
    /**
     * Keep track of coupons that were entered but don't exist.
     *
     * @param string $code
     */
    public function set_coupon_not_invalid( $code )
    {
        $this->invalid_coupons = array_diff( $this->invalid_coupons, [ mb_strtolower( $code ) ] );
    }
    
    /**
     * Add info about price calculation.
     *
     * @param string $type Info category.
     * @param string $message Info text.
     */
    public function add_info( $type, $message )
    {
        if ( !is_array( $this->info ) ) {
            $this->info = [];
        }
        if ( !isset( $this->info[$type] ) ) {
            $this->info[$type] = [];
        }
        $this->info[$type][] = $message;
        $this->info[$type] = array_unique( $this->info[$type] );
    }
    
    /**
     * Get info about price calculation.
     *
     * @return array
     */
    protected function get_info()
    {
        return $this->info;
    }
    
    /**
     * Parse a string of coupon(s) to an array.
     *
     * @param string $coupons
     * @return array
     */
    protected function parse_coupons( $coupons = '' )
    {
        $this->applied_coupons = [];
        $this->invalid_coupons = [];
        if ( !$coupons ) {
            return [];
        }
        /**
         * Lowercase, explode and trim.
         */
        $coupons = array_map( 'trim', explode( ',', mb_strtolower( $coupons ) ) );
        /**
         * Filter out empty and duplicate values.
         */
        $coupons = array_unique( array_filter( $coupons ) );
        /**
         * Invalid unless proven otherwise.
         */
        $this->invalid_coupons = $coupons;
        /**
         * Return first coupon when multiple are not allowed.
         */
        
        if ( count( $coupons ) > 1 && !$this->calendar->allows_using_multiple_coupons() ) {
            $this->add_info( 'coupons', esc_html__( 'Only one coupon can be used at a time.', 'booking-weir' ) );
            return [ $coupons[0] ];
        }
        
        return $coupons;
    }
    
    /**
     * Get the service used with the booking the price is calculated for.
     *
     * @return array|false
     */
    public function get_service()
    {
        return $this->service;
    }
    
    /**
     * Is an extra selected.
     *
     * @param string $id Extra ID.
     * @return bool
     */
    public function has_extra( $id )
    {
        return in_array( $id, array_keys( $this->extras ) );
    }

}