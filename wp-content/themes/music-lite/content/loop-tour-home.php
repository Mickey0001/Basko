<?php
/**
 * This template displays the tour date loop
 *
 * @package Music Lite
 * @since Music Lite 1.0
 */

?>

<?php
	$wp_query = new WP_Query( array(
		'post_type' 					=> 'tour-date',
		'suppress_filters'		=> 0,
		'posts_per_page'			=> 4,
		'orderby' 						=> 'meta_value',
		'meta_key' 						=> 'tour_date_info_timestamp',
		'order'								=> 'ASC',
		'meta_query' => array(array(
			'key' => 'tour_date_info_timestamp',
			'value' => strtotime( '-5 days' ),
			'compare' => '>',
			'type' => 'NUMERIC',
			),
		),
	));

	if ( $wp_query->have_posts() ) : while ( $wp_query->have_posts() ) : $wp_query->the_post();

			$previous_date = organic_cc_tour_date_get_meta( 'tour_date_info_timestamp' ) < strtotime( '-1 days' ) ? true : false;
			$tour_date = organic_cc_tour_date_get_meta( 'tour_date_info_date' );
			$tour_date_time = organic_cc_tour_date_get_meta( 'tour_date_info_time' );
			$tour_date_venue = organic_cc_tour_date_get_meta( 'tour_date_info_venue' );
			$tour_date_venue_url = organic_cc_tour_date_get_meta( 'tour_date_info_venue_ticketing_url' );
			$tour_date_location = organic_cc_tour_date_get_meta( 'tour_date_info_location_city_state_province_country_etc_' );
		?>

		<!-- BEGIN .tour-date-->
		<div class="tour-date music-lite-bg-dark <?php if ( $previous_date ) { echo esc_attr( 'previous-date' ); } ?>">

				<?php if ( '' != $tour_date || '' != $tour_date_time ) { ?>
				<p class="tour-date-datetime">
					<?php echo esc_html( $tour_date );
					if ( '' != $tour_date && '' != $tour_date_time ) { echo ' - '; }
					if ( '' != $tour_date_time ) { echo esc_html( $tour_date_time ); }
					?>
				</p>
				<?php } ?>

				<?php if ( '' != $tour_date_venue ) { ?>
					<h6 class="tour-date-venue"><?php echo esc_html( $tour_date_venue ); ?></h6>
				<?php } ?>

				<?php if ( '' != $tour_date_location ) { ?>
					<p class="tour-date-location"><?php echo esc_html( $tour_date_location ); ?></p>
				<?php } ?>

				<?php if ( '' != $tour_date_venue_url ) { ?>
					<p class="tour-ticket"><a href="<?php echo esc_url( $tour_date_venue_url ); ?>" target="_blank">
						<?php esc_html_e( 'Buy Tickets', 'music-lite' ); ?>
					</a></p>
				<?php } ?>

		<!-- END .tour-date -->
		</div>

	<?php endwhile; ?>
	<?php endif; ?>

<?php wp_reset_postdata(); ?>
