<?php
if (!defined('ABSPATH')) {
    exit;
}

class IOWD_Settings
{

    private $tabs = array();
    public  $photo_gallery_dir = null;


    public function __construct()
    {
        $fields = $this->get_fields();

        $this->tabs = array(
            'general'    => array(
                'name'   => __("General", IOWD_PREFIX),
                'fields' => $fields["general_fields"],
            ),
            'conversion' => array(
                'name'   => __("Conversion", IOWD_PREFIX),
                'fields' => $fields["conversion_fields"],
            ),
            'advanced'   => array(
                'name'   => __("Other", IOWD_PREFIX),
                'fields' => $fields["advanced_fields"],
            ),

        );
    }

    private function get_fields()
    {
        $fields = array();
        // general
        $fields["general_fields"] = array(
            "keep_exif_data"                     => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Keep EXIF data", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => __("Keep such interchange information as date and time the image was taken, if a flash was used,shutter speed, exposure compensation, F number, etc. EXIF data makes image files larger but if you are a photographer you may want to preserve this information.", IOWD_PREFIX),
            ),
            "exclude_full_size_metadata_removal" => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Exclude full-size images from exif data removal", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => __("Do not remove the descriptive information on full-size images.", IOWD_PREFIX),
            ),
            "keep_originals"                     => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Keep originals", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => "",
            ),
            "jpg_optimization_levels"            => array(
                "type"      => "select",
                "choices"   => array(
                    0 => array(
                        "label" => __("No Compression", IOWD_PREFIX),
                        "value" => 0
                    ),
                    1 => array(
                        "label" => __("Lossless Compression", IOWD_PREFIX),
                        "value" => "lossless"

                    ),
                    2 => array(
                        "label" => __("Lossy Compression ", IOWD_PREFIX),
                        "value" => "lossy40"
                    ),
                    3 => array(
                        "label" => __("Maximum Lossy Compression (disabled in free version)", IOWD_PREFIX),
                        "value" => "",
                        "attr"  => "disabled",
                    ),

                ),
                "label"     => __("JPG Optimization Level", IOWD_PREFIX),
                "classes"   => "iowd-elem-250",
                "attr"      => "",
                "help_text" => "",
                "tooltip"   => __("Lossy: while the size reduction is greater,perceptible loss of quality of the image  is possible. Lossless: there will be no loss of the full information contained in the original file.", IOWD_PREFIX),
            ),
            "png_optimization_levels"            => array(
                "type"      => "select",
                "choices"   => array(
                    0 => array(
                        "label" => __("No Compression", IOWD_PREFIX),
                        "value" => 0
                    ),
                    1 => array(
                        "label" => __("Lossless Compression", IOWD_PREFIX),
                        "value" => "lossless"

                    ),
                    2 => array(
                        "label" => __("Lossy Compression ", IOWD_PREFIX),
                        "value" => "lossy40"
                    ),
                    3 => array(
                        "label" => __("Maximum Lossy Compression (disabled in free version)", IOWD_PREFIX),
                        "value" => "",
                        "attr"  => "disabled",
                    ),

                ),
                "label"     => __("PNG Optimization Level", IOWD_PREFIX),
                "classes"   => "iowd-elem-250",
                "attr"      => "",
                "help_text" => "",
                "tooltip"   => __("Lossy: while the size reduction is greater,perceptible loss of quality of the image  is possible. Lossless: there will be no loss of the full information contained in the original file.", IOWD_PREFIX),
            ),
            "gif_optimization_levels"            => array(
                "type"      => "select",
                "choices"   => array(
                    0 => array(
                        "label" => __("No Compression", IOWD_PREFIX),
                        "value" => 0
                    ),
                    1 => array(
                        "label" => __("Lossless Compression", IOWD_PREFIX),
                        "value" => "lossless"

                    )
                ),
                "label"     => __("GIF Optimization Level", IOWD_PREFIX),
                "classes"   => "iowd-elem-250",
                "attr"      => "",
                "help_text" => "",
                "tooltip"   => __("Lossless: there will be no loss of the full information contained in the original file.", IOWD_PREFIX),
            ),
            "pdf_optimization_levels"            => array(
                "type"      => "select",
                "choices"   => array(
                    0 => array(
                        "label" => __("No Compression", IOWD_PREFIX),
                        "value" => 0
                    )
                ),
                "label"     => __("PDF Optimization Level", IOWD_PREFIX),
                "classes"   => "iowd-elem-250 iowd-disable",
                "attr"      => "disabled",
                "help_text" => "",
                "pro_text"  => __("This option is disabled in free version.", IOWD_PREFIX),
                "tooltip"   => __("Lossless: there will be no loss of the full information contained in the original file.", IOWD_PREFIX),
            )
        );
        // advanced


        $fields["advanced_fields"] = array(
            "scheduled_optimization" => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "iowd-disable",
                        "attr"    => "disabled",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "iowd-disable",
                        "attr"    => "disabled",
                    ),

                ),
                "label"     => __("Scheduled optimization", IOWD_PREFIX),
                "help_text" => "",
                "pro_text"  => __("This option is disabled in free version.", IOWD_PREFIX),
                "tooltip"   => __("The images will be automatically optimized with the chosen frequency.", IOWD_PREFIX),
            ),

            "exclude_full_size" => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Exclude full-size images from optimization", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => __("Do not optimize full size images.", IOWD_PREFIX),
            ),

            "enable_resizing"     => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Resize media full-size images", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => "",
            ),
            "resize_media_images" => array(
                "type"      => "custom",
                "label"     => __("Media images dimensions", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => __("Change the original size of the full-size images.", IOWD_PREFIX),
            ),

            "enable_resizing_other" => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Resize other directory images", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => "",
            ),
            "resize_other_images"   => array(
                "type"      => "custom",
                "label"     => __("Other directory images dimensions", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => "",
            ),
            "optimize_thumbs"       => array(
                "type"      => "custom",
                "label"     => __("Optimize these sizes", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => __("Only optimize images of  the selected size", IOWD_PREFIX),
            ),
            "skip_small_images"     => array(
                "type"      => "text",
                "label"     => __("Don't optimize images smaller than", IOWD_PREFIX),
                "classes"   => "iowd-elem-80",
                "attr"      => "",
                "help_text" => __("In megabytes", IOWD_PREFIX),
                "tooltip"   => "",
            ),
            "skip_large_images"     => array(
                "type"      => "text",
                "label"     => __("Don't optimize images larger than", IOWD_PREFIX),
                "classes"   => "iowd-elem-80",
                "attr"      => "",
                "help_text" => __("In megabytes", IOWD_PREFIX),
                "tooltip"   => "",
            ),

            "optimize_once_more" => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("In other optimize once more, if optimization level has changed", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => "",
            ),
        );
        // conversion
        $fields["conversion_fields"] = array(
            "enable_conversion" => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Enable conversion", IOWD_PREFIX),
                "help_text" => "",
                "tooltip"   => "",
            ),
            "jpg_to_png"        => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Enable jpg to png conversion", IOWD_PREFIX),
                "help_text" => __("PNG uses lossless compression. It is recommended to use for logos, and other pictures with transparent backgrounds. This option removes image metadata and increases CPU usage.", IOWD_PREFIX),
                "tooltip"   => __("Removes metadata and increases cpu usage dramatically", IOWD_PREFIX),
            ),
            "jpg_to_webp"       => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Enable jpg to webP conversion", IOWD_PREFIX),
                "help_text" => __("WebP lets you have 25-34% smaller images and speed up your website. JPG to WebP conversion is lossy, but it will not affect the image quality significantly.", IOWD_PREFIX),
                "tooltip"   => __("JPG to WebP conversion is lossy, but quality loss is minimal", IOWD_PREFIX),
            ),
            "png_to_jpg"        => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Enable png to jpg conversion", IOWD_PREFIX),
                "help_text" => __("JPG format is recommended to use for photographs and similar high-resolution images. Since it uses lossy compression, some of the image data is lost when the image is compressed.", IOWD_PREFIX),
                "tooltip"   => __("This is not a lossless conversion", IOWD_PREFIX),
            ),
            "png_to_webp"       => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Enable png to webP conversion", IOWD_PREFIX),
                "help_text" => __("PNG to WebP conversion is lossless. WebP images are 26% smaller in size, than their PNGs. This lets you make your website faster.", IOWD_PREFIX),
                "tooltip"   => __("PNG to WebP conversion is lossless.", IOWD_PREFIX),
            ),
            "gif_to_png"        => array(
                "type"      => "radio",
                "choices"   => array(
                    0 => array(
                        "label"   => __("No", IOWD_PREFIX),
                        "value"   => 0,
                        "classes" => "",
                        "attr"    => "",
                    ),
                    1 => array(
                        "label"   => __("Yes", IOWD_PREFIX),
                        "value"   => 1,
                        "classes" => "",
                        "attr"    => "",
                    ),

                ),
                "label"     => __("Enable gif to png conversion", IOWD_PREFIX),
                "help_text" => __("PNG uses lossless compression. It is recommended for logos and pictures with transparent backgrounds. Note, that animated GIFs cannot be converted.", IOWD_PREFIX),
                "tooltip"   => __("Gif to png conversion has no wornings.", IOWD_PREFIX),
            ),

        );


        return $fields;

    }

    private static function get_gallery_dir()
    {
        global $wd_bwg_options;
        if ($wd_bwg_options) {
            $photo_gallery_dir = ABSPATH . "/" .$wd_bwg_options->images_directory . '/photo-gallery';

            return $photo_gallery_dir;
        }

        return null;
    }

    public static function save_settings()
    {
        if(isset($_POST["action"]) && $_POST["action"] == "save_settings") {

            check_admin_referer('nonce_' . IOWD_PREFIX, 'nonce_' . IOWD_PREFIX);
            if (isset($_POST["standard_setting"]) && $_POST["standard_setting"]) {
                self::save_standard_settings();
            } else {
                $old_settings = json_decode(get_option(IOWD_PREFIX . "_options"), true);
                $new_settings = array();
                foreach ($old_settings as $setting_key => $setting) {
                    $new_settings[$setting_key] = isset($_POST[$setting_key]) ? esc_html($_POST[$setting_key]) : $setting;
                }
                if ($new_settings["scheduled_optimization"] == "0") {
                    wp_clear_scheduled_hook('iowd_optimize');
                }

                $photo_gallery_dir = self::get_gallery_dir();

                $other_dirs = $new_settings["other_folders"] ? json_decode(htmlspecialchars_decode(stripslashes($new_settings["other_folders"])), true) : array();
                $other_dirs = array_keys($other_dirs);
                if (!in_array($photo_gallery_dir, $other_dirs)) {
                    $new_settings["optimize_gallery"] = 0;
                } else {
                    $new_settings["optimize_gallery"] = 1;
                }

                update_option(IOWD_PREFIX . "_options", json_encode($new_settings));
            }
            $iowd_tabs_active = isset($_POST["iowd_tabs_active"]) ? $_POST["iowd_tabs_active"] : "general";
            $ids = isset($_POST["ids"]) ? $_POST["ids"] : "";
            IOWD_Helper::redirect(array("page" => "iowd_settings", "msg" => "1", "iowd_tabs_active" => $iowd_tabs_active, "ids" => $ids));
        }
    }

    public static function save_standard_settings()
    {
        if (isset($_POST["standard_setting"])) {
            $value = $_POST["standard_setting"] == "conservative" || $_POST["standard_setting"] == "balanced" || $_POST["standard_setting"] == "extreme" ? $_POST["standard_setting"] : "conservative";

            if (get_option(IOWD_PREFIX . "_standart_setting")) {
                update_option(IOWD_PREFIX . "_standart_setting", $value);
            } else {
                add_option(IOWD_PREFIX . "_standart_setting", $value, '', 'no');
            }

            $settings = json_decode(get_option(IOWD_PREFIX . "_options"), true);

            if ($value == "conservative") {
                $settings["keep_exif_data"] = 1;
                $settings["exclude_full_size_metadata_removal"] = 1;
                $settings["exclude_full_size"] = 1;
                $settings["jpg_optimization_levels"] = "lossless";
                $settings["png_optimization_levels"] = "lossless";
                $settings["gif_optimization_levels"] = "lossless";
                $settings["pdf_optimization_levels"] = "lossless";

                update_option(IOWD_PREFIX . "_options", json_encode($settings));
            } else if ($value == "balanced") {
                $settings["keep_exif_data"] = 0;
                $settings["exclude_full_size_metadata_removal"] = 0;
                $settings["exclude_full_size"] = 1;
                $settings["jpg_optimization_levels"] = "lossy40";
                $settings["png_optimization_levels"] = "lossy40";
                $settings["gif_optimization_levels"] = "lossless";
                $settings["pdf_optimization_levels"] = "lossless";
                update_option(IOWD_PREFIX . "_options", json_encode($settings));
            }
        }
    }

    public function display()
    {
        $this->photo_gallery_dir = self::get_gallery_dir();
        // get options
        $options = json_decode(get_option(IOWD_PREFIX . "_options"), true);
        // get mode
        $mode = get_option(IOWD_PREFIX . "_mode");
        $mode = $mode ? $mode : "standart";

        // get standart setting
        $standart_setting = get_option(IOWD_PREFIX . "_standart_setting");
        $standart_setting = $standart_setting ? $standart_setting : "conservative";

        // statistics data
        $stat = $this->get_statistics();

        // scan
        $optimize = new IOWD_Optimize();
        $other_folders = json_decode(htmlspecialchars_decode(stripslashes($options["other_folders"])), true);

        $other = empty($other_folders) === false && is_array($other_folders) ? true : false;
        
        $iowd_tabs_active = isset($_REQUEST["iowd_tabs_active"]) ? $_REQUEST["iowd_tabs_active"] : "general";
        $iowd_sizes = IOWD_Helper::wp_get_image_sizes();

        // if from bilk optimaze
        $attachments = array();
        $ids = isset($_GET["ids"]) && $_GET["ids"] ? explode(",", $_GET["ids"]) : array();
        if ($ids) {
            $optimize = new IOWD_Optimize(true);
            $attachments = $optimize->get_attachments($ids);
        }
        $last_optimized_data = get_site_transient("iowd_last_optimized_data");
        $last_optimized_data = $last_optimized_data ? json_decode($last_optimized_data, true) : array("image_size" => 0, "image_orig_size" => 0, "image_count" => 0);

        $last_optimized_data_reduced = $last_optimized_data["image_orig_size"] - $last_optimized_data["image_size"];
        $last_optimized_data_reduced_percent = $last_optimized_data["image_orig_size"] ? ($last_optimized_data_reduced / $last_optimized_data["image_orig_size"]) * 100 : 0;
        $last_optimized_data_reduced_percent = number_format($last_optimized_data_reduced_percent, 2);

        $msg = "";
        $msg_style = "";
        $msg_class = "";

        if ($options["jpg_optimization_levels"] == "0" && $options["png_optimization_levels"] == "0" && $options["gif_optimization_levels"] == "0" && $options["pdf_optimization_levels"] == "0") {

            $msg = __("Please select at least one optimization level for image types you want to optimize.", IOWD_PREFIX);
            $msg_style = 'style="display: block;"';
            $msg_class = "iowd_msg_div_error";
        } else if (get_site_transient("iowd_done_") == "1") {
            $images_count = get_transient("iowd_images_count_start") ? get_transient("iowd_images_count_start") : 0;
            $images_count = $images_count ? $images_count : "No";
            $skipped = $images_count - $last_optimized_data["image_count"];
            $msg = $last_optimized_data["image_count"] . " " . __("images have been optimized.", IOWD_PREFIX);
            if ($skipped) {
                $msg .= " " . $skipped . " " . __("images were skipped.", IOWD_PREFIX);
            }
            $msg_style = 'style="display: block;"';
            $msg_class = "iowd_msg_div_msg";
            delete_site_transient("iowd_done_");
            delete_transient("iowd_images_count_start");
            delete_transient("iowd_optimizing_post_ids");
        } else if (get_transient("iowd_response_error")) {
            $error = get_transient("iowd_response_error");

            if ($error == "no_job") {
                $msg = __("Something went wrong. Please try after few minutes.", IOWD_PREFIX);
            } elseif ($error == "no_agreement") {
                $msg = __("No agreement found.", IOWD_PREFIX);
            } else if ($error == "empty_credentials") {
                $msg = __("Empty credentials, please try again.", IOWD_PREFIX);
            }  else if ($error == "no_image_data"){
                $msg = __("No images found, please check your options.", IOWD_PREFIX);
            }

            if ($msg) {
                $msg_style = 'style="display: block;"';
                $msg_class = "iowd_msg_div_error";
            }
            delete_transient("iowd_response_error");
        }


        $limitation = IOWD_Helper::limitation();
        // require view template

        require_once(IOWD_DIR_VIEWS . '/iowd_settings_display.php');
    }

    public function get_statistics()
    {
        global $wpdb;
        $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "iowd_images");

        $total_size = 0;
        $total_orig_size = 0;
        $total_size_other = 0;
        $total_orig_size_other = 0;


        $protocaol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
        foreach ($rows as $row) {
            /*                $file =  str_replace( $protocaol . '://' . $_SERVER['SERVER_NAME'], $_SERVER['DOCUMENT_ROOT'], $row->path ) ;
                            if( !file_exists( $file ) ){
                                continue;
                            }
                            else {
                                if( $row->media == 1 ){
                                    if( get_post_status ( $row->post_id ) == false ){
                                        continue;
                                    }
                                }
                                else{
                                    //to do
                                }

                            }*/

            if ($row->media == 0) {
                $total_size_other += $row->image_size;
                $total_orig_size_other += $row->image_orig_size;
            } else {
                $total_size += $row->image_size;
                $total_orig_size += $row->image_orig_size;
            }


        }

        $total_reduced = $total_orig_size - $total_size;
        $total_reduced_persent = $total_orig_size ? ($total_reduced / $total_orig_size) * 100 : 0;

        $total_reduced_other = $total_orig_size_other - $total_size_other;
        $total_reduced_persent_other = $total_orig_size_other ? ($total_reduced_other / $total_orig_size_other) * 100 : 0;

        $total = $total_reduced + $total_reduced_other;
        $total_persent = ($total_orig_size + $total_orig_size_other) ? ($total / ($total_orig_size + $total_orig_size_other)) * 100 : 0;

        $total_reduced_persent = number_format($total_reduced_persent, 2);
        $total_reduced_persent_other = number_format($total_reduced_persent_other, 2);
        $total_persent = number_format($total_persent, 2);

        return array(
            "total_reduced"               => $total_reduced,
            "total_reduced_persent"       => $total_reduced_persent,
            "total_reduced_other"         => $total_reduced_other,
            "total_reduced_persent_other" => $total_reduced_persent_other,
            "total"                       => $total,
            "total_persent"               => $total_persent,
        );
    }


}


