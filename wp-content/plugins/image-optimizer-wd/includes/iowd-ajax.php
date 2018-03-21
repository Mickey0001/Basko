<?php
if (!defined('ABSPATH')) {
    exit;
}

class IOWD_Ajax
{

    /**
     *
     */

    public static function finish_bulk()
    {
        $abort = get_option("iowd_abort");
        if (!$abort) {
            $id = isset($_POST["postId"]) && $_POST["postId"] ? $_POST["postId"] : '';
            $done = get_site_transient("iowd_done_" . $id);

            $images_count_start = get_transient("iowd_images_count_start");
            // get optimization data
            $last_optimized_data = get_site_transient("iowd_last_optimized_data");
            $last_optimized_data = $last_optimized_data ? json_decode($last_optimized_data, true) : array("image_size" => 0, "image_orig_size" => 0, "image_count" => 0);

            if ($done) {
                $response = array("status" => "ok");
                if ($id) {
                    // get file type
                    $path = get_attached_file($id);
                    $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    global $wpdb;
                    $row = $wpdb->get_row("SELECT COUNT(*) AS image_count, SUM(image_orig_size) AS image_orig_size, SUM(image_size) AS image_size, post_id FROM " . $wpdb->prefix . "iowd_images WHERE post_id='" . $id . "' GROUP BY post_id");

                    $reduced = $row->image_orig_size - $row->image_size;
                    $reduced_percent = $row->image_orig_size ? ($reduced / $row->image_orig_size) * 100 : 0;
                    $reduced_percent = number_format($reduced_percent, 2);

                    $sizes = IOWD_Helper::get_attachmnet_sizes($id);
                    $html = '<div class="iowd_' . $id . '">
                            <div><a href="#" onclick="iowdStatus(this);return false;" data-id="' . $id . '"><small>' . __("Stats", IOWD_PREFIX) . ' [+]</small></a></div>
                            <div>' . $row->image_count . " " . __("images optimized by ", IOWD_PREFIX) . IOWD_Util::format_bytes($reduced) . ' ( ' . number_format($reduced_percent, 2) . '% )</div>
                            <div>Size: ' . IOWD_Util::format_bytes($sizes["total"]) . '</div>';
                    if ($type != "webp" && get_option("wdd_user_hash")) {
                        $html .= '<a href="#" class="iowd-optimize" data-id="' . $id . '">
                                <span>' . __("Reoptimize", IOWD_PREFIX) . '</span>
                                <img src="' . IOWD_URL_IMG . '/spinner.gif" class="iowd-spinner" style="display:none; vertical-align:sub;" />
                            </a>';
                    }
                    $html .= '</div>';
                    $response["html"] = $html;
                    delete_site_transient("iowd_done_" . $id);
                    delete_transient("iowd_images_count_start");
                }
            } else {
                $response = array("status" => "pending", "data" => array("all" => $images_count_start, "optimized" => $last_optimized_data["image_count"]));
            }
        } else {
            delete_option("iowd_abort");
            $response = array("status" => "abort", "data" => array("all" => 0, "optimized" => 0));
        }

        echo json_encode($response);
        wp_die();
    }

    public static function abort()
    {
        delete_transient("iowd_images_count_start");
        delete_transient("iowd_optimizing_post_ids");
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%iowd_done_%' OR option_name LIKE '%iowd_received_images_%'");
        add_option("iowd_abort", 1);
        wp_die();
    }

    public static function update_already_used()
    {
        $data = IOWD_Helper::limitation(true);
        echo json_encode($data);
        wp_die();
    }

    public static function get_attachment_data()
    {
        $ids = isset($_POST["ID"]) && $_POST["ID"] ? explode(",", $_POST["ID"]) : array();
        $other = isset($_POST["other"]) && $_POST["other"] ? true : false;
        $single = isset($_POST["bulk"]) ? false : true;

        $optimize = new IOWD_Optimize($single);
        $response = $optimize->get_temp_data($ids, $other, $single);

        echo json_encode($response);
        wp_die();

    }

    public static function optimize()
    {
        $single = isset($_POST["bulk"]) ? false : true;
        $iteration = isset($_POST["iteration"]) ? $_POST["iteration"] : 0;
        $id = isset($_POST["ID"]) && $_POST["ID"] ? $_POST["ID"] : '';

        $optimize = new IOWD_Optimize($single);
        $response = $optimize->optimize($iteration, $id);

        echo json_encode($response);
        wp_die();

    }

