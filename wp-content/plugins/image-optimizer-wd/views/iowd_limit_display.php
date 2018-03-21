<?php
if (!defined('ABSPATH')) {
    exit;
}

?>

<div
        class="iowd-toggle-container iowd_limit_content">
    <div class="iowd-toggle">
        <h2>
            <?php _e("Your plan", IOWD_PREFIX); ?>
            <img
                    class="iowd_update_alreday_used"
                    src="<?php echo IOWD_URL_IMG . "/reset.png"; ?>" title="<?php _e("Update data", IOWD_PREFIX); ?>">
            <img
                    src="<?php echo IOWD_URL_IMG; ?>/spinner.gif"
                    class="iowd-spinner-select-already-used"
                    style="display:none; vertical-align:sub;"/>
            <span class="iowd-toggle-indicator iowd-toggle-open"></span>
        </h2>
    </div>
    <div class="iowd-toggle-body">
        <?php
        if ($limitation["limit"]) {
            ?>
            <div
                    class="iowd_stat-row">
                <b class="iowd-blue-txt">
                    <?php echo sprintf(__("You can optimize %d images every %s.", IOWD_PREFIX), $limitation["limit"], $limitation["period"]); ?>
                </b>
            </div>
            <div
                    class="iowd_stat-row">
                <div
                        class="iowd_stat-cell">
                    <b>
                        <?php echo __("Already optimized", IOWD_PREFIX); ?>
                    </b>
                </div>
                <div
                        class="iowd_stat-cell">
                    <b class="iowd_already_used_cell">
                        <?php
                        echo '<span class="images_count">' . $limitation["already_optimized"] . '</span> ';
                        echo __("images", IOWD_PREFIX);
                        if ($limitation["already_optimized"] >= $limitation["limit"]) {
                            echo "<span style='color:red'><br>( " . __("Expired", IOWD_PREFIX) . " )</span>";
                        }
                        ?>
                    </b>
                </div>
            </div>
            <div
                    class="iowd_stat-row">
                <div
                        class="iowd_stat-cell">
                    <b>
                        <?php echo __("Remained", IOWD_PREFIX); ?>
                    </b>
                </div>
                <div
                        class="iowd_stat-cell">
                    <b class="iowd_remained_cell">
                        <?php
                        if ($limitation["limit"] - $limitation["already_optimized"] >= 0) {
                            echo '<span class="images_count">' . ($limitation["limit"] - $limitation["already_optimized"]) . '</span> ';
                            echo __("images", IOWD_PREFIX);
                        }
                        ?>
                    </b>
                </div>
            </div>
            <?php
        } else {
            _e("No data.", IOWD_PREFIX);
        }

        ?>
    </div>
</div>