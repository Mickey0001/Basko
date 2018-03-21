<?php
if (!defined('ABSPATH')) {
    exit;
}


?>

<div
    class="iowd-popup-help">
    <div
        class="iowd-opacity"></div>
    <div
        class="iowd iowd-how-works-container">
        <div
            class="iowd-popup-help-close">
            ×
        </div>
        <div
            class="iowd-standart-mode-view iowd-standart-mode-view-help">
            <div
                class="iowd-standart-cell iowd-standart-cell-active"
                data-value="conservative"><?php _e("CONSERVATIVE", IOWD_PREFIX); ?></div>
            <div
                class="iowd-standart-cell"
                data-value="balanced"><?php _e("BALANCED", IOWD_PREFIX); ?></div>
            <div
                class="iowd-standart-cell "
                data-value="extreme"><?php _e("EXTREME", IOWD_PREFIX); ?></div>
        </div>

        <div
            class="iowd-compare">
            <div
                class="iowd-compare-cell">
                <h2><?php _e("Original", IOWD_PREFIX); ?></h2>
                <img
                    src="<?php echo IOWD_URL_IMG . "/help.jpg"; ?>"
                    width="100%">
                <div>
                    757
                    KB
                </div>
            </div>
            <div
                class="iowd-compare-cell">
                <h2><?php _e("Optimized", IOWD_PREFIX); ?></h2>
                <img
                    src="<?php echo IOWD_URL_IMG . "/helpconservative.jpg"; ?>"
                    class="iowd-optimized-img"
                    width="100%">
                <div
                    class="iowd-help-optimized">
                    749
                    KB
                </div>
            </div>
        </div>
        <div
            class="iowd-help-stat-container">
            <div
                class="iowd-help-stat">
                <h3><?php _e("Reduced by", IOWD_PREFIX); ?>
                    <span
                        class="iowd-stat-val">8 KB (4.65%)</span>
                </h3>
                <p>
                    <img
                        src="<?php echo IOWD_URL_IMG . "/plus.png"; ?>"
                        class="iowd-help-type">
                    <span
                        class="iowd-help-type-txt"><?php _e("Light reduction", IOWD_PREFIX); ?></span>
                    <span
                        class="iowd-help-up-to"> <?php _e("up to", IOWD_PREFIX); ?>
                        <span
                            class="iowd-percent">20%</span></span>
                </p>
                <p>
                    <img
                        src="<?php echo IOWD_URL_IMG . "/plus.png"; ?>"
                        class="iowd-help-exif">
                    <?php _e("Keeps EXIF data", IOWD_PREFIX); ?>
                </p>
                <p>
                    <img
                        src="<?php echo IOWD_URL_IMG . "/plus.png"; ?>"
                        class="iowd-help-full">
                    <?php _e("Keeps Full-sized images", IOWD_PREFIX); ?>
                </p>
            </div>


        </div>
    </div>
</div>
