<?php
if (!defined('ABSPATH')) {
    exit;
}

class IOWD_MediaLibrary
{

    public function __construct()
    {
        // add bulc action for wd optimize
        add_action('admin_footer', array($this, 'custom_bulk_admin_footer'));
        add_action('admin_action_wd_optimize', array($this, 'wd_optimize_bulk_handler'));

        //wd optimize button to grid view, modal
        add_filter('attachment_fields_to_edit', array($this, 'add_wd_optimize_button'), 10, 2);
        add_filter('manage_media_columns', array($this, 'manage_columns'));
        add_action('manage_media_custom_column', array($this, 'media_wd_optimize_column'), 10, 2);

        add_action('restrict_manage_posts', array($this, 'size_filter'));

        add_filter('posts_where', array($this, 'where_size'));
    }

    /**
     * Adds wd optimize bulk action to media library page
     *
     */
    public function wd_optimize_bulk_handler()
    {

        if ((empty($_REQUEST['action']) || 'wd_optimize' != $_REQUEST['action'])) {
            return false;
        }
        if (empty($_REQUEST['media']) || !is_array($_REQUEST['media'])) {
            return false;
        }
        check_admin_referer('bulk-media');

        $ids = implode(",", array_map('intval', $_REQUEST['media']));

        IOWD_Helper::redirect(array("page" => IOWD_PREFIX . "_settings", "ids" => $ids));
        exit();

    }

    /**
     * Adds wd optimize button to attachement modal and media grid view
     *
     */
    public function add_wd_optimize_button($form_fields, $post)
    {
        if (!wp_attachment_is_image($post->ID)) {
            return $form_fields;
        }

        $form_fields['wd_optimize'] = array(
            'label'         => __('Optimize', IOWD_PREFIX),
            'input'         => 'html',
            'html'          => $this->wd_optimize_html($post->ID),
            'show_in_edit'  => true,
            'show_in_modal' => true,
        );


        return $form_fields;
    }

    public function wd_optimize_html($id)
    {
        $db = new IOWDDB();
        $db->set_table_name("iowd_images");
        $limitation = IOWD_Helper::limitation();
        global $wpdb;
        $row = $wpdb->get_row("SELECT COUNT(case when image_orig_size = image_size then null else 1 end) AS images_count, SUM(image_orig_size) AS image_orig_size, SUM(image_size) AS image_size, path  FROM " . $wpdb->prefix . "iowd_images WHERE post_id='" . $id . "'  GROUP BY post_id");

        $path = get_attached_file($id);
        $options = json_decode(get_option(IOWD_PREFIX . "_options"), true);
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $db_post_ids = get_transient("iowd_optimizing_post_ids") ? get_transient("iowd_optimizing_post_ids") : array();
        if ($row) {
            $reduse = $row->image_orig_size - $row->image_size;
            $redused_persent = $row->image_orig_size ? ($reduse / $row->image_orig_size) * 100 : 0;
            $sizes = IOWD_Helper::get_attachmnet_sizes($id);
            $html = '<div class="iowd_' . $id . '">
						<div><a href="#" onclick="iowdStatus(this);return false;" data-id="' . $id . '"><small>' . __("Stats", IOWD_PREFIX) . ' [+]</small></a></div>
						<div>' . $row->images_count . " " . __("images optimized by ", IOWD_PREFIX) . IOWD_Util::format_bytes($reduse) . ' ( ' . number_format($redused_persent, 2) . '% )</div>
						<div>Size: ' . IOWD_Util::format_bytes($sizes["total"]) . '</div>';
            if ($type != "webp" && $limitation["already_optimized"] < $limitation["limit"]) {
                $html .= '<a href="#" class="iowd-optimize" data-id="' . $id . '">
							<span>' . __("Reoptimize", IOWD_PREFIX) . '</span>
							<img src="' . IOWD_URL_IMG . '/spinner.gif" class="iowd-spinner" style="display:none; vertical-align:sub;" />
						</a>';
            }
            $html .= '</div>';
        } else {
            if ($limitation["already_optimized"] > $limitation["limit"]) {
                $html = sprintf(__("Your subscription plan allows optimizing %s images per month. This limitation has expired for current month.", IOWD_PREFIX), $limitation["limit"]);
            } else if (($type == "png" && $options["png_optimization_levels"] == "0") || (($type == "jpg" || $type == "jpeg") && $options["jpg_optimization_levels"] == "0") || ($type == "gif" && $options["gif_optimization_levels"] == "0") ) {
                $html = sprintf(__("Please select %s optimization level from ", IOWD_PREFIX), $type);
                $html .= '<a href="upload.php?page=iowd_settings">' . __("Settings", IOWD_PREFIX) . '</a>.';
            } else if (in_array($id, $db_post_ids)) {
                $html = __("Processing optimize", IOWD_PREFIX);
            } else if ($type == "png" || $type == "jpg" || $type == "jpeg" || $type == "gif") {
                $html = '<div class="iowd_' . $id . '"><button class="button button-secondary  iowd-optimize" data-id="' . $id . '">
						<span>' . __("Optimize", IOWD_PREFIX) . '</span>
						<img src="' . IOWD_URL_IMG . '/spinner.gif" class="iowd-spinner" style="display:none; vertical-align:sub;" />
					</button>';

                $html .= '</div>';
            } else {
                $html = '';
            }
        }

        return $html;
    }


