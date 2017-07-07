<?php
/**
 * Plugin Name: WooCommerce Store-wide Discount
 * Version: 2.0.1
 * Description: Add a discount to all your products!
 * Author: Emil Kjær Eriksen <hello@emileriksen.me>
 * Text Domain: wcswd
 * Domain Path: /languages/
 * License: GPL v3
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}

require __DIR__ . '/includes/Functions.php';

/**
 * Return plugin container.
 */
// @codingStandardsIgnoreLine
function WCSWD() {
	static $container = null;

	if ( is_null( $container ) ) {
		$container = new \Pimple\Container();
	}

	return $container;
}

WCSWD()['version'] = '2.0.1';
WCSWD()['textdomain'] = 'wcswd';
WCSWD()['plugin_path'] = untrailingslashit( plugin_dir_path( __FILE__ ) );
WCSWD()['plugin_url'] = untrailingslashit( plugins_url( '/', __FILE__ ) );
WCSWD()['languages_path'] = WCSWD()['plugin_path'] . '/languages/';

WCSWD()['localizer'] = function( $c ) {
	return new \WCSWD\Localizer( $c['textdomain'], $c['languages_path'] );
};
add_action( 'plugins_loaded', array( WCSWD()['localizer'], 'load_plugin_textdomain' ) );

WCSWD()['discounter'] = function( $c ) {
	return new \WCSWD\Frontend\Discounter();
};
add_action( 'woocommerce_init', array( WCSWD()['discounter'], 'init' ) );

WCSWD()['admin_settings'] = function( $c ) {
    return new \WCSWD\Admin\Settings\StoreWideDiscount();
};
add_filter( 'woocommerce_get_settings_pages', function( $settings ) {
    $settings[] = WCSWD()['admin_settings'];
} );

do_action( 'wcswd_init', WCSWD() );
