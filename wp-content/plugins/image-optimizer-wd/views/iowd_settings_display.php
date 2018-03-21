<?php
if (!defined('ABSPATH')) {
    exit;
}


require_once IOWD_DIR_VIEWS . '/iowd_dir_tree_display.php';
require_once IOWD_DIR_VIEWS . '/iowd_how_it_works_display.php';

?>
<div
        class="iowd_msg_div iowd_msg_div_msg <?php echo $msg_class; ?>" <?php echo $msg_style; ?> >
    <div
            class="iowd_msg_div_text">
        <?php echo $msg; ?>
    </div>
    <div
            class="iowd_msg_div_close">
        ×
    </div>
</div>

<form
        method="post"
        id="settings_form"
        action="">
    <?php wp_nonce_field('nonce_' . IOWD_PREFIX, 'nonce_' . IOWD_PREFIX); ?>
    <div
            class="iowd_header">
        <div
                class="iowd_header_cells">
            <div
                    class="iowd_actions">
                <div
                        class="iowd_actions_tabs">
                    <div
                            class="iowd_tab iowd_tab_standart <?php echo $mode == "standart" ? "iowd_tab_active" : ""; ?>">
                        <a href="admin.php?page=iowd_settings&iowd_mode=standart">
                            <?php _e("Easy mode", IOWD_PREFIX); ?>
                        </a>
                    </div>
                    <div
                            class="iowd_tab iowd_tab_advanced <?php echo $mode == "advanced" ? "iowd_tab_active" : ""; ?>">
                        <a href="admin.php?page=iowd_settings&iowd_mode=advanced"><?php _e("Advanced", IOWD_PREFIX); ?></a>
                    </div>
                </div>
                <div
                        class="iowd_actions_content">
                    <div
                            class="iowd_quick_settings">
                        <div
                                class="iowd_quick_settings_row">
                            <div
                                    class="iowd_quick_settings_cell">
                                <?php if ($mode == "standart") { ?>
                                    <a class="iowd-how-works"
                                       href="#"><?php _e("How it works?", IOWD_PREFIX); ?></a>
                                <?php } ?>
                            </div>
                            <div
                                    class="iowd_quick_settings_cell">
                                <label
                                        title="<?php _e("Automatically optimize the images on upload.", IOWD_PREFIX); ?>"><?php _e("Auto optimize", IOWD_PREFIX); ?></label>
                                <label
                                        class="iowd-switch">
                                    <input
                                            type="checkbox"
                                            class="iowd_quick_settings_el iowd_quick_automatically_optimize"
                                            name="automatically_optimize"
                                            value="1" <?php echo $options["automatically_optimize"] == 1 ? "checked" : ""; ?>>
                                    <div
                                            class="iowd-slider"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div
                            class="iowd_optimizing_msg">
                        <?php _e("Please don't leave the page while uploading.", IOWD_PREFIX); ?>
                    </div>
                    <?php if ($mode == "standart") { ?>
                        <div
                                class="iowd-standart-mode-view iowd-standart-mode-view1">
                            <div
                                    class="iowd-standart-cell <?php echo $standart_setting == "conservative" ? "iowd-standart-cell-active" : ""; ?>"
                                    data-value="conservative"><?php _e("CONSERVATIVE", IOWD_PREFIX); ?></div>
                            <div
                                    class="iowd-standart-cell <?php echo $standart_setting == "balanced" ? "iowd-standart-cell-active" : ""; ?>"
                                    data-value="balanced"><?php _e("BALANCED", IOWD_PREFIX); ?></div>
                            <div
                                    class="iowd-standart-cell iowd-btn-disabled"
                                    data-value="extreme"
                                    title="<?php _e("This option is disabled in free version.", IOWD_PREFIX); ?>">
                                <?php _e("EXTREME", IOWD_PREFIX); ?>
                            </div>
                        </div>
                        <input
                                type="hidden"
                                value=""
                                name="standard_setting">

                    <?php }

                    if (get_transient("iowd_optimizing_post_ids") && get_transient("iowd_images_count_start")) { ?>

                        <div
                                class="iowd-optimized-txt">
                            <?php
                            echo '<strong>';
                            echo sprintf(__("%u images are optimizing...", IOWD_PREFIX), get_transient("iowd_images_count_start"));
                            echo '</strong>';
                            ?>
                            <img
                                    src="<?php echo IOWD_URL_IMG . '/spinner.gif'; ?>"
                                    class="iowd-spinner"
                                    style="display:inline-block; vertical-align:sub;"/>
                            <br>
                            <small>
                                <a href="#"
                                   class="iowd-abort"
                                   style="display:inline-block"
                                ><?php _e("Cancel", IOWD_PREFIX); ?>
                                </a>
                            </small>
                        </div>
                        <?php
                    } else {
                        if (empty($attachments) === true) { ?>
                            <div
                                    class="iowd-main iowd-stat-ajax">
                                <span><b>&nbsp;</b></span>
                            </div>
                        <?php } ?>
                        <?php if (empty($attachments) === false) {
                            $attachment_rows = "";
                            $all_images_count = 0;
                            for ($i = 0; $i < count($attachments); $i++) {
                                foreach ($attachments[$i] as $attachment) {
                                    $attachment_rows .= '<div class="attachment-row attachment-row-' . $attachment["post_id"] . '">';
                                    $attachment_rows .= basename($attachment["path"]);
                                    if ($attachment["size"] == "full") {
                                        $attachment_rows .= '<span class="iowd_remove_attachment" data-id="' . $attachment["post_id"] . '"> × </span>';
                                    }
                                    $attachment_rows .= '</div>';
                                    $all_images_count++;
                                }
                            }
                            ?>
                            <div
                                    class="iowd-media-seleced-b">
                                <b><?php echo sprintf(__("You are going to optimize %d (totally %d) media images ", IOWD_PREFIX), count($ids), $all_images_count); ?></b>
                            </div>
                            <div
                                    class="iowd-main iowd-media-seleced">
                                <?php
                                echo $attachment_rows;
                                ?>
                            </div>
                        <?php } ?>
                        <div
                                class="iowd-main">
                            <div class="iowd-butn-ajax-container"
                                 data-attachments="<?php echo empty($attachments) ? 0 : 1; ?>" style="display:none;">
                                <?php
                                if ($limitation["already_optimized"] < $limitation["limit"]) {
                                    $optimize_btn_class = "iowd-btn-primary";
                                    $optimize_btn_id = "iowd_optimizing";
                                    $return_false = "";
                                } else {
                                    $optimize_btn_class = "iowd-btn-disabled";
                                    $optimize_btn_id = "";
                                    $return_false = "onclick='return false;'";
                                }
                                ?>
                                <button
                                        class="iowd-btn <?php echo $optimize_btn_class; ?>"
                                        id="<?php echo $optimize_btn_id; ?>" <?php echo $return_false; ?>>
                                    <?php
                                    _e("Bulk Optimizing", IOWD_PREFIX);
                                    ?>
                                </button>
                                &nbsp;
                                <img
                                        src="<?php echo IOWD_URL_IMG . '/spinner.gif'; ?>"
                                        class="iowd-spinner"
                                        style="display:none; vertical-align:sub;"/>
                                <?php if (empty($attachments) === true) { ?>
                                    <div
                                            class="iowd-help iowd-help-from-media">
                                        <?php _e("You can optimize individual images via your ", IOWD_PREFIX); ?>
                                        <strong><a
                                                    href="<?php echo admin_url("upload.php"); ?>"><?php _e("Media Library", IOWD_PREFIX); ?></a></strong>.
                                    </div>
                                <?php } else {
                                    ?>
                                    <a href="upload.php?page=iowd_settings"
                                       class="iowd-btn iowd-btn-secondary iowd-cancel"
                                       data-cancel-type="<?php echo empty($attachments) === true ? 1 : 0; ?>"><?php _e("Cancel", IOWD_PREFIX); ?></a>
                                    <div
                                            class="iowd-respose-status"></div>
                                <?php } ?>
                            </div>
                            <div
                                    class="iowd-optimized-txt iowd-optimized-ajax-text" style="display:none;">
                            </div>
                            <div
                                    class="iowd-loading-bar">
                                <div
                                        class="iowd-loading-bar-inner"></div>
                            </div>
                            <a href="#"
                               class="iowd-abort">
                                <?php _e("Cancel", IOWD_PREFIX); ?>
                            </a>
                        </div>
                        <?php
                    }
                    if ($mode == "advanced") {
                        require_once(IOWD_DIR_VIEWS . '/iowd_settings_form_display.php');
                    }
                    ?>

                </div>
                <?php if (!is_null($this->photo_gallery_dir)) { ?>
                    <div
                            class="iowd-toggle-container iowd_actions_content iowd_wd_plugins">
                        <div class="iowd-toggle">
                            <h2>
                                <?php _e("Optimize images from Photo Gallery plugin", IOWD_PREFIX); ?>
                                <span class="iowd-toggle-indicator iowd-toggle-open"></span>
                            </h2>
                        </div>
                        <div class="iowd-toggle-body">
                            <label><?php _e("Enable Photo Gallery optimization", IOWD_PREFIX); ?></label>
                            <label
                                    class="iowd-switch">
                                <input
                                        type="checkbox"
                                        class="iowd_quick_settings_el iowd_optimize_gallery"
                                        name="optimize_gallery"
                                        value="1" <?php echo $options["optimize_gallery"] == 1 ? "checked" : ""; ?>>
                                <div
                                        class="iowd-slider"></div>
                            </label>
                            <img
                                    class="iowd_update_dirs"
                                    src="<?php echo IOWD_URL_IMG . "/reset.png"; ?>"
                                    title="<?php _e("Update data", IOWD_PREFIX); ?>">
                            <img
                                    src="<?php echo IOWD_URL_IMG; ?>/spinner.gif"
                                    class="iowd-spinner-select"
                                    style="display:none; vertical-align:sub;"/>
                            <div
                                    class="iowd-dir-gallery-paths"></div>
                            <input type="hidden" name="gallery_upload_dir"
                                   value="<?php echo $this->photo_gallery_dir; ?>">
                        </div>
                    </div>
                <?php } ?>
                <div
                        class="iowd-toggle-container iowd_actions_content iowd_other_dirs_container">
                    <div class="iowd-toggle">
                        <h2>
                            <?php _e("Other directories", IOWD_PREFIX); ?>
                            <span class="iowd-toggle-indicator iowd-toggle-open"></span>
                        </h2>
                    </div>
                    <div class="iowd-toggle-body">
                        <p><?php _e("Optimize images from directories other than the WordPress media library. Simply add any directories you wish to optimize.", IOWD_PREFIX); ?></p>
                        <button
                                class="iowd-open-dir-tree-btn iowd-btn iowd-btn-small iowd-btn-primary"><?php _e("Select Directory", IOWD_PREFIX); ?></button>
                        <img
                                class="iowd_update_dirs"
                                src="<?php echo IOWD_URL_IMG . "/reset.png"; ?>"
                                title="<?php _e("Update data", IOWD_PREFIX); ?>">
                        <img
                                src="<?php echo IOWD_URL_IMG; ?>/spinner.gif"
                                class="iowd-spinner-select"
                                style="display:none; vertical-align:sub;"/>
                        <div
                                class="iowd-dir-paths"></div>
                        <input
                                type="hidden"
                                name="other_folders"
                                value="<?php echo stripslashes($options["other_folders"]); ?>">
                    </div>
                </div>
            </div>
            <div
                    class="iowd_stat">
                <div
                        class="iowd-toggle-container iowd_stat_content">
                    <div class="iowd-toggle">
                        <h2>
                            <?php _e("Statistics", IOWD_PREFIX); ?>
                            <span class="iowd-toggle-indicator iowd-toggle-open"></span>
                        </h2>
                    </div>
                    <div class="iowd-toggle-body">
                        <div
                                class="iowd_stat-row">
                            <div
                                    class="iowd-stat-progress-bar">
                                <div
                                        class="iowd-stat-progress-bar-inner"></div>
                            </div>
                            <div
                                    class="iowd-stat-ratio"></div>
                        </div>
                        <div
                                class="iowd_stat-row">
                            <div
                                    class="iowd_stat-cell">
                                <b><?php _e("Last optimization", IOWD_PREFIX); ?></b>
                            </div>
                            <div
                                    class="iowd_stat-cell">
                                <b><?php echo "<span class='iowd_total_reduced'>" . IOWD_Util::format_bytes($last_optimized_data_reduced) . "</span> ( <span class='iowd_total_reduced_persent'>" . $last_optimized_data_reduced_percent . "</span>% )"; ?></b>
                            </div>
                        </div>
                        <div
                                class="iowd_stat-row">
                            <div
                                    class="iowd_stat-cell">
                                <b><?php _e("Media library total reduced", IOWD_PREFIX); ?></b>
                            </div>
                            <div
                                    class="iowd_stat-cell">
                                <b><?php echo "<span class='iowd_total_reduced'>" . IOWD_Util::format_bytes($stat["total_reduced"]) . "</span> ( <span class='iowd_total_reduced_persent'>" . $stat["total_reduced_persent"] . "</span>% )"; ?></b>
                            </div>
                        </div>
                        <div
                                class="iowd_stat-row">
                            <div
                                    class="iowd_stat-cell">
                                <b><?php _e("Other directories total reduced ", IOWD_PREFIX); ?></b>
                            </div>
                            <div
                                    class="iowd_stat-cell">
                                <b><?php echo "<span class='iowd_total_reduced_other'>" . IOWD_Util::format_bytes($stat["total_reduced_other"]) . "</span> ( <span class='iowd_total_reduced_persent_other'>" . $stat["total_reduced_persent_other"] . "</span>% )"; ?></b>
                            </div>
                        </div>
                        <div
                                class="iowd_stat-row">
                            <div
                                    class="iowd_stat-cell">
                                <b><?php _e("Total", IOWD_PREFIX); ?></b>
                            </div>
                            <div
                                    class="iowd_stat-cell">
                                <b><?php echo "<span class='iowd_total'>" . IOWD_Util::format_bytes($stat["total"]) . "</span> ( <span class='iowd_total_persent'>" . $stat["total_persent"] . "</span>% )"; ?></b>
                            </div>
                        </div>
                    </div>

                </div>
                <?php
                require_once IOWD_DIR_VIEWS . "/iowd_limit_display.php";
                ?>
            </div>
        </div>
    </div>


    <input
            type="hidden"
            name="iowd_tabs_active"
            id="iowd_tabs_active"
            value="<?php echo $iowd_tabs_active; ?>">
    <input
            type="hidden"
            name="ids"
            id="ids"
            value="<?php echo implode(",", $ids); ?>">
    <input
            type="hidden"
            name="action"
            id="action"
            value="save_settings">
</form>
