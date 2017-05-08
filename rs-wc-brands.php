<?php
/*
Plugin Name: RS WooCommerce Brands
Version:     1.0.0
Plugin URI:  http://radleysustaire.com/
Description: WordPress plugin which adds a new taxonomy to your products, Brands. Includes a widget to filter brands for use in your store's sidebar, and some utility functions to use brands in other situations.
Author:      Radley Sustaire
Author URI:  mailto:radleygh@gmail.com
License:     Copyright (c) 2017 Radley Sustaire
*/

if ( !defined( 'ABSPATH' ) ) exit;

define( 'RSWCB_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'RSWCB_PATH', dirname(__FILE__) );
define( 'RSWCB_VERSION', '0.0.1' );

add_action( 'plugins_loaded', 'rswcb_init_plugin' );
register_activation_hook( __FILE__, 'rswcb_plugin_activate' );
register_deactivation_hook( __FILE__, 'rswcb_plugin_deactivate' );

// Initialize plugin: Load plugin files
function rswcb_init_plugin() {
	if ( !class_exists('acf') ) {
		add_action( 'admin_notices', 'rswcb_warn_no_acf' );
		return;
	}
	
	include_once( RSWCB_PATH . '/includes/brands.php' );
}

// Display ACF required warning on admin if ACF is not activated
function rswcb_warn_no_acf() {
	?>
	<div class="error">
		<p><strong>RS WooCommerce Brands:</strong> This plugin requires Advanced Custom Fields PRO in order to operate. Please install and activate ACF Pro, or disable this plugin.</p>
	</div>
	<?php
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