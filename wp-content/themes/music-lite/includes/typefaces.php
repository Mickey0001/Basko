<?php
/**
 * Google Fonts Implementation
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

/**
 * Register Google Font URLs
 *
 * @since Music Lite 1.0
 */
function music_lite_fonts_url() {
	$fonts_url = '';

	/*
	Translators: If there are characters in your language that are not
	* supported by Lora, translate this to 'off'. Do not translate
	* into your own language.
	*/

	$roboto = _x( 'on', 'Roboto font: on or off', 'music-lite' );
	$montserrat = _x( 'on', 'Montserrat font: on or off', 'music-lite' );
	$anton = _x( 'on', 'Anton font: on or off', 'music-lite' );
	$noto_serif = _x( 'on', 'Noto Serif font: on or off', 'music-lite' );

	if ( 'off' !== $roboto || 'off' !== $montserrat || 'off' !== $anton || 'off' !== $noto_serif ) {

		$font_families = array();

		if ( 'off' !== $roboto ) {
			$font_families[] = 'Roboto:300,300i,400,400i,700,700i';
		}

		if ( 'off' !== $montserrat ) {
			$font_families[] = 'Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i';
		}

		if ( 'off' !== $anton ) {
			$font_families[] = 'Anton';
		}

		if ( 'off' !== $noto_serif ) {
			$font_families[] = 'Noto Serif:400,400i,700,700i';
		}

		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext' ),
		);

		$fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css' );
	}

	return esc_url_raw( $fonts_url );
}

/**
 * Enqueue Google Fonts on Front End
 *
 * @since Music Lite 1.0
 */
function music_lite_scripts_styles() {
	wp_enqueue_style( 'music-lite-fonts', music_lite_fonts_url(), array(), null );
}
add_action( 'wp_enqueue_scripts', 'music_lite_scripts_styles' );

/**
 * Add Google Scripts for use with the editor
 *
 * @since Music Lite 1.0
 */
function music_lite_editor_styles() {
	add_editor_style( array( 'css/style-editor.css', music_lite_fonts_url() ) );
}
add_action( 'after_setup_theme', 'music_lite_editor_styles' );
