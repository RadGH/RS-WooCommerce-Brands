<?php
/*
Plugin Name: RS WooCommerce Brands
Version:     1.0.1
Plugin URI:  http://radleysustaire.com/
Description: WordPress plugin which adds a new taxonomy to your products, Brands. Includes a widget to filter brands for use in your store's sidebar, and some utility functions to use brands in other situations.
Author:      Radley Sustaire
Author URI:  mailto:radleygh@gmail.com
License:     Copyright (c) 2017 Radley Sustaire
*/

if ( !defined( 'ABSPATH' ) ) exit;

define( 'RSWCB_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'RSWCB_PATH', dirname(__FILE__) );
define( 'RSWCB_VERSION', '1.0.1' );

add_action( 'plugins_loaded', 'rswcb_init_plugin' );
register_activation_hook( __FILE__, 'rswcb_plugin_activate' );
register_deactivation_hook( __FILE__, 'rswcb_plugin_deactivate' );

// Initialize plugin: Load plugin files
function rswcb_init_plugin() {
	include_once( RSWCB_PATH . '/includes/brands.php' );
	
	include_once( RSWCB_PATH . '/widgets/class-rs-wc-brands-widget.php' );
	include_once( RSWCB_PATH . '/includes/widgets.php' );
}

// When activating the plugin: flush rewrite rules, set up custom user roles
function rswcb_plugin_activate() {
	include_once( RSWCB_PATH . '/includes/brands.php' );
	
	flush_rewrite_rules();
}

// When deactivating the plugin: flush rewrite rules
function rswcb_plugin_deactivate() {
	flush_rewrite_rules();
}