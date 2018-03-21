<?php
/**
 * The default sidebar template for our theme.
 * This template is used to display the sidebar on pages.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

?>

<?php if ( is_active_sidebar( 'sidebar-1' ) ) { ?>

	<div class="sidebar">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</div>

<?php } ?>
