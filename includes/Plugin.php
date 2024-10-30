<?php

namespace wsd\bw;

use  wsd\bw\core\admin\About ;
use  wsd\bw\core\admin\Admin ;
use  wsd\bw\core\booking\Booking ;
use  wsd\bw\core\booking\Payment ;
use  wsd\bw\core\calendars\CalendarFactory ;
use  wsd\bw\core\calendars\Calendars ;
use  wsd\bw\config\Config ;
use  wsd\bw\core\Blocks ;
use  wsd\bw\core\Cron ;
use  wsd\bw\core\Email ;
use  wsd\bw\core\events\EventFactory ;
use  wsd\bw\core\events\EventPostType ;
use  wsd\bw\core\Logger ;
use  wsd\bw\core\Notices ;
use  wsd\bw\core\Pdf ;
use  wsd\bw\core\rest\API ;
use  wsd\bw\core\Sanitizer ;
use  wsd\bw\core\ScriptData ;
use  wsd\bw\core\Shortcode ;
use  wsd\bw\integrations\woocommerce\WooCommerce ;
/**
 * Main Plugin class.
 *
 * Responsible for initializing the plugin.
 */
final class Plugin
{
    /**
     * The plugin context object
     *
     * @var Context
     */
    private  $context ;
    /**
     * Main instance of the plugin.
     *
     * @var Plugin|null
     */
    private static  $instance = null ;
    /**
     * Sets the plugin main file.
     *
     * @param string $main_file Absolute path to the plugin main file.
     */
    public function __construct( $main_file )
    {
        $this->context = new Context( $main_file );
    }
    
    /**
     * Registers the plugin with WordPress.
     */
    public function register()
    {
        add_filter( 'bw_context', [ $this, 'context' ] );
        add_action( 'after_setup_theme', [ $this, 'load_textdomain' ] );
        add_action( 'init', [ $this, 'white_label' ], 4 );
        add_action( 'init', [ $this, 'init' ], 5 );
    }
    
    /**
     * Retrieves the plugin context object.
     *
     * @return Context Plugin context.
     */
    public function context()
    {
        return $this->context;
    }
    
    /**
     * Retrieves the main instance of the plugin.
     *
     * @return Plugin Plugin main instance.
     */
    public static function instance()
    {
        return static::$instance;
    }
    
    /**
     * Loads the plugin main instance and initializes it.
     *
     * @param string $main_file Absolute path to the plugin main file.
     * @return bool True if the plugin main instance could be loaded, false otherwise.
     */
    public static function load( $main_file )
    {
        if ( static::$instance !== null ) {
            return false;
        }
        static::$instance = new static( $main_file );
        static::$instance->register();
        return true;
    }
    
