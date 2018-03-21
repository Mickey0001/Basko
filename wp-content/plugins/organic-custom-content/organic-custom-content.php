<?php
/**
 *
 * @link              https://organicthemes.com
 * @since             1.0.0
 * @package           Organic Custom Content
 *
 * @wordpress-plugin
 * Plugin Name: Organic Custom Content
 * Plugin URI: https://organicthemes.com
 * Description: The Organic Custom Content plugin registers Custom Post Types for various Organic Themes. This plugin is only usable with specific Organic Themes.
 * Version: 1.0.5
 * GitHub Plugin URI: https://github.com/Invulu/organic-custom-content
 * Author: Organic Themes
 * Author URI: https://organicthemes.com
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: organic-custom-content
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Initialize Plugin
if ( ! function_exists( 'organic_cc_init' ) ) {
	function organic_cc_init() {

		// Include Custom Content Functions.
		include_once plugin_dir_path( __FILE__ ) . 'organic-custom-content-functions.php';

		// Find out which parent theme is currently Active
		$organic_cc_current_theme = organic_cc_which_theme_is_active();

		// Run functions for active theme
		switch ($organic_cc_current_theme) {
			case 'organic-entertainer':
			case 'organic-music':
			case 'music-lite':

				// Tour Date CPT
				organic_cc_register_cpt_tour_date();

			break;
			default:

		}
	}
}
add_action( 'init', 'organic_cc_init', 20 );
