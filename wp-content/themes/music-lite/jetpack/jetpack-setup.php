<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

/**
 * Add support for Jetpack's Featured Content and Infinite Scroll
 */

if ( ! function_exists( 'music_lite_jetpack_setup' ) ) :

function music_lite_jetpack_setup() {

	// See: http://jetpack.me/support/infinite-scroll/ for more.
	add_theme_support( 'infinite-scroll', array(
		'container' 			=> 'infinite-container',
		'wrapper'					=> false,
		'render' 					=> 'music_lite_render_infinite',
		'footer_widgets' 	=> array( 'footer' ),
		'footer'         	=> 'wrap',
	) );

}
endif;
add_action( 'after_setup_theme', 'music_lite_jetpack_setup' );

/**
 * Infinite Scroll: function for rendering posts
 */

if ( ! function_exists( 'music_lite_render_infinite' ) ) :

function music_lite_render_infinite() {
	if ( is_home() ) {
		get_template_part( 'content/loop', 'blog' );
	} else {
		get_template_part( 'content/loop', 'archive' );
	}
}
endif;

/**
 * Enables Jetpack's Infinite Scroll in archives, but not on home blog.
 *
 * @return bool
 */

if ( ! function_exists( 'music_lite_jetpack_infinite_scroll_supported' ) ) :

function music_lite_jetpack_infinite_scroll_supported() {
	return current_theme_supports( 'infinite-scroll' ) && ( is_archive() || is_search() ) && ! is_home();
}
endif;
add_filter( 'infinite_scroll_archive_supported', 'music_lite_jetpack_infinite_scroll_supported' );
