<?php

if ( !defined( 'ABSPATH' ) ) exit;

// Register Custom Taxonomy
function rswcb_register_brands_taxonomy() {
	$labels = array(
		'name'                       => 'Brands',
		'singular_name'              => 'Brand',
		'menu_name'                  => 'Brands',
		'all_items'                  => 'All Brands',
		'parent_item'                => 'Parent Brand',
		'parent_item_colon'          => 'Parent Brand:',
		'new_item_name'              => 'New Brand Name',
		'add_new_item'               => 'Add New Brand',
		'edit_item'                  => 'Edit Brand',
		'update_item'                => 'Update Brand',
		'view_item'                  => 'View Brand',
		'separate_items_with_commas' => 'Separate brands with commas',
		'add_or_remove_items'        => 'Add or remove brands',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Brands',
		'search_items'               => 'Search Brand',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No brands',
		'items_list'                 => 'Brands list',
		'items_list_navigation'      => 'Brands list navigation',
	);
	
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'query_var'                  => 'product_brand',
	);
	
	register_taxonomy( 'rswc_brand', array( 'product' ), $args );
}
add_action( 'init', 'rswcb_register_brands_taxonomy', 6 );


function rswcb_prepend_brand_name( $title, $post_id = null ) {
	if ( is_admin() ) return $title;
	if ( did_action( 'wp_footer' ) || doing_action( 'wp_footer' ) ) return $title;
	if ( $post_id === null ) return $title;
	
	if ( get_post_type( $post_id ) == 'product' ) {
		$brands = get_the_terms( $post_id, 'rswc_brand' );
		
		if ( $brands ) foreach( $brands as $brand ) {
			if ( doing_action( 'wp_head' ) )
				$title = $brand->name . ' ' . $title; // No html in <head>
			else
				$title = '<strong class="brand-name">' . $brand->name . '</strong> ' . $title;
			break;
		}
	}
	
	return $title;
}
add_filter( 'the_title', 'rswcb_prepend_brand_name', 10, 2 );