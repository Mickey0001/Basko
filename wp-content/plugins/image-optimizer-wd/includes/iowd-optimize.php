<?php
if (!defined('ABSPATH')) {
    exit;
}


class IOWD_Optimize
{
    private $options;
    private $is_single = false;
    private $allowed_types = array("jpg", "jpeg", "png", "gif");
    private $images_count = 60;


    public function __construct($single = false)
    {
        $this->is_single = $single;
        $this->options = json_decode(get_option(IOWD_PREFIX . "_options"), true);
        if ($single == true) {
            $this->images_count = count(IOWD_Helper::wp_get_image_sizes()) + 1;
        }

    }

    public function get_temp_data($ids = array(), $other = false, $single = false, $meta = array())
    {
        $id = $single == true ? ($meta ? "auto_" . $ids[0] : $ids[0]) : '';
        // delete all remained data
        delete_option("iowd_image_temp_data_" . $id);
        delete_option("iowd_data_count_" . $id);
        delete_site_transient("iowd_last_optimized_data");
        delete_transient("iowd_images_count_start");
        delete_site_transient("iowd_done_" . $id);
        delete_option("iowd_crd_" . $id);
        delete_option("iowd_abort");
        delete_site_transient("iowd_received_images_". $id);

        //check
        $folder_name =  md5(site_url());
        $request_data = array(
            "domain"       => site_url(),
            "folder"       => $folder_name,
        );

        IOWD_Api::set_post_data($request_data);
        IOWD_Api::set_api_action("validate");
        $response = IOWD_Api::api_request();

        if ($response["status"] == "error") {
            set_transient("iowd_response_error", $response["error"]);
            IOWD_Helper::update_already_used();
            return $response;
        }

        if (!empty($response["credentials"])) {
            $credentials = array(
                'key'    => $response["credentials"]["AccessKeyId"],
                'secret' => $response["credentials"]["SecretAccessKey"],
                'token'  => $response["credentials"]["SessionToken"]
            );
            add_option("iowd_crd_" . $id, $credentials);
        } else {
            set_transient("iowd_response_error", 'empty_credentials');

            return array("status" => "error", "error" => __("Empty credentials, please try again.", IOWD_PREFIX));
        }

        $images_data = $this->get_attachments($ids, $other, $meta, $response["remained"]);

        $data_count = count($images_data);
        if($data_count == 0){
            set_transient("iowd_response_error", 'no_image_data');
            return array("status" => "error", "data_count" => $data_count);
        }
        // add temp data
        add_option("iowd_image_temp_data_" . $id, $images_data);
        add_option("iowd_data_count_" . $id, $data_count);

        return array("status" => "ok", "data_count" => $data_count);
    }

