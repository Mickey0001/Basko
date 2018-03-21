<?php
/**
 * The Header for our theme.
 * Displays all of the <head> section and everything up till <div id="wrap">
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

?><!DOCTYPE html>

<html class="no-js" <?php language_attributes(); ?>>

<head>
<link href="https://fonts.googleapis.com/css?family=Karla" rel="stylesheet">
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<!-- BEGIN #wrapper -->
<div id="wrapper">

	<!-- BEGIN #header -->
	<div id="header">

		<!-- BEGIN #nav-bar -->
		<div id="nav-bar">

			<?php if ( has_nav_menu( 'social-menu' ) || get_theme_mod( 'music_lite_contact_phone' ) || get_theme_mod( 'music_lite_contact_email' ) ) { ?>

			<!-- BEGIN .row -->
			<div class="row">

				<?php if ( get_theme_mod( 'music_lite_contact_phone' ) || get_theme_mod( 'music_lite_contact_email' ) ) { ?>

				<div class="contact-info align-left">
					<?php if ( get_theme_mod( 'music_lite_contact_phone' ) ) { ?>
						<span><i class="fa fa-phone"></i> &nbsp;<a href="tel:<?php echo esc_attr( get_theme_mod( 'music_lite_contact_phone' ) ); ?>"><?php echo esc_attr( get_theme_mod( 'music_lite_contact_phone' ) ); ?></a></span>
					<?php } ?>
					<?php if ( get_theme_mod( 'music_lite_contact_email' ) ) { ?>
						<span><i class="fa fa-envelope"></i> &nbsp;<a href="mailto:<?php echo esc_attr( get_theme_mod( 'music_lite_contact_email' ) ); ?>"><?php echo esc_attr( get_theme_mod( 'music_lite_contact_email' ) ); ?></a></span>
					<?php } ?>
				</div>

				<?php } ?>

				<?php if ( has_nav_menu( 'social-menu' ) ) { ?>

				<div class="align-right">

					<?php wp_nav_menu( array(
						'theme_location' => 'social-menu',
						'title_li' => '',
						'depth' => 1,
						'container_class' => 'social-menu',
						'menu_class'      => 'social-icons',
						'link_before'     => '<span>',
						'link_after'      => '</span>',
						)
					); ?>

				</div>

				<?php } ?>

			<!-- END .row -->
			</div>

			<?php } ?>

			<!-- BEGIN .flex-row -->
			<div class="flex-row">

				<!-- BEGIN .site-logo -->
				<div class="site-logo">

					<?php if ( ! is_page_template( 'template-home.php' ) && '1' == get_theme_mod( 'music_lite_site_logo', '1' ) ) { ?>
						<?php the_custom_logo(); ?>
					<?php } ?>

					<?php if ( is_front_page() && is_home() ) { ?>
						<h1 class="site-title">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo wp_kses_post( get_bloginfo( 'name' ) ); ?></a>
						</h1>
					<?php } else { ?>
						<p class="site-title">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo wp_kses_post( get_bloginfo( 'name' ) ); ?></a>
						</p>
					<?php } ?>

				<!-- END .site-logo -->
				</div>

			<?php if ( has_nav_menu( 'main-menu' ) ) { ?>

				<!-- BEGIN #navigation -->
				<nav id="navigation" class="navigation-main">

					<?php
						wp_nav_menu( array(
							'theme_location'		=> 'main-menu',
							'title_li'					=> '',
							'depth'							=> 4,
							'fallback_cb'			 	=> 'wp_page_menu',
							'container_class' 	=> '',
							'menu_class'				=> 'menu',
							)
						);
					?>

				<!-- END #navigation -->
				</nav>

				<button id="menu-toggle" class="menu-toggle" href="#sidr">
					<svg class="icon-menu-open" version="1.1" id="icon-open" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
						 width="24px" height="24px" viewBox="0 0 24 24" enable-background="new 0 0 24 24" xml:space="preserve">
						<rect y="2" width="24" height="2"/>
						<rect y="11" width="24" height="2"/>
						<rect y="20" width="24" height="2"/>
					</svg>
					<svg class="icon-menu-close" version="1.1" id="icon-close" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 24 24" enable-background="new 0 0 24 24" xml:space="preserve">
						<rect x="0" y="11" transform="matrix(-0.7071 -0.7071 0.7071 -0.7071 12 28.9706)" width="24" height="2"/>
						<rect x="0" y="11" transform="matrix(-0.7071 0.7071 -0.7071 -0.7071 28.9706 12)" width="24" height="2"/>
					</svg>
				</button>

			<?php } ?>

			<!-- END .flex-row -->
			</div>

		<!-- END #nav-bar -->
		</div>

		<?php $header_image = get_header_image(); if ( ( is_home() || is_archive() || is_search() || is_attachment() ) && ! empty( $header_image ) ) { ?>

		<!-- BEGIN #custom-header -->
		<div id="custom-header">

			<!-- BEGIN .row -->
			<div class="row">

				<div id="masthead" class="vertical-center">

				<?php if ( is_front_page() && is_home() ) { ?>
					<h2 class="site-description">
						<?php echo html_entity_decode( get_bloginfo( 'description' ) ); ?>
					</h2>
				<?php } else { ?>
					<p class="site-description">
						<?php echo html_entity_decode( get_bloginfo( 'description' ) ); ?>
					</p>
				<?php } ?>

				</div>

				<?php the_custom_header_markup(); ?>

			<!-- END .row -->
			</div>

		<!-- END #custom-header -->
		</div>

		<?php } ?>

	<!-- END #header -->
	</div>

	<!-- BEGIN .container -->
	<div class="container">
