<?php
if (!defined('ABSPATH')) {
    exit;
}

class IOWD_Helper
{


    public static function message($message, $type)
    {
        return '<div class="iowd-messages">
				<div class="' . $type . '">
					<p><strong>' . $message . '</strong></p>
				</div>
			</div>';
    }

    public static function redirect(array $url, $page = "admin.php")
    {
        return wp_safe_redirect(add_query_arg($url, admin_url($page)));
    }

    public static function dir_tree($dir_path, $only_dirs = true)
    {

        if (file_exists($dir_path)) {
            echo "<ul>";
            $dir = scandir($dir_path);

            foreach ($dir as $file) {

                if ($file == "." || $file == ".." || strpos($file, ".") !== false) {
                    continue;
                }

                if (is_dir($dir_path . "/" . $file) || $only_dirs === false) {

                    echo '<li class="iowd-dir-tree-title" data-path="' . str_replace("\\", "/", $dir_path) . "/" . $file . '" ><a href="#" ><img src="' . IOWD_URL_IMG . '/folder.png" class="iowd-dir-tree-icon">' . $file . '</a>';
                }

                if (is_dir($dir_path . "/" . $file) === true) {
                    self::dir_tree($dir_path . "/" . $file, $only_dirs);
                }

            }
            echo "</li></ul>";
        }


    }

    public static function get_images_from_dir_recursiv($dir_path, &$image_paths = array())
    {
        global $wd_bwg_options;
        $options = json_decode(get_option(IOWD_PREFIX . "_options"), true);
        if (file_exists($dir_path)) {
            $dir = scandir($dir_path);
            $images = glob($dir_path . '/*.{jpg,png,gif,jpeg,pdf}', GLOB_BRACE);

            $images = $images ? $images : array();
            if ($images) {
                $protocaol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
                array_walk($images, create_function('&$value', '$value = str_replace( "' . $_SERVER['DOCUMENT_ROOT'] . '", "' . $protocaol . '://' . $_SERVER['SERVER_NAME'] . '", $value );'));
            }
            $image_paths = array_merge($image_paths, $images);
            foreach ($dir as $file) {
                if ($file == "." || $file == "..") {
                    continue;
                }
                if(!(strpos($file, ".original") !== false && $options["exclude_full_size"] == "0" && !empty($wd_bwg_options))){
                    if( (strpos($file, ".") !== false)){
                        continue;
                    }
                }
                if (is_dir($dir_path . "/" . $file)) {
                    self::get_images_from_dir_recursiv($dir_path . "/" . $file, $image_paths);
                }

            }
        }

        return $image_paths;

    }

    public static function get_images_from_dir($dir_path)
    {
        if (file_exists($dir_path)) {
            $images = glob($dir_path . '/*.{jpg,png,gif,jpeg,pdf}', GLOB_BRACE);

            $images = $images ? $images : array();
            if ($images) {
                $protocaol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
                array_walk($images, create_function('&$value', '$value = str_replace( "' . $_SERVER['DOCUMENT_ROOT'] . '", "' . $protocaol . '://' . $_SERVER['SERVER_NAME'] . '", $value );'));
            }
        }

        return $images;

    }

    public static function get_attachment_ids()
    {
        $args = array(
            'post_type'   => 'attachment',
            'numberposts' => -1
        );
        $attachments = get_posts($args);
        $images = array();

        foreach ($attachments as $attachment) {
            $images[] = $attachment->ID;
        }

        return $images;
    }

    public static function wp_get_image_sizes()
    {
        $iowd_sizes = array();
        $sizes = get_intermediate_image_sizes();
        global $_wp_additional_image_sizes;
        foreach ($sizes as $size) {
            if ($size == 'thumbnail' || $size == 'medium' || $size == 'medium_large' || $size == 'large') {
                $iowd_sizes[$size]["width"] = get_option($size . '_size_w');
                $iowd_sizes[$size]["height"] = get_option($size . '_size_h');
            } elseif (isset($_wp_additional_image_sizes[$size])) {
                $iowd_sizes[$size] = array(
                    'width'  => $_wp_additional_image_sizes[$size]['width'],
                    'height' => $_wp_additional_image_sizes[$size]['height'],
                );
            }
        }
        if ($iowd_sizes['medium_large']['width'] == 0) {
            $iowd_sizes['medium_large']['width'] = '768';
        }
        if ($iowd_sizes['medium_large']['height'] == 0) {
            $iowd_sizes['medium_large']['height'] = '9999';
        }

        return $iowd_sizes;
    }

    public static function pn_get_attachment_id_from_url($attachment_url = '')
    {

        global $wpdb;
        $attachment_id = false;

        // If there is no url, return.
        if ('' == $attachment_url)
            return;

        // Get the upload directory paths
        $upload_dir_paths = wp_upload_dir();

        // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
        if (false !== strpos($attachment_url, $upload_dir_paths['baseurl'])) {

            // If this is the URL of an auto-generated thumbnail, get the URL of the original image
            $attachment_url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url);

            // Remove the upload path base directory from the attachment URL
            $attachment_url = str_replace($upload_dir_paths['baseurl'] . '/', '', $attachment_url);

            // Finally, run a custom database query to get the attachment ID from the modified attachment URL
            $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url));

        }

        return $attachment_id;
    }

    public static function iowd_is_url_exists($url)
    {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);

        return $status;
    }

    public static function get_attachmnet_sizes($id)
    {
        $attachmnet_sizes = array();
        $full_file = get_attached_file($id);
        $full_file_size = file_exists($full_file) ? filesize($full_file) : 0;

        $meta = wp_get_attachment_metadata($id);
        $sizes = isset($meta["sizes"]) ? $meta["sizes"] : array();
        $attachmnet_sizes["full"] = $full_file_size;
        $attachment_root_dir = str_replace(basename($full_file), "", $full_file);
        $total = $full_file_size;
        foreach ($sizes as $size => $file) {
            $image_size = file_exists($attachment_root_dir . $file["file"]) ? filesize($attachment_root_dir . $file["file"]) : 0;
            $attachmnet_sizes[$size] = $image_size;
            $total += $image_size;

        }
        $attachmnet_sizes["total"] = $total;

        return $attachmnet_sizes;

    }

    public static function limitation($refresh = false)
    {
        $already_optimized = get_site_transient("iowd_already_optimized");
        if(is_null($already_optimized) || $already_optimized === false ||  $refresh == true){
            $already_optimized = self::update_already_used();
        }

        return array(
            "limit"  => 1000,
            "period" => "1 month",
            "already_optimized" => $already_optimized,
        );
    }

    public static function update_already_used()
    {
        require_once IOWD_DIR_INCLUDES . "/iowd-api.php";
        $request_data = array(
            "domain" => site_url(),
            "uhash"  => "",
        );
        IOWD_Api::set_post_data($request_data);
        IOWD_Api::set_api_action("used");
        $response = IOWD_Api::api_request();
        $already_optimized = isset($response["already_optimized"]) ? $response["already_optimized"] : 0;

        set_site_transient("iowd_already_optimized", $already_optimized, 43200);

        return $already_optimized;
    }


}