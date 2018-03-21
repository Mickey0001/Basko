<?php

/**
 * Plugin Name: Photo Gallery
 * Plugin URI: https://web-dorado.com/products/wordpress-photo-gallery-plugin.html
 * Description: This plugin is a fully responsive gallery plugin with advanced functionality.  It allows having different image galleries for your posts and pages. You can create unlimited number of galleries, combine them into albums, and provide descriptions and tags.
 * Version: 1.3.68
 * Author: Photo Gallery Team
 * Author URI: https://web-dorado.com/wordpress-plugins-bundle.html
 * License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

define('WD_BWG_DIR', WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)));
define('WD_BWG_URL', plugins_url(plugin_basename(dirname(__FILE__))));
define('WD_BWG_NAME', plugin_basename(dirname(__FILE__)));
define('WD_BWG_PRO', false);
define('WD_BWG_VERSION', get_option('wd_bwg_version'));
define('WD_BWG_PREFIX', 'bwg');
define('WD_BWG_NICENAME', __( 'Photo Gallery', WD_BWG_PREFIX ));
$wd_bwg_inline_stiles = FALSE;

function bwg_use_home_url() {
  $home_url = str_replace("http://", "", home_url());
  $home_url = str_replace("https://", "", $home_url);
  $pos = strpos($home_url, "/");
  if ($pos) {
    $home_url = substr($home_url, 0, $pos);
  }
  $site_url = str_replace("http://", "", WD_BWG_URL);
  $site_url = str_replace("https://", "", $site_url);
  $pos = strpos($site_url, "/");
  if ($pos) {
    $site_url = substr($site_url, 0, $pos);
  }
  return $site_url != $home_url;
}

if (bwg_use_home_url()) {
  define('WD_BWG_FRONT_URL', home_url("wp-content/plugins/" . plugin_basename(dirname(__FILE__))));
}
else {
  define('WD_BWG_FRONT_URL', WD_BWG_URL);
}

require_once(WD_BWG_DIR . '/framework/BWGOptions.php');
$wd_bwg_options = new WD_BWG_Options();

$WD_BWG_UPLOAD_DIR = $wd_bwg_options->images_directory . '/photo-gallery';

// Plugin menu.
function bwg_options_panel() {
  $parent_slug = null;
  if ( get_option( "bwg_subscribe_done" ) == 1 ) {
    add_menu_page('Photo Gallery', 'Photo Gallery', 'manage_options', 'galleries_bwg', 'bwg_gallery', WD_BWG_URL . '/images/icons/best-wordpress-gallery.png');
    $parent_slug = "galleries_bwg";
  }

  $galleries_page = add_submenu_page($parent_slug, __('Add Galleries/Images', 'bwg_back'), __('Add Galleries/Images', 'bwg_back'), 'manage_options', 'galleries_bwg', 'bwg_gallery');
  add_action('admin_print_styles-' . $galleries_page, 'bwg_styles');
  add_action('admin_print_scripts-' . $galleries_page, 'bwg_scripts');
  add_action('load-' . $galleries_page, 'bwg_add_galleries_per_page_option');

  $albums_page = add_submenu_page($parent_slug, __('Albums', 'bwg_back'), __('Albums', 'bwg_back'), 'manage_options', 'albums_bwg', 'bwg_gallery');
  add_action('admin_print_styles-' . $albums_page, 'bwg_styles');
  add_action('admin_print_scripts-' . $albums_page, 'bwg_scripts');
  add_action('load-' . $albums_page, 'bwg_add_albums_per_page_option');

  $tags_page = add_submenu_page($parent_slug, __('Tags', 'bwg_back'), __('Tags', 'bwg_back'), 'manage_options', 'tags_bwg', 'bwg_gallery');
  add_action('admin_print_styles-' . $tags_page, 'bwg_styles');
  add_action('admin_print_scripts-' . $tags_page, 'bwg_scripts');
  add_action('load-' . $tags_page, 'bwg_add_tags_per_page_option');

  $options_page = add_submenu_page($parent_slug, __('Options', 'bwg_back'), __('Options', 'bwg_back'), 'manage_options', 'options_bwg', 'bwg_gallery');
  add_action('admin_print_styles-' . $options_page, 'bwg_styles');
  add_action('admin_print_scripts-' . $options_page, 'bwg_options_scripts');

  $themes_page = add_submenu_page($parent_slug, __('Themes', 'bwg_back'), __('Themes', 'bwg_back'), 'manage_options', 'themes_bwg', 'bwg_gallery');
  add_action('admin_print_styles-' . $themes_page, 'bwg_styles');
  add_action('admin_print_scripts-' . $themes_page, 'bwg_options_scripts');
  add_action('load-' . $themes_page, 'bwg_add_themes_per_page_option');

  add_submenu_page($parent_slug, __('Generate Shortcode', 'bwg_back'), __('Generate Shortcode', 'bwg_back'), 'manage_options', 'BWGShortcode', 'bwg_gallery');
  
  $licensing_plugins_page = add_submenu_page($parent_slug, __('Get Pro', 'bwg_back'), __('Get Pro', 'bwg_back'), 'manage_options', 'licensing_bwg', 'bwg_gallery');
  add_action('admin_print_styles-' . $licensing_plugins_page, 'bwg_licensing_styles');

  $uninstall_page = add_submenu_page($parent_slug, __('Uninstall', 'bwg_back'), __('Uninstall', 'bwg_back'), 'manage_options', 'uninstall_bwg', 'bwg_gallery');
  add_action('admin_print_styles-' . $uninstall_page, 'bwg_styles');
  add_action('admin_print_scripts-' . $uninstall_page, 'bwg_options_scripts');

  add_menu_page(__('Photo Gallery Add-ons','bwg_back'), __('Photo Gallery Add-ons','bwg_back'), 'manage_options', 'addons_bwg', 'bwg_addons', WD_BWG_URL . '/addons/images/add-ons-icon.png');
}
add_action('admin_menu', 'bwg_options_panel');

function bwg_gallery() {
  global $wpdb;
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  $page = WDWLibrary::get('page');
  if (($page != '') && (($page == 'galleries_bwg') || ($page == 'albums_bwg') || ($page == 'tags_bwg') || ($page == 'options_bwg') || ($page == 'themes_bwg') || ($page == 'licensing_bwg') || ($page == 'uninstall_bwg') || ($page == 'BWGShortcode'))) {
    require_once(WD_BWG_DIR . '/admin/controllers/BWGController' . (($page == 'BWGShortcode') ? $page : ucfirst(strtolower($page))) . '.php');
    $controller_class = 'BWGController' . ucfirst(strtolower($page));
    $controller = new $controller_class();
    $controller->execute();
  }
}

function bwg_addons() {
  if (function_exists('current_user_can')) {
    if (!current_user_can('manage_options')) {
      die('Access Denied');
    }
  }
  else {
    die('Access Denied');
  }
  require_once(WD_BWG_DIR . '/addons/addons.php');
  wp_register_style('bwg_addons', WD_BWG_URL . '/addons/style.css', array(), wd_bwg_version());
  wp_print_styles('bwg_addons');
  bwg_addons_display();
}

function bwg_ajax_frontend() {
  if (function_exists('switch_to_locale') && function_exists('get_locale')) {
    switch_to_locale(get_locale());
  }
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  $page = WDWLibrary::get('action');
  if (($page != '') && ($page == 'GalleryBox')) {
    require_once(WD_BWG_DIR . '/frontend/controllers/BWGController' . ucfirst($page) . '.php');
    $controller_class = 'BWGController' . ucfirst($page);
    $controller = new $controller_class();
    $controller->execute();
  }
}

add_action('wp_ajax_bwg_UploadHandler', 'bwg_UploadHandler');
add_action('wp_ajax_addAlbumsGalleries', 'bwg_ajax');
add_action('wp_ajax_addImages', 'bwg_filemanager_ajax');
add_action('wp_ajax_addMusic', 'bwg_filemanager_ajax');
add_action('wp_ajax_addEmbed', 'bwg_add_embed_ajax');
add_action('wp_ajax_editThumb', 'bwg_ajax');
add_action('wp_ajax_addTags', 'bwg_ajax');
add_action('wp_ajax_bwg_edit_tag', 'bwg_edit_tag');
add_action('wp_ajax_GalleryBox', 'bwg_ajax_frontend');
add_action('wp_ajax_bwg_captcha', 'bwg_captcha');

add_action('wp_ajax_nopriv_GalleryBox', 'bwg_ajax_frontend');
add_action('wp_ajax_nopriv_bwg_captcha', 'bwg_captcha');

// Upload.
function bwg_UploadHandler() {
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  if(!WDWLibrary::verify_nonce('bwg_UploadHandler')){
      die('Sorry, your nonce did not verify.');
  }
  require_once(WD_BWG_DIR . '/filemanager/UploadHandler.php');
}

function bwg_filemanager_ajax() {
  if (function_exists('current_user_can')) {
    if (!current_user_can('manage_options')) {
      die('Access Denied');
    }
  }
  else {
    die('Access Denied');
  }
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  $page = WDWLibrary::get('action');

  if (($page != '') && (($page == 'addImages') || ($page == 'addMusic'))) {
    if (!WDWLibrary::verify_nonce($page)) {
      die('Sorry, your nonce did not verify.');
    }
    require_once(WD_BWG_DIR . '/filemanager/controller.php');
    $controller_class = 'FilemanagerController';
    $controller = new $controller_class();
    $controller->execute();
  }
}

function bwg_add_embed_ajax() {
  if (function_exists('current_user_can')) {
    if (!current_user_can('manage_options')) {
      die('Access Denied');
    }
  }
  else {
    die('Access Denied');
  }
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  if (!WDWLibrary::verify_nonce('')) {
    die(WDWLibrary::delimit_wd_output(json_encode(array("error", "Sorry, your nonce did not verify."))));
  }

  require_once(WD_BWG_DIR . '/framework/WDWLibraryEmbed.php');
  $embed_action = WDWLibrary::get('action');

  if (($embed_action != '') && ($embed_action == 'addEmbed')) {
    $url_to_embed = WDWLibrary::get('URL_to_embed');

    $data = WDWLibraryEmbed::add_embed($url_to_embed);

    echo WDWLibrary::delimit_wd_output($data);
    wp_die();
  }
  die('Nothing to add');
}

function bwg_edit_tag() {
  if (function_exists('current_user_can')) {
    if (!current_user_can('manage_options')) {
      die('Access Denied');
    }
  }
  else {
    die('Access Denied');
  }
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  if (!WDWLibrary::verify_nonce('')) {
    die('Sorry, your nonce did not verify.');
  }
  require_once(WD_BWG_DIR . '/admin/controllers/BWGControllerTags_bwg.php');
  $controller_class = 'BWGControllerTags_bwg';
  $controller = new $controller_class();
  $controller->edit_tag();
}

function bwg_ajax() {
  if (function_exists('current_user_can')) {
    if (!current_user_can('manage_options')) {
      die('Access Denied');
    }
  }
  else {
    die('Access Denied');
  }
  global $wpdb;
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  $page = WDWLibrary::get('action');
  if ($page != '' && (($page == 'BWGShortcode') || ($page == 'addAlbumsGalleries') || ($page == 'editThumb') || ($page == 'addTags'))) {
    if (!WDWLibrary::verify_nonce($page) && ($page != 'BWGShortcode')) {
      die('Sorry, your nonce did not verify.');
    }

    require_once(WD_BWG_DIR . '/admin/controllers/BWGController' . ucfirst($page) . '.php');
    $controller_class = 'BWGController' . ucfirst($page);
    $controller = new $controller_class();
    $controller->execute();
  }
}

function bwg_create_taxonomy() {
  register_taxonomy('bwg_tag', 'post', array(
    'public' => TRUE,
    'show_ui' => FALSE,
    'show_in_nav_menus' => FALSE,
    'show_tagcloud' => TRUE,
    'hierarchical' => FALSE,
    'label' => 'Photo Gallery',
    'query_var' => TRUE,
    'rewrite' => TRUE));
}
add_action('init', 'bwg_create_taxonomy', 0);

function photo_gallery($id) {
  echo bwg_shortcode(array('id' => $id));
}

function bwg_shortcode($params) {
  if ( is_admin() && defined('DOING_AJAX') && !DOING_AJAX) {
    return;
  }
  if (isset($params['id'])) {
    global $wpdb;
    $shortcode = $wpdb->get_var($wpdb->prepare("SELECT tagtext FROM " . $wpdb->prefix . "bwg_shortcode WHERE id='%d'", $params['id']));
    if ($shortcode) {
      $shortcode_params = explode('" ', $shortcode);
      foreach ($shortcode_params as $shortcode_param) {
        $shortcode_param = str_replace('"', '', $shortcode_param);
        $shortcode_elem = explode('=', $shortcode_param);
        $params[str_replace(' ', '', $shortcode_elem[0])] = $shortcode_elem[1];
      }
    }
    else {
		return;
    }
  }  	
	$pairs = array();
	$pairs = WDWLibrary::get_shortcode_option_params( $params );
	ob_start();
	bwg_front_end( $pairs );
	return str_replace(array("\r\n", "\n", "\r"), '', ob_get_clean());
    // return ob_get_clean();
}
add_shortcode('Best_Wordpress_Gallery', 'bwg_shortcode');

$bwg = 0;
function bwg_front_end($params) {
  require_once(WD_BWG_DIR . '/frontend/controllers/BWGController' . ucfirst($params['gallery_type']) . '.php');
  $controller_class = 'BWGController' . ucfirst($params['gallery_type']) . '';
  $controller = new $controller_class();
  global $bwg;
  $controller->execute($params, 1, $bwg);
  $bwg++;

  return;
}

// Add the Photo Gallery button.
function bwg_add_button($buttons) {
  array_push($buttons, "bwg_mce");
  return $buttons;
}

// Register Photo Gallery button.
function bwg_register($plugin_array) {
  $url = WD_BWG_URL . '/js/bwg_editor_button.js';
  $plugin_array["bwg_mce"] = $url;
  return $plugin_array;
}

function bwg_admin_ajax() {
  ?>
  <script>
    var bwg_admin_ajax = '<?php echo add_query_arg(array('action' => 'BWGShortcode'), admin_url('admin-ajax.php')); ?>';
    var bwg_plugin_url = '<?php echo WD_BWG_URL; ?>';
  </script>
  <?php
}
add_action('admin_head', 'bwg_admin_ajax');

// Add the Photo Gallery button to editor.
add_action('wp_ajax_BWGShortcode', 'bwg_ajax');
add_filter('mce_external_plugins', 'bwg_register');
add_filter('mce_buttons', 'bwg_add_button', 0);

function bwg_register_widgets() {
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  require_once(WD_BWG_DIR . '/admin/controllers/BWGControllerWidget.php');
  register_widget("BWGControllerWidget");
  require_once(WD_BWG_DIR . '/admin/controllers/BWGControllerWidgetSlideshow.php');
  register_widget("BWGControllerWidgetSlideshow");
}
// Photo Gallery Widget.
if (class_exists('WP_Widget')) {
  add_action('widgets_init', 'bwg_register_widgets');
}
// Intro tour
function bwg_pointer_init() {
    include_once (WD_BWG_DIR .'/includes/bwg_pointers.php');
    new BWG_pointers();
}
add_action('admin_init', 'bwg_pointer_init');

// Activate plugin.
function bwg_activate() {
  global $wpdb;
  $bwg_shortcode = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "bwg_shortcode` (
    `id` bigint(20) NOT NULL,
    `tagtext` mediumtext NOT NULL,
    PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8;";
  $wpdb->query($bwg_shortcode);
  $bwg_gallery = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "bwg_gallery` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` mediumtext NOT NULL,
    `page_link` mediumtext NOT NULL,
    `preview_image` mediumtext NOT NULL,
    `random_preview_image` mediumtext NOT NULL,
    `order` bigint(20) NOT NULL,
    `author` bigint(20) NOT NULL,
    `published` tinyint(1) NOT NULL,
    `gallery_type` varchar(32) NOT NULL,
    `gallery_source` varchar(256) NOT NULL,
    `autogallery_image_number` int(4) NOT NULL,
    `update_flag` varchar(32) NOT NULL,
    PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8;";
  $wpdb->query($bwg_gallery);
  $bwg_album = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "bwg_album` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` mediumtext NOT NULL,
    `preview_image` mediumtext NOT NULL,
    `random_preview_image` mediumtext NOT NULL,
    `order` bigint(20) NOT NULL,
    `author` bigint(20) NOT NULL,
    `published` tinyint(1) NOT NULL,
    PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8;";
  $wpdb->query($bwg_album);
  $bwg_album_gallery = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "bwg_album_gallery` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `album_id` bigint(20) NOT NULL,
    `is_album` tinyint(1) NOT NULL,
    `alb_gal_id` bigint(20) NOT NULL,
    `order` bigint(20) NOT NULL,
    PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8;";
  $wpdb->query($bwg_album_gallery);
  $bwg_image = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "bwg_image` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `gallery_id` bigint(20) NOT NULL,
    `slug` longtext NOT NULL,
    `filename` varchar(255) NOT NULL,
    `image_url` mediumtext NOT NULL,
    `thumb_url` mediumtext NOT NULL,
    `description` mediumtext NOT NULL,
    `alt` mediumtext NOT NULL,
    `date` varchar(128) NOT NULL,
    `size` varchar(128) NOT NULL,
    `filetype` varchar(128) NOT NULL,
    `resolution` varchar(128) NOT NULL,
    `author` bigint(20) NOT NULL,
    `order` bigint(20) NOT NULL,
    `published` tinyint(1) NOT NULL,
    `comment_count` bigint(20) NOT NULL,
    `avg_rating` float(20) NOT NULL,
    `rate_count` bigint(20) NOT NULL,
    `hit_count` bigint(20) NOT NULL,
    `redirect_url` varchar(255) NOT NULL,
    `pricelist_id` bigint(20) NOT NULL,

    PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8;";
  $wpdb->query($bwg_image);
  $bwg_image_tag = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "bwg_image_tag` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `tag_id` bigint(20) NOT NULL,
    `image_id` bigint(20) NOT NULL,
    `gallery_id` bigint(20) NOT NULL,
    PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8;";
  $wpdb->query($bwg_image_tag);
  $bwg_theme = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "bwg_theme` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `options` longtext NOT NULL,
    `default_theme` tinyint(1) NOT NULL,
    PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8;";
  $wpdb->query($bwg_theme);
  $bwg_image_comment = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "bwg_image_comment` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `image_id` bigint(20) NOT NULL,
    `name` varchar(255) NOT NULL,
    `date` varchar(64) NOT NULL,
    `comment` mediumtext NOT NULL,
    `url` mediumtext NOT NULL,
    `mail` mediumtext NOT NULL,
    `published` tinyint(1) NOT NULL,
    PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8;";
  $wpdb->query($bwg_image_comment);

  $bwg_image_rate = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "bwg_image_rate` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `image_id` bigint(20) NOT NULL,
    `rate` float(16) NOT NULL,
    `ip` varchar(64) NOT NULL,
    `date` varchar(64) NOT NULL,
    PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8;";
  $wpdb->query($bwg_image_rate);

  $exists_default = $wpdb->get_var('SELECT count(id) FROM ' . $wpdb->prefix . 'bwg_theme');
  $theme1 = array(
      'thumb_margin' => 4,
      'thumb_padding' => 0,
      'thumb_border_radius' => '0',
      'thumb_border_width' => 0,
      'thumb_border_style' => 'none',
      'thumb_border_color' => 'CCCCCC',
      'thumb_bg_color' => 'FFFFFF',
      'thumbs_bg_color' => 'FFFFFF',
      'thumb_bg_transparent' => 0,
      'thumb_box_shadow' => '0px 0px 0px #888888',
      'thumb_transparent' => 100,
      'thumb_align' => 'center',
      'thumb_hover_effect' => 'scale',
      'thumb_hover_effect_value' => '1.1',
      'thumb_transition' => 1,
      'thumb_title_font_color' => 'CCCCCC',
      'thumb_title_font_style' => 'segoe ui',
      'thumb_title_pos' => 'bottom',
      'thumb_title_font_size' => 16,
      'thumb_title_font_weight' => 'bold',
      'thumb_title_margin' => '2px',
      'thumb_title_shadow' => '0px 0px 0px #888888',
      'thumb_gal_title_font_color' => 'CCCCCC',
      'thumb_gal_title_font_style' => 'segoe ui',
      'thumb_gal_title_font_size' => 16,
      'thumb_gal_title_font_weight' => 'bold',
      'thumb_gal_title_margin' => '2px',
      'thumb_gal_title_shadow' => '0px 0px 0px #888888',
      'thumb_gal_title_align' => 'center',

      'page_nav_position' => 'bottom',
      'page_nav_align' => 'center',
      'page_nav_number' => 0,
      'page_nav_font_size' => 12,
      'page_nav_font_style' => 'segoe ui',
      'page_nav_font_color' => '666666',
      'page_nav_font_weight' => 'bold',
      'page_nav_border_width' => 1,
      'page_nav_border_style' => 'solid',
      'page_nav_border_color' => 'E3E3E3',
      'page_nav_border_radius' => '0',
      'page_nav_margin' => '0',
      'page_nav_padding' => '3px 6px',
      'page_nav_button_bg_color' => 'FFFFFF',
      'page_nav_button_bg_transparent' => 100,
      'page_nav_box_shadow' => '0',
      'page_nav_button_transition' => 1,
      'page_nav_button_text' => 0,

      'lightbox_overlay_bg_color' => '000000',
      'lightbox_overlay_bg_transparent' => 70,
      'lightbox_bg_color' => '000000',
      'lightbox_ctrl_btn_pos' => 'bottom',
      'lightbox_ctrl_btn_align' => 'center',
      'lightbox_ctrl_btn_height' => 20,
      'lightbox_ctrl_btn_margin_top' => 10,
      'lightbox_ctrl_btn_margin_left' => 7,
      'lightbox_ctrl_btn_transparent' => 100,
      'lightbox_ctrl_btn_color' => 'FFFFFF',
      'lightbox_toggle_btn_height' => 14,
      'lightbox_toggle_btn_width' => 100,
      'lightbox_ctrl_cont_bg_color' => '000000',
      'lightbox_ctrl_cont_transparent' => 65,
      'lightbox_ctrl_cont_border_radius' => 4,
      'lightbox_close_btn_transparent' => 100,
      'lightbox_close_btn_bg_color' => '000000',
      'lightbox_close_btn_border_width' => 2,
      'lightbox_close_btn_border_radius' => '16px',
      'lightbox_close_btn_border_style' => 'none',
      'lightbox_close_btn_border_color' => 'FFFFFF',
      'lightbox_close_btn_box_shadow' => '0',
      'lightbox_close_btn_color' => 'FFFFFF',
      'lightbox_close_btn_size' => 10,
      'lightbox_close_btn_width' => 20,
      'lightbox_close_btn_height' => 20,
      'lightbox_close_btn_top' => '-10',
      'lightbox_close_btn_right' => '-10',
      'lightbox_close_btn_full_color' => 'FFFFFF',
      'lightbox_rl_btn_bg_color' => '000000',
      'lightbox_rl_btn_border_radius' => '20px',
      'lightbox_rl_btn_border_width' => 0,
      'lightbox_rl_btn_border_style' => 'none',
      'lightbox_rl_btn_border_color' => 'FFFFFF',
      'lightbox_rl_btn_box_shadow' => '',
      'lightbox_rl_btn_color' => 'FFFFFF',
      'lightbox_rl_btn_height' => 40,
      'lightbox_rl_btn_width' => 40,
      'lightbox_rl_btn_size' => 20,
      'lightbox_close_rl_btn_hover_color' => 'CCCCCC',
      'lightbox_comment_pos' => 'left',
      'lightbox_comment_width' => 400,
      'lightbox_comment_bg_color' => '000000',
      'lightbox_comment_font_color' => 'CCCCCC',
      'lightbox_comment_font_style' => 'segoe ui',
      'lightbox_comment_font_size' => 12,
      'lightbox_comment_button_bg_color' => '616161',
      'lightbox_comment_button_border_color' => '666666',
      'lightbox_comment_button_border_width' => 1,
      'lightbox_comment_button_border_style' => 'none',
      'lightbox_comment_button_border_radius' => '3px',
      'lightbox_comment_button_padding' => '3px 10px',
      'lightbox_comment_input_bg_color' => '333333',
      'lightbox_comment_input_border_color' => '666666',
      'lightbox_comment_input_border_width' => 1,
      'lightbox_comment_input_border_style' => 'none',
      'lightbox_comment_input_border_radius' => '0',
      'lightbox_comment_input_padding' => '2px',
      'lightbox_comment_separator_width' => 1,
      'lightbox_comment_separator_style' => 'solid',
      'lightbox_comment_separator_color' => '383838',
      'lightbox_comment_author_font_size' => 14,
      'lightbox_comment_date_font_size' => 10,
      'lightbox_comment_body_font_size' => 12,
      'lightbox_comment_share_button_color' => 'CCCCCC',
      'lightbox_filmstrip_pos' => 'top',
      'lightbox_filmstrip_rl_bg_color' => '3B3B3B',
      'lightbox_filmstrip_rl_btn_size' => 20,
      'lightbox_filmstrip_rl_btn_color' => 'FFFFFF',
      'lightbox_filmstrip_thumb_margin' => '0 1px',
      'lightbox_filmstrip_thumb_border_width' => 1,
      'lightbox_filmstrip_thumb_border_style' => 'solid',
      'lightbox_filmstrip_thumb_border_color' => '000000',
      'lightbox_filmstrip_thumb_border_radius' => '0',
      'lightbox_filmstrip_thumb_deactive_transparent' => 80,
      'lightbox_filmstrip_thumb_active_border_width' => 0,
      'lightbox_filmstrip_thumb_active_border_color' => 'FFFFFF',
      'lightbox_rl_btn_style' => 'fa-chevron',
      'lightbox_rl_btn_transparent' => 80,
      'lightbox_bg_transparent' => 100,

      'album_compact_back_font_color' => '000000',
      'album_compact_back_font_style' => 'segoe ui',
      'album_compact_back_font_size' => 16,
      'album_compact_back_font_weight' => 'bold',
      'album_compact_back_padding' => '0',
      'album_compact_title_font_color' => 'CCCCCC',
      'album_compact_title_font_style' => 'segoe ui',
      'album_compact_thumb_title_pos' => 'bottom',
      'album_compact_title_font_size' => 16,
      'album_compact_title_font_weight' => 'bold',
      'album_compact_title_margin' => '2px',
      'album_compact_title_shadow' => '0px 0px 0px #888888',
      'album_compact_thumb_margin' => 4,
      'album_compact_thumb_padding' => 0,
      'album_compact_thumb_border_radius' => '0',
      'album_compact_thumb_border_width' => 0,
      'album_compact_thumb_border_style' => 'none',
      'album_compact_thumb_border_color' => 'CCCCCC',
      'album_compact_thumb_bg_color' => 'FFFFFF',
      'album_compact_thumbs_bg_color' => 'FFFFFF',
      'album_compact_thumb_bg_transparent' => 0,
      'album_compact_thumb_box_shadow' => '0px 0px 0px #888888',
      'album_compact_thumb_transparent' => 100,
      'album_compact_thumb_align' => 'center',
      'album_compact_thumb_hover_effect' => 'scale',
      'album_compact_thumb_hover_effect_value' => '1.1',
      'album_compact_thumb_transition' => 0,
      'album_compact_gal_title_font_color' => 'CCCCCC',
      'album_compact_gal_title_font_style' => 'segoe ui',
      'album_compact_gal_title_font_size' => 16,
      'album_compact_gal_title_font_weight' => 'bold',
      'album_compact_gal_title_margin' => '2px',
      'album_compact_gal_title_shadow' => '0px 0px 0px #888888',
      'album_compact_gal_title_align' => 'center',

      'album_extended_thumb_margin' => 2,
      'album_extended_thumb_padding' => 0,
      'album_extended_thumb_border_radius' => '0',
      'album_extended_thumb_border_width' => 0,
      'album_extended_thumb_border_style' => 'none',
      'album_extended_thumb_border_color' => 'CCCCCC',
      'album_extended_thumb_bg_color' => 'FFFFFF',
      'album_extended_thumbs_bg_color' => 'FFFFFF',
      'album_extended_thumb_bg_transparent' => 0,
      'album_extended_thumb_box_shadow' => '',
      'album_extended_thumb_transparent' => 100,
      'album_extended_thumb_align' => 'left',
      'album_extended_thumb_hover_effect' => 'scale',
      'album_extended_thumb_hover_effect_value' => '1.1',
      'album_extended_thumb_transition' => 0,
      'album_extended_back_font_color' => '000000',
      'album_extended_back_font_style' => 'segoe ui',
      'album_extended_back_font_size' => 20,
      'album_extended_back_font_weight' => 'bold',
      'album_extended_back_padding' => '0',
      'album_extended_div_bg_color' => 'FFFFFF',
      'album_extended_div_bg_transparent' => 0,
      'album_extended_div_border_radius' => '0 0 0 0',
      'album_extended_div_margin' => '0 0 5px 0',
      'album_extended_div_padding' => 10,
      'album_extended_div_separator_width' => 1,
      'album_extended_div_separator_style' => 'solid',
      'album_extended_div_separator_color' => 'E0E0E0',
      'album_extended_thumb_div_bg_color' => 'FFFFFF',
      'album_extended_thumb_div_border_radius' => '0',
      'album_extended_thumb_div_border_width' => 1,
      'album_extended_thumb_div_border_style' => 'solid',
      'album_extended_thumb_div_border_color' => 'E8E8E8',
      'album_extended_thumb_div_padding' => '5px',
      'album_extended_text_div_bg_color' => 'FFFFFF',
      'album_extended_text_div_border_radius' => '0',
      'album_extended_text_div_border_width' => 1,
      'album_extended_text_div_border_style' => 'solid',
      'album_extended_text_div_border_color' => 'E8E8E8',
      'album_extended_text_div_padding' => '5px',
      'album_extended_title_span_border_width' => 1,
      'album_extended_title_span_border_style' => 'none',
      'album_extended_title_span_border_color' => 'CCCCCC',
      'album_extended_title_font_color' => '000000',
      'album_extended_title_font_style' => 'segoe ui',
      'album_extended_title_font_size' => 16,
      'album_extended_title_font_weight' => 'bold',
      'album_extended_title_margin_bottom' => 2,
      'album_extended_title_padding' => '2px',
      'album_extended_desc_span_border_width' => 1,
      'album_extended_desc_span_border_style' => 'none',
      'album_extended_desc_span_border_color' => 'CCCCCC',
      'album_extended_desc_font_color' => '000000',
      'album_extended_desc_font_style' => 'segoe ui',
      'album_extended_desc_font_size' => 14,
      'album_extended_desc_font_weight' => 'normal',
      'album_extended_desc_padding' => '2px',
      'album_extended_desc_more_color' => 'F2D22E',
      'album_extended_desc_more_size' => 12,
      'album_extended_gal_title_font_color' => 'CCCCCC',
      'album_extended_gal_title_font_style' => 'segoe ui',
      'album_extended_gal_title_font_size' => 16,
      'album_extended_gal_title_font_weight' => 'bold',
      'album_extended_gal_title_margin' => '2px',
      'album_extended_gal_title_shadow' => '0px 0px 0px #888888',
      'album_extended_gal_title_align' => 'center',

      'masonry_thumb_padding' => 4,
      'masonry_thumb_border_radius' => '0',
      'masonry_thumb_border_width' => 0,
      'masonry_thumb_border_style' => 'none',
      'masonry_thumb_border_color' => 'CCCCCC',
      'masonry_thumbs_bg_color' => 'FFFFFF',
      'masonry_thumb_bg_transparent' => 0,
      'masonry_thumb_transparent' => 100,
      'masonry_thumb_align' => 'center',
      'masonry_thumb_hover_effect' => 'scale',
      'masonry_thumb_hover_effect_value' => '1.1',
      'masonry_thumb_transition' => 0,
      'masonry_thumb_gal_title_font_color' => 'CCCCCC',
      'masonry_thumb_gal_title_font_style' => 'segoe ui',
      'masonry_thumb_gal_title_font_size' => 16,
      'masonry_thumb_gal_title_font_weight' => 'bold',
      'masonry_thumb_gal_title_margin' => '2px',
      'masonry_thumb_gal_title_shadow' => '0px 0px 0px #888888',
      'masonry_thumb_gal_title_align' => 'center',

      'slideshow_cont_bg_color' => '000000',
      'slideshow_close_btn_transparent' => 100,
      'slideshow_rl_btn_bg_color' => '000000',
      'slideshow_rl_btn_border_radius' => '20px',
      'slideshow_rl_btn_border_width' => 0,
      'slideshow_rl_btn_border_style' => 'none',
      'slideshow_rl_btn_border_color' => 'FFFFFF',
      'slideshow_rl_btn_box_shadow' => '0px 0px 0px #000000',
      'slideshow_rl_btn_color' => 'FFFFFF',
      'slideshow_rl_btn_height' => 40,
      'slideshow_rl_btn_size' => 20,
      'slideshow_rl_btn_width' => 40,
      'slideshow_close_rl_btn_hover_color' => 'CCCCCC',
      'slideshow_filmstrip_pos' => 'top',
      'slideshow_filmstrip_thumb_border_width' => 1,
      'slideshow_filmstrip_thumb_border_style' => 'solid',
      'slideshow_filmstrip_thumb_border_color' =>  '000000',
      'slideshow_filmstrip_thumb_border_radius' => '0',
      'slideshow_filmstrip_thumb_margin' =>  '0 1px',
      'slideshow_filmstrip_thumb_active_border_width' => 0,
      'slideshow_filmstrip_thumb_active_border_color' => 'FFFFFF',
      'slideshow_filmstrip_thumb_deactive_transparent' => 80,
      'slideshow_filmstrip_rl_bg_color' => '3B3B3B',
      'slideshow_filmstrip_rl_btn_color' => 'FFFFFF',
      'slideshow_filmstrip_rl_btn_size' => 20,
      'slideshow_title_font_size' => 16,
      'slideshow_title_font' => 'segoe ui',
      'slideshow_title_color' => 'FFFFFF',
      'slideshow_title_opacity' => 70,
      'slideshow_title_border_radius' => '5px',
      'slideshow_title_background_color' => '000000',
      'slideshow_title_padding' => '0 0 0 0',
      'slideshow_description_font_size' => 14,
      'slideshow_description_font' => 'segoe ui',
      'slideshow_description_color' => 'FFFFFF',
      'slideshow_description_opacity' => 70,
      'slideshow_description_border_radius' => '0',
      'slideshow_description_background_color' => '000000',
      'slideshow_description_padding' => '5px 10px 5px 10px',
      'slideshow_dots_width' => 12,
      'slideshow_dots_height' => 12,
      'slideshow_dots_border_radius' => '5px',
      'slideshow_dots_background_color' => 'F2D22E',
      'slideshow_dots_margin' => 3,
      'slideshow_dots_active_background_color' => 'FFFFFF',
      'slideshow_dots_active_border_width' => 1,
      'slideshow_dots_active_border_color' => '000000',
      'slideshow_play_pause_btn_size' => 60,
      'slideshow_rl_btn_style' => 'fa-chevron',

      'blog_style_margin' => '2px',
      'blog_style_padding' => '0',
      'blog_style_border_radius' => '0',
      'blog_style_border_width' => 1,
      'blog_style_border_style' => 'solid',
      'blog_style_border_color' => 'F5F5F5',
      'blog_style_bg_color' => 'FFFFFF',    
      'blog_style_transparent' => 80,
      'blog_style_box_shadow' => '',
      'blog_style_align' => 'center',
      'blog_style_share_buttons_margin' => '5px auto 10px auto',
      'blog_style_share_buttons_border_radius' => '0',
      'blog_style_share_buttons_border_width' => 0,
      'blog_style_share_buttons_border_style' => 'none',
      'blog_style_share_buttons_border_color' => '000000',
      'blog_style_share_buttons_bg_color' => 'FFFFFF',
      'blog_style_share_buttons_align' => 'right',
      'blog_style_img_font_size' => 16,
      'blog_style_img_font_family' => 'segoe ui',
      'blog_style_img_font_color' => '000000',
      'blog_style_share_buttons_color' => 'B3AFAF',
      'blog_style_share_buttons_bg_transparent' => 0,
      'blog_style_share_buttons_font_size' => 20,
      'blog_style_gal_title_font_color' => 'CCCCCC',
      'blog_style_gal_title_font_style' => 'segoe ui',
      'blog_style_gal_title_font_size' => 16,
      'blog_style_gal_title_font_weight' => 'bold',
      'blog_style_gal_title_margin' => '2px',
      'blog_style_gal_title_shadow' => '0px 0px 0px #888888',
      'blog_style_gal_title_align' => 'center',

      'image_browser_margin' =>  '2px auto',
      'image_browser_padding' =>  '4px',
      'image_browser_border_radius'=>  '0',
      'image_browser_border_width' =>  1,
      'image_browser_border_style' => 'none',
      'image_browser_border_color' => 'F5F5F5',
      'image_browser_bg_color' => 'EBEBEB',
      'image_browser_box_shadow' => '',
      'image_browser_transparent' => 80,
      'image_browser_align' => 'center',	
      'image_browser_image_description_margin' => '0px 5px 0px 5px',
      'image_browser_image_description_padding' => '8px 8px 8px 8px',
      'image_browser_image_description_border_radius' => '0',
      'image_browser_image_description_border_width' => 1,
      'image_browser_image_description_border_style' => 'none',
      'image_browser_image_description_border_color' => 'FFFFFF',
      'image_browser_image_description_bg_color' => 'EBEBEB',
      'image_browser_image_description_align' => 'center',	
      'image_browser_img_font_size' => 15,
      'image_browser_img_font_family' => 'segoe ui',
      'image_browser_img_font_color' => '000000',
      'image_browser_full_padding' => '4px',
      'image_browser_full_border_radius' => '0',
      'image_browser_full_border_width' => 2,
      'image_browser_full_border_style' => 'none',
      'image_browser_full_border_color' => 'F7F7F7',
      'image_browser_full_bg_color' => 'F5F5F5',
      'image_browser_full_transparent' => 90,
      'image_browser_image_title_align' => 'top',
      'image_browser_gal_title_font_color' => 'CCCCCC',
      'image_browser_gal_title_font_style' => 'segoe ui',
      'image_browser_gal_title_font_size' => 16,
      'image_browser_gal_title_font_weight' => 'bold',
      'image_browser_gal_title_margin' => '2px',
      'image_browser_gal_title_shadow' => '0px 0px 0px #888888',
      'image_browser_gal_title_align' => 'center',

      'lightbox_info_pos' => 'top',
      'lightbox_info_align' => 'right',
      'lightbox_info_bg_color' => '000000',
      'lightbox_info_bg_transparent' => 70,
      'lightbox_info_border_width' => 1,
      'lightbox_info_border_style' => 'none',
      'lightbox_info_border_color' => '000000',
      'lightbox_info_border_radius' => '5px',
      'lightbox_info_padding' => '5px',
      'lightbox_info_margin' => '15px',
      'lightbox_title_color' => 'FFFFFF',
      'lightbox_title_font_style' => 'segoe ui',
      'lightbox_title_font_weight' => 'bold',
      'lightbox_title_font_size' => 18,
      'lightbox_description_color' => 'FFFFFF',
      'lightbox_description_font_style' => 'segoe ui',
      'lightbox_description_font_weight' => 'normal',
      'lightbox_description_font_size' => 14,

      'lightbox_rate_pos' => 'bottom',
      'lightbox_rate_align' => 'right',
      'lightbox_rate_icon' => 'star',
      'lightbox_rate_color' => 'F9D062',
      'lightbox_rate_size' => 20,
      'lightbox_rate_stars_count' => 5,
      'lightbox_rate_padding' => '15px',
      'lightbox_rate_hover_color' => 'F7B50E',

      'lightbox_hit_pos' => 'bottom',
      'lightbox_hit_align' => 'left',
      'lightbox_hit_bg_color' => '000000',
      'lightbox_hit_bg_transparent' => 70,
      'lightbox_hit_border_width' => 1,
      'lightbox_hit_border_style' => 'none',
      'lightbox_hit_border_color' => '000000',
      'lightbox_hit_border_radius' => '5px',
      'lightbox_hit_padding' => '5px',
      'lightbox_hit_margin' => '0 5px',
      'lightbox_hit_color' => 'FFFFFF',
      'lightbox_hit_font_style' => 'segoe ui',
      'lightbox_hit_font_weight' => 'normal',
      'lightbox_hit_font_size' => 14,
      'masonry_description_font_size' => 12,
			'masonry_description_color' => 'CCCCCC',
			'masonry_description_font_style' => 'segoe ui',

			'album_masonry_back_font_color' => '000000',
      'album_masonry_back_font_style' => 'segoe ui',
      'album_masonry_back_font_size' => 16,
      'album_masonry_back_font_weight' => 'bold',
      'album_masonry_back_padding' => '0',
      'album_masonry_title_font_color' => 'CCCCCC',
      'album_masonry_title_font_style' => 'segoe ui',
      'album_masonry_thumb_title_pos' => 'bottom',
      'album_masonry_title_font_size' => 16,
      'album_masonry_title_font_weight' => 'bold',
      'album_masonry_title_margin' => '2px',
      'album_masonry_title_shadow' => '0px 0px 0px #888888',
      'album_masonry_thumb_margin' => 4,
      'album_masonry_thumb_padding' => 0,
      'album_masonry_thumb_border_radius' => '0',
      'album_masonry_thumb_border_width' => 0,
      'album_masonry_thumb_border_style' => 'none',
      'album_masonry_thumb_border_color' => 'CCCCCC',
      'album_masonry_thumb_bg_color' => 'FFFFFF',
      'album_masonry_thumbs_bg_color' => 'FFFFFF',
      'album_masonry_thumb_bg_transparent' => 0,
      'album_masonry_thumb_box_shadow' => '0px 0px 0px #888888',
      'album_masonry_thumb_transparent' => 100,
      'album_masonry_thumb_align' => 'center',
      'album_masonry_thumb_hover_effect' => 'scale',
      'album_masonry_thumb_hover_effect_value' => '1.1',
      'album_masonry_thumb_transition' => 0,
      'album_masonry_gal_title_font_color' => 'CCCCCC',
      'album_masonry_gal_title_font_style' => 'segoe ui',
      'album_masonry_gal_title_font_size' => 16,
      'album_masonry_gal_title_font_weight' => 'bold',
      'album_masonry_gal_title_margin' => '2px',
      'album_masonry_gal_title_shadow' => '0px 0px 0px #888888',
      'album_masonry_gal_title_align' => 'center',
      
      'mosaic_thumb_padding' => 4,
      'mosaic_thumb_border_radius' => '0',
      'mosaic_thumb_border_width' => 0,
      'mosaic_thumb_border_style' => 'none',
      'mosaic_thumb_border_color' => 'CCCCCC',
      'mosaic_thumbs_bg_color' => 'FFFFFF',
      'mosaic_thumb_bg_transparent' => 0,
      'mosaic_thumb_transparent' => 100,
      'mosaic_thumb_align' => 'center',
      'mosaic_thumb_hover_effect' => 'scale',
      'mosaic_thumb_hover_effect_value' => '1.1',
      'mosaic_thumb_title_font_color' => 'CCCCCC',
      'mosaic_thumb_title_font_style' => 'segoe ui',
      'mosaic_thumb_title_font_weight' => 'bold',
      'mosaic_thumb_title_margin' => '2px',
      'mosaic_thumb_title_shadow' => '0px 0px 0px #888888',
      'mosaic_thumb_title_font_size' => 16,
      'mosaic_thumb_gal_title_font_color' => 'CCCCCC',
      'mosaic_thumb_gal_title_font_style' => 'segoe ui',
      'mosaic_thumb_gal_title_font_size' => 16,
      'mosaic_thumb_gal_title_font_weight' => 'bold',
      'mosaic_thumb_gal_title_margin' => '2px',
      'mosaic_thumb_gal_title_shadow' => '0px 0px 0px #888888',
      'mosaic_thumb_gal_title_align' => 'center',
      
      'carousel_cont_bg_color' => '000000',
      'carousel_cont_btn_transparent' =>  0, 
      'carousel_close_btn_transparent' =>  100, 
      'carousel_rl_btn_bg_color' => '000000',
      'carousel_rl_btn_border_radius' => '20px',  
      'carousel_rl_btn_border_width' =>  0,
      'carousel_rl_btn_border_style' => 'none',
      'carousel_rl_btn_border_color' => 'FFFFFF',       
      'carousel_rl_btn_color' => 'FFFFFF',
      'carousel_rl_btn_height' => 40, 
      'carousel_rl_btn_size' => 20,
      'carousel_play_pause_btn_size' => 20,
      'carousel_rl_btn_width' => 40,
      'carousel_close_rl_btn_hover_color' => 'CCCCCC',
      'carousel_rl_btn_style' => 'fa-chevron',
      'carousel_mergin_bottom' => '0.5',     
      'carousel_font_family' => 'segoe ui',
      'carousel_feature_border_width' => 2,
      'carousel_feature_border_style' => 'solid',
      'carousel_feature_border_color' => '5D204F',       
      'carousel_caption_background_color' => '000000',
      'carousel_caption_bottom' => 0,
      'carousel_caption_p_mergin' => 0, 
      'carousel_caption_p_pedding' => 5,   
      'carousel_caption_p_font_weight' => 'bold', 
      'carousel_caption_p_font_size' => 14,
      'carousel_caption_p_color' => 'white',
      'carousel_title_opacity' => 100,
      'carousel_title_border_radius' => '5px',
      'mosaic_thumb_transition' => 1,
    );
    $theme2 = array(
      'thumb_margin' => 4,
      'thumb_padding' => 4,
      'thumb_border_radius' => '0',
      'thumb_border_width' => 5,
      'thumb_border_style' => 'none',
      'thumb_border_color' => 'FFFFFF',
      'thumb_bg_color' => 'E8E8E8',
      'thumbs_bg_color' => 'FFFFFF',
      'thumb_bg_transparent' => 0,
      'thumb_box_shadow' => '0px 0px 0px #888888',
      'thumb_transparent' => 100,
      'thumb_align' => 'center',
      'thumb_hover_effect' => 'rotate',
      'thumb_hover_effect_value' => '2deg',
      'thumb_transition' => 1,
      'thumb_title_font_color' => 'CCCCCC',
      'thumb_title_font_style' => 'segoe ui',
      'thumb_title_pos' => 'bottom',
      'thumb_title_font_size' => 16,
      'thumb_title_font_weight' => 'bold',
      'thumb_title_margin' => '5px',
      'thumb_title_shadow' => '',
      'thumb_gal_title_font_color' => 'CCCCCC',
      'thumb_gal_title_font_style' => 'segoe ui',
      'thumb_gal_title_font_size' => 16,
      'thumb_gal_title_font_weight' => 'bold',
      'thumb_gal_title_margin' => '2px',
      'thumb_gal_title_shadow' => '0px 0px 0px #888888',
      'thumb_gal_title_align' => 'center',

      'page_nav_position' => 'bottom',
      'page_nav_align' => 'center',
      'page_nav_number' => 0,
      'page_nav_font_size' => 12,
      'page_nav_font_style' => 'segoe ui',
      'page_nav_font_color' => '666666',
      'page_nav_font_weight' => 'bold',
      'page_nav_border_width' => 1,
      'page_nav_border_style' => 'none',
      'page_nav_border_color' => 'E3E3E3',
      'page_nav_border_radius' => '0',
      'page_nav_margin' => '0',
      'page_nav_padding' => '3px 6px',
      'page_nav_button_bg_color' => 'FCFCFC',
      'page_nav_button_bg_transparent' => 100,
      'page_nav_box_shadow' => '0',
      'page_nav_button_transition' => 1,
      'page_nav_button_text' => 0,

      'lightbox_overlay_bg_color' => '000000',
      'lightbox_overlay_bg_transparent' => 70,
      'lightbox_bg_color' => '000000',
      'lightbox_ctrl_btn_pos' => 'bottom',
      'lightbox_ctrl_btn_align' => 'center',
      'lightbox_ctrl_btn_height' => 20,
      'lightbox_ctrl_btn_margin_top' => 10,
      'lightbox_ctrl_btn_margin_left' => 7,
      'lightbox_ctrl_btn_transparent' => 80,
      'lightbox_ctrl_btn_color' => 'FFFFFF',
      'lightbox_toggle_btn_height' => 14,
      'lightbox_toggle_btn_width' => 100,
      'lightbox_ctrl_cont_bg_color' => '000000',
      'lightbox_ctrl_cont_transparent' => 80,
      'lightbox_ctrl_cont_border_radius' => 4,
      'lightbox_close_btn_transparent' => 95,
      'lightbox_close_btn_bg_color' => '000000',
      'lightbox_close_btn_border_width' => 0,
      'lightbox_close_btn_border_radius' => '16px',
      'lightbox_close_btn_border_style' => 'none',
      'lightbox_close_btn_border_color' => 'FFFFFF',
      'lightbox_close_btn_box_shadow' => '',
      'lightbox_close_btn_color' => 'FFFFFF',
      'lightbox_close_btn_size' => 10,
      'lightbox_close_btn_width' => 20,
      'lightbox_close_btn_height' => 20,
      'lightbox_close_btn_top' => '-10',
      'lightbox_close_btn_right' => '-10',
      'lightbox_close_btn_full_color' => 'FFFFFF',
      'lightbox_rl_btn_bg_color' => '000000',
      'lightbox_rl_btn_border_radius' => '20px',
      'lightbox_rl_btn_border_width' => 2,
      'lightbox_rl_btn_border_style' => 'none',
      'lightbox_rl_btn_border_color' => 'FFFFFF',
      'lightbox_rl_btn_box_shadow' => '',
      'lightbox_rl_btn_color' => 'FFFFFF',
      'lightbox_rl_btn_height' => 40,
      'lightbox_rl_btn_width' => 40,
      'lightbox_rl_btn_size' => 20,
      'lightbox_close_rl_btn_hover_color' => 'FFFFFF',
      'lightbox_comment_pos' => 'left',
      'lightbox_comment_width' => 400,
      'lightbox_comment_bg_color' => '000000',
      'lightbox_comment_font_color' => 'CCCCCC',
      'lightbox_comment_font_style' => 'segoe ui',
      'lightbox_comment_font_size' => 12,
      'lightbox_comment_button_bg_color' => '333333',
      'lightbox_comment_button_border_color' => '666666',
      'lightbox_comment_button_border_width' => 1,
      'lightbox_comment_button_border_style' => 'none',
      'lightbox_comment_button_border_radius' => '3px',
      'lightbox_comment_button_padding' => '3px 10px',
      'lightbox_comment_input_bg_color' => '333333',
      'lightbox_comment_input_border_color' => '666666',
      'lightbox_comment_input_border_width' => 1,
      'lightbox_comment_input_border_style' => 'none',
      'lightbox_comment_input_border_radius' => '0',
      'lightbox_comment_input_padding' => '3px',
      'lightbox_comment_separator_width' => 1,
      'lightbox_comment_separator_style' => 'solid',
      'lightbox_comment_separator_color' => '2B2B2B',
      'lightbox_comment_author_font_size' => 14,
      'lightbox_comment_date_font_size' => 10,
      'lightbox_comment_body_font_size' => 12,
      'lightbox_comment_share_button_color' => 'FFFFFF',
      'lightbox_filmstrip_pos' => 'top',
      'lightbox_filmstrip_rl_bg_color' => '2B2B2B',
      'lightbox_filmstrip_rl_btn_size' => 20,
      'lightbox_filmstrip_rl_btn_color' => 'FFFFFF',
      'lightbox_filmstrip_thumb_margin' => '0 1px',
      'lightbox_filmstrip_thumb_border_width' => 1,
      'lightbox_filmstrip_thumb_border_style' => 'none',
      'lightbox_filmstrip_thumb_border_color' => '000000',
      'lightbox_filmstrip_thumb_border_radius' => '0',
      'lightbox_filmstrip_thumb_deactive_transparent' => 80,
      'lightbox_filmstrip_thumb_active_border_width' => 0,
      'lightbox_filmstrip_thumb_active_border_color' => 'FFFFFF',
      'lightbox_rl_btn_style' => 'fa-chevron',
      'lightbox_rl_btn_transparent' => 80,
      'lightbox_bg_transparent' => 100,

      'album_compact_back_font_color' => '000000',
      'album_compact_back_font_style' => 'segoe ui',
      'album_compact_back_font_size' => 14,
      'album_compact_back_font_weight' => 'normal',
      'album_compact_back_padding' => '0',
      'album_compact_title_font_color' => 'CCCCCC',
      'album_compact_title_font_style' => 'segoe ui',
      'album_compact_thumb_title_pos' => 'bottom',
      'album_compact_title_font_size' => 16,
      'album_compact_title_font_weight' => 'bold',
      'album_compact_title_margin' => '5px',
      'album_compact_title_shadow' => '',
      'album_compact_thumb_margin' => 4,
      'album_compact_thumb_padding' => 4,
      'album_compact_thumb_border_radius' => '0',
      'album_compact_thumb_border_width' => 1,
      'album_compact_thumb_border_style' => 'none',
      'album_compact_thumb_border_color' => '000000',
      'album_compact_thumb_bg_color' => 'E8E8E8',
      'album_compact_thumbs_bg_color' => 'FFFFFF',
      'album_compact_thumb_bg_transparent' => 100,
      'album_compact_thumb_box_shadow' => '',
      'album_compact_thumb_transparent' => 100,
      'album_compact_thumb_align' => 'center',
      'album_compact_thumb_hover_effect' => 'rotate',
      'album_compact_thumb_hover_effect_value' => '2deg',
      'album_compact_thumb_transition' => 1,
      'album_compact_gal_title_font_color' => 'CCCCCC',
      'album_compact_gal_title_font_style' => 'segoe ui',
      'album_compact_gal_title_font_size' => 16,
      'album_compact_gal_title_font_weight' => 'bold',
      'album_compact_gal_title_margin' => '2px',
      'album_compact_gal_title_shadow' => '0px 0px 0px #888888',
      'album_compact_gal_title_align' => 'center',

      'album_extended_thumb_margin' => 2,
      'album_extended_thumb_padding' => 4,
      'album_extended_thumb_border_radius' => '0',
      'album_extended_thumb_border_width' => 4,
      'album_extended_thumb_border_style' => 'none',
      'album_extended_thumb_border_color' => 'E8E8E8',
      'album_extended_thumb_bg_color' => 'E8E8E8',
      'album_extended_thumbs_bg_color' => 'FFFFFF',
      'album_extended_thumb_bg_transparent' => 100,
      'album_extended_thumb_box_shadow' => '',
      'album_extended_thumb_transparent' => 100,
      'album_extended_thumb_align' => 'left',
      'album_extended_thumb_hover_effect' => 'rotate',
      'album_extended_thumb_hover_effect_value' => '2deg',
      'album_extended_thumb_transition' => 0,
      'album_extended_back_font_color' => '000000',
      'album_extended_back_font_style' => 'segoe ui',
      'album_extended_back_font_size' => 16,
      'album_extended_back_font_weight' => 'bold',
      'album_extended_back_padding' => '0',
      'album_extended_div_bg_color' => 'FFFFFF',
      'album_extended_div_bg_transparent' => 0,
      'album_extended_div_border_radius' => '0',
      'album_extended_div_margin' => '0 0 5px 0',
      'album_extended_div_padding' => 10,
      'album_extended_div_separator_width' => 1,
      'album_extended_div_separator_style' => 'none',
      'album_extended_div_separator_color' => 'CCCCCC',
      'album_extended_thumb_div_bg_color' => 'FFFFFF',
      'album_extended_thumb_div_border_radius' => '0',
      'album_extended_thumb_div_border_width' => 0,
      'album_extended_thumb_div_border_style' => 'none',
      'album_extended_thumb_div_border_color' => 'CCCCCC',
      'album_extended_thumb_div_padding' => '0',
      'album_extended_text_div_bg_color' => 'FFFFFF',
      'album_extended_text_div_border_radius' => '0',
      'album_extended_text_div_border_width' => 1,
      'album_extended_text_div_border_style' => 'none',
      'album_extended_text_div_border_color' => 'CCCCCC',
      'album_extended_text_div_padding' => '5px',
      'album_extended_title_span_border_width' => 1,
      'album_extended_title_span_border_style' => 'none',
      'album_extended_title_span_border_color' => 'CCCCCC',
      'album_extended_title_font_color' => '000000',
      'album_extended_title_font_style' => 'segoe ui',
      'album_extended_title_font_size' => 16,
      'album_extended_title_font_weight' => 'bold',
      'album_extended_title_margin_bottom' => 2,
      'album_extended_title_padding' => '2px',
      'album_extended_desc_span_border_width' => 1,
      'album_extended_desc_span_border_style' => 'none',
      'album_extended_desc_span_border_color' => 'CCCCCC',
      'album_extended_desc_font_color' => '000000',
      'album_extended_desc_font_style' => 'segoe ui',
      'album_extended_desc_font_size' => 14,
      'album_extended_desc_font_weight' => 'normal',
      'album_extended_desc_padding' => '2px',
      'album_extended_desc_more_color' => 'FFC933',
      'album_extended_desc_more_size' => 12,
      'album_extended_gal_title_font_color' => 'CCCCCC',
      'album_extended_gal_title_font_style' => 'segoe ui',
      'album_extended_gal_title_font_size' => 16,
      'album_extended_gal_title_font_weight' => 'bold',
      'album_extended_gal_title_margin' => '2px',
      'album_extended_gal_title_shadow' => '0px 0px 0px #888888',
      'album_extended_gal_title_align' => 'center',

      'masonry_thumb_padding' => 4,
      'masonry_thumb_border_radius' => '2px',
      'masonry_thumb_border_width' => 1,
      'masonry_thumb_border_style' => 'none',
      'masonry_thumb_border_color' => 'CCCCCC',
      'masonry_thumbs_bg_color' => 'FFFFFF',
      'masonry_thumb_bg_transparent' => 0,
      'masonry_thumb_transparent' => 80,
      'masonry_thumb_align' => 'center',
      'masonry_thumb_hover_effect' => 'rotate',
      'masonry_thumb_hover_effect_value' => '2deg',
      'masonry_thumb_transition' => 0,
      'masonry_thumb_gal_title_font_color' => 'CCCCCC',
      'masonry_thumb_gal_title_font_style' => 'segoe ui',
      'masonry_thumb_gal_title_font_size' => 16,
      'masonry_thumb_gal_title_font_weight' => 'bold',
      'masonry_thumb_gal_title_margin' => '2px',
      'masonry_thumb_gal_title_shadow' => '0px 0px 0px #888888',
      'masonry_thumb_gal_title_align' => 'center',

      'slideshow_cont_bg_color' => '000000',
      'slideshow_close_btn_transparent' => 100,
      'slideshow_rl_btn_bg_color' => '000000',
      'slideshow_rl_btn_border_radius' => '20px',
      'slideshow_rl_btn_border_width' => 0,
      'slideshow_rl_btn_border_style' => 'none',
      'slideshow_rl_btn_border_color' => 'FFFFFF',
      'slideshow_rl_btn_box_shadow' => '',
      'slideshow_rl_btn_color' => 'FFFFFF',
      'slideshow_rl_btn_height' => 40,
      'slideshow_rl_btn_size' => 20,
      'slideshow_rl_btn_width' => 40,
      'slideshow_close_rl_btn_hover_color' => 'DBDBDB',
      'slideshow_filmstrip_pos' => 'bottom',
      'slideshow_filmstrip_thumb_border_width' => 1,
      'slideshow_filmstrip_thumb_border_style' => 'none',
      'slideshow_filmstrip_thumb_border_color' =>  '000000',
      'slideshow_filmstrip_thumb_border_radius' => '0',
      'slideshow_filmstrip_thumb_margin' =>  '0 1px',
      'slideshow_filmstrip_thumb_active_border_width' => 0,
      'slideshow_filmstrip_thumb_active_border_color' => 'FFFFFF',
      'slideshow_filmstrip_thumb_deactive_transparent' => 80,
      'slideshow_filmstrip_rl_bg_color' => '303030',
      'slideshow_filmstrip_rl_btn_color' => 'FFFFFF',
      'slideshow_filmstrip_rl_btn_size' => 20,
      'slideshow_title_font_size' => 16,
      'slideshow_title_font' => 'segoe ui',
      'slideshow_title_color' => 'FFFFFF',
      'slideshow_title_opacity' => 70,
      'slideshow_title_border_radius' => '5px',
      'slideshow_title_background_color' => '000000',
      'slideshow_title_padding' => '5px 10px 5px 10px',
      'slideshow_description_font_size' => 14,
      'slideshow_description_font' => 'segoe ui',
      'slideshow_description_color' => 'FFFFFF',
      'slideshow_description_opacity' => 70,
      'slideshow_description_border_radius' => '0',
      'slideshow_description_background_color' => '000000',
      'slideshow_description_padding' => '5px 10px 5px 10px',
      'slideshow_dots_width' => 10,
      'slideshow_dots_height' => 10,
      'slideshow_dots_border_radius' => '10px',
      'slideshow_dots_background_color' => '292929',
      'slideshow_dots_margin' => 1,
      'slideshow_dots_active_background_color' => '292929',
      'slideshow_dots_active_border_width' => 2,
      'slideshow_dots_active_border_color' => 'FFC933',
      'slideshow_play_pause_btn_size' => 60,
      'slideshow_rl_btn_style' => 'fa-chevron',

      'blog_style_margin' => '2px',
      'blog_style_padding' => '4px',
      'blog_style_border_radius' => '0',
      'blog_style_border_width' => 1,
      'blog_style_border_style' => 'none',
      'blog_style_border_color' => 'CCCCCC',
      'blog_style_bg_color' => 'E8E8E8',    
      'blog_style_transparent' => 70,
      'blog_style_box_shadow' => '',
      'blog_style_align' => 'center',
      'blog_style_share_buttons_margin' => '5px auto 10px auto',
      'blog_style_share_buttons_border_radius' => '0',
      'blog_style_share_buttons_border_width' => 0,
      'blog_style_share_buttons_border_style' => 'none',
      'blog_style_share_buttons_border_color' => '000000',
      'blog_style_share_buttons_bg_color' => 'FFFFFF',
      'blog_style_share_buttons_align' => 'right',
      'blog_style_img_font_size' => 16,
      'blog_style_img_font_family' => 'segoe ui',
      'blog_style_img_font_color' => '000000',
      'blog_style_share_buttons_color' => 'A1A1A1',
      'blog_style_share_buttons_bg_transparent' => 0,
      'blog_style_share_buttons_font_size' => 20,
      'blog_style_image_title_align' => 'top',
      'blog_style_gal_title_font_color' => 'CCCCCC',
      'blog_style_gal_title_font_style' => 'segoe ui',
      'blog_style_gal_title_font_size' => 16,
      'blog_style_gal_title_font_weight' => 'bold',
      'blog_style_gal_title_margin' => '2px',
      'blog_style_gal_title_shadow' => '0px 0px 0px #888888',
      'blog_style_gal_title_align' => 'center',

      'image_browser_margin' =>  '2px auto',
      'image_browser_padding' =>  '4px',
      'image_browser_border_radius'=>  '2px',
      'image_browser_border_width' =>  1,
      'image_browser_border_style' => 'none',
      'image_browser_border_color' => 'E8E8E8',
      'image_browser_bg_color' => 'E8E8E8',
      'image_browser_box_shadow' => '',
      'image_browser_transparent' => 80,
      'image_browser_align' => 'center',	
      'image_browser_image_description_margin' => '24px 0px 0px 0px',
      'image_browser_image_description_padding' => '8px 8px 8px 8px',
      'image_browser_image_description_border_radius' => '0',
      'image_browser_image_description_border_width' => 1,
      'image_browser_image_description_border_style' => 'none',
      'image_browser_image_description_border_color' => 'FFFFFF',
      'image_browser_image_description_bg_color' => 'E8E8E8',
      'image_browser_image_description_align' => 'center',	
      'image_browser_img_font_size' => 14,
      'image_browser_img_font_family' => 'segoe ui',
      'image_browser_img_font_color' => '000000',
      'image_browser_full_padding' => '4px',
      'image_browser_full_border_radius' => '0',
      'image_browser_full_border_width' => 1,
      'image_browser_full_border_style' => 'solid',
      'image_browser_full_border_color' => 'EDEDED',
      'image_browser_full_bg_color' => 'FFFFFF',
      'image_browser_full_transparent' => 90,
      'image_browser_image_title_align' => 'top',
      'image_browser_gal_title_font_color' => 'CCCCCC',
      'image_browser_gal_title_font_style' => 'segoe ui',
      'image_browser_gal_title_font_size' => 16,
      'image_browser_gal_title_font_weight' => 'bold',
      'image_browser_gal_title_margin' => '2px',
      'image_browser_gal_title_shadow' => '0px 0px 0px #888888',
      'image_browser_gal_title_align' => 'center',

      'lightbox_info_pos' => 'top',
      'lightbox_info_align' => 'right',
      'lightbox_info_bg_color' => '000000',
      'lightbox_info_bg_transparent' => 70,
      'lightbox_info_border_width' => 1,
      'lightbox_info_border_style' => 'none',
      'lightbox_info_border_color' => '000000',
      'lightbox_info_border_radius' => '5px',
      'lightbox_info_padding' => '5px',
      'lightbox_info_margin' => '15px',
      'lightbox_title_color' => 'FFFFFF',
      'lightbox_title_font_style' => 'segoe ui',
      'lightbox_title_font_weight' => 'bold',
      'lightbox_title_font_size' => 18,
      'lightbox_description_color' => 'FFFFFF',
      'lightbox_description_font_style' => 'segoe ui',
      'lightbox_description_font_weight' => 'normal',
      'lightbox_description_font_size' => 14,

      'lightbox_rate_pos' => 'bottom',
      'lightbox_rate_align' => 'right',
      'lightbox_rate_icon' => 'star',
      'lightbox_rate_color' => 'F9D062',
      'lightbox_rate_size' => 20,
      'lightbox_rate_stars_count' => 5,
      'lightbox_rate_padding' => '15px',
      'lightbox_rate_hover_color' => 'F7B50E',

      'lightbox_hit_pos' => 'bottom',
      'lightbox_hit_align' => 'left',
      'lightbox_hit_bg_color' => '000000',
      'lightbox_hit_bg_transparent' => 70,
      'lightbox_hit_border_width' => 1,
      'lightbox_hit_border_style' => 'none',
      'lightbox_hit_border_color' => '000000',
      'lightbox_hit_border_radius' => '5px',
      'lightbox_hit_padding' => '5px',
      'lightbox_hit_margin' => '0 5px',
      'lightbox_hit_color' => 'FFFFFF',
      'lightbox_hit_font_style' => 'segoe ui',
      'lightbox_hit_font_weight' => 'normal',
      'lightbox_hit_font_size' => 14,
      'masonry_description_font_size' => 12,
			'masonry_description_color' => 'CCCCCC',
			'masonry_description_font_style' => 'segoe ui',

			'album_masonry_back_font_color' => '000000',
      'album_masonry_back_font_style' => 'segoe ui',
      'album_masonry_back_font_size' => 14,
      'album_masonry_back_font_weight' => 'normal',
      'album_masonry_back_padding' => '0',
      'album_masonry_title_font_color' => 'CCCCCC',
      'album_masonry_title_font_style' => 'segoe ui',
      'album_masonry_thumb_title_pos' => 'bottom',
      'album_masonry_title_font_size' => 16,
      'album_masonry_title_font_weight' => 'bold',
      'album_masonry_title_margin' => '5px',
      'album_masonry_title_shadow' => '',
      'album_masonry_thumb_margin' => 4,
      'album_masonry_thumb_padding' => 4,
      'album_masonry_thumb_border_radius' => '0',
      'album_masonry_thumb_border_width' => 1,
      'album_masonry_thumb_border_style' => 'none',
      'album_masonry_thumb_border_color' => '000000',
      'album_masonry_thumb_bg_color' => 'E8E8E8',
      'album_masonry_thumbs_bg_color' => 'FFFFFF',
      'album_masonry_thumb_bg_transparent' => 100,
      'album_masonry_thumb_box_shadow' => '',
      'album_masonry_thumb_transparent' => 100,
      'album_masonry_thumb_align' => 'center',
      'album_masonry_thumb_hover_effect' => 'rotate',
      'album_masonry_thumb_hover_effect_value' => '2deg',
      'album_masonry_thumb_transition' => 1,
      'album_masonry_gal_title_font_color' => 'CCCCCC',
      'album_masonry_gal_title_font_style' => 'segoe ui',
      'album_masonry_gal_title_font_size' => 16,
      'album_masonry_gal_title_font_weight' => 'bold',
      'album_masonry_gal_title_margin' => '2px',
      'album_masonry_gal_title_shadow' => '0px 0px 0px #888888',
      'album_masonry_gal_title_align' => 'center',

      'mosaic_thumb_padding' => 4,
      'mosaic_thumb_border_radius' => '2px',
      'mosaic_thumb_border_width' => 1,
      'mosaic_thumb_border_style' => 'none',
      'mosaic_thumb_border_color' => 'CCCCCC',
      'mosaic_thumbs_bg_color' => 'FFFFFF',
      'mosaic_thumb_bg_transparent' => 0,
      'mosaic_thumb_transparent' => 80,
      'mosaic_thumb_align' => 'center',
      'mosaic_thumb_hover_effect' => 'rotate',
      'mosaic_thumb_hover_effect_value' => '2deg',
      'mosaic_thumb_title_font_color' => 'CCCCCC',
      'mosaic_thumb_title_font_style' => 'segoe ui',
      'mosaic_thumb_title_font_weight' => 'bold',
      'mosaic_thumb_title_margin' => '2px',
      'mosaic_thumb_title_shadow' => '0px 0px 0px #888888',
      'mosaic_thumb_title_font_size' => 16,
      'mosaic_thumb_gal_title_font_color' => 'CCCCCC',
      'mosaic_thumb_gal_title_font_style' => 'segoe ui',
      'mosaic_thumb_gal_title_font_size' => 16,
      'mosaic_thumb_gal_title_font_weight' => 'bold',
      'mosaic_thumb_gal_title_margin' => '2px',
      'mosaic_thumb_gal_title_shadow' => '0px 0px 0px #888888',
      'mosaic_thumb_gal_title_align' => 'center',
      
      'carousel_cont_bg_color' => '000000',
      'carousel_cont_btn_transparent' =>  0, 
      'carousel_close_btn_transparent' =>  100, 
      'carousel_rl_btn_bg_color' => '000000',
      'carousel_rl_btn_border_radius' => '20px',  
      'carousel_rl_btn_border_width' =>  0,
      'carousel_rl_btn_border_style' => 'none',
      'carousel_rl_btn_border_color' => 'FFFFFF',       
      'carousel_rl_btn_color' => 'FFFFFF',
      'carousel_rl_btn_height' => 40, 
      'carousel_rl_btn_size' => 20,
      'carousel_play_pause_btn_size' => 20,
      'carousel_rl_btn_width' => 40,
      'carousel_close_rl_btn_hover_color' => 'CCCCCC',
      'carousel_rl_btn_style' => 'fa-chevron',
      'carousel_mergin_bottom' => '0.5',    
      'carousel_font_family' => 'segoe ui',
      'carousel_feature_border_width' => 2,
      'carousel_feature_border_style' => 'solid',
      'carousel_feature_border_color' => '5D204F',       
      'carousel_caption_background_color' => '000000',
      'carousel_caption_bottom' => 0,
      'carousel_caption_p_mergin' => 0, 
      'carousel_caption_p_pedding' => 5,   
      'carousel_caption_p_font_weight' => 'bold', 
      'carousel_caption_p_font_size' => 14,
      'carousel_caption_p_color' => 'white',
      'carousel_title_opacity' => 100,
      'carousel_title_border_radius' => '5px',
      'mosaic_thumb_transition' => 1
    );
    $theme1 = json_encode($theme1);
    $theme2 = json_encode($theme2);
  if (!$exists_default) {
    $wpdb->insert($wpdb->prefix . 'bwg_theme', array(
      'id' => 1,
      'name' => 'Theme 1',
      'options' => $theme1,
      'default_theme' => 1
    ));

    $wpdb->insert($wpdb->prefix . 'bwg_theme', array(
      'id' => 2,
      'name' => 'Theme 2',
      'options' => $theme2,
      'default_theme' => 0
    ));
  }

  $version = get_option('wd_bwg_version');
  $new_version = '1.3.68';
  if ($version && version_compare($version, $new_version, '<')) {
    require_once WD_BWG_DIR . "/update/bwg_update.php";
    bwg_update($version);
    update_option("wd_bwg_version", $new_version);
    delete_user_meta(get_current_user_id(), 'bwg_photo_gallery');
  }
  elseif (!$version) {
    update_user_meta(get_current_user_id(),'bwg_photo_gallery', '1');
    add_option("wd_bwg_version", $new_version, '', 'no');
    add_option("wd_bwg_theme_version", '1.0.0', '', 'no');
  }
  else {
    add_option("wd_bwg_version", $new_version, '', 'no');
    add_option("wd_bwg_theme_version", '1.0.0', '', 'no');
  }
}

function bwg_global_activate($networkwide) {
  if (function_exists('is_multisite') && is_multisite()) {
    // Check if it is a network activation - if so, run the activation function for each blog id.
    if ($networkwide) {
      global $wpdb;
      // Get all blog ids.
      $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
      foreach ($blogids as $blog_id) {
        switch_to_blog($blog_id);
        bwg_activate();
        restore_current_blog();
      }
      return;
    }
  }
  bwg_activate();
}
register_activation_hook(__FILE__, 'bwg_global_activate');

function bwg_new_blog_added($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
  if (is_plugin_active_for_network('photo-gallery/photo-gallery.php')) {
    global $wpdb;
    switch_to_blog($blog_id);
    bwg_activate();
    restore_current_blog();
  }
}
add_action('wpmu_new_blog', 'bwg_new_blog_added', 10, 6);

/*there is no instagram provider for https*/
wp_oembed_add_provider( '#https://instagr(\.am|am\.com)/p/.*#i', 'https://api.instagram.com/oembed', true );

