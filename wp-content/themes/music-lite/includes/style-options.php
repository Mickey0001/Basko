<?php
/**
 * This template is used to manage style options.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

/**
 * Changes styles upon user defined options.
 */
function music_lite_custom_styles() {

	$header_image = get_header_image();
	$display_title = get_theme_mod( 'music_lite_site_title', '1' );
	$display_tagline = get_theme_mod( 'music_lite_site_tagline', '1' );
	?>

	<style>

	#wrapper .post-area {
		background-color: #<?php echo esc_attr( get_theme_mod( 'background_color', '111111' ) ); ?> ;
	}

	.wp-custom-header {
		<?php if ( ! empty( $header_image ) ) { ?>
			background-image: url('<?php header_image(); ?>');
		<?php } ?>
	}

	#wrapper .site-title {
		<?php
		if ( '1' != $display_title ) {
			echo
			'position: absolute;
			text-indent: -9999px;
			margin: 0px;
			padding: 0px;';
		};
		?>
	}

	#wrapper .site-description {
		<?php
		if ( '1' != $display_tagline ) {
			echo
			'position: absolute;
			left: -9999px;
			margin: 0px;
			padding: 0px;';
		};
		?>
	}

	</style>

	<?php
}
add_action( 'wp_head', 'music_lite_custom_styles', 100 );