    /**
     * Initialize plugin.
     */
    public function init()
    {
        $sanitizer = new Sanitizer( $this->context );
        $sanitizer->register();
        $this->context->add( 'sanitizer', $sanitizer );
        $calendar_settings = new Config( $this->context, $this->context->config_path( 'CALENDAR_SETTINGS_SCHEMA' ) );
        $this->context->add( 'calendar-settings-schema', $calendar_settings );
        $event_meta = new Config( $this->context, $this->context->config_path( 'EVENT_META_SCHEMA' ) );
        $this->context->add( 'event-meta-schema', $event_meta );
        $event_types = new Config( $this->context, $this->context->config_path( 'EVENT_TYPES' ) );
        $this->context->add( 'event-types', $event_types );
        $booking_statuses = new Config( $this->context, $this->context->config_path( 'BOOKING_STATUSES' ) );
        $this->context->add( 'booking-statuses', $booking_statuses );
        $field_types = new Config( $this->context, $this->context->config_path( 'FIELD_TYPES' ) );
        $this->context->add( 'field-types', $field_types );
        $default_fields = new Config( $this->context, $this->context->config_path( 'DEFAULT_FIELDS' ) );
        $this->context->add( 'default-fields', $default_fields );
        $calendar_factory = new CalendarFactory( $this->context );
        $this->context->add( 'calendar-factory', $calendar_factory );
        $event_factory = new EventFactory( $this->context );
        $this->context->add( 'event-factory', $event_factory );
        $logger = new Logger( $this->context );
        $logger->register();
        $this->context->add( 'logger', $logger );
        $cron = new Cron( $this->context, $logger );
        $cron->register();
        $this->context->add( 'cron', $cron );
        $notices = new Notices( $this->context );
        $notices->register();
        $this->context->add( 'notices', $notices );
        $pdf = new Pdf( $this->context );
        $pdf->register();
        $this->context->add( 'pdf', $pdf );
        $email = new Email( $this->context );
        $email->register();
        $this->context->add( 'email', $email );
        $event_post_type = new EventPostType( $this->context, $event_meta, $event_factory );
        $event_post_type->register();
        $this->context->add( 'event-post-type', $event_post_type );
        $script_data = new ScriptData(
            $this->context,
            $event_meta,
            $event_types,
            $booking_statuses
        );
        $script_data->register();
        $this->context->add( 'script-data', $script_data );
        $rest = new API( $this->context );
        $rest->register();
        $this->context->add( 'rest', $rest );
        $admin = new Admin( $this->context, $event_post_type, $script_data );
        $admin->register();
        $this->context->add( 'admin', $admin );
        $about = new About( $this->context );
        $about->register();
        $this->context->add( 'about', $about );
        $calendars = new Calendars(
            $this->context,
            $calendar_settings,
            $calendar_factory,
            $admin
        );
        $this->context->add( 'calendars', $calendars );
        $payment = new Payment(
            $this->context,
            $calendars,
            $email,
            $pdf,
            $notices
        );
        $payment->register();
        $this->context->add( 'payment', $payment );
        $booking = new Booking(
            $this->context,
            $calendars,
            $payment,
            $notices,
            $email,
            $sanitizer
        );
        $booking->register();
        $this->context->add( 'booking', $booking );
        $shortcode = new Shortcode( $this->context, $calendars );
        $shortcode->register();
        $this->context->add( 'shortcode', $shortcode );
        $blocks = new Blocks( $this->context );
        $blocks->register();
        $this->context->add( 'blocks', $blocks );
        
        if ( class_exists( 'WooCommerce' ) ) {
            $wc = new WooCommerce( $this->context, $calendars );
            $wc->register();
            $this->context->add( 'wc', $wc );
        }
    
    }
    
    /**
     * Load plugin textdomain.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain( $this->context->plugin_slug(), false, apply_filters( 'bw_plugin_textdomain_path', false ) );
    }
    
    /**
     * White label mode modifications.
     */
    public function white_label()
    {
        if ( !defined( 'BOOKING_WEIR_WHITE_LABEL' ) ) {
            return;
        }
        $plugin = get_plugin_data( BOOKING_WEIR_FILE, false, false );
        $white_label_name = _x( 'Booking', 'White label plugin name', 'booking-weir' );
        $white_label_strings = [
            __( 'Booking Weir', 'booking-weir' ) => $white_label_name,
            $plugin['Name']                      => $white_label_name,
            $plugin['Title']                     => $white_label_name,
            $plugin['Description']               => _x( 'Booking plugin.', 'White label plugin description', 'booking-weir' ),
            $plugin['PluginURI']                 => '',
            $plugin['Author']                    => '',
            $plugin['AuthorName']                => '',
            $plugin['AuthorURI']                 => '',
        ];
        /**
         * Note: `_x()` values don't seem to pass through here.
         */
        add_filter(
            'gettext_' . $this->context->plugin_slug(),
            function ( $translation, $text ) use( $white_label_strings ) {
            if ( in_array( $text, array_keys( $white_label_strings ) ) ) {
                return $white_label_strings[$text];
            }
            return $translation;
        },
            50,
            2
        );
        add_action( 'admin_print_styles', function () {
            $styles = 'a[href*="admin.php?page=booking-weir-account"],' . 'a[href*="admin.php?page=booking-weir-pricing"],' . '[data-plugin*="booking-weir.php"] .row-actions > .upgrade,' . '[data-plugin*="booking-weir.php"] .row-actions > .booking-weir {' . 'display: none !important;' . '}';
            printf( '<style id="bw-white-label">%s</style>', $styles );
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } );
    }

}