    public static function get_stats()
    {
        $optimized_data = array();

        $post_id = isset($_POST["ID"]) ? (int)$_POST["ID"] : 0;
        if ($post_id) {
            global $wpdb;
            $posts = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "iowd_images WHERE post_id = '" . $post_id . "'");
            if ($posts) {
                $sizes = IOWD_Helper::get_attachmnet_sizes($post_id);
                unset($sizes["total"]);
                $optimized_data = array();
                foreach ($posts as $post) {
                    $obj = new stdClass();
                    $obj->image_size = $post->image_size;
                    $obj->image_orig_size = $post->image_orig_size;
                    $obj->size = $post->size;
                    $optimized_data[$post->size] = $obj;
                }
                $all_sizes = array_keys($sizes);
                $optimized_sizes = array_keys($optimized_data);
                $not_optimized_sizes = array_diff($all_sizes, $optimized_sizes);

                if (empty($not_optimized_sizes) === false) {
                    foreach ($not_optimized_sizes as $not_optimized_size) {
                        $obj = new stdClass();
                        $obj->image_size = $obj->image_orig_size = $sizes[$not_optimized_size];
                        $obj->size = $not_optimized_size;
                        $optimized_data[$not_optimized_size] = $obj;
                    }
                }
            }
        }
        $response = '';
        if ($optimized_data) {
            $response =
                '<table class="iowd_stat_table">
					<tr>
						<th></th>
						<th>' . __("Original size", IOWD_PREFIX) . '</th>
						<th>' . __("Size", IOWD_PREFIX) . '</th>
						<th>' . __("Reduced By", IOWD_PREFIX) . '</th>
					</tr>';
            $total = 0;
            foreach ($optimized_data as $post) {
                $reduse = $post->image_orig_size - $post->image_size;
                $redused_persent = ($reduse / $post->image_orig_size) * 100;
                $response .=
                    '<tr>
							<td>' . ucfirst($post->size) . '</td>
							<td>' . IOWD_Util::format_bytes($post->image_orig_size) . '</td>
							<td>' . IOWD_Util::format_bytes($post->image_size) . '</td>';

                if ($post->image_orig_size == $post->image_size) {
                    $response .= '<td>' . __("Skipped", IOWD_PREFIX) . '</td>';
                } else {
                    $response .=
                        '<td>' . IOWD_Util::format_bytes($reduse) . '( ' . number_format($redused_persent, 2) . '% ) </td>';
                }

                $response .= '</tr>';
                $total += $post->image_size;
            }


            $response .= '</table>';
            $response .= '
							<br>  
							<div>
								<b>' . __("Total size", IOWD_PREFIX) . ':&nbsp; 
								' . IOWD_Util::format_bytes($total) . ' </b>
							</div>';
        }

