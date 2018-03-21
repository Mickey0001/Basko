<?php
/**
 * This template displays the post loop.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<!-- BEGIN .post-holder -->
	<div class="post-holder">

		<!-- BEGIN .article -->
		<article class="article">

			<?php if ( ! has_post_thumbnail() ) { ?>
				<h1 class="headline"><?php the_title(); ?></h1>
			<?php } ?>

			<?php the_content(); ?>

			<?php wp_link_pages(array(
				'before' => '<p class="page-links"><span class="link-label">' . esc_html__( 'Pages:', 'music-lite' ) . '</span>',
				'after' => '</p>',
				'link_before' => '<span>',
				'link_after' => '</span>',
				'next_or_number' => 'next_and_number',
				'nextpagelink' => esc_html__( 'Next', 'music-lite' ),
				'previouspagelink' => esc_html__( 'Previous', 'music-lite' ),
				'pagelink' => '%',
				'echo' => 1,
				)
			); ?>

			<!-- BEGIN .post-meta -->
			<div class="post-meta">

				<!-- BEGIN .post-author -->
				<div class="post-author">
					<p class="align-left"><em><?php esc_html_e( 'by', 'music-lite' ); ?></em> <?php esc_url( the_author_posts_link() ); ?> <span class="author-avatar"><?php echo get_avatar( get_the_author_meta( 'user_email' ), 28 ); ?></span></p>
				<!-- END .post-author -->
				</div>

				<!-- BEGIN .post-date -->
				<div class="post-date">
					<p class="align-left"><?php music_lite_posted_on(); ?></p>
					<p class="align-right"><?php edit_post_link( esc_html__( '(Edit)', 'music-lite' ), '', '' ); ?></p>
				<!-- END .post-date -->
				</div>

				<?php $tag_list = get_the_tag_list( esc_html__( ', ', 'music-lite' ) ); if ( ! empty( $tag_list ) || has_category() ) { ?>
					<!-- BEGIN .post-taxonomy -->
					<div class="post-taxonomy">
						<p class="align-left"><?php esc_html_e( 'Category:', 'music-lite' ); ?> <?php the_category( ', ' ); ?><p>
						<?php if ( ! empty( $tag_list ) ) { ?>
							<p class="align-right"><?php esc_html_e( 'Tags:', 'music-lite' ); ?> <?php the_tags( '' ); ?></p>
						<?php } ?>
					<!-- END .post-taxonomy -->
					</div>
				<?php } ?>

				<!-- BEGIN .post-navigation -->
				<div class="post-navigation">
					<div class="previous-post"><?php previous_post_link( '&larr; %link' ); ?></div>
					<div class="next-post"><?php next_post_link( '%link &rarr;' ); ?></div>
				<!-- END .post-navigation -->
				</div>

			<!-- END .post-meta -->
			</div>

		<!-- END .article -->
		</article>

	<!-- END .post-holder -->
	</div>

	<?php if ( comments_open() || '0' != get_comments_number() ) { comments_template(); } ?>

<?php endwhile; else : ?>

	<!-- BEGIN .post-holder -->
	<div class="post-holder">

		<!-- BEGIN .article -->
		<article class="article">

			<?php get_template_part( 'content/content', 'none' ); ?>

		<!-- END .article -->
		</article>

	<!-- END .post-holder -->
	</div>

<?php endif; ?>