function bwg_update_hook() {
  $version = get_option('wd_bwg_version');
  $new_version = '1.3.68';
  if ($version && version_compare($version, $new_version, '<')) {
    require_once WD_BWG_DIR . "/update/bwg_update.php";
    bwg_update($version);
    update_option("wd_bwg_version", $new_version);
  }
}

function bwg_global_update() {
  if (function_exists('is_multisite') && is_multisite()) {
    global $wpdb;
    // Get all blog ids.
    $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    foreach ($blogids as $blog_id) {
      switch_to_blog($blog_id);
      bwg_update_hook();
      restore_current_blog();
    }
    return;
  }
  bwg_update_hook();
}

if (!isset($_GET['action']) || $_GET['action'] != 'deactivate') {
  add_action('admin_init', 'bwg_global_update');
}

// Plugin styles.
function bwg_styles() {
  wp_admin_css('thickbox');
  wp_enqueue_style('bwg_tables', WD_BWG_URL . '/css/bwg_tables.css', array(), wd_bwg_version());
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  $google_fonts = WDWLibrary::get_google_fonts();

  for ($i = 0; $i < count($google_fonts); $i = $i + 120) {
    $fonts = array_slice($google_fonts, $i, 120);
    $query = implode("|", str_replace(' ', '+', $fonts));
    $url = 'https://fonts.googleapis.com/css?family=' . $query . '&subset=greek,latin,greek-ext,vietnamese,cyrillic-ext,latin-ext,cyrillic';
    wp_enqueue_style('bwg_googlefonts_' . $i, $url, null, null);
  }
  wp_enqueue_style('bwg_deactivate-css',  WD_BWG_URL . '/wd/assets/css/deactivate_popup.css', array(), wd_bwg_version());
}

