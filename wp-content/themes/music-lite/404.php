<?php
/**
 * This page template is used to display a 404 error message.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

get_header(); ?>

<!-- BEGIN .row -->
<div class="row">

	<!-- BEGIN .content -->
	<div class="content">

	<?php if ( is_active_sidebar( 'sidebar-1' ) ) { ?>

		<!-- BEGIN .eleven columns -->
		<div class="eleven columns">

		<!-- BEGIN .post-area -->
		<div class="post-area">

			<!-- BEGIN .page-holder -->
			<div class="page-holder">

				<!-- BEGIN .article -->
				<article class="article">

					<?php get_template_part( 'content/content', 'none' ); ?>

				<!-- END .article -->
				</article>

			<!-- END .page-holder -->
			</div>

		<!-- END .post-area -->
		</div>

		<!-- END .eleven columns -->
		</div>

		<!-- BEGIN .five columns -->
		<div class="five columns">

			<?php get_sidebar(); ?>

		<!-- END .five columns -->
		</div>

	<?php } else { ?>

		<!-- BEGIN .sixteen columns -->
		<div class="sixteen columns">

			<!-- BEGIN .post-area no-sidebar -->
			<div class="post-area no-sidebar">

				<!-- BEGIN .page-holder -->
				<div class="page-holder">

					<!-- BEGIN .article -->
					<article class="article">

						<h1><?php esc_html_e( 'Not Found, Error 404', 'music-lite' ); ?></h1>
						<p><?php esc_html_e( 'The page you are looking for no longer exists.', 'music-lite' ); ?></p>

					<!-- END .article -->
					</article>

				<!-- END .page-holder -->
				</div>

			<!-- END .post-area no-sidebar -->
			</div>

		<!-- END .sixteen columns -->
		</div>

	<?php } ?>

	<!-- END .content -->
	</div>

<!-- END .row -->
</div>

<?php get_footer(); ?>