    public function get_attachments($ids = array(), $include_others = false, $meta1 = array(), $remained = null, $limit = null)
    {
        ini_set('max_execution_time', 300);

        clearstatcache();
        $options = $this->options;
        $image_data = array();
        global $wpdb;
        $optimized_data = array();
        $optimized_data_ids = array();
        $_optimized_data = $wpdb->get_results("SELECT path, post_id, size, already_optimized FROM " . $wpdb->prefix . "iowd_images");
        foreach ($_optimized_data as $data) {
            $optimization_type = $this->get_current_opt_level($data->path);
            $already_optimized = explode(",", $data->already_optimized);
            $already_optimized = array_unique($already_optimized);

            if ($options["optimize_once_more"] == "1" && (($optimization_type == "losselss" && empty($already_optimized) == true) || ($optimization_type == "lossy40" && ($already_optimized == array("lossless") || empty($already_optimized) == true)) || ($optimization_type == "lossy" && !in_array("lossy", $already_optimized)))) {
                continue;
            }
            $optimized_data[$data->post_id . "_" . $data->size] = $data->path;
            if (isset($optimized_data_ids[$data->path])) {
                $optimized_data_ids[$data->path][] = $data->post_id;
            } else {
                $optimized_data_ids[$data->path] = array($data->post_id);
            }
        }

        $optimize_thumbs = explode(",", $options["optimize_thumbs"]);
        $uploads = wp_get_upload_dir();

        $limitation = IOWD_Helper::limitation();

        global $wpdb;

        $where_ids = $ids ? "AND ID IN (" . implode(",", $ids) . ")" : "";
        $limit_by = $limit !== null ? ' LIMIT ' . $limit . ', 2000' : '';

        $attachments = $wpdb->get_results("SELECT T_POSTS.ID, T_POSTMETA.meta_value  FROM " . $wpdb->prefix . "posts AS T_POSTS LEFT JOIN " . $wpdb->prefix . "postmeta AS T_POSTMETA ON 
        T_POSTS.ID = T_POSTMETA.post_id AND T_POSTMETA.meta_key = '_wp_attachment_metadata' WHERE post_type='attachment' " . $where_ids . $limit_by);

        if (empty($attachments) === false) {
            foreach ($attachments as $attachment) {
                if (count($image_data) > $limitation["limit"] && is_null($limit)) {
                    break;
                }
                $meta = $attachment->meta_value ? unserialize($attachment->meta_value) : $meta1;
                $sizes = isset($meta["sizes"]) ? $meta["sizes"] : array();
                $file = isset($meta["file"]) ? $meta["file"] : "no_file";
                $file_name = $uploads['basedir'] . "/" . $file;
                $path = $uploads['baseurl'] . "/" . $file;

                //check for getting correct data
                if (!$ids) {
                    if (in_array($path, $optimized_data) && in_array($attachment->ID, $optimized_data_ids[$path])) {
                        continue;
                    }
                }
                if (!file_exists($file_name)) {
                    continue;
                }
                $type = pathinfo($file_name, PATHINFO_EXTENSION);
                $size = filesize($file_name);

                // check type
                if (!in_array(strtolower($type), $this->allowed_types) || $this->check_optimization_level($type) === false) {
                    continue;
                }

                if (($this->skip_small_images($size) === true && $this->skip_large_images($size) === true && $options["exclude_full_size"] == 0) || $this->is_single == true) {
                    if (is_null($limit)) {
                        $image = array();
                        $image["path"] = $path;
                        $image["file"] = $file_name;
                        $image["post_id"] = $attachment->ID;
                        $image["size"] = "full";
                        $image["image_size"] = $size;
                        $image["media"] = 1;
                        $image["converted"] = 0;
                        $image["resized"] = 0;
                        $image["single"] = $this->is_single;
                        $image["transparent"] = $type == "png" && IOWD_Util::is_transparent_png($file_name) ? 1 : 0;

                        $image_data[] = $image;
                    } else {
                        $image_data["media"][$attachment->ID . "_full"] = $path;
                        $image_data["attachments"][$attachment->ID] = $attachment->ID;
                    }

                }
                $attachment_root_url = str_replace(basename($path), "", $path);
                $attachment_root_dir = str_replace(basename($file_name), "", $file_name);

                foreach ($sizes as $size => $file) {
                    if (in_array($size, $optimize_thumbs)) {
                        if (!file_exists($attachment_root_dir . $file["file"])) {
                            continue;
                        }
                        if (!$ids) {
                            if (in_array($attachment_root_url . $file["file"], $optimized_data) && in_array($attachment->ID , $optimized_data_ids[$attachment_root_url . $file["file"]]) ) {
                                continue;
                            }
                        }
                        $image_size = filesize($attachment_root_dir . $file["file"]);
                        if (($this->skip_small_images($image_size) === true && $this->skip_large_images($image_size) === true) || $this->is_single == true) {
                            if (is_null($limit)) {
                                $image = array();
                                $image["path"] = $attachment_root_url . $file["file"];
                                $image["file"] = $attachment_root_dir . $file["file"];
                                $image["post_id"] = $attachment->ID;
                                $image["size"] = $size;
                                $image["image_size"] = $image_size;
                                $image["media"] = 1;
                                $image["converted"] = 0;
                                $image["resized"] = 0;
                                $image["single"] = $this->is_single;
                                $image["transparent"] = $type == "png" && IOWD_Util::is_transparent_png($attachment_root_dir . $file["file"]) ? 1 : 0;
                                $image_data[] = $image;
                            } else {
                                $image_data["media_sizes"][$attachment->ID . "_" . $size] = $attachment_root_url . $file["file"];
                                $image_data["attachments"][$attachment->ID] = $attachment->ID;
                            }
                        }
                    }
                }
            }
        }

        if ($include_others == true && ((count($image_data) < $limitation["limit"] && is_null($limit)) || $limit !== null)) {

            $protocaol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';

            $all_dirs = $options["other_folders"] ? json_decode(htmlspecialchars_decode(stripslashes($options["other_folders"])), true) : array();

            $other_images = array();
            if (empty($all_dirs) === false) {
                foreach ($all_dirs as $dir => $image_path) {
                    foreach ($image_path as $path) {
                        $other_images[] = $path;
                    }
                }
            }

            $other_images = array_unique($other_images);

            $i = 1;
            if (empty($other_images) === false) {
                global $wpdb;
                $max_iowd_post = $wpdb->get_row("SELECT post_id, id FROM " . $wpdb->prefix . "iowd_images WHERE post_id LIKE '%iowd_%' ORDER BY id DESC LIMIT 0,1");

                $max_iowd_post = $max_iowd_post ? substr($max_iowd_post->post_id, 5) + 1 : 1;

                foreach ($other_images as $other_image) {
                    if (count($image_data) > $limitation["limit"] && is_null($limit)) {
                        break;
                    }
                    if (in_array($other_image, $optimized_data)) {
                        continue;
                    }
                    if ($options["exclude_full_size"] == "1" && strpos($other_image, ".original") !== false) {
                        continue;
                    }

                    $other_path = str_replace($protocaol . '://' . $_SERVER['SERVER_NAME'], $_SERVER['DOCUMENT_ROOT'], $other_image);

                    if (!file_exists($other_path)) {
                        continue;
                    }
                    $size = filesize($other_path);
                    $type = pathinfo($other_image, PATHINFO_EXTENSION);

                    if ($this->check_optimization_level($type) === false || $this->skip_small_images($size) === false || $this->skip_large_images($size) === false || !in_array(strtolower($type), $this->allowed_types)) {
                        continue;
                    }
                    if (is_null($limit)) {
                        $image = array();
                        $image["path"] = $other_image;
                        $image["file"] = str_replace($protocaol . '://' . $_SERVER['SERVER_NAME'], $_SERVER['DOCUMENT_ROOT'], $other_image);
                        $image["post_id"] = "iowd_" . $max_iowd_post;
                        $image["size"] = "full";

                        $image["image_size"] = $size;
                        $image["media"] = 0;
                        $image["converted"] = 0;
                        $image["resized"] = 0;
                        $image["single"] = $this->is_single;
                        $image["transparent"] = $type == "png" && IOWD_Util::is_transparent_png($image["file"]) ? 1 : 0;

                        $image_data[] = $image;
                    } else {
                        $image_data["other"]["iowd_" . $max_iowd_post . "_full"] = $other_image;
                    }

                    $i++;
                    $max_iowd_post++;
                }
            }
        }

        if (is_null($limit)) {
            if (!is_null($remained)) {
                $image_data = array_slice($image_data, 0, $remained);
            }

            $images_count = count($image_data);

            $attachments_data = array();
            if ($images_count > 0) {
                if ($images_count > $this->images_count) {
                    for ($i = 0; $i < ceil($images_count / $this->images_count); $i++) {
                        $attachments_data[$i] = array_slice($image_data, $i * $this->images_count, $this->images_count);
                    }
                } else {
                    $attachments_data[0] = $image_data;
                }
            }

            return $attachments_data;
        } else {
            set_site_transient("iowd_temp_scan_data_" . $limit, $image_data);

            return $image_data ? true : false;
        }

    }


    public function scan($all_attachments)
    {
        global $wpdb;
        $options = $this->options;
        $_db_optimized_data = $wpdb->get_results("SELECT `path`, `post_id`, `size`, `already_optimized` FROM " . $wpdb->prefix . "iowd_images");

        // all data
        $media = isset($all_attachments["media"]) ? $all_attachments["media"] : array();
        $media_sizes = isset($all_attachments["media_sizes"]) ? $all_attachments["media_sizes"] : array();
        $other = isset($all_attachments["other"]) ? $all_attachments["other"] : array();
        $unique_attachments = isset($all_attachments["attachments"]) ? $all_attachments["attachments"] : array();

        $all_media_sizes = array_merge($media, $media_sizes);
        $all_data_sizes = array_merge($all_media_sizes, $other);
        $all_data = array_unique(array_merge($media, $other));

        $optimized_data_other = array();
        $optimized_data_media = array();
        $optimized_data_media_sizes = array();


        $db_optimized_data = array();
        if ($_db_optimized_data) {
            foreach ($_db_optimized_data as $data) {
                $optimization_type = $this->get_current_opt_level($data->path);
                $already_optimized = explode(",", $data->already_optimized);
                $already_optimized = array_unique($already_optimized);

                if ($options["optimize_once_more"] == "1" && (($optimization_type == "losselss" && empty($already_optimized) == true) || ($optimization_type == "lossy40" && ($already_optimized == array("lossless") || empty($already_optimized) == true)) || ($optimization_type == "lossy" && !in_array("lossy", $already_optimized)))) {
                    continue;
                }
                $post_id = $data->post_id;
                $path = $data->path;

                if (strpos($post_id , "iowd") !== false ) {
                    $optimized_data_other[$post_id] = $path;
                } else {
                    if ($data->size == "full") {
                        $optimized_data_media[$post_id . "_" . $data->size] = $path;
                    }
                    $optimized_data_media_sizes[$post_id . "_" . $data->size] = $path;
                }
                $db_optimized_data[$post_id . "_" . $data->size] = $path;
            }

        }

        $optimized_data = array_merge($optimized_data_other, $optimized_data_media);

        /*$not_optimized_data = array_diff($all_data, $optimized_data);
        $not_optimized_data_sizes = array_diff($all_data_sizes, $db_optimized_data);
        $not_optimized_data_media = array_diff($media, $optimized_data_media);
        $not_optimized_data_media_sizes = array_diff($all_media_sizes, $optimized_data_media_sizes);
        $not_optimized_data_other = array_diff($other, $optimized_data_other);*/

        $all_data_sizes = array_merge($all_data_sizes, $db_optimized_data);
        $all_data = array_merge($all_data, $optimized_data);


        return array(
            "optimized_data"                 => count($optimized_data),
            "optimized_data_sizes"           => count($db_optimized_data),
            "not_optimized_data_media"       => count($unique_attachments),
            "not_optimized_data_media_sizes" => count($all_media_sizes),
            "not_optimized_data_other"       => count($other),
            "all_data_sizes"                 => count($all_data_sizes),
            "all_data"                       => count($all_data),
        );
    }

    public function scanOld()
    {
        $options = $this->options;
        $db_optimized_data = get_site_transient("iowd_optimized");
        $db_optimized_data = $db_optimized_data ? $db_optimized_data : array();

        // all data
        global $wpdb;
        $attachments = $wpdb->get_col("SELECT T_POSTMETA.meta_value  FROM ". $wpdb->prefix . "posts AS T_POSTS LEFT JOIN ". $wpdb->prefix . "postmeta AS T_POSTMETA ON 
        T_POSTS.ID = T_POSTMETA.post_id AND T_POSTMETA.meta_key = '_wp_attachment_metadata' WHERE post_type='attachment' ");


        $all_dirs = $options["other_folders"] ? json_decode(htmlspecialchars_decode(stripslashes($options["other_folders"])), true) : array();

        $other_images = array();
        if (empty($all_dirs) === false) {
            foreach ($all_dirs as $dir => $image_path) {
                foreach ($image_path as $path) {
                    $other_images[] = $path;
                }
            }
        }
        $other_images = count(array_unique($other_images));


        $optimized_data_other = 0;
        $optimized_data_media = 0;
        $optimized_data_media_sizes = 0;

        $all_data = count($attachments) + $other_images;
        $all_media_sizes = array_reduce($attachments, function($initial, $value){

            $value = unserialize($value);
            $initial += 1 + count($value["sizes"]);

            return $initial;
        });

        $post_ids = array();
        if($db_optimized_data){
            foreach($db_optimized_data as $post_id => $path){
                $parts = explode("_", $post_id);
                $post_id = $parts[1];

                if(count($parts) == 3 && $parts[0] == "iowd"){
                    $post_id = $path;
                    $optimized_data_other++;
                } else {
                    if($parts[1] == "full"){
                        $optimized_data_media++;
                    }
                    $optimized_data_media_sizes++;
                }

                $post_ids[] = $post_id;
            }
            $db_optimized_data = count($db_optimized_data);
        } else {
            $db_optimized_data = 0;
        }

        $optimized_data = count($post_ids);
        $optimized_data_sizes = $db_optimized_data;

        $not_optimized_data = $all_data - $optimized_data;
        $not_optimized_data_sizes = $all_media_sizes + $other_images - $optimized_data_sizes;

        $not_optimized_data_media = count($attachments) - $optimized_data_media;
        $not_optimized_data_media_sizes = $all_media_sizes - $optimized_data_media_sizes;

        $not_optimized_data_other = $other_images - $optimized_data_other;
        $all_data_sizes = $all_media_sizes + $other_images;

        return array(
            "optimized_data"                 => $optimized_data,
            "optimized_data_sizes"           => $optimized_data_sizes,
            "not_optimized_data"             => $not_optimized_data,
            "not_optimized_data_sizes"       => $not_optimized_data_sizes,
            "not_optimized_data_media"       => $not_optimized_data_media,
            "not_optimized_data_media_sizes" => $not_optimized_data_media_sizes,
            "not_optimized_data_other"       => $not_optimized_data_other,
            "all_data"                       => $all_data,
            "all_data_sizes"                 => $all_data_sizes,
        );
    }

    private function check_optimization_level($mime_type)
    {
        $options = $this->options;

        switch (strtolower($mime_type)) {
            case "jpg":
            case "jpeg":
                if (!$options["jpg_optimization_levels"]) {
                    return false;
                }
                break;

            case "png":
                if (!$options["png_optimization_levels"]) {
                    return false;
                }
                break;

            case "gif":
                if (!$options["gif_optimization_levels"]) {
                    return false;
                }
                break;

            case "pdf":
                if (!$options["pdf_optimization_levels"]) {
                    return false;
                }
                break;
        }

        return true;
    }

    private function skip_small_images($size)
    {
        $options = $this->options;
        if ($size < floatval($options["skip_small_images"]) * 1048576 && $options["skip_small_images"]) {
            return false;
        }

        return true;
    }

    private function skip_large_images($size)
    {
        $options = $this->options;
        if ($size > floatval($options["skip_large_images"]) * 1048576 && $options["skip_large_images"]) {
            return false;
        }

        return true;
    }

    private function get_current_opt_level($path)
    {
        $options = $this->options;
        $mime_type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime_type = $mime_type == "jpeg" ? "jpg" : $mime_type;
        $optimization_type = isset($options[$mime_type . "_optimization_levels"]) ? $options[$mime_type . "_optimization_levels"] : "0";

        return $optimization_type;
    }

    public function optimize($iteration, $id = '', $auto = 0, $meta = array())
    {
        $abort = get_option("iowd_abort");
        if (!$abort) {
            $images_data = get_option("iowd_image_temp_data_" . $id);
            if ($this->is_single == true) {
                $images_data = $images_data[0];
            } else {
                $images_data = $images_data[$iteration];
            }

            if (empty($images_data) === false) {

                $options = json_decode(get_option(IOWD_PREFIX . "_options"), true);
                $s3 = new IOWD_Amazon_S3($id);

                $data = array();
                $response = array();
                $post_ids = array();
                for ($i = 0; $i < count($images_data); $i++) {
                    $image_data = $images_data[$i];
                    if ($options["keep_originals"] == 1) {
                        $this->keep_originals($image_data["file"]);
                    }
                    // if enable resizing
                    if ($options["enable_resizing"] == 1 || $options["enable_resizing_other"] == 1) {
                        $resize_data = $this->resize($image_data, $meta);
                        $image_data = $resize_data[0];
                        $meta = $resize_data[1];
                    }

                    // upload files to s3
                    $response[] = $s3->upload($image_data["file"]);

                    $data[$i] = array(
                        "path"        => $image_data["path"],
                        "file"        => $image_data["file"],
                        "transparent" => $image_data["transparent"],
                        "post_id"     => $image_data["post_id"],
                        "size"        => $image_data["size"],
                        "image_size"  => $image_data["image_size"],
                    );
                    $post_ids[] = $image_data["post_id"];
                }

                //update image count data
                $iowd_images_count_start = (int)get_transient("iowd_images_count_start");
                $iowd_images_count_start += count($images_data);
                set_transient("iowd_images_count_start", $iowd_images_count_start);

                //update post ids data
                $db_post_ids = get_transient("iowd_optimizing_post_ids") ? get_transient("iowd_optimizing_post_ids") : array();
                $db_post_ids = array_merge($db_post_ids, $post_ids);
                $db_post_ids = array_unique($db_post_ids);
                set_transient("iowd_optimizing_post_ids", $db_post_ids);

                GuzzleHttp\Promise\settle($response)->wait();
                $response = $this->api_call_ajax($data, $s3->folder_name, $s3->sub_folder_name, $s3->credentials, $iteration, $id);
                if (isset($response["status"]) && $response["status"] == "error") {
                    set_transient("iowd_response_error", $response["error"]);
                    delete_transient("iowd_images_count_start");
                    delete_transient("iowd_optimizing_post_ids");
                }
                if ($auto == 0) {
                    return array("iowd_images_count_start" => $iowd_images_count_start, "response" => $response);
                }
            }
        } else {
            delete_option("iowd_abort");
            if ($auto == 0) {
                return array("iowd_images_count_start" => 0, "response" => array("status" => "abort"));
            }
        }

        return $meta;

    }

    public function resize($item, $meta = array())
    {
        $db = new IOWDDB_Class();
        $db->set_table_name("iowd_images");
        $options = json_decode(get_option(IOWD_PREFIX . "_options"), true);
        // do optional resizing and coversions
        if ($item) {
            $iowd_image = new IOWDImage();
            $row = $db->get_row_by_field(array("path" => $item["path"]));
            $resized = $row && $row->resized ? 1 : 0;
            $item["resized"] = $resized;

            if ($resized == 0) {
                $iowd_image->load_image($item["file"]);
                $flag = false;
                list($width) = @getimagesize($item["file"]);
                if ($item["media"] == 1 && $options["resize_media_images_width"] && $options["resize_media_images_height"] && $width > $options["resize_media_images_width"] && $options["enable_resizing"] == 1 && $item["size"] == "full") {
                    $iowd_image->resize($options["resize_media_images_width"], $options["resize_media_images_height"]);
                    $flag = true;
                } else if ($item["media"] == 0 && $options["resize_other_images_width"] && $options["resize_other_images_height"] && $width > $options["resize_other_images_width"] && $options["enable_resizing_other"] == 1) {
                    $iowd_image->resize($options["resize_other_images_width"], $options["resize_other_images_height"]);
                    $flag = true;
                }

                if ($flag == true) {
                    $item["resized"] = 1;
                    list($width, $height) = @getimagesize($item["file"]);
                    if (!$meta) {
                        $_wp_attachment_metadata = get_post_meta($item["post_id"], "_wp_attachment_metadata");
                        $_wp_attachment_metadata = $_wp_attachment_metadata[0];
                        $_wp_attachment_metadata["width"] = $width;
                        $_wp_attachment_metadata["height"] = $height;
                        update_post_meta($item["post_id"], "_wp_attachment_metadata", $_wp_attachment_metadata);
                    } else {
                        $meta["width"] = $width;
                        $meta["height"] = $height;
                    }
                }

            }

        }

        return array($item, $meta);
    }

    public function keep_originals($file)
    {
        $filename = basename($file);
        $dest = str_replace($filename, "", $file);
        $copy = true;
        if (!file_exists($dest . "/.iowd_orig")) {
            mkdir($dest . "/.iowd_orig");
        } else {
            if (file_exists($dest . "/.iowd_orig/" . $filename)) {
                $copy = false;
            }
        }
        if ($copy) {
            copy($dest . $filename, $dest . "/.iowd_orig/" . $filename);
        }

    }

    public function api_call_ajax($data, $folder_name, $sub_folder_name, $credentials, $iteration, $id)
    {

        if ($data) {
            // make api call
            $wd_options = json_decode(get_option(IOWD_PREFIX . "_options"), true);
            $options = array(
                "keep_exif_data"                     => $wd_options["keep_exif_data"],
                "exclude_full_size_metadata_removal" => $wd_options["exclude_full_size_metadata_removal"],
                "jpg_optimization_levels"            => $wd_options["jpg_optimization_levels"],
                "png_optimization_levels"            => $wd_options["png_optimization_levels"],
                "gif_optimization_levels"            => $wd_options["gif_optimization_levels"],
            );

            $request_data = array(
                "domain"          => site_url(),
                "folder_name"     => $folder_name,
                "sub_folder_name" => $sub_folder_name,
                "options"         => $options,
                "images_data"     => $data,
                "credentials"     => $credentials,
                "iteration"       => $iteration,
                "id"              => $id,
            );
            $headers = array(
                //'accept'       => 'application/json',
                //'content-type' => 'application/json',
            );

            IOWD_Api::set_post_headers_data($headers);
            IOWD_Api::set_post_data($request_data);
            IOWD_Api::set_api_action("compress");
            $response = IOWD_Api::api_request();

            return $response;
        }

        return false;
    }


    public function save_data_to_db($body, $iterator, $post_id = '')
    {
        global $wpdb;
        $options = json_decode(get_option(IOWD_PREFIX . "_options"), true);

        $request = get_option("iowd_image_temp_data_" . $post_id);
        $request = isset($request[$iterator]) ? $request[$iterator] : array();
        $_image_size = 0;
        $_image_orig_size = 0;
        $_image_count = 0;

        for ($i = 0; $i < count($request); $i++) {
            $request_row = $request[$i];
            $response_row = isset($body[$request_row["post_id"] . ":" . $request_row["size"]]) ? $body[$request_row["post_id"] . ":" . $request_row["size"]] : false;

            if ($response_row === false) {
                continue;
            }
            $existing_row = $wpdb->get_row("SELECT id, image_orig_size, image_size,already_optimized FROM " . $wpdb->prefix . "iowd_images WHERE path='" . $request_row["path"] . "' AND post_id='" . $request_row["post_id"] . "'");

            $type = strtolower(pathinfo($request_row["path"], PATHINFO_EXTENSION));
            if ($existing_row && $existing_row->image_size == $response_row["image_size"] && $type != "webp") {
                continue;
            }

            if ($options["enable_conversion"] == 1 && !($request_row["media"] == 0 && strpos($request_row["path"], "photo-gallery"))) {
                $request_row = $this->convert($request_row);
            }

            $image_size = isset($request_row["converted_image_size"]) ? $request_row["converted_image_size"] : ((int)$response_row["image_size"] > (int)$request_row["image_size"] ? $request_row["image_size"] : $response_row["image_size"]);

            $row_data = array(
                "post_id"       => $request_row["post_id"],
                "size"          => $request_row["size"],
                "path"          => $request_row["path"],
                "image_size"    => $image_size,
                "status"        => $response_row["status"],
                "updated_date"  => date("Y-m-d H:i:s"),
                "media"         => $request_row["media"],
                "resized"       => $request_row["resized"],
                "converted"     => $request_row["converted"],
            );
            $types = array("%s", "%s", "%s", "%d", "%s", "%s", "%d", "%d", "%d");
            $optimization_type = $this->get_current_opt_level($request_row["path"]);
            if (!$existing_row) {
                $row_data["already_optimized"] = $optimization_type;
                $row_data["image_orig_size"] = $request_row["image_size"];

                $img_orig_size = $request_row["image_size"];
                array_push($types, "%s", "%d");
                // insert to db
                $wpdb->insert($wpdb->prefix . "iowd_images", $row_data, $types);
            } else {
                $row_data["already_optimized"] = $existing_row->already_optimized . "," . $optimization_type;
                array_push($types, "%s");
                $img_orig_size = $existing_row->image_orig_size;
                // update db
                $wpdb->update($wpdb->prefix . "iowd_images", $row_data, array("id" => $existing_row->id), $types);
            }
            $_image_size += $row_data["image_size"];
            $_image_orig_size += $img_orig_size;
            $_image_count += 1;
        }

        $last_optimized_data = get_site_transient("iowd_last_optimized_data");
        $last_optimized_data = $last_optimized_data ? json_decode($last_optimized_data, true) : array("image_size" => 0, "image_orig_size" => 0, "image_count" => 0);

        $last_optimized_data["image_size"] = $last_optimized_data["image_size"] + $_image_size;
        $last_optimized_data["image_orig_size"] = $last_optimized_data["image_orig_size"] + $_image_orig_size;
        $last_optimized_data["image_count"] = $last_optimized_data["image_count"] + $_image_count;

        $already_optimized = get_site_transient("iowd_already_optimized");
        set_site_transient("iowd_already_optimized", ($already_optimized + $_image_count), 43200);

        set_site_transient("iowd_last_optimized_data", json_encode($last_optimized_data));


    }


    public function convert(&$item)
    {
        require_once IOWD_DIR_CLASSES . "/iowdimage.php";
        require_once IOWD_DIR_INCLUDES . "/iowd-util.php";
        $options = json_decode(get_option(IOWD_PREFIX . "_options"), true);

        $iowd_image = new IOWDImage();
        $original_file = $item["file"];

        $mime_type = strtolower(pathinfo($item["file"], PATHINFO_EXTENSION));
        $flag = false;
        $iowd_image->load_image($original_file);
        if (($options["jpg_to_png"] == 1 && ($mime_type == "jpg" || $mime_type == "jpeg")) || ($options["gif_to_png"] == 1 && $mime_type == "gif")) {
            $iowd_image->convert_to_png();
            $flag = "png";

        } else if ($options["png_to_jpg"] == 1 && $mime_type == "png") {
            $iowd_image->convert_to_jpg();
            $flag = "jpg";
        } else if (($options["jpg_to_webp"] == 1 && ($mime_type == "jpg" || $mime_type == "jpeg")) || ($options["png_to_webp"] == 1 && $mime_type == "png")) {
            $iowd_image->convert_to_webp();
            $flag = "webp";

        }

        if ($flag) {
            $item["converted"] = 1;
            if ($item["media"] == 1) {
                if ($item["size"] == "full") {
                    global $wpdb;
                    // Update the post into the database
                    $wpdb->query("UPDATE wp_posts SET guid = CONCAT(TRIM(TRAILING '" . $mime_type . "' FROM guid), '" . $flag . "'),

                         post_mime_type = 'image/" . $flag . "'  WHERE ID='" . $item["post_id"] . "'");


                    // Update the post meta into the database
                    $_wp_attached_file = get_post_meta($item["post_id"], "_wp_attached_file");
                    $_wp_attached_file = $_wp_attached_file[0];
                    $updated__wp_attached_file = IOWD_Util::str_lreplace($mime_type, $flag, $_wp_attached_file);
                    update_post_meta($item["post_id"], "_wp_attached_file", $updated__wp_attached_file);

                    $_wp_attachment_metadata = get_post_meta($item["post_id"], "_wp_attachment_metadata");
                    $_wp_attachment_metadata = $_wp_attachment_metadata[0];
                    $_wp_attachment_metadata["file"] = IOWD_Util::str_lreplace($mime_type, $flag, $_wp_attachment_metadata["file"]);
                    update_post_meta($item["post_id"], "_wp_attachment_metadata", $_wp_attachment_metadata);

                } else {
                    $_wp_attachment_metadata = get_post_meta($item["post_id"], "_wp_attachment_metadata");
                    $_wp_attachment_metadata = $_wp_attachment_metadata[0];
                    $_wp_attachment_metadata["sizes"][$item["size"]]["file"] = IOWD_Util::str_lreplace($mime_type, $flag, $_wp_attachment_metadata["sizes"][$item["size"]]["file"]);
                    $_wp_attachment_metadata["sizes"][$item["size"]]["mime-type"] = "image/" . $flag;
                    update_post_meta($item["post_id"], "_wp_attachment_metadata", $_wp_attachment_metadata);

                }
            }

            $image_name = str_replace("." . $mime_type, "", basename($item["file"]));
            $converted_path = str_replace(basename($item["path"]), "", $item["path"]) . $image_name . "." . $flag;

            $converted_file = str_replace(basename($item["file"]), "", $item["file"]) . $image_name . "." . $flag;

            $converted_image_size = filesize($converted_file);

            $item["converted_image_size"] = $converted_image_size;
            $item["path"] = $converted_path;


            unlink($original_file);
        }

        return $item;

    }


}
