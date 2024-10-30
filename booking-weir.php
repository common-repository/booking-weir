<?php

/**
 * Plugin Name:       Booking Weir
 * Plugin URI:        https://chap.website/booking-weir
 * Description:       Display a calendar that allows to select and book a time.
 * Version:           1.0.11
 * Requires at least: 5.6
 * Requires PHP:      7.0
 * Author:            websevendev
 * Author URI:        https://chap.website/author/websevendev
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       booking-weir
 */
defined( 'ABSPATH' ) || exit;

if ( !function_exists( 'wsd_bw_fs' ) ) {
    function wsd_bw_fs()
    {
        global  $wsd_bw_fs ;
        
        if ( !isset( $wsd_bw_fs ) ) {
            require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
            $wsd_bw_fs = fs_dynamic_init( [
                'id'             => '7570',
                'slug'           => 'booking-weir',
                'type'           => 'plugin',
                'public_key'     => 'pk_3c1adf0ee68ed0ae18eae451475b8',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => [
                'slug'       => 'booking-weir',
                'first-path' => 'admin.php?page=booking-weir',
                'contact'    => !defined( 'BOOKING_WEIR_WHITE_LABEL' ) || !BOOKING_WEIR_WHITE_LABEL,
                'support'    => !defined( 'BOOKING_WEIR_WHITE_LABEL' ) || !BOOKING_WEIR_WHITE_LABEL,
            ],
                'is_live'        => true,
            ] );
        }
        
        return $wsd_bw_fs;
    }
    
    wsd_bw_fs();
    wsd_bw_fs()->add_filter( 'plugin_icon', function () {
        return dirname( __FILE__ ) . '/assets/img/icon.png';
    } );
    do_action( 'wsd_bw_fs_loaded' );
}

if ( !function_exists( 'get_plugin_data' ) ) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$plugin = get_plugin_data( __FILE__, false, false );
defined( 'BOOKING_WEIR_VER' ) || define( 'BOOKING_WEIR_VER', $plugin['Version'] );
define( 'BOOKING_WEIR_FILE', __FILE__ );
define( 'BOOKING_WEIR_DIR', dirname( __FILE__ ) );
register_activation_hook( BOOKING_WEIR_FILE, function ( $network_wide ) {
    do_action( 'bw_activate', $network_wide );
} );
register_deactivation_hook( BOOKING_WEIR_FILE, function ( $network_wide ) {
    do_action( 'bw_deactivate', $network_wide );
} );
if ( file_exists( BOOKING_WEIR_DIR . '/vendor/autoload.php' ) ) {
    include BOOKING_WEIR_DIR . '/vendor/autoload.php';
}
include BOOKING_WEIR_DIR . '/includes/util/Helpers.php';
include BOOKING_WEIR_DIR . '/includes/util/Datetime.php';
wsd\bw\Plugin::load( BOOKING_WEIR_FILE );