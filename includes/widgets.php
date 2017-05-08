<?php

if ( !defined( 'ABSPATH' ) ) exit;

function rswcb_register_brands_widget() {
	register_widget( 'WC_Widget_Product_Brands' );
}
add_action( 'widgets_init', 'rswcb_register_brands_widget' );