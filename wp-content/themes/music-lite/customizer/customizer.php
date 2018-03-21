<?php
/**
 * Theme customizer with real-time update
 *
 * Very helpful: http://ottopress.com/2012/theme-customizer-part-deux-getting-rid-of-options-pages/
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

/**
 * Begin the customizer functions.
 *
 * @param array $wp_customize Returns classes and sanitized inputs.
 */
function music_lite_theme_customizer( $wp_customize ) {

	include( get_template_directory() . '/customizer/customizer-controls.php');

	include( get_template_directory() . '/customizer/customizer-sanitize.php');

	/**
	 * Render the site title for the selective refresh partial.
	 *
	 * @since Music Lite 1.0
	 * @see music_lite_customize_register()
	 *
	 * @return void
	 */
	function music_lite_customize_partial_blogname() {
		bloginfo( 'name' );
	}

	/**
	 * Render the site tagline for the selective refresh partial.
	 *
	 * @since Music Lite 1.0
	 * @see music_lite_customize_register()
	 *
	 * @return void
	 */
	function music_lite_customize_partial_blogdescription() {
		bloginfo( 'description' );
	}

	// Set site name and description text to be previewed in real-time.
	$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'blogname', array(
			'selector' => '.site-title a',
			'container_inclusive' => false,
			'render_callback' => 'music_lite_customize_partial_blogname',
		) );
		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector' => '.site-description',
			'container_inclusive' => false,
			'render_callback' => 'music_lite_customize_partial_blogdescription',
		) );
	}

	/*
	-------------------------------------------------------------------------------------------------------
		Site Title Section
	-------------------------------------------------------------------------------------------------------
	*/

		// Custom Display Site Title Option.
		$wp_customize->add_setting( 'music_lite_site_title', array(
			'default'						=> '1',
			'sanitize_callback'	=> 'music_lite_sanitize_checkbox',
			'transport'					=> 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'music_lite_site_title', array(
			'label'			=> esc_html__( 'Display Site Title', 'music-lite' ),
			'type'			=> 'checkbox',
			'section'		=> 'title_tagline',
			'settings'	=> 'music_lite_site_title',
			'priority'	=> 10,
		) ) );

		// Custom Display Tagline Option.
		$wp_customize->add_setting( 'music_lite_site_tagline', array(
			'default'						=> '1',
			'sanitize_callback'	=> 'music_lite_sanitize_checkbox',
			'transport'					=> 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'music_lite_site_tagline', array(
			'label'			=> esc_html__( 'Display Site Tagline', 'music-lite' ),
			'type'			=> 'checkbox',
			'section'		=> 'title_tagline',
			'settings'	=> 'music_lite_site_tagline',
			'priority'	=> 12,
		) ) );

		// Custom Display Logo Option.
		$wp_customize->add_setting( 'music_lite_site_logo', array(
			'default'						=> '1',
			'sanitize_callback'	=> 'music_lite_sanitize_checkbox',
			'transport'					=> 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'music_lite_site_logo', array(
			'label'			=> esc_html__( 'Display Logo In Header', 'music-lite' ),
			'type'			=> 'checkbox',
			'section'		=> 'title_tagline',
			'settings'	=> 'music_lite_site_logo',
			'priority'	=> 14,
		) ) );

		// Logo Align.
		$wp_customize->add_setting( 'music_lite_logo_align', array(
				'default' 					=> 'left',
				'sanitize_callback'	=> 'music_lite_sanitize_align',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'music_lite_logo_align', array(
				'type'			=> 'radio',
				'label' 		=> esc_html__( 'Logo & Title Alignment', 'music-lite' ),
				'section' 	=> 'title_tagline',
				'choices' 	=> array(
						'left' 		=> esc_html__( 'Left Align', 'music-lite' ),
						'center' 	=> esc_html__( 'Center Align', 'music-lite' ),
						'right' 	=> esc_html__( 'Right Align', 'music-lite' ),
				),
				'priority' => 20,
		) ) );

		// Site Description Align.
		$wp_customize->add_setting( 'music_lite_desc_align', array(
			'default'						=> 'center',
			'sanitize_callback'	=> 'music_lite_sanitize_align',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'music_lite_desc_align', array(
			'type' 		=> 'radio',
			'label' 	=> esc_html__( 'Site Description Alignment', 'music-lite' ),
			'section' => 'title_tagline',
			'choices' => array(
				'left' 		=> esc_html__( 'Left Align', 'music-lite' ),
				'center' 	=> esc_html__( 'Center Align', 'music-lite' ),
				'right' 	=> esc_html__( 'Right Align', 'music-lite' ),
			),
			'priority' => 25,
		) ) );

		/*
		-------------------------------------------------------------------------------------------------------
			Theme Options Panel
		-------------------------------------------------------------------------------------------------------
		*/

		$wp_customize->add_panel( 'music_lite_theme_options', array(
			'priority'				=> 1,
			'capability'			=> 'edit_theme_options',
			'theme_supports'	=> '',
			'title'						=> esc_html__( 'Theme Options', 'music-lite' ),
			'description'			=> esc_html__( 'This panel allows you to customize specific areas of the theme.', 'music-lite' ),
		) );

	//-------------------------------------------------------------------------------------------------------------------//
	// Contact Section
	//-------------------------------------------------------------------------------------------------------------------//

	$wp_customize->add_section( 'music_lite_contact_section' , array(
		'title'       => esc_html__( 'Contact Info', 'music-lite' ),
		'priority'    => 90,
		'panel' => 'music_lite_theme_options',
	) );

		// Contact Email
		$wp_customize->add_setting( 'music_lite_contact_email', array(
			'sanitize_callback' => 'sanitize_email',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'music_lite_contact_email', array(
			'label'		=> esc_html__( 'Email Address', 'music-lite' ),
			'section'	=> 'music_lite_contact_section',
			'settings'	=> 'music_lite_contact_email',
			'type'		=> 'text',
			'priority' => 20,
		) ) );

		// Contact Phone
		$wp_customize->add_setting( 'music_lite_contact_phone', array(
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'music_lite_contact_phone', array(
			'label'		=> esc_html__( 'Phone Number', 'music-lite' ),
			'section'	=> 'music_lite_contact_section',
			'settings'	=> 'music_lite_contact_phone',
			'type'		=> 'text',
			'priority' => 40,
		) ) );

		/*
		-------------------------------------------------------------------------------------------------------
			Page Templates Section
		-------------------------------------------------------------------------------------------------------
		*/

		$wp_customize->add_section( 'music_lite_templates_section' , array(
			'title'			=> esc_html__( 'Home Template Options', 'music-lite' ),
			'priority'	=> 100,
			'panel'			=> 'music_lite_theme_options',
		) );

		// Featured Link
		$wp_customize->add_setting( 'music_lite_home_link', array(
			'default' => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'music_lite_home_link', array(
			'label'		=> esc_html__( 'Home Featured Link', 'music-lite' ),
			'section'	=> 'music_lite_templates_section',
			'settings'	=> 'music_lite_home_link',
			'type'		=> 'text',
			'priority' => 20,
		) ) );

		// Featured Link Text
		$wp_customize->add_setting( 'music_lite_home_link_text', array(
			'default' => 'Learn More',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'music_lite_home_link_text', array(
			'label'		=> esc_html__( 'Home Featured Link Text', 'music-lite' ),
			'section'	=> 'music_lite_templates_section',
			'settings'	=> 'music_lite_home_link_text',
			'type'		=> 'text',
			'priority' => 40,
		) ) );

}
add_action( 'customize_register', 'music_lite_theme_customizer' );

/**
 * Binds JavaScript handlers to make Customizer preview reload changes
 * asynchronously.
 */
function music_lite_customize_preview_js() {
	wp_enqueue_script( 'music-customizer', get_template_directory_uri() . '/customizer/js/customizer.js', array( 'customize-preview' ), '1.0', true );
}
add_action( 'customize_preview_init', 'music_lite_customize_preview_js' );