    public function size_filter()
    {
        global $pagenow;
        if ($pagenow == 'upload.php') {

            $file_size = filter_input(INPUT_GET, 'size', FILTER_SANITIZE_STRING);
            $file_size = trim($file_size);
            $file_size = !empty($file_size) ? $file_size : false;

            $sizes = array(
                ""         => __("Larger than", IOWD_PREFIX),
                "500000"   => "0.5MB",
                "1000000"  => "1MB",
                "2000000"  => "2MB",
                "4000000"  => "4MB",
                "8000000"  => "8MB",
                "14000000" => "14MB",
            );
            $html = '<select  name="size" class="postform">';
            foreach ($sizes as $key => $value) {
                $html .= '<option value="' . $key . '" ' . selected($file_size, $key, false) . '>' . $value . '</option>';
            }
            $html .= '</select>';


            echo $html;
        }
    }


    public function where_size($sql)
    {
        global $pagenow, $wpdb;
        if ($pagenow == 'upload.php') {
            $file_size = filter_input(INPUT_GET, 'size', FILTER_SANITIZE_STRING);
            $file_size = trim($file_size);
            $file_size = !empty($file_size) ? $file_size : false;

            // get all attachments
            if ($file_size) {
                $file_size = $file_size * 1000;
                $attachments = get_posts(array(
                    'post_type'   => 'attachment',
                    'post__in'    => array(),
                    'numberposts' => -1
                ));
                $ids = array();
                foreach ($attachments as $attachment) {
                    $size = filesize(get_attached_file($attachment->ID));
                    if ($size > $file_size) {
                        $ids[] = $attachment->ID;
                    }

                }

                $sql .= count($ids) > 0 ? " AND $wpdb->posts.id IN (" . implode(",", $ids) . ") " : " AND 0 ";

            }

        }

        return $sql;
    }


    public function custom_bulk_admin_footer()
    {
        global $pagenow;

        if ($pagenow == 'upload.php' || $pagenow == 'post.php') {
            ?>
            <div
                class="iowd_opacity"></div>
            <div
                class="iowd_stats">
                <div
                    class="iowd_close">
                    ×
                </div>
                <div
                    class="iowd_stats_body"></div>
            </div>

            <script
                type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('<option>').val('wd_optimize').text('<?php _e('Optimize', IOWD_PREFIX)?>').appendTo("select[name='action']");
                });
            </script>
            <style>
                .iowd_opacity, .iowd_stats {
                    position: fixed;
                    top: 0px;
                    left: 0px;
                    bottom: 0px;
                    right: 0px;
                    padding: 23px;
                }

                .iowd_opacity {
                    background: #000;
                    opacity: 0.6;
                    z-index: 9999999;
                    display: none;
                }

                .iowd_stats {
                    margin: auto;
                    z-index: 9999999;
                    background: #fff;
                    width: 570px;
                    height: 300px;
                    opacity: 1;
                    display: none;
                    overflow: auto;
                    max-height: 60%;
                    box-shadow: -1px -1px 13px #32373c;
                    border-radius: 4px;
                }

                .iowd_close {
                    font-size: 18px;
                    cursor: pointer;
                    text-align: right;
                }

                .iowd_stat_table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .iowd_stat_table th, .iowd_stat_table td {
                    text-align: left;
                    padding: 10px;
                    border-bottom: 1px solid #ddd;
                }
            </style>
            <?php
        }
    }

    public function manage_columns($defaults)
    {
        $defaults['wd_optimize'] = __('Optimize', IOWD_PREFIX);

        return $defaults;
    }

    public function media_wd_optimize_column($column_name, $id)
    {
        if ('wd_optimize' == $column_name) {
            echo $this->wd_optimize_html($id);
        }

    }


}

new IOWD_MediaLibrary();
