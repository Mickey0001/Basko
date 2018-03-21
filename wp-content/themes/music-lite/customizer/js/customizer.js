( function( $ ) {

	"use strict";

	/**
	 * Real-time preview of the site title and description text.
	 */
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).html( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).html( to );
		} );
	} );

	/**
	 * Real-time preview of the site title.
	 */
	wp.customize( 'music_lite_site_title', function( value ) {
		value.bind( function( to ) {
			if ( '1' != to ) {
				$( '.site-title' ).css( {
					'padding' : '0',
					'text-indent' : '-9999px',
					'position': 'absolute'
				} );
			} else {
				$( '.site-title' ).css( {
					'padding' : '24px 12px',
					'text-indent' : '0',
					'position': 'relative'
				} );
			}
		} );
	} );

	/**
	 * Real-time preview of the site tagline.
	 */
	wp.customize( 'music_lite_site_tagline', function( value ) {
		value.bind( function( to ) {
			if ( '1' != to ) {
				$( '.site-description' ).css( {
					'padding' : '0',
					'left' : '-9999px',
					'position': 'absolute'
				} );
			} else {
				$( '.site-description' ).css( {
					'padding' : '12px',
					'left' : '0',
					'position': 'relative'
				} );
			}
		} );
	} );

	/**
	 * Real-time preview of the site background color.
	 */
	wp.customize( 'background_color', function( value ) {
		value.bind( function( to ) {
			$( 'body, .post-area' ).css( 'background-color', to );
			$( '.post-area' ).bgBrightness();
		} );
	} );

})( jQuery );
