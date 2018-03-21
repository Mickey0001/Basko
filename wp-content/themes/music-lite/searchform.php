<?php
/**
 * The search form template for our theme.
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

?>

<form method="get" id="searchform" class="clearfix" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
	<label for="s" class="assistive-text"><?php esc_html_e( 'Search', 'music-lite' ); ?></label>
	<input type="text" class="field" name="s" value="<?php echo get_search_query(); ?>" id="s" placeholder="<?php esc_attr_e( 'Search &hellip;', 'music-lite' ); ?>" />
	<input type="submit" class="submit" name="submit" id="searchsubmit" value="<?php esc_attr_e( 'Go', 'music-lite' ); ?>" />
</form>
