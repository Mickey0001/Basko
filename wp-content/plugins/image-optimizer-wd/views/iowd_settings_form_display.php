<?php
if (!defined('ABSPATH')) {
    exit;
}

?>
<!-- Settings -->
<div
    class="iowd-setings">
    <div>
        <ul class="iowd_tabs iowd_tabs_1 iowd-clear">
            <?php foreach ($this->tabs as $tab_id => $tab) { ?>
                <li>
                    <a href="#<?php echo $tab_id; ?>"><?php echo $tab["name"]; ?></a>
                </li>
            <?php } ?>
        </ul>
    </div>
    <div
        class="iowd_tabs_container iowd_tabs_container_1">
        <?php foreach ($this->tabs as $tab_id => $tab) { ?>
            <div
                id="<?php echo $tab_id; ?>"
                class="iowd_tabs_container_item">
                <div
                    class="iowd-table">
                    <?php if ($tab["fields"]) { ?>
                        <?php foreach ($tab["fields"] as $field_id => $field) { ?>
                            <div
                                class="iowd-table-row">
                                <div
                                    class="iowd-table-cell">
                                    <label
                                        for="<?php echo $field_id; ?>"
                                        title="<?php echo $field["tooltip"]; ?>"><?php echo $field["label"]; ?>:
                                        </label>
                                </div>
                                <div
                                    class="iowd-table-cell">
                                    <?php if ($field["type"] == "text") { ?>
                                        <input
                                            type="text"
                                            name="<?php echo $field_id; ?>"
                                            id="<?php echo $field_id; ?>"
                                            value="<?php echo $options[$field_id]; ?>"
                                            class="<?php echo $field["classes"]; ?>"
                                            <?php echo $field["attr"]; ?> >
                                    <?php } else if ($field["type"] == "select") { ?>
                                        <select
                                            name="<?php echo $field_id; ?>"
                                            id="<?php echo $field_id; ?>"
                                            class="<?php echo $field["classes"]; ?>"
                                            <?php echo $field["attr"]; ?> >
                                            <?php foreach ($field["choices"] as $option) {
                                                $selected = $options[$field_id] == $option["value"] ? "selected" : ""; ?>
                                                <option
                                                    <?php echo isset($option["attr"]) ? $option["attr"] : ""; ?>
                                                    value="<?php echo $option["value"]; ?>" <?php echo $selected; ?>><?php echo $option["label"]; ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } else if ($field["type"] == "radio") { ?>
                                        <?php for ($i = 0; $i < count($field["choices"]); $i++) {
                                            $option = $field["choices"][$i];
                                            $checked = $options[$field_id] == $option["value"] ? "checked" : ""; ?>
                                            <input
                                                type="radio"
                                                name="<?php echo $field_id; ?>"
                                                id="<?php echo $field_id . $i; ?>"
                                                value="<?php echo $option["value"]; ?>"
                                                class="<?php echo $option["classes"]; ?>"
                                                <?php echo $option["attr"]; ?>
                                                <?php echo $checked; ?> >
                                            <label
                                                for="<?php echo $field_id . $i; ?>"><?php echo $option["label"]; ?></label>
                                        <?php } ?>

                                    <?php } else if ($field["type"] == "custom" && ($field_id == "resize_media_images" || $field_id == "resize_other_images")) { ?>
                                        <label
                                            for="<?php echo $field_id; ?>_width"><?php _e("Width", IOWD_PREFIX); ?></label>
                                        <input
                                            type="text"
                                            name="<?php echo $field_id; ?>_width"
                                            id="<?php echo $field_id; ?>_width"
                                            class="iowd-elem-80"
                                            value="<?php echo $options[$field_id . "_width"]; ?>">
                                        <label
                                            for="<?php echo $field_id; ?>_height"><?php _e("Height", IOWD_PREFIX); ?></label>
                                        <input
                                            type="text"
                                            name="<?php echo $field_id; ?>_height"
                                            id="<?php echo $field_id; ?>_height"
                                            class="iowd-elem-80"
                                            value="<?php echo $options[$field_id . "_height"]; ?>">

                                    <?php } else if ($field["type"] == "custom" && $field_id == "optimize_thumbs") {
                                        foreach ($iowd_sizes as $size => $value) {
                                            ?>
                                            <div>
                                                <input
                                                    type="checkbox"
                                                    class="wp_sizes"
                                                    value="<?php echo $size; ?>"
                                                    id="wp_<?php echo $size; ?>" <?php echo in_array($size, explode(",", $options["optimize_thumbs"])) ? "checked" : ""; ?>>
                                                <label
                                                    for="wp_<?php echo $size; ?>">
                                                    <?php echo $size . " (" . $value["width"] . "X" . $value["height"] . ")"; ?>
                                                </label>
                                            </div>

                                        <?php } ?>
                                        <input
                                            type="hidden"
                                            name="optimize_thumbs"
                                            value="<?php echo $options["optimize_thumbs"]; ?>">
                                    <?php } ?>
                                    <!-- help text -->
                                    <?php if ($field["help_text"] != "") { ?>
                                        <br>
                                        <small><?php echo $field["help_text"]; ?></small>
                                    <?php } ?>
                                    <?php if (isset($field["pro_text"]) && $field["pro_text"] != "") { ?>
                                        <br>
                                        <small class="iowd-pro"><?php echo $field["pro_text"]; ?></small>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php }
                    }
                    ?>
                </div>
            </div>

        <?php } ?>
    </div>
    <div
        class="iowd_save_btn">
        <button
            class="iowd-btn iowd-btn-primary"
            onclick="save_settings();"><?php _e("Save Settings", IOWD_PREFIX); ?></button>
    </div>
</div>