// Plugin scripts.
function bwg_scripts() {
  global $wd_bwg_options;
  wp_enqueue_script('thickbox');
  wp_enqueue_script('bwg_admin', WD_BWG_URL . '/js/bwg.js', array(), wd_bwg_version());
  wp_localize_script('bwg_admin', 'bwg_objectL10B', array(
    'bwg_field_required' => __('field is required.', 'bwg_back'),
    'bwg_select_image' => __('You must select an image file.', 'bwg_back'),
    'bwg_select_audio' => __('You must select an audio file.', 'bwg_back'),
    'bwg_client_id' => __('You do not have Instagram CLIENT_ID. Input its value in Options->Embed options. ', 'bwg_back'),
    'bwg_post_number' => __('Instagram recent post number must be between 1 and 33.', 'bwg_back'),
    'bwg_not_empty' => __('The gallery is not empty. Please delete all the images first.', 'bwg_back'),
    'bwg_enter_url' => __('Please enter url to embed.', 'bwg_back'),
    'bwg_cannot_response' => __('Error: cannot get response from the server.', 'bwg_back'),
    'bwg_something_wrong' => __('Error: something wrong happened at the server.', 'bwg_back'),
    'bwg_error' => __('Error', 'bwg_back'),
    'bwg_show_order' => __('Show order column', 'bwg_back'),
    'bwg_hide_order' => __('Hide order column', 'bwg_back'),
    'selected' => __('Selected', 'bwg_back'),
    'item' => __('item', 'bwg_back'),
    'saved' => __('Items Succesfully Saved.', 'bwg_back'),
    'recovered' => __('Item Succesfully Recovered.', 'bwg_back'),
    'published' => __('Item Succesfully Published.', 'bwg_back'),
    'unpublished' => __('Item Succesfully Unpublished.', 'bwg_back'),
    'deleted' => __('Item Succesfully Deleted.', 'bwg_back'),
    'one_item' => __('You must select at least one item.', 'bwg_back'),
    'resized' => __('Items Succesfully resized.', 'bwg_back'),
    'watermark_set' => __('Watermarks Succesfully Set.', 'bwg_back'),
    'reset' => __('Items Succesfully Reset.', 'bwg_back'),
    'save_tag' => __('Save Tag', 'bwg_back'),
    'delete_alert' => __('Do you want to delete selected items?', 'bwg_back'),
    'default_warning' => __('This action will reset gallery type to mixed and will save that choice. You cannot undo it.', 'bwg_back'),
    'change_warning' => __('After pressing save/apply buttons, you cannot change gallery type back to Instagram!', 'bwg_back'),
    'other_warning' => __('This action will reset gallery type to mixed and will save that choice. You cannot undo it.', 'bwg_back'),
    'insert' => __('Insert', 'bwg_back'),
    'import_failed' => __('Failed to import images from media library', 'bwg_back'),
    'wp_upload_dir' => wp_upload_dir(),
    'ajax_url' => wp_nonce_url( admin_url('admin-ajax.php'), 'bwg_UploadHandler', 'bwg_nonce' ),
    'uploads_url' => site_url() . '/' . $wd_bwg_options->images_directory . '/photo-gallery',
  ));

  global $wp_scripts;
  if (isset($wp_scripts->registered['jquery'])) {
    $jquery = $wp_scripts->registered['jquery'];
    if (!isset($jquery->ver) OR version_compare($jquery->ver, '1.8.2', '<')) {
      wp_deregister_script('jquery');
      wp_register_script('jquery', FALSE, array('jquery-core', 'jquery-migrate'), '1.10.2' );
    }
  }
  wp_enqueue_script('jquery');
  wp_enqueue_script('jquery-ui-sortable');
}

