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
		'show_tagcloud'              => true,
	);
	
	register_taxonomy( 'rswc_brands', array( 'product' ), $args );
}
add_action( 'init', 'rswcb_register_brands_taxonomy', 6 );