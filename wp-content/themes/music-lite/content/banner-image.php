<?php
/**
 * This template is used to display the banner image on posts and pages.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

?>

<?php $header_image = get_custom_header_markup(); ?>
<?php $thumb = ( '' != get_the_post_thumbnail() ) ? wp_get_attachment_image_src( get_post_thumbnail_id(), 'music-lite-featured-large' ) : false; ?>

<?php if ( is_page_template( 'template-home.php' ) ) { ?>

	<!-- BEGIN .row -->
	<div class="row">

		<div class="feature-img banner-img" <?php if ( has_post_thumbnail() ) { ?> style="background-image: url(<?php echo esc_url( $thumb[0] ); ?>);"<?php } elseif ( ! empty( $header_image ) ) { ?> style="background-image: url(<?php header_image(); ?>);"<?php } ?>>

			<div class="img-title">

				<?php the_custom_logo(); ?>

				<h1 class="headline"><?php the_title(); ?></h1>

				<?php if ( ! empty( $post->post_excerpt ) ) { ?>
					<div class="excerpt"><?php the_excerpt(); ?></div>
				<?php } ?>

				<?php if ( '' != get_theme_mod( 'music_lite_home_link', '' ) && get_theme_mod( 'music_lite_home_link', '' ) ) { ?>
					<div class="align-center text-center">
						<a class="button" href="<?php echo esc_url( get_theme_mod( 'music_lite_home_link', '' ) ); ?>"><?php echo esc_attr( get_theme_mod( 'music_lite_home_link_text', 'Learn More' ) ); ?></a>
					</div>
				<?php } ?>

			</div>

			<?php the_custom_header_markup(); ?>

		</div>

	<!-- END .row -->
	</div>

<?php } elseif ( has_post_thumbnail() ) { ?>

	<!-- BEGIN .row -->
	<div class="row">

		<div class="feature-img banner-img" style="background-image: url(<?php echo esc_url( $thumb[0] ); ?>);">
			<div class="img-title">
				<h1 class="headline"><?php the_title(); ?></h1>
				<?php if ( is_page() && ! empty( $post->post_excerpt ) ) { ?>
					<div class="excerpt"><?php the_excerpt(); ?></div>
				<?php } ?>
			</div>
			<?php the_post_thumbnail( 'music-lite-featured-large' ); ?>
		</div>

	<!-- END .row -->
	</div>

<?php } ?>

<?php $tour_query = new WP_Query( array( 'post_type' => 'tour-date' ) ); ?>

<?php if ( is_page_template( 'template-home.php' ) && $tour_query->have_posts() ) { ?>
	<div class="tour-dates">
		<div class="flex-row">
			<?php get_template_part( 'content/loop-tour', 'home' ); ?>
		</div>
	</div>
<?php } ?>