/* Add pagination to gallery admin pages.*/
function bwg_add_galleries_per_page_option(){
  $option = 'per_page';  
  $args_galleries = array(
    'label' => 'Items',
    'default' => 20,
    'option' => 'bwg_galleries_per_page'
  );
    add_screen_option( $option, $args_galleries ); 
}
function bwg_add_albums_per_page_option(){
  $option = 'per_page';  
  $args_albums = array(
    'label' => 'Items',
    'default' => 20,
    'option' => 'bwg_albums_per_page'
  );
    add_screen_option( $option, $args_albums ); 
}
function bwg_add_tags_per_page_option(){
  $option = 'per_page';  
  $args_tags = array(
    'label' => 'Tags',
    'default' => 20,
    'option' => 'bwg_tags_per_page'
  );
    add_screen_option( $option, $args_tags ); 
}
function bwg_add_themes_per_page_option(){
  $option = 'per_page';  
  $args_themes = array(
    'label' => 'Themes',
    'default' => 20,
    'option' => 'bwg_themes_per_page'
  );
    add_screen_option( $option, $args_themes ); 
}
function bwg_add_comments_per_page_option(){
  $option = 'per_page';  
  $args_comments = array(
    'label' => 'Comments',
    'default' => 20,
    'option' => 'bwg_comments_per_page'
  );
    add_screen_option( $option, $args_comments ); 
}
function bwg_add_rates_per_page_option(){
  $option = 'per_page';  
  $args_rates = array(
    'label' => 'Ratings',
    'default' => 20,
    'option' => 'bwg_rates_per_page'
  );
    add_screen_option( $option, $args_rates ); 
}

