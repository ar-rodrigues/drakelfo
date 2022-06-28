<?php
/**
Plugin Name: WooCommerce - Hide categories where no products are visible to user
Plugin URI: https://www.randall.nl
Description: Excludes categories with no visible products from the WooCommerce category overview in shop
Version: 1.2
Author: Randall Kam
Author URI: https://www.randall.nl
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'ExcludeCats' ) ) :

class ExcludeCats {

	public $version = '1.2',
		   $do = false,
		   $exclude = array();

	protected static $_instance = null;

	/**
	 * Main Plugin Instance
	 *
	 * Ensures only one instance of plugin is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
		add_action('wp action', array( $this, 'checkShopSearch' ), 10, 1 );
		
		// CHECK CATEGORIES FOR VISIBLE PRODUCTS
		add_action('wp_head', array( $this, 'get_excluded_cats' ), 10, 3 );
		
		// ADD THE SHOP FILTER
		add_filter( 'woocommerce_product_subcategories_args', array( $this, 'filter_woocommerce_product_subcategories_args'), 10, 1 );
		
		// ADD THE WIDGET SIDEBAR FILTER
		add_filter( 'woocommerce_product_categories_widget_args', array( $this, 'kfg_exclude_categories_from_widget'), 10, 1 );
		
 
	}

	// CHECK IF WE NEED TO RUN EVERYTHING
	public function checkShopSearch($query){
		if(is_shop() || is_search()){
			$this->do = true;
		}
	}
	
	// GET CATEGORIES WITH NO VISIBLE PRODUCTS AND PUT IN GLOBAL IF GLOBAL FALSE
	public function get_excluded_cats( $terms, $taxonomies = NULL, $args = NULL ) {
		
		if(!$terms){
			return;
		}
		
		$current_tax = get_query_var( 'product_cat' );
		$term =get_term_by( 'slug', $current_tax, 'product_cat');
		$term_id = $term->term_id;
		
		$args = array(
			'parent' => $term_id,
			'hide_empty' => false,
			'hierarchical' => false,
		);
		
		$product_categories = get_terms( 'product_cat', $args );		
		
		// if a product category and on the shop page
		//if ( in_array( 'product_cat', $taxonomies ) && ! is_admin() && is_shop() ) {
		if ( ! is_admin() && is_shop() ) {
			
			foreach ( $product_categories as $key => $term ) {
				
				unset($this->exclude);
				
				if($term->taxonomy=='product_cat'){
					
					$posts         = get_posts( array( 'post_type' => 'product', 'posts_per_page' => -1, 'product_cat' => $term->slug, 'fields' => 'ids' ) );
					$show_category = false;
					
					foreach ( $posts as $post ) {

						$product         = new WC_Product( $post );
						$visible_product = $product->is_visible();

						if ( true === $visible_product ) {
							$show_category = true;
							break;
						}

					}
				
				
				}
				
				if ( false === $show_category ) {
					$this->exclude[] = $term->term_id;
				}
			}
		}
	}	
	
	public function get_parent_cats ($cat_termid, $found = array()) {
		array_push ($found, $cat_termid);
		$term = get_term_by( 'term_id', $cat_termid, 'product_cat');

		if( !is_object($term) ) {
			return $found;
		}
		
		if($term->parent > 0){
			return $this->get_parent_cats($term->parent, $found);
		}
		return $found;
	}	

	// ADD FILTERS FOR CATEGORIES AND EXCLUDE EMPTY
	public function filter_woocommerce_product_subcategories_args( $temp_args = array() ) { 

		$current_tax = get_query_var( 'product_cat' );
		$term =get_term_by( 'slug', $current_tax, 'product_cat');
		
		if( !is_object($term) ) {
			return $temp_args;
		}		
		
		$term_id = $term->term_id;
		
		$temp_args = array(
			'parent'       => $term_id,
			'menu_order'   => 'ASC',
			'hide_empty'   => 1,
			'hierarchical' => 1,
			'taxonomy'     => 'product_cat',
			'pad_counts'   => 1,
			'include'		=> NULl,
			'exclude'		=> $this->exclude,
		);
		
		return $temp_args; 
	}
	
	public function kfg_exclude_categories_from_widget( $category_list_args ) {
		
		$current_tax 	= get_query_var( 'product_cat' );
		$term 			= get_term_by( 'slug', $current_tax, 'product_cat');
		
		if( !is_object($term) ) {
			return $category_list_args;
		}
		
		$term_id 		= $term->term_id;
		
		$parents = $this->get_parent_cats($term_id);
		
		$args = array(
			'hide_empty' => false,
			'hierarchical' => true,
		);
		
		$product_categories = get_terms( 'product_cat', $args );
		$wexclude = array();
		
		foreach ( $product_categories as $category ) {
			$posts         = get_posts( array( 'post_type' => 'product', 'posts_per_page' => -1, 'product_cat' => $category->slug, 'fields' => 'ids' ) );
			$show_category = false;
			foreach ( $posts as $post ) {
				$product         = new wC_Product( $post );
				$visible_product = $product->is_visible();
				if ( true === $visible_product ) {
					$show_category = true;
					break;
				}
			}
			if ( false === $show_category || ( $category->parent > 0 && !in_array($category->parent,$parents) ) ) {
				$wexclude[] = $category->term_id;
			}
		}
		if ( ! empty( $wexclude ) ) {
			$category_list_args['exclude'] = implode( ',', $wexclude );
			unset( $category_list_args['include'] );
		}
		return $category_list_args;
	}	

} // class ExcludeCats

endif; // class_exists

/**
 * Returns the main instance of the plugin class to prevent the need to use globals.
 *
 * @since  2.0
 * @return WooCommerce_PostcodeAPInu
 */
function ExcludeCats() {
	return ExcludeCats::instance();
}

ExcludeCats(); // load plugin