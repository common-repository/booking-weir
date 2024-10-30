<?php

namespace wsd\bw\core\booking;

use  wsd\bw\Context ;
use  wsd\bw\core\calendars\Calendars ;
use  wsd\bw\core\Email ;
use  wsd\bw\core\Notices ;
use  wsd\bw\core\Pdf ;
use  wsd\bw\core\events\Event ;
use  WP_Query ;
/**
 * Payment class.
 */
final class Payment
{
    /** @var Context */
    protected  $context ;
    /** @var Calendars */
    protected  $calendars ;
    /** @var Email */
    protected  $email ;
    /** @var Pdf */
    protected  $pdf ;
    /** @var Notices */
    protected  $notices ;
    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        Calendars $calendars,
        Email $email,
        Pdf $pdf,
        Notices $notices
    )
    {
        $this->context = $context;
        $this->calendars = $calendars;
        $this->email = $email;
        $this->pdf = $pdf;
        $this->notices = $notices;
    }
    
    /**
     * Registers functionality through WordPress hooks.
     */
    public function register()
    {
        add_action( 'init', [ $this, 'load_payment_methods' ] );
    }
    
    /**
     * Load all payment methods.
     */
    public function load_payment_methods()
    {
        foreach ( $this->context->files( 'includes/core/booking/paymentmethods' ) as $payment_method ) {
            include_once $payment_method;
        }
        foreach ( apply_filters( 'bw_payment_method_classes', [] ) as $class ) {
            $class = new $class( $this->context );
            $class->register();
        }
    }
    
    /**
     * Get available payment methods.
     * Payment methods can register themselves using the filter.
     *
     * @return array [payment_method_id => payment_method_label]
     */
    public static function get_methods()
    {
        return apply_filters( 'bw_payment_methods', [] );
    }
    
    public static function get_method_data()
    {
        return apply_filters( 'bw_payment_method_data', [] );
    }
    
    public function get_price_types()
    {
        return apply_filters( 'bw_price_types', [] );
    }
    
    public static function get_instructions( $event )
    {
        $method = $event->get_payment_method();
        if ( !$method ) {
            return '';
        }
        $type = $event->get_payment_type();
        $instructions = apply_filters( 'bw_payment_method_instructions', [] );
        $payment_type = ( $type['amount'] >= 100 ? 'full' : 'escrow' );
        if ( !isset( $instructions[$method] ) || !isset( $instructions[$method][$payment_type] ) ) {
            return '';
        }
        return sprintf(
            $instructions[$method][$payment_type],
            '<a href="' . esc_url( $event->get_booking_link() ) . '" target="_blank" rel="noopener noreferrer">',
            '</a>',
            (int) $type['amount'] . '%'
        );
    }
    
    /**
     * Get transaction info.
     * Payment methods should utilize the filter to provide the info when possible.
     *
     * @param string $payment_method
     * @param string $transaction_id
     * @return mixed `array` of transaction info, `string` error message or `bool` false for no support.
     */
    public static function get_transaction( $payment_method, $transaction_id )
    {
        return apply_filters(
            'bw_get_transaction',
            false,
            $payment_method,
            $transaction_id
        );
    }
    
    /**
     * Send invoice e-mail with generated PDF.
     *
     * @param Event $event
     * @return bool
     */
    public function send_invoice( Event $event )
    {
        $calendar = $event->get_calendar();
        if ( !$calendar->get_setting( 'invoiceEmailEnabled' ) ) {
            return false;
        }
        $attachments = [];
        
        if ( $calendar->get_setting( 'invoicePdfEnabled' ) ) {
            $requires_payment = $calendar->get_default_payment_method() !== false;
            if ( $requires_payment || $calendar->get_setting( 'invoicePdfNoPayment' ) ) {
                $attachments[] = $this->pdf->get_invoice_path( $event );
            }
        }
        
        $mail_data = apply_filters( 'bw_invoice_email', [
            'email'       => $event->get_email(),
            'title'       => esc_html_x( 'Booking invoice', 'Invoice e-mail title', 'booking-weir' ),
            'content'     => strtr( $calendar->get_setting( 'templateInvoiceEmailContent' ), $event->get_template_strings() ),
            'headers'     => [ 'Content-Type: text/html; charset=UTF-8' ],
            'attachments' => $attachments,
        ], $event );
        return $this->email->send( $mail_data, $calendar );
    }
    
    /**
     * Find an event using a transaction ID.
     *
     * @param mixed $transaction_id
     * @return Event|false
     */
    public function get_event( $transaction_id )
    {
        $wp_query = new WP_Query( [
            'post_type'      => $this->context->get( 'event-post-type' )::SLUG,
            'posts_per_page' => 1,
            'meta_key'       => 'bw_transaction_id',
            'meta_value'     => $this->context->get( 'sanitizer' )->sanitize_id( $transaction_id ),
            'post_status'    => 'publish',
        ] );
        
        if ( $wp_query->have_posts() ) {
            while ( $wp_query->have_posts() ) {
                $wp_query->the_post();
                $id = get_the_ID();
                $calendar_id = get_post_meta( $id, 'bw_calendar_id', true );
                if ( $calendar = $this->calendars->get_calendar( $calendar_id ) ) {
                    
                    if ( $event = $calendar->get_event( $id ) ) {
                        wp_reset_postdata();
                        return $event;
                    }
                
                }
            }
            wp_reset_postdata();
        }
        
        return false;
    }
    
    protected function print_errors( $errors )
    {
        $this->notices->add_errors( esc_html__( 'Payment errors', 'booking-weir' ), $errors );
    }

}