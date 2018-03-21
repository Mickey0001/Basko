<?php

/*
-------------------------------------------------------------------------------------------------------
	Default Title Text
-------------------------------------------------------------------------------------------------------
*/

function organic_cc_tour_date_title_text( $title ) {
	$screen = get_current_screen();
	if ( 'tour-date' == $screen->post_type ) {
		$title = esc_html__( 'Enter Tour Date Title', 'organic-custom-content' );
	}
	return $title;
}
add_filter( 'enter_title_here', 'organic_cc_tour_date_title_text' );

/*
-------------------------------------------------------------------------------------------------------
	New Icon For Tour Date
-------------------------------------------------------------------------------------------------------
*/

function organic_cc_tour_date_custom_css() {
	echo "<style type='text/css' media='screen'>
		#adminmenu .menu-icon-tour-date div.wp-menu-image:before {
			content: '\\f508'; // This is where you enter the dashicon font code
		}
		</style>";
}
add_action( 'admin_head', 'organic_cc_tour_date_custom_css' );

/*
-------------------------------------------------------------------------------------------------------
	Use Tour Date For Title
-------------------------------------------------------------------------------------------------------
*/

function organic_cc_tour_date_title( $title, $post_id ) {

	$time = get_post_meta( $post_id, 'tour_date_info_time', true );
	$date = get_post_meta( $post_id, 'tour_date_info_date', true );
	$new_title = '';

	if ( '' != $date ) {
		$new_title .= $date;
	}
	if ( '' != $date && '' != $time ) {
		$new_title .= ' - ';
	}
	if ( '' != $time ) {
		$new_title .= $time;
	}

	if ( '' != $new_title ) {
		return $new_title;
	} else {
		return $title;
	}

}
add_filter( 'the_title', 'organic_cc_tour_date_title', 10, 2 );

/*
-------------------------------------------------------------------------------------------------------
	Add Custom Meta Fields to Tour Dates
-------------------------------------------------------------------------------------------------------
*/

function organic_cc_tour_date_get_meta( $value ) {
	global $post;

	$field = get_post_meta( $post->ID, $value, true );
	if ( ! empty( $field ) ) {
		return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
	} else {
		return false;
	}
}

function organic_cc_tour_date_info_add_meta_box() {
	add_meta_box(
		'tour_date_info-tour-date-info',
		__( 'Tour Date Info', 'organic-custom-content' ),
		'organic_cc_tour_date_info_html',
		'tour-date',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'organic_cc_tour_date_info_add_meta_box' );

function organic_cc_tour_date_info_html( $post ) {
	wp_nonce_field( '_tour_date_info_nonce', 'tour_date_info_nonce' ); ?>

	<p>
		<label for="tour_date_info_location_city_state_province_country_etc_"><?php _e( 'Location (City, State)', 'organic-custom-content' ); ?></label><br>
		<input type="text" name="tour_date_info_location_city_state_province_country_etc_" id="tour_date_info_location_city_state_province_country_etc_" value="<?php echo esc_attr( organic_cc_tour_date_get_meta( 'tour_date_info_location_city_state_province_country_etc_' ) ); ?>">
	</p>	<p>
		<label for="tour_date_info_venue"><?php _e( 'Venue', 'organic-custom-content' ); ?></label><br>
		<input type="text" name="tour_date_info_venue" id="tour_date_info_venue" value="<?php echo esc_attr( organic_cc_tour_date_get_meta( 'tour_date_info_venue' ) ); ?>">
	</p>	<p>
		<label for="tour_date_info_venue_ticketing_url"><?php _e( 'Venue/Ticketing URL', 'organic-custom-content' ); ?></label><br>
		<input type="text" name="tour_date_info_venue_ticketing_url" id="tour_date_info_venue_ticketing_url" value="<?php echo esc_url( organic_cc_tour_date_get_meta( 'tour_date_info_venue_ticketing_url' ) ); ?>">
	</p>	<p>
		<label for="tour_date_info_date"><?php _e( 'Date', 'organic-custom-content' ); ?></label><br>
		<input type="text" name="tour_date_info_date" id="tour_date_info_date" value="<?php echo esc_attr( organic_cc_tour_date_get_meta( 'tour_date_info_date' ) ); ?>">
	</p>	<p>
		<label for="tour_date_info_time"><?php _e( 'Time', 'organic-custom-content' ); ?></label><br>
		<input type="text" name="tour_date_info_time" id="tour_date_info_time" value="<?php echo esc_attr( organic_cc_tour_date_get_meta( 'tour_date_info_time' ) ); ?>">
	</p><?php
}

function organic_cc_tour_date_info_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['tour_date_info_nonce'] ) || ! wp_verify_nonce( $_POST['tour_date_info_nonce'], '_tour_date_info_nonce' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$tour_date_info_location_city_state_province_country_etc = sanitize_text_field( $_POST['tour_date_info_location_city_state_province_country_etc_'] );
	$tour_date_info_venue = sanitize_text_field( $_POST['tour_date_info_venue'] );
	$tour_date_info_venue_ticketing_url = esc_url( $_POST['tour_date_info_venue_ticketing_url'] );
	$tour_date_info_date = sanitize_text_field( $_POST['tour_date_info_date'] );
	$tour_date_info_time = sanitize_text_field( $_POST['tour_date_info_time'] );

	if ( isset( $tour_date_info_location_city_state_province_country_etc ) ) {
		update_post_meta( $post_id, 'tour_date_info_location_city_state_province_country_etc_', $tour_date_info_location_city_state_province_country_etc );
	}
	if ( isset( $tour_date_info_venue ) ) {
		update_post_meta( $post_id, 'tour_date_info_venue', $tour_date_info_venue );
	}
	if ( isset( $tour_date_info_venue_ticketing_url ) ) {
		update_post_meta( $post_id, 'tour_date_info_venue_ticketing_url', $tour_date_info_venue_ticketing_url );
	}
	if ( isset( $tour_date_info_date ) ) {
		update_post_meta( $post_id, 'tour_date_info_date', $tour_date_info_date );
	}
	update_post_meta( $post_id, 'tour_date_info_timestamp', strtotime( $tour_date_info_date ) );
	if ( isset( $tour_date_info_time ) ) {
		update_post_meta( $post_id, 'tour_date_info_time', $tour_date_info_time ); }
}
add_action( 'save_post', 'organic_cc_tour_date_info_save' );
