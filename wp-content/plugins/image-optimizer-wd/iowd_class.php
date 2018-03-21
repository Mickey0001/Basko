<?php

if (!defined('ABSPATH')) {
    exit;
}

class IOWD
{

    protected static $instance = null;
    private static $version = '1.0.10';
    private static $page;
    private $reg_autoloader = false;
    private $options = array();

    private function __construct()
    {

        self::$page = isset($_GET["page"]) ? $_GET["page"] : 'iowd';
        $this->options = json_decode(get_option(IOWD_PREFIX . "_options"), true);

        add_action('admin_init', array('IOWD_Settings', 'save_settings'));
        // add_action('admin_post_nopriv_save_settings', array('IOWD_Settings', 'save_settings'));

        // ajax
        add_action('wp_ajax_choose_dirs', array($this, 'ajax'));
        add_action('wp_ajax_optimize', array($this, 'ajax'));
        add_action('wp_ajax_get_attachment_data', array($this, 'ajax'));
        add_action('wp_ajax_finish_bulk', array($this, 'ajax'));
        add_action('wp_ajax_abort', array($this, 'ajax'));

        add_action('wp_ajax_filter_report', array($this, 'ajax'));
        add_action('wp_ajax_get_stats', array($this, 'ajax'));
        add_action('wp_ajax_clear_report', array($this, 'ajax'));
        add_action('wp_ajax_quick_settings', array($this, 'ajax'));
        add_action('wp_ajax_update_already_used', array($this, 'ajax'));

        add_action('admin_notices', array($this, 'notice'));

        add_action('wp_ajax_get_subdirs', array($this, 'ajax'));
        add_action('wp_ajax_scan', array($this, 'ajax'));
        add_action('wp_ajax_scan_all', array($this, 'ajax'));

        // autoloder
        add_action('init', array($this, 'register_autoloader'));

        require_once IOWD_DIR_INCLUDES . "/iowd-helper.php";
        $limitation = IOWD_Helper::limitation();
        if ($limitation["already_optimized"] < $limitation["limit"]) {
            add_filter('wp_update_attachment_metadata', array($this, 'auto_optimize'), 15, 2);
        }

        add_action('admin_init', array($this, 'actions'));

        // Add menu
        add_action('admin_menu', array($this, 'admin_menu'), 9);

        // Add admin styles and scripts
        add_action('admin_enqueue_scripts', array($this, 'styles'));
        add_action('admin_enqueue_scripts', array($this, 'scripts'));

        add_action('admin_init', array($this, 'includes'));

        if (!get_option(IOWD_PREFIX . "_options")) {
            self::add_options_to_db(true);
        }

        if (isset($_GET["page"]) && $_GET["page"] == "overview_iowd") {
            set_transient(IOWD_PREFIX . '_overview_visited', 1, '', 'no');
        }

        // add meta fields
        add_filter("plugin_row_meta", array($this, 'meta_links'), 10, 2);

    }
    public function meta_links($meta_fields, $file)
    {
        if (IOWD_MAIN_FILE == $file) {
            $plugin_url = "https://wordpress.org/support/plugin/image-optimizer-wd";
            $prefix = 'iowd';
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

    public static function activate()
    {
        //delete_transient('iowd_update_check');
        require_once IOWD_DIR_INCLUDES . "/iowd-helper.php";
        IOWD_Helper::update_already_used();
        $version = get_option(IOWD_PREFIX . "_version");
        if (get_option(IOWD_PREFIX . "_pro")) {
            update_option(IOWD_PREFIX . "_pro", "yes");
        } else {
            add_option(IOWD_PREFIX . "_pro", "yes", '', 'no');
        }

        require_once IOWD_DIR_CLASSES . "/iowddb.php";
        $db = new IOWDDB();
        if ($version && version_compare($version, self::$version, '<')) {
            update_option(IOWD_PREFIX . "_version", self::$version);
            $db->update();

        } else {
            add_option(IOWD_PREFIX . "_version", self::$version, '', 'no');

            if (!get_option(IOWD_PREFIX . "_mode")) {
                add_option(IOWD_PREFIX . '_mode', "standart", '', 'no');
            }

            if (!get_option(IOWD_PREFIX . "_standart_setting")) {
                add_option(IOWD_PREFIX . '_standart_setting', "conservative", '', 'no');
            }
            set_transient(IOWD_PREFIX . '_overview_visited', 1, '', 'no');
            self::add_options_to_db();
            $db->create_iowd_images_table();
        }

    }

    private static function add_options_to_db($delete = false)
    {
        if ($delete == true) {
            delete_option(IOWD_PREFIX . "_options");
        }
        require_once IOWD_DIR_INCLUDES . "/iowd-helper.php";
        $sizes = IOWD_Helper::wp_get_image_sizes();
        $sizes = is_array($sizes) && empty($sizes) === false ? implode(",", array_keys($sizes)) : "";

        $options = array(
            "api_key"                            => "",
            "automatically_optimize"             => "0",
            "keep_exif_data"                     => "1",
            "keep_originals"                     => "0",
            "jpg_optimization_levels"            => "lossy40",
            "png_optimization_levels"            => "lossy40",
            "gif_optimization_levels"            => "lossless",
            "pdf_optimization_levels"            => "0",
            "scheduled_optimization"             => "0",
            "scheduled_optimization_recurrence"  => "",
            "other_folders"                      => "",
            "resize_media_images_width"          => "",
            "resize_media_images_height"         => "",
            "enable_resizing"                    => "",
            "enable_resizing_other"              => "",
            "resize_other_images_width"          => "",
            "resize_other_images_height"         => "",
            "optimize_thumbs"                    => $sizes,
            "skip_small_images"                  => "",
            "skip_large_images"                  => "",
            "exclude_full_size"                  => "1",
            "exclude_full_size_metadata_removal" => "0",
            "optimize_once_more"                 => "1",
            "enable_conversion"                  => "0",
            "jpg_to_png"                         => "0",
            "png_to_jpg"                         => "0",
            "gif_to_png"                         => "0",
            "jpg_to_webp"                        => "0",
            "png_to_webp"                        => "0",
            "optimize_gallery"                   => "0",
        );
        add_option(IOWD_PREFIX . "_options", json_encode($options), '', 'no');
    }

    public static function deactivate()
    {
        wp_clear_scheduled_hook('iowd_optimize');
    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public static function autoload($class)
    {
        $class = str_replace("_", "-", strtolower($class));
        $include_file = IOWD_DIR_INCLUDES . "/" . $class . ".php";
        if (file_exists($include_file)) {
            include_once $include_file;
        }
    }

    public static function autoloadProxy($class)
    {
        __autoload($class);
    }

    // Return an instance of this class.

    public function notice()
    {
        if ($this->is_iowd_page()) {
            $limitation = IOWD_Helper::limitation();
            $whitelist = array(
                '127.0.0.1',
                '::1'
            );
            if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
                echo "<div class='error'><p>" . __("Image optimizing is disabled on Localhost. Please install the plugin on a live server to optimize images.", IOWD_PREFIX) . "</p></div>";
            }
            if ($limitation["already_optimized"] >= $limitation["limit"]) {
                echo "<div class='error'><p>" . sprintf(__("Your subscription plan allows optimizing %s images per month. This limitation has expired for current month.", IOWD_PREFIX), $limitation["limit"]) . "</p></div>";
            }
            if (!class_exists("WP_REST_Controller")) {
                echo "<div class='error'><p>" . __("Image Optimizer plugin requires WordPress 4.7 or higher.", IOWD_PREFIX) . "</p></div>";
            }
        }


    }

    // Admin menu

    public function actions()
    {

        if (isset($_GET["iowd_mode"])) {
            $mode = ($_GET["iowd_mode"] == "standart" || $_GET["iowd_mode"] == "advanced") ? $_GET["iowd_mode"] : "standart";

            if (get_option(IOWD_PREFIX . "_mode")) {
                update_option(IOWD_PREFIX . "_mode", $mode);
            } else {
                add_option(IOWD_PREFIX . '_mode', $mode, '', 'no');
            }
            if ($mode == "standart") {
                $standart_setting = get_option(IOWD_PREFIX . "_standart_setting");
                $settings = json_decode(get_option(IOWD_PREFIX . "_options"), true);
                if ($standart_setting == "conservative") {
                    $settings["keep_exif_data"] = 1;
                    $settings["exclude_full_size_metadata_removal"] = 1;
                    $settings["exclude_full_size"] = 1;
                    $settings["jpg_optimization_levels"] = "lossless";
                    $settings["png_optimization_levels"] = "lossless";
                    $settings["gif_optimization_levels"] = "lossless";
                    $settings["pdf_optimization_levels"] = "lossless";

                    update_option(IOWD_PREFIX . "_options", json_encode($settings));
                } else if ($standart_setting == "balanced") {
                    $settings["keep_exif_data"] = 1;
                    $settings["exclude_full_size_metadata_removal"] = 1;
                    $settings["exclude_full_size"] = 1;
                    $settings["jpg_optimization_levels"] = "lossy40";
                    $settings["png_optimization_levels"] = "lossy40";
                    $settings["gif_optimization_levels"] = "lossless";
                    $settings["pdf_optimization_levels"] = "lossless";

                    update_option(IOWD_PREFIX . "_options", json_encode($settings));
                } else if ($standart_setting == "extreme") {
                    $settings["keep_exif_data"] = 0;
                    $settings["exclude_full_size_metadata_removal"] = 0;
                    $settings["exclude_full_size"] = 0;
                    $settings["jpg_optimization_levels"] = "lossy";
                    $settings["png_optimization_levels"] = "lossy";
                    $settings["gif_optimization_levels"] = "lossless";
                    $settings["pdf_optimization_levels"] = "lossless";

                    update_option(IOWD_PREFIX . "_options", json_encode($settings));
                }

            }

            IOWD_Helper::redirect(array("page" => "iowd_settings"));
        }

    }


    // Admin main function

    public function auto_optimize($meta, $id)
    {
        $options = $this->options;
        if ($options["automatically_optimize"] == 0) {
            return $meta;
        }
        $optimize = new IOWD_Optimize(true);
        $ids = is_array($id) ? $id : array($id);

        $optimize->get_temp_data($ids, false, true, $meta);
        $meta = $optimize->optimize(0, "auto_" . $id, 1, $meta);

        return $meta;
    }

    public function scheduled_optimization()
    {
        $options = $this->options;

        if ($options["scheduled_optimization"] == 0) {
            return;
        }
        $optimize = new IOWD_Optimize();
        $data_count = $optimize->get_temp_data();
        for ($i = 0; $i < $data_count; $i++) {
            $optimize->optimize($i);
        }

    }

    // Register autoloader

    public function admin_menu()
    {
        $parent_slug = null;
        $main_title = 'Image Optimizer';
        if (get_option("iowd_subscribe_done") == 1) {
            $parent_slug = "iowd_settings";
            /*if (!get_transient(IOWD_PREFIX . '_overview_visited')) {
                $main_title .= ' <span class="update-plugins count-2" style="background-color: #d54e21;position: absolute; right: 6.4px;"> <span class="plugin-count">1</span></span>';
            }*/
            add_menu_page("Image Optimizer", $main_title, 'manage_options', $parent_slug, array($this, 'iowd_admin'), IOWD_URL_IMG . "/icon.png");
        }
        add_submenu_page($parent_slug, "Image Optimizer", "Image Optimizer", 'manage_options', "iowd_settings", array($this, 'iowd_admin'));

        add_submenu_page($parent_slug, 'Image Optimizer', 'Report', 'manage_options', 'iowd_report', array($this, 'iowd_admin'));
    }

    public function iowd_admin()
    {
        if ($this->is_iowd_page() == true) {
            $this->upgrade_pro();
            $this->user_manual();
            $view_class = ucfirst(strtolower(self::$page));
            $view = new $view_class();
            $view->display();
        }
    }

    private function is_iowd_page()
    {
        $iowd_pages = array(
            IOWD_PREFIX . "_settings",
            IOWD_PREFIX . "_report",
        );

        if (in_array(self::$page, $iowd_pages) == true) {
            return true;
        }

        return false;

    }

    public function ajax()
    {
        check_ajax_referer('nonce_' . IOWD_PREFIX, 'nonce_' . IOWD_PREFIX);
        $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';

        if ($action) {
            if (method_exists('IOWD_Ajax', $action)) {
                IOWD_Ajax::$action();
            }
        }
        wp_die();

    }

    // Admin styles

    public function register_autoloader()
    {
        if ($this->reg_autoloader) {
            return;
        }
        spl_autoload_register(array(__CLASS__, "autoload"));

        if (function_exists("__autoload")) {
            spl_autoload_register(array(__CLASS__, "autoloadProxy"));
        }

        $this->reg_autoloader = true;
    }

    // Admin scripts

    public function includes()
    {
        require_once IOWD_DIR_CLASSES . "/iowddb.php";
        require_once IOWD_DIR_CLASSES . "/iowdimage.php";
    }

    public function styles()
    {
        wp_admin_css('thickbox');
        if ($this->is_iowd_page() == true) {
            wp_enqueue_style(IOWD_PREFIX . '_admin_main-css', IOWD_URL_CSS . '/admin_main.css', array(), self::$version);
            wp_enqueue_style(IOWD_PREFIX . '_calendar-css', IOWD_URL_CSS . '/calendar-jos.css');
        }

    }

    public function scripts()
    {
        $options = $this->options;
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui');
        wp_enqueue_script('jquery-ui-tooltip');

        // api js
        wp_enqueue_script(IOWD_PREFIX . '_api-js', IOWD_URL_JS . '/api.js', array(), self::$version);
        wp_localize_script(IOWD_PREFIX . '_api-js', 'apiText', array(
            "error_1" => __("Invalid Api key, or Api key not found.", IOWD_PREFIX),

        ));

        // admin main js
        wp_enqueue_script(IOWD_PREFIX . '_admin_main-js', IOWD_URL_JS . '/admin_main.js', array(), self::$version);

        global $pagenow;
        $is_media_page = ($pagenow == 'upload.php' || $pagenow == 'post.php') && !(isset($_GET["page"]) && $_GET["page"] == "iowd_settings") ? 1 : 0;

        wp_localize_script(IOWD_PREFIX . '_admin_main-js', 'iowd', array(
            "options"               => get_option(IOWD_PREFIX . "_options", array()),
            "finishUploadingBulk"   => __(" images are ready to be optimized.", IOWD_PREFIX),
            "finishUploadingSingle" => __("Processing optimize", IOWD_PREFIX),
            "ajaxURL"               => admin_url('admin-ajax.php'),
            "nonce"                 => wp_create_nonce('nonce_' . IOWD_PREFIX),
            "page"                  => self::$page,
            "iowd_active_tab"       => (isset($_REQUEST["iowd_tabs_active"]) ? $_REQUEST["iowd_tabs_active"] : ""),
            "is_media_page"         => $is_media_page,
            "iowd_image_url"        => IOWD_URL_IMG,
            "enable_resizing"       => $options["enable_resizing"],
            "enable_resizing_other" => $options["enable_resizing_other"],
            "iowd_optimizing"       => (isset($_GET["iowd_optimizing"]) ? $_GET["iowd_optimizing"] : 0),

        ));


        if ($this->is_iowd_page() == true) {

            wp_enqueue_script(IOWD_PREFIX . '_settings-js', IOWD_URL_JS . '/settings.js', array(), self::$version);

            $from_gallery = isset($_GET["target"]) && $_GET["target"] == "wd_gallery" ? 1 : 0;

            wp_localize_script(IOWD_PREFIX . '_settings-js', 'iowdSettingsGlobal', array(
                "image_url"             => IOWD_URL_IMG,
                "ajaxURL"               => admin_url('admin-ajax.php'),
                "nonce"                 => wp_create_nonce('nonce_' . IOWD_PREFIX),
                "page"                  => (isset($_GET["page"]) ? $_GET["page"] : IOWD_PREFIX . "_settings"),
                "save_dirs_txt"         => __("Save directories", IOWD_PREFIX),
                "save_gallery_dirs_txt" => __("Save Gallery directories", IOWD_PREFIX),
                "from_gallery"          => $from_gallery,
                "overview_visited"      => get_transient(IOWD_PREFIX . '_overview_visited'),
            ));

            wp_enqueue_script(IOWD_PREFIX . '_calendar-js', IOWD_URL_JS . '/calendar.js', array(), self::$version, true);
            wp_enqueue_script(IOWD_PREFIX . '_calendar_function-js', IOWD_URL_JS . '/calendar_function.js', array(), self::$version, true);

        }

    }

    public function upgrade_pro()
    {
        ?>
        <div class="tenweb_banner wd-clear">
            <div class="tenweb_banner-left">
                <div class="tenweb_plugin_logo"></div>
                <div class="tenweb_plugin_name"><?php _e("Premium Image Optimizer", IOWD_PREFIX); ?></div>
            </div>
            <div class="tenweb_and"> & </div>
            <div class="tenweb_desc">
                <h3 class="tenweb_desc_h3 screen"><?php _e("Other solutions essential for your WordPress site", IOWD_PREFIX); ?></h3>
            </div>
            <div class="tenweb_banner-center wd-clear">
                <div class="tenweb_services">
                    <h3 class="tenweb_desc_h3 screen"><?php _e("Other solutions essential for your WordPress site", IOWD_PREFIX); ?></h3>
                    <h3 class="tenweb_desc_h3 mobile"><?php _e("And other solutions essential for your WordPress site", IOWD_PREFIX); ?></h3>
                    <div><span class="dashboard"><?php _e("Unified Dashboard", IOWD_PREFIX); ?></span><span
                                class="pro-plugins"><?php _e("60+ Plugins/Add-ons", IOWD_PREFIX); ?></span><span
                                class="backup"><?php _e("Backup", IOWD_PREFIX); ?></span><span
                                class="seo"><?php _e("SEO", IOWD_PREFIX); ?></span><span
                                class="themes"><?php _e("Premium WP Themes", IOWD_PREFIX); ?></span></div>
                </div>
            </div>
            <div class="tenweb_banner-right">
                <div class="tenweb_banner_logo"></div>
                <a href="https://10web.io/services/image-optimizer/" target="_blank"
                   class="button"><?php _e("Get free for 14 days", IOWD_PREFIX); ?></a>
            </div>
        </div>
        <?php
    }


    public function user_manual()
    {
        $page = isset($_GET["page"]) ? $_GET["page"] : "";
        ?>
        <div class="iowd_upgrade wd-clear">
            <div class="wd-left">
                <?php
                switch ($page) {
                    case "iowd_settings": ?>
                        <div style="font-size: 14px;margin-top: 6px;">
                            <?php
                            if (get_option(IOWD_PREFIX . '_mode') == "standart") {
                                _e("This section allows you quickly optimize the pictures, without going through advanced settings.", IOWD_PREFIX); ?>
                                <a style="color: #5CAEBD; text-decoration: none;border-bottom: 1px dotted;"
                                   target="_blank"
                                   href="http://docs.10web.io/docs/image-optimizer-wd/easy-mode.html"><?php _e("Read More in User Manual.", IOWD_PREFIX); ?></a>
                                <?php
                            } else {
                                _e("This section allows you configure image optimization and set up what best fits your website.", IOWD_PREFIX); ?>
                                <a style="color: #5CAEBD; text-decoration: none;border-bottom: 1px dotted;"
                                   target="_blank"
                                   href="http://docs.10web.io/docs/image-optimizer-wd/advanced-mode"><?php _e("Read More in User Manual.", IOWD_PREFIX); ?></a>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                        break;
                    case "iowd_report":
                        ?>
                        <div style="font-size: 14px;margin-top: 6px;">
                            <?php _e("This section allows you to check the optimization results.", IOWD_PREFIX); ?>
                            <a style="color: #5CAEBD; text-decoration: none;border-bottom: 1px dotted;" target="_blank"
                               href="http://docs.10web.io/docs/image-optimizer-wd/statistics-report"><?php _e("Read More in User Manual.", IOWD_PREFIX); ?></a>
                        </div>
                        <?php
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

}



