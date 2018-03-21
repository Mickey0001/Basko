<?php
if (!defined('ABSPATH')) {
    exit;
}
$content_dir_path = get_home_path();
?>

<div
    class="iowd iowd-popup">
    <div
        class="iowd-opacity"></div>
    <div
        class="iowd_other_folders_container">
        <div
            class="iowd_folders">
            <div
                class="header">
                <div
                    class="iowd-popup-close">
                    ×
                </div>
                <h2>
                    <?php _e("Select directory", IOWD_PREFIX); ?>
                </h2>
            </div>
            <div
                class="body-wrap">
                <?php
                echo "<ul>";
                foreach (scandir($content_dir_path) as $file) {
                    if ('.' === $file || '..' === $file || !is_dir($content_dir_path . "/" . $file)) {
                        continue;
                    }

                    echo '<li class="iowd-dir-tree-title" data-path="' . str_replace("\\", "/", $content_dir_path) . "/" . $file . '" ><a href="#" ><img src="' . IOWD_URL_IMG . '/folder.png" class="iowd-dir-tree-icon">' . $file . '</a>';

                }
                echo "</ul>";
                //IOWD_Helper::dir_tree( $content_dir_path ); ?>
            </div>

            <div
                class="iowd-selected_bottom">
                <input
                    type="text"
                    class="iowd-selected-dir"
                    placeholder="<?php _e("Directory path", IOWD_PREFIX); ?>">

                <div
                    class="iowd-select-dir">
                    <button
                        class="iowd-select-dir-btn iowd-btn iowd-btn-small iowd-btn-primary"><?php _e("Select Directory", IOWD_PREFIX); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>



