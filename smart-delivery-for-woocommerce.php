<?php
/**
 * Plugin Name: Smart Delivery for WooCommerce
 * Description: Smart WooCommerce delivery-date scheduling with Persian/English UI, Jalali/Gregorian calendars, holidays and configurable checkout layouts.
 * Version: 1.4.0
 * Author: Sahand Rezvan
 * Text Domain: smart-delivery-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 10.9
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SDW_VERSION', '1.4.0' );
define( 'SDW_FILE', __FILE__ );
define( 'SDW_DIR', plugin_dir_path( __FILE__ ) );
define( 'SDW_URL', plugin_dir_url( __FILE__ ) );

require_once SDW_DIR . 'includes/class-sdw-calendar.php';
require_once SDW_DIR . 'includes/class-sdw-iran-calendar.php';
require_once SDW_DIR . 'includes/class-sdw-core.php';
require_once SDW_DIR . 'includes/class-sdw-delivery.php';
require_once SDW_DIR . 'admin/class-sdw-admin.php';
require_once SDW_DIR . 'public/class-sdw-checkout.php';

register_activation_hook( __FILE__, array( 'SDW_Core', 'activate' ) );
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );
add_action( 'plugins_loaded', function() { SDW_Core::instance(); } );
