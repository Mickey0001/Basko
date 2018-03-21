<?php

/*
-------------------------------------------------------------------------------------------------------
	Get The Active Parent Theme
-------------------------------------------------------------------------------------------------------
*/
function organic_cc_which_theme_is_active() {
  $theme = get_template();
  return $theme;
}

/*
-------------------------------------------------------------------------------------------------------
	Register Tour Date Custom Post Type
-------------------------------------------------------------------------------------------------------
*/
function organic_cc_register_cpt_tour_date() {

  include_once plugin_dir_path( __FILE__ ) . '/post-types/post-type-tour-date.php';

	$labels = array(
		'name'								=> _x( 'Tour Dates', 'Tour Dates', 'organic-custom-content' ),
		'singular_name'				=> _x( 'Tour Date', 'Tour Date', 'organic-custom-content' ),
		'add_new'							=> _x( 'Add New Date', 'Add New Date', 'organic-custom-content' ),
		'add_new_item' 				=> __( 'Add New Tour Date', 'organic-custom-content' ),
		'edit_item' 					=> __( 'Edit Tour Date', 'organic-custom-content' ),
		'new_item' 						=> __( 'New Tour Date', 'organic-custom-content' ),
		'view_item' 					=> __( 'View Tour Date', 'organic-custom-content' ),
		'search_items' 				=> __( 'Search Tour Dates', 'organic-custom-content' ),
		'not_found' 					=> __( 'No tour dates found', 'organic-custom-content' ),
		'not_found_in_trash' 	=> __( 'No tour dates found in Trash', 'organic-custom-content' ),
		'parent_item_colon' 	=> __( 'Parent Tour Date:', 'organic-custom-content' ),
		'menu_name' 					=> __( 'Tour Dates', 'organic-custom-content' ),
	);

	$args = array(
		'labels' 							=> $labels,
		'hierarchical' 				=> false,
		'description' 				=> __( 'The Organic Themes Tour Date custom post type.', 'organic-custom-content' ),
		'supports' 						=> false,
		'public' 							=> false,
		'show_ui' 						=> true,
		'show_in_nav_menus' 	=> false,
		'publicly_queryable' 	=> true,
		'exclude_from_search' => true,
		'has_archive' 				=> false,
		'query_var' 					=> true,
		'can_export' 					=> true,
		'rewrite' 						=> true,
		'capability_type' 		=> 'post',
		'menu_icon' 					=> '',
	);

	register_post_type( 'tour-date', $args );

}
