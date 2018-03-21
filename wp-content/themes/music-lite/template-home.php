<?php
/**
Template Name: Home
 *
 * This template is used to display the home page.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

get_header(); ?>

<!-- BEGIN .post class -->
<div <?php post_class( 'home-page' ); ?> id="page-<?php the_ID(); ?>">

	<?php get_template_part( 'content/banner', 'image' ); ?>

	<?php if ( is_active_sidebar( 'home-widgets' ) ) { ?>

	<!-- BEGIN .organic-ocw-container -->
	<div class="organic-ocw-container">

		<!-- BEGIN .home-widgets -->
		<div class="home-widgets">

			<?php dynamic_sidebar( 'home-widgets' ); ?>

		<!-- END .home-widgets -->
		</div>

	<!-- END .organic-ocw-container -->
	</div>

	<?php } ?>

<!-- END .post class -->
</div>

<?php get_footer(); ?>
