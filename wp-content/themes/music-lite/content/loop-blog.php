<?php
/**
 * This template displays the blog loop.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<!-- BEGIN .post class -->
	<div <?php post_class( 'blog-holder' ); ?> id="post-<?php the_ID(); ?>">

		<?php if ( has_post_thumbnail() ) { ?>
			<a class="feature-img" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php echo esc_attr( sprintf( esc_html__( 'Permalink to %s', 'music-lite' ), the_title_attribute( 'echo=0' ) ) ); ?>"><?php the_post_thumbnail( 'music-lite-featured-large' ); ?></a>
		<?php } ?>

		<!-- BEGIN .article -->
		<article class="article">

			<h2 class="headline"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_attr( the_title_attribute() ); ?>"><?php the_title(); ?></a></h2>

			<!-- BEGIN .post-meta -->
			<div class="post-meta">

				<div class="post-author">
					<p><em><?php esc_html_e( 'by', 'music-lite' ); ?></em> <?php esc_url( the_author_posts_link() ); ?> <span class="author-avatar"><?php echo get_avatar( get_the_author_meta( 'user_email' ), 28 ); ?></span></p>
				</div>

				<div class="post-date">
					<p class="align-left">
						<?php music_lite_posted_on(); ?>
					</p>
					<?php if ( comments_open() ) { ?>
							<p class="align-right">
								<a href="<?php the_permalink(); ?>#comments"><?php comments_number( esc_html__( 'Leave a Comment', 'music-lite' ), esc_html__( '1 Comment', 'music-lite' ), '% Comments' ); ?></a>
							</p>
						<?php } ?>
				</div>

			<!-- END .post-meta -->
			</div>

			<?php the_content( esc_html__( 'Read More', 'music-lite' ) ); ?>

		<!-- END .article -->
		</article>

	<!-- END .post class -->
	</div>

<?php endwhile; ?>

	<?php if ( $wp_query->max_num_pages > 1 ) { ?>

		<?php the_posts_pagination( array(
			'prev_text' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Previous Page', 'music-lite' ) . ' </span>&laquo;',
			'next_text' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Next Page', 'music-lite' ) . ' </span>&raquo;',
		) ); ?>

	<?php } ?>

<?php else : ?>

	<!-- BEGIN .page-holder -->
	<div class="page-holder">

		<!-- BEGIN .article -->
		<article class="article">

			<?php get_template_part( 'content/content', 'none' ); ?>

		<!-- END .article -->
		</article>

	<!-- END .page-holder -->
	</div>

<?php endif; ?>