add_filter('set-screen-option', 'bwg_set_option_galleries', 10, 3);
add_filter('set-screen-option', 'bwg_set_option_albums', 10, 3);
add_filter('set-screen-option', 'bwg_set_option_tags', 10, 3);
add_filter('set-screen-option', 'bwg_set_option_themes', 10, 3);
add_filter('set-screen-option', 'bwg_set_option_comments', 10, 3);
add_filter('set-screen-option', 'bwg_set_option_rates', 10, 3);
 
function bwg_set_option_galleries($status, $option, $value) {
    if ( 'bwg_galleries_per_page' == $option ) return $value;
    return $status;
}
function bwg_set_option_albums($status, $option, $value) {
    if ( 'bwg_albums_per_page' == $option ) return $value;
    return $status;
}
function bwg_set_option_tags($status, $option, $value) {
    if ( 'bwg_tags_per_page' == $option ) return $value;
    return $status;
}
function bwg_set_option_themes($status, $option, $value) {
    if ( 'bwg_themes_per_page' == $option ) return $value;
    return $status;
}
function bwg_set_option_comments($status, $option, $value) {
    if ( 'bwg_comments_per_page' == $option ) return $value;
    return $status;
}
function bwg_set_option_rates($status, $option, $value) {
    if ( 'bwg_rates_per_page' == $option ) return $value;
    return $status;
}
function bwg_licensing_styles() {
  wp_enqueue_style('bwg_licensing', WD_BWG_URL . '/css/bwg_licensing.css');
}


