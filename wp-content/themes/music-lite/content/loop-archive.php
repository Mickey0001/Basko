<?php
/**
 * This template displays the archive loop.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

?>

<?php if ( have_posts() ) : ?>

	<h1 class="archive-title"><?php the_archive_title(); ?></h1>

<?php while ( have_posts() ) : the_post(); ?>

	<!-- BEGIN .post class -->
	<div <?php post_class( 'archive-holder' ); ?> id="post-<?php the_ID(); ?>">

		<!-- BEGIN .article -->
		<article class="article">

			<h2 class="headline"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

			<?php if ( has_post_thumbnail() ) { ?>
				<a class="feature-img" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php echo esc_attr( sprintf( esc_html__( 'Permalink to %s', 'music-lite' ), the_title_attribute( 'echo=0' ) ) ); ?>"><?php the_post_thumbnail( 'music-lite-featured-large' ); ?></a>
			<?php } ?>

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

			<?php the_excerpt(); ?>

			<?php $tag_list = get_the_tag_list( esc_html__( ', ', 'music-lite' ) ); if ( ! empty( $tag_list ) || has_category() ) { ?>

				<!-- BEGIN .post-meta -->
				<div class="post-meta">

					<!-- BEGIN .post-taxonomy -->
					<div class="post-taxonomy">

						<p class="align-left"><?php esc_html_e( 'Category:', 'music-lite' ); ?> <?php the_category( ', ' ); ?><p>
						<?php if ( ! empty( $tag_list ) ) { ?>
							<p class="align-right"><?php esc_html_e( 'Tags:', 'music-lite' ); ?> <?php the_tags( '' ); ?></p>
						<?php } ?>

					<!-- END .post-taxonomy -->
					</div>

				<!-- END .post-meta -->
				</div>

			<?php } ?>

		<!-- END .article -->
		</article>

	<!-- END .post class -->
	</div>

<?php endwhile; ?>

<?php the_posts_pagination( array(
	'prev_text' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Previous Page', 'music-lite' ) . ' </span>&laquo;',
	'next_text' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Next Page', 'music-lite' ) . ' </span>&raquo;',
) ); ?>

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
