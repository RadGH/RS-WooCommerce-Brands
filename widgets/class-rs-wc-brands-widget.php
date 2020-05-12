<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Brands Widget.
 */
class WC_Widget_Product_Brands extends WC_Widget {
	
	public $brand_ancestors;
	
	public $current_brand;
	
	public $index = 0;
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'rs-wc rs-wc-brands-widget';
		$this->widget_description = 'A list or dropdown of product brands.';
		$this->widget_id          = 'rswc_product_brands';
		$this->widget_name        = 'WooCommerce product brands';
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Product Brands', 'woocommerce' ),
				'label' => __( 'Title', 'woocommerce' )
			),
			'dropdown' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show as dropdown', 'woocommerce' )
			),
			'count' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show product counts', 'woocommerce' )
			),
			'hierarchical' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show hierarchy', 'woocommerce' )
			),
			'show_children_only' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Only show children of the current brand', 'woocommerce' )
			),
			'hide_empty' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Hide empty brands', 'woocommerce' )
			),
			'show_option_all' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show option for "All" (remove filtering)', 'woocommerce' )
			),
			'show_option_all_text'  => array(
				'type'  => 'text',
				'std'   => __( 'Any Brand', 'woocommerce' ),
				'label' => __( '"All" option text', 'woocommerce' )
			),
			'display_limit'  => array(
				'type'  => 'number',
				'std'   => 0,
				'min'   => 0,
				'label' => __( 'Display Limit (0 to show all)', 'rs-wc-brands' )
			)
		);
		
		parent::__construct();
	}
	
	/**
	 * Get a number of terms with the most posts, sorted alphabetically
	 *
	 * @param string $taxonomy
	 * @param int $max
	 *
	 * @return array|bool|int|WP_Error
	 */
	function get_popular_terms( $taxonomy = 'rswc_brand', $max = 10 ) {
		// Get children sorted by count, highest first
		$args = array(
			'taxonomy' => $taxonomy,
			'parent' => 0,
			'count' => true,
			'orderby' => 'count',
			'order' => 'DESC',
		);
		
		$terms = get_terms($args);
		if ( empty($terms) ) return false;
		
		// Use the first N terms, discard the rest
		if ( count($terms) > $max ) {
			$terms = array_slice( $terms, 0, $max );
		}
		
		// Sort by name now that we've removed the lower end of terms
		if ( $terms ) usort( $terms, array(&$this, '__sort_by_object_name') );
		
		// The array is now sorted by terms
		return $terms;
	}
	
	function __sort_by_object_name( $a, $b ) {
		return strcmp($a->name, $b->name);
	}
	
	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $wp_query, $post;
		
		$count                = isset( $instance['count'] ) ? $instance['count'] : $this->settings['count']['std'];
		$hierarchical         = isset( $instance['hierarchical'] ) ? $instance['hierarchical'] : $this->settings['hierarchical']['std'];
		$show_children_only   = isset( $instance['show_children_only'] ) ? $instance['show_children_only'] : $this->settings['show_children_only']['std'];
		$dropdown             = isset( $instance['dropdown'] ) ? $instance['dropdown'] : $this->settings['dropdown']['std'];
		$hide_empty           = isset( $instance['hide_empty'] ) ? $instance['hide_empty'] : $this->settings['hide_empty']['std'];
		$show_option_all      = isset( $instance['show_option_all'] ) ? $instance['show_option_all'] : $this->settings['show_option_all']['std'];
		$show_option_all_text = isset( $instance['show_option_all_text'] ) ? $instance['show_option_all_text'] : $this->settings['show_option_all_text']['std'];
		$dropdown_args        = array( 'hide_empty' => $hide_empty );
		$list_args            = array( 'show_count' => $count, 'hierarchical' => $hierarchical, 'taxonomy' => 'rswc_brand', 'hide_empty' => $hide_empty );
		$orderby              = 'name'; // 'menu_order' also supported but the category is not sortable
		
		if ( empty($show_option_all_text) ) $show_option_all_text = $this->settings['show_option_all']['std'];
		$dropdown_args['show_option_all'] = $show_option_all ? $show_option_all_text : '';
		$list_args['show_option_all'] = $show_option_all ? $show_option_all_text : '';
		
		// Custom display limit
		$display_limit = isset( $instance['display_limit'] ) ? $instance['display_limit'] : $this->settings['display_limit']['std'];
		
		// Menu Order
		$list_args['menu_order'] = false;
		if ( $orderby == 'order' ) {
			$list_args['menu_order'] = 'asc';
		} else {
			$list_args['orderby']    = 'title';
		}
		
		// Setup Current Brand
		$this->current_brand   = false;
		$this->brand_ancestors = array();
		$query_term = get_query_var( 'product_brand' );
		
		if ( is_tax( 'rswc_brand' ) ) {
			
			$this->current_brand   = $wp_query->queried_object;
			$this->brand_ancestors = get_ancestors( $this->current_brand->term_id, 'rswc_brand' );
		
		}else if ( $query_term ) {
			
			$this->current_brand = get_term_by( 'slug', $query_term, 'rswc_brand' );
			if ( $this->current_brand ) {
				$this->brand_ancestors = get_ancestors( $this->current_brand->term_id, 'rswc_brand' );
			}
			
		} elseif ( is_singular( 'product' ) ) {
			
			$product_brand = wc_get_product_terms( $post->ID, 'rswc_brand', apply_filters( 'rswc_product_brands_widget_product_terms_args', array( 'orderby' => 'parent' ) ) );
			
			if ( ! empty( $product_brand ) ) {
				$this->current_brand   = end( $product_brand );
				$this->brand_ancestors = get_ancestors( $this->current_brand->term_id, 'rswc_brand' );
			}
			
		}
		
		// Show Siblings and Children Only
		if ( $show_children_only && $this->current_brand ) {
			
			// Top level is needed
			$top_level = get_terms(
				'rswc_brand',
				array(
					'fields'       => 'ids',
					'parent'       => 0,
					'hierarchical' => true,
					'hide_empty'   => false
				)
			);
			
			// Direct children are wanted
			$direct_children = get_terms(
				'rswc_brand',
				array(
					'fields'       => 'ids',
					'parent'       => $this->current_brand->term_id,
					'hierarchical' => true,
					'hide_empty'   => false
				)
			);
			
			// Gather siblings of ancestors
			$siblings  = array();
			if ( $this->brand_ancestors ) {
				foreach ( $this->brand_ancestors as $ancestor ) {
					$ancestor_siblings = get_terms(
						'rswc_brand',
						array(
							'fields'       => 'ids',
							'parent'       => $ancestor,
							'hierarchical' => false,
							'hide_empty'   => false
						)
					);
					$siblings = array_merge( $siblings, $ancestor_siblings );
				}
			}
			
			if ( $hierarchical ) {
				$include = array_merge( $top_level, $this->brand_ancestors, $siblings, $direct_children, array( $this->current_brand->term_id ) );
			} else {
				$include = array_merge( $direct_children );
			}
			
			$dropdown_args['include'] = implode( ',', $include );
			$list_args['include']     = implode( ',', $include );
			
			if ( empty( $include ) ) {
				return;
			}
			
		} elseif ( $show_children_only ) {
			$dropdown_args['depth']        = 1;
			$dropdown_args['child_of']     = 0;
			$dropdown_args['hierarchical'] = 1;
			$list_args['depth']            = 1;
			$list_args['child_of']         = 0;
			$list_args['hierarchical']     = 1;
		}
		
		$this->widget_start( $args, $instance );
		
		// Dropdown
		if ( $dropdown ) {
			$dropdown_defaults = array(
				'id'                 => 'product_brand-select-' . $this->index,
				'name'               => 'product_brand',
				'value_field'        => 'slug',
				'show_count'         => $count,
				'hierarchical'       => $hierarchical,
				'show_uncategorized' => 0,
				'orderby'            => $orderby,
				'selected'           => $this->current_brand ? $this->current_brand->slug : '',
				'taxonomy'           => 'rswc_brand',
			);
			
			$dropdown_args = wp_parse_args( $dropdown_args, $dropdown_defaults );
			
			$url = get_post_type_archive_link( 'product' );
			$form_url_tax = false;
			
			if ( is_tax( 'product_cat' ) ) $form_url_tax = 'product_cat';
			else if ( is_tax( 'product_tag' ) ) $form_url_tax = 'product_tag';
			
			if ( $form_url_tax ) $url = get_term_link( get_queried_object() );
			
			echo '<form action="', $url ,'" method="GET" class="autosubmit">';
			
			global $wp_query;
			
			if ( $wp_query->query ) foreach( $wp_query->query as $name => $value ) {
				if ( $name == 'product_brand' ) continue;
				if ( $name == $form_url_tax && is_tax( $form_url_tax ) ) continue; // from form action
				
				echo '<input type="hidden" name="'. esc_attr( $name ) .'" value="'. esc_attr( $value ) .'">' . "\n";
			}
			
			wp_dropdown_categories( apply_filters( 'rswc_brand_widget_dropdown_args', $dropdown_args ) );
			
			echo '</form>';
			
			// List
		} else {
			
			include_once( WC()->plugin_path() . '/includes/walkers/class-product-cat-list-walker.php' );
			
			$list_args['title_li']                   = '';
			$list_args['pad_counts']                 = 1;
			$list_args['show_option_none']           = __('No product brands exist.', 'woocommerce' );
			$list_args['current_brand']              = ( $this->current_brand ) ? $this->current_brand->term_id : '';
			$list_args['current_brand_ancestors']    = $this->brand_ancestors;
			$list_args['taxonomy']                   = 'rswc_brand';
			
			
			// Display only popular terms, or all terms?
			if ( $display_limit > 0 ) {
				$terms = $this->get_popular_terms( 'rswc_brand', $display_limit );
			}else{
				$terms = get_terms( $list_args );
			}
			
			echo '<ul class="product-brands wc-term-list rs-wc-initial-brands">';
			
			if ( empty($terms) ) {
				
				if ( $list_args['show_option_none'] ) {
					echo '<li class="option-none">', $list_args['show_option_none'], '</li>';
				}
				
			}else{
				
				if ( $list_args['show_option_all'] ) {
					$active = empty(get_query_var( 'product_brand' ));
					
					echo '<li class="term-link option-all ', ($active ? 'term-active' : 'term-inactive'), '">';
						echo '<a href="', esc_attr( remove_query_arg( 'product_brand' ) ),'">';
							echo $list_args['show_option_all'];
						echo '</a>';
					echo '</li>';
				}
				
				foreach( $terms as $i => $term ) {
					$active = get_query_var( 'product_brand' ) == $term->slug;
					$link = get_post_type_archive_link('product');
					if ( get_query_var( 'product_brand' ) ) $link = add_query_arg( 'product_brand', get_query_var( 'product_brand' ), $link );
					if ( get_query_var( 'orderby' ) ) $link = add_query_arg( 'orderby', get_query_var( 'orderby' ), $link );
					if ( get_query_var( 'order' ) ) $link = add_query_arg( 'order', get_query_var( 'order' ), $link );
					
					echo '<li class="term-link term-id-', $term->term_id, ' ', ($active ? 'term-active' : 'term-inactive'), '">';
						echo '<a href="', esc_attr( $link ),'">';
							echo esc_html( $term->name );
						echo '</a>';
						
						if ( $count ) {
							echo ' <span class="term-count">(', $term->count, ')</span>';
						}
						
					echo '</li>';
				}
				
			}
			
			echo '</ul>';
			
			// If only popular terms were displayed above, make a button to "show all"
			if ( $display_limit > 0 && !empty($terms) && count($terms) >= $display_limit ) {
				$all_terms = get_terms( $list_args );
				
				$js = "jQuery(this).closest('.rs-wc-brands-widget').find('ul.rs-wc-initial-brands').remove().end().find('ul.rs-wc-all-terms').css('display', ''); jQuery(this).closest('div.rs-wc-show-all-brands').remove(); return false;";
				
				echo '<div class="rs-wc-show-all-brands">';
					echo '<a href="" class="button rs-wc-show-all-brands-button" onclick="'. esc_attr($js) .'">Show All Brands</a>';
				echo '</div>';
				
				echo '<ul class="product-brands wc-term-list rs-wc-all-terms" style="display: none;">';
				
				if ( $all_terms ) {
					
					if ( $list_args['show_option_all'] ) {
						$active = empty(get_query_var( 'product_brand' ));
						
						echo '<li class="term-link option-all ', ($active ? 'term-active' : 'term-inactive'), '">';
						echo '<a href="', esc_attr( remove_query_arg( 'product_brand' ) ),'">';
						echo $list_args['show_option_all'];
						echo '</a>';
						echo '</li>';
					}
					
					foreach( $all_terms as $i => $term ) {
						$active = get_query_var( 'product_brand' ) == $term->slug;
						$link = get_post_type_archive_link('product');
						if ( get_query_var( 'product_brand' ) ) $link = add_query_arg( 'product_brand', get_query_var( 'product_brand' ), $link );
						if ( get_query_var( 'orderby' ) ) $link = add_query_arg( 'orderby', get_query_var( 'orderby' ), $link );
						if ( get_query_var( 'order' ) ) $link = add_query_arg( 'order', get_query_var( 'order' ), $link );
						
						echo '<li class="term-link term-id-', $term->term_id, ' ', ($active ? 'term-active' : 'term-inactive'), '">';
						echo '<a href="', esc_attr( $link ),'">';
						echo esc_html( $term->name );
						echo '</a>';
						
						if ( $count ) {
							echo ' <span class="term-count">(', $term->count, ')</span>';
						}
						
						echo '</li>';
					}
					
				}
				
				echo '</ul>';
			}
		}
		
		$this->widget_end( $args );
	}
}