function bwg_options_scripts() {
  wp_enqueue_script('thickbox');
  wp_enqueue_script('bwg_admin', WD_BWG_URL . '/js/bwg.js', array(), wd_bwg_version());
  global $wp_scripts;
  if (isset($wp_scripts->registered['jquery'])) {
    $jquery = $wp_scripts->registered['jquery'];
    if (!isset($jquery->ver) OR version_compare($jquery->ver, '1.8.2', '<')) {
      wp_deregister_script('jquery');
      wp_register_script('jquery', FALSE, array('jquery-core', 'jquery-migrate'), '1.10.2' );
    }
  }
  wp_enqueue_script('jquery');
  wp_enqueue_script('jscolor', WD_BWG_URL . '/js/jscolor/jscolor.js', array(), '1.3.9');
  wp_localize_script('bwg_admin', 'bwg_objectL10B', array(
    'bwg_field_required'  => __('field is required.', 'bwg_back'),
    'bwg_select_image'  => __('You must select an image file.', 'bwg_back'),
    'bwg_select_audio'  => __('You must select an audio file.', 'bwg_back'),
    'bwg_access_token'  => __('You do not have Instagram access token. Sign in with Instagram in Options->Social options. ', 'bwg_back'),
    'bwg_post_number'  => __('Instagram recent post number must be between 1 and 33.', 'bwg_back'),
    'bwg_not_empty'  => __('The gallery is not empty. Please delete all the images first.', 'bwg_back'),
    'bwg_enter_url'  => __('Please enter url to embed.', 'bwg_back'),
    'bwg_cannot_response'  => __('Error: cannot get response from the server.', 'bwg_back'),
    'bwg_something_wrong'  => __('Error: something wrong happened at the server.', 'bwg_back'),
    'bwg_error'  => __('Error', 'bwg_back'),
    'bwg_show_order'  => __('Show order column', 'bwg_back'),
    'bwg_hide_order'  => __('Hide order column', 'bwg_back'),
    'selected'  => __('Selected', 'bwg_back'),
    'item'  => __('item', 'bwg_back'),
    'saved'  => __('Items Succesfully Saved.', 'bwg_back'),
    'recovered'  => __('Item Succesfully Recovered.', 'bwg_back'),
    'published'  => __('Item Succesfully Published.', 'bwg_back'),
    'unpublished'  => __('Item Succesfully Unpublished.', 'bwg_back'),
    'deleted'  => __('Item Succesfully Deleted.', 'bwg_back'),
    'one_item'  => __('You must select at least one item.', 'bwg_back'),
    'resized'  => __('Items Succesfully resized.', 'bwg_back'),
    'watermark_set'  => __('Watermarks Succesfully Set.', 'bwg_back'),
    'reset'  => __('Items Succesfully Reset.', 'bwg_back'),
  ));
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  wp_localize_script('bwg_admin', 'bwg_objectGGF', WDWLibrary::get_google_fonts());

  wp_enqueue_script('bwg-deactivate-popup', WD_BWG_URL . '/wd/assets/js/deactivate_popup.js', array(), wd_bwg_version(), true );
  $admin_data = wp_get_current_user();

  wp_localize_script( 'bwg-deactivate-popup', 'bwgWDDeactivateVars', array(
    "prefix" => "bwg" ,
    "deactivate_class" => 'bwg_deactivate_link',
    "email" => $admin_data->data->user_email,
    "plugin_wd_url" => "https://web-dorado.com/products/wordpress-photo-gallery-plugin.html",
  ));
}

function bwg_front_end_scripts() {
  $version = wd_bwg_version();

  wp_register_script('bwg_frontend', WD_BWG_FRONT_URL . '/js/bwg_frontend.js', array('jquery'), $version);
  wp_register_style('bwg_frontend', WD_BWG_FRONT_URL . '/css/bwg_frontend.css', array(), $version);
  wp_register_script('bwg_sumoselect', WD_BWG_FRONT_URL . '/js/jquery.sumoselect.min.js', array('jquery'), '3.0.2');
  wp_register_style('bwg_sumoselect', WD_BWG_FRONT_URL . '/css/sumoselect.css', array(), '3.0.2');
  // Styles/Scripts for popup.
  wp_register_style('bwg_font-awesome', WD_BWG_FRONT_URL . '/css/font-awesome/font-awesome.css', array(), '4.6.3');
  wp_register_script('bwg_jquery_mobile', WD_BWG_FRONT_URL . '/js/jquery.mobile.js', array('jquery'), $version);
  wp_register_script('bwg_mCustomScrollbar', WD_BWG_FRONT_URL . '/js/jquery.mCustomScrollbar.concat.min.js', array('jquery'), $version);
  wp_register_style('bwg_mCustomScrollbar', WD_BWG_FRONT_URL . '/css/jquery.mCustomScrollbar.css', array(), $version);
  wp_register_script('jquery-fullscreen', WD_BWG_FRONT_URL . '/js/jquery.fullscreen-0.4.1.js', array('jquery'), '0.4.1');
  wp_register_script('bwg_gallery_box', WD_BWG_FRONT_URL . '/js/bwg_gallery_box.js', array('jquery'), $version);
  wp_localize_script('bwg_gallery_box', 'bwg_objectL10n', array(
    'bwg_field_required'  => __('field is required.', 'bwg'),
    'bwg_mail_validation' => __('This is not a valid email address.', 'bwg'),
    'bwg_search_result' => __('There are no images matching your search.', 'bwg'),
  ));
  wp_localize_script('bwg_sumoselect', 'bwg_objectsL10n', array(
    'bwg_select_tag'  => __('Select Tag', 'bwg'),
    'bwg_search' => __('Search', 'bwg'),
  ));

  // Google fonts.
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  $google_fonts = WDWLibrary::get_used_google_fonts();
  if (!empty($google_fonts)) {
    $query = implode("|", str_replace(' ', '+', $google_fonts));
    $url = 'https://fonts.googleapis.com/css?family=' . $query . '&subset=greek,latin,greek-ext,vietnamese,cyrillic-ext,latin-ext,cyrillic';
    wp_register_style('bwg_googlefonts', $url, null, null);
  }

  global $wd_bwg_options;
  if (!$wd_bwg_options->use_inline_stiles_and_scripts) {
    wp_enqueue_style('bwg_frontend');
    wp_enqueue_style('bwg_font-awesome');
    wp_enqueue_style('bwg_mCustomScrollbar');
    wp_enqueue_style('bwg_googlefonts');
    wp_enqueue_style('bwg_sumoselect');
    wp_enqueue_script('bwg_frontend');
    wp_enqueue_script('bwg_sumoselect');
    wp_enqueue_script('bwg_jquery_mobile');
    wp_enqueue_script('bwg_mCustomScrollbar');
    wp_enqueue_script('jquery-fullscreen');
    wp_enqueue_script('bwg_gallery_box');
  }
}
add_action('wp_enqueue_scripts', 'bwg_front_end_scripts');

