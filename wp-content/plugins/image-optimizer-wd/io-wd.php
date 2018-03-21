<?php

/**
 * Plugin Name: Image Optimizer WD
 * Plugin URI: https://10web.io/services/image-optimizer/
 * Description: Image Optimizer WordPress plugin enables you to resize, compress and optimize PNG, JPG, GIF files while maintaining image quality.
 * Version: 1.0.10
 * Author: WebDorado
 * Author URI: https://web-dorado.com/
 * License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

define('IOWD_DIR', WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)));
define('IOWD_URL', plugins_url(plugin_basename(dirname(__FILE__))));
define('IOWD_DIR_INCLUDES', IOWD_DIR . '/includes');
define('IOWD_DIR_CLASSES', IOWD_DIR . '/classes');
define('IOWD_DIR_VIEWS', IOWD_DIR . '/views');
define('IOWD_URL_CSS', IOWD_URL . '/assets/css');
define('IOWD_URL_JS', IOWD_URL . '/assets/js');
define('IOWD_URL_IMG', IOWD_URL . '/assets/img');


define('IOWD_NAME', plugin_basename(dirname(__FILE__)));
define('IOWD_MAIN_FILE', plugin_basename(__FILE__));
define('IOWD_PREFIX', "iowd");
//define( 'IOWD_API_URL', "http://local.web-dorado.info/IO_api/v1/" );			
define('IOWD_API_URL', "https://optimizer.10web.io/api/");

setlocale(LC_ALL, 'en_US.UTF-8');
if (version_compare(phpversion(), "5.4", '>')) {
    $uri = $_SERVER['REQUEST_URI'];
    if (version_compare(phpversion(), "5.4", '>') && (strpos($uri, "iowd") || (defined('DOING_AJAX') && DOING_AJAX && isset($_POST["action"]) && $_POST["action"] == "optimize"))) {
        require_once('vendor/autoload.php');
    }

    if (class_exists("WP_REST_Controller")) {
        require_once('iowd-rest.php');
        add_action('rest_api_init', function () {
            $rest = new IOWD_Rest();
            $rest->register_routes();
        });
    }

    if (is_admin() || (defined('DOING_CRON') && DOING_CRON)) {
        require_once('iowd_class.php');
        register_activation_hook(__FILE__, array('IOWD', 'activate'));

        if (version_compare(phpversion(), "5.4", '>')) {
            require_once(IOWD_DIR_INCLUDES . '/iowd-media-library.php');
            add_action('plugins_loaded', array('IOWD', 'get_instance'));
            register_deactivation_hook(__FILE__, array('IOWD', 'deactivate'));
        }

    }

    if (!class_exists("TenWeb")) {
        require_once(IOWD_DIR . '/wd/start.php');
    }

    global $iowd_plugin_options;

    $iowd_plugin_options = array(
        "prefix"                 => IOWD_PREFIX,
        "wd_plugin_id"           => 69,
        "plugin_title"           => "Image Optimizer WD",
        "plugin_wordpress_slug"  => "image-optimizer-wd",
        "plugin_dir"             => IOWD_DIR,
        "plugin_main_file"       => __FILE__,
        "description"            => 'Optimize images and increase page load time!',
        "plugin_features"        => array(
            0 => array(
                "title"       => __("DASHBOARD REPORTS", IOWD_PREFIX),
                "description" => __("See how much space you’ve saved. Get reports on compression results for images you've optimized directly in your 10Web dashboard.", IOWD_PREFIX),
                "logo"        => IOWD_URL_IMG . "/overview/Reports.svg"
            ),
            1 => array(
                "title"       => __("SCHEDULE OPTIMIZATION", IOWD_PREFIX),
                "description" => __("Automatically optimize new images with scheduled optimization functionality. Choose to optimize images on an hourly, twice daily or daily basis.", IOWD_PREFIX),
                "logo"        => IOWD_URL_IMG . "/overview/Scheduling.svg"
            ),
            2 => array(
                "title"       => __("OPTIMIZE MORE IMAGES", IOWD_PREFIX),
                "description" => __("Have more than 1000 images on your website? Choose the premium plan to compress and optimize all images on your website.", IOWD_PREFIX),
                "logo"        => IOWD_URL_IMG . "/overview/More-Images.svg"
            ),
            3 => array(
                "title"       => __("EXTREME COMPRESSION", IOWD_PREFIX),
                "description" => __("Reduces image size up to 90% by choosing the extreme compression mode that will resize images with a tiny loss of quality.", IOWD_PREFIX),
                "logo"        => IOWD_URL_IMG . "/overview/Compression-Modes.svg"
            ),
            4 => array(
                "title"       => __("PDF FILE OPTIMIZATION", IOWD_PREFIX),
                "description" => __("Get the ability to compress and optimize any PDF documents on your WordPress website without losing image quality.", IOWD_PREFIX),
                "logo"        => IOWD_URL_IMG . "/overview/PDF-File-Optimization.svg"
            ),

        ),
        "user_guide"             => array(),
        "overview_welcome_image" => IOWD_URL_IMG . "/img-opt-logo.svg",
        "video_youtube_id"       => "",
        "plugin_wd_url"          => "https://10web.io/services/image-optimizer/",
        "plugin_wd_demo_link"    => "",
        "plugin_wd_addons_link"  => "",
        "after_subscribe"        => "admin.php?page=iowd_settings",
        "plugin_wizard_link"     => "",
        "plugin_menu_title"      => __('Image optimizer', IOWD_PREFIX),
        "plugin_menu_icon"       => IOWD_URL_IMG . "/icon.png",
        "deactivate"             => true,
        "subscribe"              => true,
        "custom_post"            => "iowd_settings",
        "menu_capability"        => "manage_options",
        "menu_position"          => null,
    );

    ten_web_init($iowd_plugin_options);
} else {
    add_action('admin_notices', 'iowd_php_version_admin_notice');
}


function iowd_php_version_admin_notice()
{
    ?>
    <div class="notice notice-error">
        <h3>Image Optimizer WD</h3>
        <p><?php _e('This version of the plugin requires PHP 5.5.0 or higher.', 'iowd'); ?></p>
        <p><?php _e('We recommend you to update PHP or ask your hosting provider to do that.', 'buwd'); ?></p>
    </div>
    <?php
}