        echo $response;
    }

    public static function choose_dirs()
    {
        global $wd_bwg_options;
        $dirs = isset($_POST["dir"]) ? json_decode(stripslashes($_POST["dir"])) : array();
        $template_other = "";
        $template_gallery = "";
        if ($dirs) {
            foreach ($dirs as $dir => $images) {
                if (!$images) {
                    $images = IOWD_Helper::get_images_from_dir_recursiv(trim($dir));
                }

                $show = $images ? '<span class="iowd-show-images">' . count($images) . ' images</span>' : '<span style="color:red; margin-left: 13px;">' . __("There are no images in this folder", IOWD_PREFIX) . '<span>';
                $images_tmpl = "";
                if ($images) {
                    $images_tmpl = "<div class='folder-images' style='display:none;'>";
                    foreach ($images as $image) {
                        $images_tmpl .= '<div class="iowd_other_img_path"><span class="iowd_image_path">' . $image . '</span> <span class="iowd_remove_img"> × </span> </div>';
                    }
                    $images_tmpl .= '</div>';
                }
                $dir_name = str_replace(get_home_path(), "", $dir);
                $template = '<div class="iowd_other_folders_row">' .
                    '<div class="other_folders" >' .
                    '<span class="iowd_other_path" title="' . $dir . '" data-name="' . $dir . '">' . $dir_name . ' </span> ' . $show .
                    '<span class="iowd_remove"> × </span>' .
                    '</div>' . $images_tmpl .

                    '</div>';

                if (strpos($dir_name, "photo-gallery") !== false && isset($wd_bwg_options)) {
                    $template_gallery .= $template;
                } else {
                    $template_other .= $template;
                }
            }

        }

        echo json_encode(array("other" => $template_other, "gallery" => $template_gallery));
        wp_die();
    }



    public static function filter_report()
    {
        $report_class = new IOWD_Report();
        $data = $report_class->report_data();
        $report_data = $data["rows"];
        $total_count = $data["total_count"];
        $total_orig_size = $data["total_image_orig_size"];
        $total_size = $data["total_image_size"];
        $limit = $data["limit"];

        require_once IOWD_DIR_VIEWS . '/iowd_report_tbody_display.php';
        wp_die();
    }

    public static function clear_report()
    {
        global $wpdb;
        $post_id = isset($_POST["post_id"]) ? $_POST["post_id"] : 0;
        $where = $post_id ? " WHERE post_id='" . $post_id . "'" : "";

        $wpdb->query("UPDATE " . $wpdb->prefix . "iowd_images SET deleted=1 " . $where);
        wp_die();
    }

    public static function quick_settings()
    {
        $name = isset($_POST["name"]) ? $_POST["name"] : false;
        $value = isset($_POST["value"]) ? $_POST["value"] : false;

        if ($name !== false && $value !== false) {
            $settings = json_decode(get_option(IOWD_PREFIX . "_options"), true);
            if (isset($settings[$name])) {
                $settings[$name] = esc_html($value);
                update_option(IOWD_PREFIX . "_options", json_encode($settings));
            }

        }
        wp_die();

    }

    public static function get_subdirs()
    {
        $dir = isset($_POST["dir"]) ? $_POST["dir"] : '';
        $html = '';

        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ('.' === $file || '..' === $file || !is_dir($dir . "/" . $file)) {
                    continue;
                }

                $html .= '<li class="iowd-dir-tree-title" data-path="' . str_replace("\\", "/", $dir) . "/" . $file . '" ><a href="#" ><img src="' . IOWD_URL_IMG . '/folder.png" class="iowd-dir-tree-icon">' . $file . '</a>';

            }

            if ($html) {
                $html = "<ul>" . $html . "</ul>";
            }
        }

        echo $html;
        //wp_die();
    }

    public static function scan()
    {
        $optimizer = new IOWD_Optimize();
        $limit = isset($_POST["limit"]) ? $_POST["limit"] : 0;

        $data = $optimizer->get_attachments(array(), false, array(), null, $limit);
        if ($data) {
            $response = array("status" => "pending");
        } else {
            $data = $optimizer->get_attachments(array(), true, array(), null, $limit);
            $response = array("status" => "done");
        }

        echo json_encode($response);
        wp_die();

    }

    public static function scan_all()
    {
        $optimizer = new IOWD_Optimize();
        $limit = isset($_POST["limit"]) ? $_POST["limit"] : 0;

        $media = array();
        $other = array();
        $media_sizes = array();
        $attachments = array();
        for ($i = 0; $i <= $limit; $i += 2000) {
            $db_attachments = get_site_transient("iowd_temp_scan_data_" . $i);
            $media_attachments = isset($db_attachments["media"]) ? $db_attachments["media"] : array();
            $media_sizes_attachments = isset($db_attachments["media_sizes"]) ? $db_attachments["media_sizes"] : array();
            $other_attachments = isset($db_attachments["other"]) ? $db_attachments["other"] : array();
            $u_attachments = isset($db_attachments["attachments"]) ? $db_attachments["attachments"] : array();

            $media = $media + $media_attachments;
            $other = $other + $other_attachments;
            $media_sizes = $media_sizes + $media_sizes_attachments;
            $attachments = $attachments + $u_attachments;

            delete_site_transient("iowd_temp_scan_data_" . $i);
        }
        $all_attachments = array(
            "media"       => $media,
            "media_sizes" => $media_sizes,
            "other"       => $other,
            "attachments" => $attachments,
        );


        $data = $optimizer->scan($all_attachments);
        echo json_encode($data);
        wp_die();

    }

}


?>