// Languages localization.
function bwg_language_load() {
  load_plugin_textdomain('bwg', FALSE, basename(dirname(__FILE__)) . '/languages');
  load_plugin_textdomain('bwg_back', FALSE, basename(dirname(__FILE__)) . '/languages/backend');
}
add_action('init', 'bwg_language_load');

function bwg_create_post_type() {
  global $wpdb;
  global $wd_bwg_options;
  if (!isset($wd_bwg_options)) {
    $wd_bwg_options = new WD_BWG_Options();
  }

  if ($wd_bwg_options->show_hide_post_meta == 1) {
     $show_hide_post_meta = array('editor', 'comments', 'thumbnail', 'title');
  }
  else {
     $show_hide_post_meta = array('editor', 'thumbnail', 'title');
  }
  if ($wd_bwg_options->show_hide_custom_post == 0) {
     $show_hide_custom_post = false;
  }
  else {
     $show_hide_custom_post = true;
  }
  $args = array(
    'public' => TRUE,
    'exclude_from_search' => TRUE,
    'publicly_queryable' => TRUE,
    'show_ui' => $show_hide_custom_post,
    'show_in_menu' => TRUE,
    'show_in_nav_menus' => FALSE,
    'permalink_epmask' => TRUE,
    'rewrite' => TRUE,
    'label'  => __('Galleries', 'bwg_back'),
    'supports' => $show_hide_post_meta
  );
  register_post_type( 'bwg_gallery', $args );

  $args = array(
    'public' => TRUE,
    'exclude_from_search' => TRUE,
    'publicly_queryable' => TRUE,
    'show_ui' => $show_hide_custom_post,
    'show_in_menu' => TRUE,
    'show_in_nav_menus' => FALSE,
    'permalink_epmask' => TRUE,
    'rewrite' => TRUE,
    'label'  => __('Albums', 'bwg_back'),
    'supports' => $show_hide_post_meta
  );
  register_post_type( 'bwg_album', $args );

  $args = array(
    'public' => TRUE,
    'exclude_from_search' => TRUE,
    'publicly_queryable' => TRUE,
    'show_ui' => $show_hide_custom_post,
    'show_in_menu' => TRUE,
    'show_in_nav_menus' => FALSE,
    'permalink_epmask' => TRUE,
    'rewrite' => TRUE,
    'label'  => __('Gallery tags', 'bwg_back'),
    'supports' => $show_hide_post_meta
  );
  register_post_type( 'bwg_tag', $args );
}
add_action( 'init', 'bwg_create_post_type' );

function bwg_widget_tag_cloud_args($args) {
  if ($args['taxonomy'] == 'bwg_tag') {
    require_once WD_BWG_DIR . "/frontend/models/BWGModelWidget.php";
    $model = new BWGModelWidgetFrontEnd();
    $tags = $model->get_tags_data(0);
  }
  return $args;
}
add_filter('widget_tag_cloud_args', 'bwg_widget_tag_cloud_args');

// Captcha.
function bwg_captcha() {
  global $wd_bwg_options;
  if (isset($_GET['action']) && esc_html($_GET['action']) == 'bwg_captcha') {
    $i = (isset($_GET["i"]) ? esc_html($_GET["i"]) : '');
    $r2 = (isset($_GET["r2"]) ? (int) $_GET["r2"] : 0);
    $rrr = (isset($_GET["rrr"]) ? (int) $_GET["rrr"] : 0);
    $randNum = 0 + $r2 + $rrr;
    $digit = (isset($_GET["digit"]) ? (int) $_GET["digit"] : 0);
    $cap_width = $digit * 10 + 15;
    $cap_height = 26;
    $cap_length_min = $digit;
    $cap_length_max = $digit;
    $cap_digital = 1;
    $cap_latin_char = 1;
    function code_generic($_length, $_digital = 1, $_latin_char = 1) {
      $dig = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
      $lat = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
      $main = array();
      if ($_digital) {
        $main = array_merge($main, $dig);
      }
      if ($_latin_char) {
        $main = array_merge($main, $lat);
      }
      shuffle($main);
      $pass = substr(implode('', $main), 0, $_length);
      return $pass;
    }
    $l = rand($cap_length_min, $cap_length_max);
    $code = code_generic($l, $cap_digital, $cap_latin_char);
    @session_start();
    $_SESSION['bwg_captcha_code'] = $code;
    $canvas = imagecreatetruecolor($cap_width, $cap_height);
    $c = imagecolorallocate($canvas, rand(150, 255), rand(150, 255), rand(150, 255));
    imagefilledrectangle($canvas, 0, 0, $cap_width, $cap_height, $c);
    $count = strlen($code);
    $color_text = imagecolorallocate($canvas, 0, 0, 0);
    for ($it = 0; $it < $count; $it++) {
      $letter = $code[$it];
      imagestring($canvas, 6, (10 * $it + 10), $cap_height / 4, $letter, $color_text);
    }
    for ($c = 0; $c < 150; $c++) {
      $x = rand(0, $cap_width - 1);
      $y = rand(0, 29);
      $col = '0x' . rand(0, 9) . '0' . rand(0, 9) . '0' . rand(0, 9) . '0';
      imagesetpixel($canvas, $x, $y, $col);
    }
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
    header('Content-Type: image/jpeg');
    imagejpeg($canvas, NULL, $wd_bwg_options->jpeg_quality);
    die('');
  }
}

function wd_bwg_version() {
  $version = WD_BWG_VERSION;
  if ($version) {
    if (WD_BWG_PRO) {
      $version = substr_replace($version, '2', 0, 1);
    }
  }
  else {
    $version = '';
  }
  return $version;
}

function bwg_register_admin_scripts() {
  wp_register_script('bwg_shortcode', WD_BWG_URL . '/js/bwg_shortcode.js', FALSE, wd_bwg_version());
  require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
  wp_localize_script('bwg_shortcode', 'bwg_objectGGF', WDWLibrary::get_google_fonts());
}
add_action('admin_enqueue_scripts', 'bwg_register_admin_scripts');

function bwg_topic() {
  $page = isset($_GET['page']) ? $_GET['page'] : '';
  $user_guide_link = 'https://web-dorado.com/wordpress-gallery/';
  $support_forum_link = 'https://wordpress.org/support/plugin/photo-gallery';
  $pro_link = 'https://web-dorado.com/files/fromPhotoGallery.php';
  $pro_icon = WD_BWG_URL . '/images/wd_logo.png';
  $support_icon = WD_BWG_URL . '/images/support.png';
  $prefix = 'bwg_back';
  $is_free = TRUE;
  switch ($page) {
    case 'galleries_bwg': {
      $help_text = 'create, edit and delete galleries';
      $user_guide_link .= 'creating-editing-galleries.html';
      break;
    }
    case 'albums_bwg': {
      $help_text = 'create, edit and delete albums';
      $user_guide_link .= 'creating-editing-albums.html';
      break;
    }
    case 'tags_bwg': {
      $help_text = 'create, edit and delete tags';
      $user_guide_link .= 'creating-editing-tag.html';
      break;
    }
    case 'options_bwg': {
      $help_text = 'change settings for different views and general options';
      $user_guide_link .= 'global-options.html';
      break;
    }
    case 'themes_bwg': {
      $help_text = 'create, edit and delete themes';
      $user_guide_link .= 'thumbnails.html';
      break;
    }
    case 'comments_bwg': {
      $help_text = 'manage the image comments';
      $user_guide_link .= 'comments-editing.html';
      break;
    }
    case 'rates_bwg': {
      $help_text = 'manage the image ratings';
      $user_guide_link .= 'ratings-editing.html';
      break;
    }
    case 'licensing_bwg': {
      $help_text = '';
      $user_guide_link .= 'creating-editing-galleries.html';
      break;
    }
    default: {
      return '';
      break;
    }
  }
  ob_start();
  ?>
  <style>
    .wd_topic {
      background-color: #ffffff;
      border: none;
      box-sizing: border-box;
      clear: both;
      color: #6e7990;
      font-size: 14px;
      font-weight: bold;
      line-height: 44px;
      padding: 0 0 0 15px;
      vertical-align: middle;
      width: 98%;
    }
    .wd_topic .wd_help_topic {
      float: left;
    }
    .wd_topic .wd_help_topic a {
      color: #0073aa;
    }
    .wd_topic .wd_help_topic a:hover {
      color: #00A0D2;
    }
    .wd_topic .wd_support {
      float: right;
      margin: 0 10px;
    }
    .wd_topic .wd_support img {
      vertical-align: middle;
    }
    .wd_topic .wd_support a {
      text-decoration: none;
      color: #6E7990;
    }
    .wd_topic .wd_pro {
      float: right;
      padding: 0;
    }
    .wd_topic .wd_pro a {
      border: none;
      box-shadow: none !important;
      text-decoration: none;
    }
    .wd_topic .wd_pro img {
      border: none;
      display: inline-block;
      vertical-align: middle;
    }
    .wd_topic .wd_pro a,
    .wd_topic .wd_pro a:active,
    .wd_topic .wd_pro a:visited,
    .wd_topic .wd_pro a:hover {
      background-color: #D8D8D8;
      color: #175c8b;
      display: inline-block;
      font-size: 11px;
      font-weight: bold;
      padding: 0 10px;
      vertical-align: middle;
    }
  </style>
  <div class="update-nag wd_topic">
    <?php
    if ($help_text) {
      ?>
      <span class="wd_help_topic">
      <?php echo sprintf(__('This section allows you to %s.', $prefix), $help_text); ?>
        <a target="_blank" href="<?php echo $user_guide_link; ?>">
        <?php _e('Read More in User Manual', $prefix); ?>
      </a>
    </span>
      <?php
    }
    if ($is_free) {
      $text = strtoupper(__('Upgrade to paid version', $prefix));
      ?>
      <div class="wd_pro">
        <a target="_blank" href="<?php echo $pro_link; ?>">
          <img alt="web-dorado.com" title="<?php echo $text; ?>" src="<?php echo $pro_icon; ?>" />
          <span><?php echo $text; ?></span>
        </a>
      </div>
      <?php
    }
    if (TRUE) {
      ?>
      <span class="wd_support">
      <a target="_blank" href="<?php echo $support_forum_link; ?>">
        <img src="<?php echo $support_icon; ?>" />
        <?php _e('Support Forum', $prefix); ?>
      </a>
    </span>
      <?php
    }
    ?>
  </div>
  <?php
  echo ob_get_clean();
}
add_action('admin_notices', 'bwg_topic', 11);

