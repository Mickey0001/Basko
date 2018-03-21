<?php
/**
 * The footer for our theme.
 * This template is used to generate the footer for the theme.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

?>

<!-- END .container -->
</div>

<?php if ( ! is_page_template( 'template-home.php' ) && ! is_home() ) { ?>

<!-- BEGIN .footer -->
<div class="footer">

	<?php if ( is_active_sidebar( 'footer' ) ) { ?>

	<!-- BEGIN .row -->
	<div class="row">

		<!-- BEGIN .content -->
		<div class="content">

			<!-- BEGIN .footer-widgets -->
			<div class="footer-widgets">

				<?php dynamic_sidebar( 'footer' ); ?>

			<!-- END .footer-widgets -->
			</div>

		<!-- END .content -->
		</div>

	<!-- END .row -->
	</div>

	<?php } ?>

	<!-- BEGIN .row -->
	<div class="row">

		<!-- BEGIN .footer-information -->
		<div class="footer-information">

			<!-- BEGIN .content -->
			<div class="content">

				<div class="align-center">

					<p><?php esc_html_e( 'Copyright', 'music-lite' ); ?> &copy; <?php echo date( esc_html__( 'Y', 'music-lite' ) ); ?> &middot; <?php esc_html_e( 'All Rights Reserved', 'music-lite' ); ?> &middot; <?php esc_html( bloginfo( 'name' ) ); ?></p>

					<p><?php printf( esc_html__( '%1$s by %2$s', 'music-lite' ), 'Music Lite', '<a href="http://organicthemes.com/">Organic Themes</a>' ); ?></p>

				</div>

			<!-- END .content -->
			</div>

		<!-- END .footer-information -->
		</div>

	<!-- END .row -->
	</div>

<!-- END .footer -->
</div>

<?php } ?>

<!-- END #wrapper -->
</div>

<?php wp_footer(); ?>

</body>
</html>