function bwg_overview() {
  if (is_admin() && !isset($_REQUEST['ajax'])) {
    if (!class_exists("DoradoWeb")) {
      require_once(WD_BWG_DIR . '/wd/start.php');
    }
    global $bwg_options;
    $bwg_options = array(
      "prefix" => "bwg",
      "wd_plugin_id" => 55,
      "plugin_title" => "Photo Gallery",
      "plugin_wordpress_slug" => "photo-gallery",
      "plugin_dir" => WD_BWG_DIR,
      "plugin_main_file" => __FILE__,
      "description" => __('Photo Gallery is a fully responsive gallery plugin with advanced functionality.  It allows having different image galleries for your posts and pages. You can create unlimited number of galleries, combine them into albums, and provide descriptions and tags.', 'bwg'),
      // from web-dorado.com
      "plugin_features" => array(
        0 => array(
          "title" => __("Easy Set-up and Management", "bwg"),
          "description" => __("Create stunning, 100% responsive, SEO-friendly photo galleries in minutes. Use the File Manager with single-step and easy-to-manage functionality to rename, upload, copy, add and remove images and image directories. Otherwise use WordPress built in media uploader.", "bwg"),
        ),
        1 => array(
          "title" => __("Unlimited Photos and Albums", "bwg"),
          "description" => __("The plugin allows creating unlimited number of galleries or albums and upload images in each gallery as many as you wish. Add single/ multiple galleries into your pages and posts with the help of functional shortcode; visual shortcodes for an easier management.", "bwg"),
        ),
        2 => array(
          "title" => __("Customizable", "bwg"),
          "description" => __("The gallery plugin is easily customizable. You can edit themes changing sizes and colors for different features. Specify the number of images to display in a single row in an album. Additionally, you can customize thumbnail images by cropping, flipping and rotating them.", "bwg"),
        ),
        3 => array(
          "title" => __("10 View Options", "bwg"),
          "description" => __("Photo Gallery plugin allows displaying galleries and albums in 10 elegant and beautiful views:, Thumbnails, Masonry, Mosaic, Slideshow, Image Browser, Masonry Album, Compact Album, Extended Album, Blog Style Gallery, Ecommerce.", "bwg"),
        ),
        4 => array(
          "title" => __("Audio and Video Support", "bwg"),
          "description" => __("You can include both videos and images within a single gallery. WordPress Photo Gallery Plugin supports YouTube and Vimeo videos within Galleries. It’s also possible to add audio tracks for the image slideshow.", "bwg"),
        )
      ),
      // user guide from web-dorado.com
      "user_guide" => array(
        0 => array(
          "main_title" => __("Installing", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/installing.html",
          "titles" => array()
        ),
        1 => array(
          "main_title" => __("Creating/Editing Galleries", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/creating-editing-galleries.html",
          "titles" => array(
            array(
              "title" => __("Instagram Gallery", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/creating-editing-galleries/instagram-gallery.html",
            ),
          )
        ),
        2 => array(
          "main_title" => __("Creating/Editing Tags", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/creating-editing-tag.html",
          "titles" => array()
        ),
        3 => array(
          "main_title" => __("Creating/Editing Albums", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/creating-editing-albums.html",
          "titles" => array()
        ),
        4 => array(
          "main_title" => __("Editing Options", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/editing-options.html",
          "titles" => array(
            array(
              "title" => __("Global Options", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/global-options.html",
            ),
            array(
              "title" => __("Watermark", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/watermark.html",
            ),
            array(
              "title" => __("Advertisement", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/advertisement.html",
            ),
            array(
              "title" => __("Lightbox", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/lightbox.html",
            ),
            array(
              "title" => __("Album Options", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/album-options.html",
            ),
            array(
              "title" => __("Slideshow", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/slideshow.html",
            ),
            array(
              "title" => __("Thumbnail Options", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/thumbnail-options.html",
            ),
            array(
              "title" => __("Image Options", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/image-options.html",
            ),
            array(
              "title" => __("Social Options", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/social-options.html",
            ),
            array(
              "title" => __("Carousel Options", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-options/carousel-options.html",
            ),
          )
        ),
        5 => array(
          "main_title" => __("Creating/Editing Themes", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/editing-themes.html",
          "titles" => array(
            array(
              "title" => __("Thumbnails", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/thumbnails.html",
            ),
            array(
              "title" => __("Masonry", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/masonry.html",
            ),
            array(
              "title" => __("Mosaic", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/mosaic.html",
            ),
            array(
              "title" => __("Slideshow", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/slideshow.html",
            ),
            array(
              "title" => __("Image Browser", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/image-browser.html",
            ),
            array(
              "title" => __("Compact Album", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/compact-album.html",
            ),
            array(
              "title" => __("Masonry Album", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/masonry-album.html",
            ),
            array(
              "title" => __("Extended Album", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/extended-album.html",
            ),
            array(
              "title" => __("Blog Style", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/blog-style.html",
            ),
            array(
              "title" => __("Lightbox", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/lightbox.html",
            ),
            array(
              "title" => __("Page Navigation", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/page-navigation.html",
            ),
            array(
              "title" => __("Carousel", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/editing-themes/carousel.html",
            ),
          )
        ),
        6 => array(
          "main_title" => __("Generating Shortcode", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/shortcode-generating.html",
          "titles" => array()
        ),
        7 => array(
          "main_title" => __("Editing Comments", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/comments-editing.html",
          "titles" => array()
        ),
        8 => array(
          "main_title" => __("Editing Ratings", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/ratings-editing.html",
          "titles" => array()
        ),
        9 => array(
          "main_title" => __("Publishing the Created Photo Gallery", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/publishing-gallery.html",
          "titles" => array(
            array(
              "title" => __("General Parameters", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/publishing-gallery/general-parameters.html",
            ),
            array(
              "title" => __("Lightbox Parameters", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/publishing-gallery/lightbox-parameters.html",
            ),
            array(
              "title" => __("Advertisement", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/publishing-gallery/advertisement.html",
            ),
          )
        ),
        10 => array(
          "main_title" => __("Publishing Photo Gallery Widgets", "bwg"),
          "url" => "https://web-dorado.com/wordpress-gallery/publishing-gallery-widgets.html",
          "titles" => array(
            array(
              "title" => __("Tag Cloud", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/publishing-gallery-widgets/tag-cloud.html",
            ),
            array(
              "title" => __("Photo Gallery Tags Cloud", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/publishing-gallery-widgets/gallery-tags-cloud.html",
            ),
            array(
              "title" => __("Photo Gallery Slideshow", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/publishing-gallery-widgets/gallery-slideshow.html",
            ),
            array(
              "title" => __("Photo Gallery Widget", "bwg"),
              "url" => "https://web-dorado.com/wordpress-gallery/publishing-gallery-widgets/gallery-widget.html",
            ),
          )
        ),
      ),
      "video_youtube_id" => "4Mxg0FsFZZE",  // e.g. https://www.youtube.com/watch?v=acaexefeP7o youtube id is the acaexefeP7o
      "plugin_wd_url" => "https://web-dorado.com/products/wordpress-photo-gallery-plugin.html",
      "plugin_wd_demo_link" => "http://wpdemo.web-dorado.com/gallery/",
      "plugin_wd_addons_link" => "https://web-dorado.com/products/wordpress-photo-gallery-plugin/add-ons.html",
      "after_subscribe" => admin_url('admin.php?page=overview_bwg'), // this can be plagin overview page or set up page
      "plugin_wizard_link" => '',
      "plugin_menu_title" => "Photo Gallery",
      "plugin_menu_icon" => WD_BWG_URL . '/images/icons/best-wordpress-gallery.png',
      "deactivate" => true,
      "subscribe" => true,
      "custom_post" => 'galleries_bwg',
      "menu_position" => null,
    );

    dorado_web_init($bwg_options);
  }
}
add_action('init', 'bwg_overview', 9);

/**
 * Show notice to install Image Optimization plugin
 */
function wdpg_io_install_notice() {
  // Show notice only on plugin pages.
  if ( !isset($_GET['page']) || strpos(esc_html($_GET['page']), '_bwg') === FALSE ) {
    return '';
  }

  wp_enqueue_script('thickbox');
  wp_enqueue_script('bwg_admin', WD_BWG_URL . '/js/bwg.js', array(), wd_bwg_version());

  // Remove old notice.
  if ( get_option('wds_io_notice_status') !== FALSE ) {
    update_option('wds_io_notice_status', '1', 'no');
  }

  $meta_value = get_option('wds_io_notice_status');
  if ( $meta_value === '' || $meta_value === FALSE ) {
    ob_start();
    $prefix = WD_BWG_PREFIX;
    $nicename = WD_BWG_NICENAME;
    $url = WD_BWG_URL;
    $dismiss_url = add_query_arg(array( 'action' => 'wd_io_dismiss' ), admin_url('admin-ajax.php'));

    $slug = 'image-optimizer-wd';
    $install_url = esc_url(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $slug), 'install-plugin_' . $slug));
    $activation_url = na_action_link($slug.'/io-wd.php', 'activate');
    $verify_url = add_query_arg( array ('action' => 'io_status'), admin_url('admin-ajax.php'));
    ?>
    <div class="notice notice-info" id="wd_io_notice_cont">
      <p>
        <img id="wd_io_logo_notice" src="<?php echo $url . '/images/iopLogo.png'; ?>" />
        <?php echo sprintf(__("%s advises: Install brand new %s plugin to optimize your website images quickly and easily.", $prefix), $nicename, '<a href="https://wordpress.org/plugins/image-optimizer-wd/" title="' . __("More details", $prefix) . '" target="_blank">' .  __("Image Optimizer WD", $prefix) . '</a>'); ?>
        <?php
        $plugin_dir = ABSPATH . 'wp-content/plugins/image-optimizer-wd/';
        if ( is_dir($plugin_dir) && !is_plugin_active( 'image-optimizer-wd/io-wd.php' ) ) {
          ?>
          <a class="button button-primary io_activaion" id="activate_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>"><?php _e("Activate", $prefix); ?></a>
          <a class="button button-primary io_activaion hide" id="optimize_now" href="<?php echo add_query_arg(array( 'page' => 'iowd_settings', 'target' => 'wd_gallery'), admin_url('admin.php'));?>" target="_blank"><?php _e("Optimize now", $prefix); ?></a>
          <span class="error_activate hide"><?php _e("Activation failed, please try again.", $prefix); ?></span>
          <?php
        } else if( ! is_dir($plugin_dir) ) {
          ?>
          <a class="button button-primary io_activaion" id="install_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>"><?php _e("Install", $prefix); ?></a>
          <a class="button button-primary io_activaion hide" id="activate_now" data-install-url="<?php echo $install_url; ?>" data-activate-url="<?php echo $activation_url; ?>"><?php _e("Activation", $prefix); ?></a>
          <a class="button button-primary io_activaion hide" id="optimize_now" href="<?php echo add_query_arg(array( 'page' => 'iowd_settings', 'target' => 'wd_gallery'), admin_url('admin.php'));?>" target="_blank"><?php _e("Optimize now", $prefix); ?></a>
          <span class="error_install hide is_active"><?php _e("Installation failed, please try again.", $prefix); ?></span>
          <?php
        }
        ?>
        <span class="spinner" id="loading"></span>
      </p>
      <button type="button" class="wd_io_notice_dissmiss notice-dismiss" onclick="jQuery('#wd_io_notice_cont').hide(); jQuery.post('<?php echo $dismiss_url; ?>');"><span class="screen-reader-text"></span></button>
      <div id="verifyUrl" data-url="<?php echo $verify_url ?>"></div>
    </div>
    <script>
      var url = jQuery(".io_activaion").attr("data-install-url");
      var activate_url = jQuery(".io_activaion").attr("data-activate-url");

      function install_io_plugin() {
        jQuery("#loading").addClass('is-active');
        jQuery(this).prop('disable',true);
        var io_plugin_url = '<?php echo plugins_url('image-optimizer-wd/io-wd.php');?>'; // Getting image optimizer plugin url

        jQuery.ajax({
          method: "POST",
          url: url,
        }).done(function() {
          jQuery.ajax({ // Check if plugin installed
            type: 'POST',
            url: jQuery("#verifyUrl").attr('data-url'),
            error: function()
            {
              jQuery("#loading").removeClass('is-active');
              jQuery(".error_install").show();
            },
            success: function(response)
            {
              var plStatus = JSON.parse(response);
              if(plStatus.status_install != 1) {
                jQuery('#install_now').addClass('hide');
                jQuery('#activate_now').removeClass('hide');
                activate_io_plugin();
              }
              else {
                jQuery("#loading").removeClass('is-active');
                jQuery(".error_install").removeClass('hide');
              }
            }
          });
        })
            .fail(function() {
              //window.location = window.location.href;
              jQuery("#loading").removeClass('is-active');
              jQuery(".error_install").removeClass('hide');
            });

      }

      function activate_io_plugin() {
        jQuery("#loading").addClass('is-active');
        jQuery.ajax({
          method: "POST",
          url: activate_url,
        }).done(function() {
          jQuery("#loading").removeClass('is-active');

          jQuery.ajax({ // Check if plugin installed
            type: 'POST',
            url: jQuery("#verifyUrl").attr('data-url'),
            error: function()
            {
              jQuery("#loading").removeClass('is-active');
              jQuery(".error_activate").removeClass('hide');
            },
            success: function(response)
            {
              var plStatus = JSON.parse(response);
              if(plStatus.status_active == 1) {
                jQuery('#install_now').addClass('hide');
                jQuery('#activate_now').addClass('hide');
                jQuery('#optimize_now').removeClass('hide');
                jQuery.post('<?php echo $dismiss_url; ?>');

              }
              else {
                jQuery("#loading").removeClass('is-active');
                jQuery(".error_activate").removeClass('hide');
              }
            }
          });

        })
            .fail(function() {
              //window.location = window.location.href;
              jQuery("#loading").removeClass('is-active');
            });
      }

      jQuery("#install_now").on("click",function(){
        install_io_plugin();
      })
      jQuery("#activate_now").on("click",function(){
        activate_io_plugin()
      })


    </script>
    <style>
      @media only screen and (max-width: 500px) {
        body #wd_backup_logo {
          max-width: 100%;
        }
        body #wd_io_notice_cont p {
          padding-right: 25px !important;
        }
      }
      .hide {
        display: none!important;
      }
      #verifyUrl{
        display: none
      }

      #loading {
        vertical-align: middle;
        float: none!important;
        margin: 0 !important;
      }
      #wd_io_logo_notice {
        height: 32px;
        float: left;
        margin-right: 10px;
      }
      .error_install, .error_activate {
        color:red;
      }
      #wd_io_notice_cont {
        position: relative;
      }
      #wd_io_notice_cont a {
        margin: 0 5px;
      }
      #wd_io_notice_cont .dashicons-dismiss:before {
        content: "\f153";
        background: 0 0;
        color: #72777c;
        display: block;
        font: 400 16px/20px dashicons;
        speak: none;
        height: 20px;
        text-align: center;
        width: 20px;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
      }
      .wd_io_notice_dissmiss {
        margin-top: 5px;
      }
    </style>
    <?php
    echo ob_get_clean();
  }
}

if( !function_exists('is_plugin_active') ) {
  include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if( !is_plugin_active( 'image-optimizer-wd/io-wd.php' ) ) {
  add_action('admin_notices', 'wdpg_io_install_notice');
}

if ( !function_exists('wd_iops_install_notice_status') ) {
  // Add usermeta to db.
  function wd_iops_install_notice_status() {
    update_option('wds_io_notice_status', '1', 'no');
  }
  add_action('wp_ajax_wd_io_dismiss', 'wd_iops_install_notice_status');
}

//Check status image optimize install
function check_io_status(){
  $status_install = 0;
  $status_active = 0;
  $plugin_dir = ABSPATH . 'wp-content/plugins/image-optimizer-wd/';
  if ( !is_dir($plugin_dir)){
    $status_install = 1;
  }else if(is_plugin_active( 'image-optimizer-wd/io-wd.php' )) {
    $status_active = 1;
  }
  $jsondata = array('status_install' => $status_install, 'status_active' => $status_active);
  echo json_encode($jsondata); exit;
}
add_action('wp_ajax_io_status', 'check_io_status');


/**
 * Get activation or deactivation link of a plugin
 *
 * @author Nazmul Ahsan <mail@nazmulahsan.me>
 * @param string $plugin plugin file name
 * @param string $action action to perform. activate or deactivate
 * @return string $url action url
 */
function na_action_link( $plugin, $action = 'activate' ) {
  if ( strpos( $plugin, '/' ) ) {
    $plugin = str_replace( '\/', '%2F', $plugin );
  }
  $url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
  $_REQUEST['plugin'] = $plugin;
  $url = wp_nonce_url( $url, $action . '-plugin_' . $plugin );
  return $url;
}

function bwg_add_plugin_meta_links($meta_fields, $file) {
  if ( plugin_basename(__FILE__) == $file ) {
    $plugin_url = "https://wordpress.org/support/plugin/photo-gallery";
    $prefix = 'bwg';
    $meta_fields[] = "<a href='" . $plugin_url . "' target='_blank'>" . __('Support Forum', $prefix) . "</a>";
    $meta_fields[] = "<a href='" . $plugin_url . "/reviews#new-post' target='_blank' title='" . __('Rate', $prefix) . "'>
            <i class='wdi-rate-stars'>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
      . "</i></a>";

    $stars_color = "#ffb900";

    echo "<style>"
      . ".wdi-rate-stars{display:inline-block;color:" . $stars_color . ";position:relative;top:3px;}"
      . ".wdi-rate-stars svg{fill:" . $stars_color . ";}"
      . ".wdi-rate-stars svg:hover{fill:" . $stars_color . "}"
      . ".wdi-rate-stars svg:hover ~ svg{fill:none;}"
      . "</style>";
  }

  return $meta_fields;
}
add_filter("plugin_row_meta", 'bwg_add_plugin_meta_links', 10, 2);
