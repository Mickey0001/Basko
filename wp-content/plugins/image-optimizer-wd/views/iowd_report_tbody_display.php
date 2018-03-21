<?php
if (!defined('ABSPATH')) {
    exit;
}
if ($total_count) {
    $trs = "";
    $protocaol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
    for ($i = 0; $i < count($report_data); $i++) {
        $row = $report_data[$i];

        $mime_type = strtolower(pathinfo($row->path, PATHINFO_EXTENSION));
        $exists = true;
        $path = str_replace($protocaol . '://' . $_SERVER['SERVER_NAME'], $_SERVER['DOCUMENT_ROOT'], $row->path);
        if (file_exists($path)) {
            $image_path = $mime_type == "pdf" ? IOWD_URL_IMG . "/document.png" : $row->path;
        } else {
            //continue;
            $exists = false;
            $image_path = IOWD_URL_IMG . "/no_image.png";
        }
        $reduse = $row->image_orig_size - $row->image_size;
        $redused_persent = number_format((($reduse / $row->image_orig_size) * 100), 2);
        $trs .= '
            <div
                class="main_tr">
                <div
                    class="iowd_reports_table_td">
                    <div
                        class="iowd-report-block">
                        <img
                            src="' . $image_path . '"
                            />
                    </div>
                    <div  class="iowd-report-block">
                        <b class="iowd-padding-left">' . basename($row->path) . '</b>
                    </div>
                    <div
                        class="iowd-report-block">
                        <button
                            class="iowd-clear-history iowd-clear-history-single"
                            data-post-id="' . $row->post_id . '" >' . __("Delete", IOWD_PREFIX) . '</button>
                    </div>

                </div>
                <div class="iowd_reports_table_td">
                    <div
                        class="iowd-report-block">
                        <div
                            class="iowd-blue-txt">' . __("Image path", IOWD_PREFIX) . '</div>
                        <div>' . $row->path . '</div>
                    </div>';
        if ($exists) {
            $trs .= '
                    <div  class="iowd-report-block">
                        <table>
                            <tr>
                                <td>' . __("Original size", IOWD_PREFIX) . '
                                    :
                                </td>
                                <td>' . IOWD_Util::format_bytes($row->image_orig_size) . '</td>
                            </tr>
                            <tr>
                                <td>' . __("Final size", IOWD_PREFIX) . '
                                    :
                                </td>
                                <td>' . IOWD_Util::format_bytes($row->image_size) . '</td>
                            </tr>
                            <tr>
                                <td>' . __("Reduced by", IOWD_PREFIX) . '
                                    :
                                </td>
                                <td>' . IOWD_Util::format_bytes($reduse) . " ( " . $redused_persent . "%)" .
                '</td>
                            </tr>
                        </table>
                    </div>';
        }
        $trs .= '</div>
                <div class="iowd_reports_table_td">
                    <div
                        class="iowd-blue-txt">' . __("Date", IOWD_PREFIX) . '</div>
                    <div>' . date("d F, Y", strtotime($row->date)) . '</div>
                </div>
            </div>';

    }

    ?>
    <div
        class="main_tr">
        <div
            class="iowd_reports_table_td">
            <b class="iowd-padding-left"><?php _e("Total", IOWD_PREFIX); ?></b>
        </div>
        <div
            class="iowd_reports_table_td">
            <div
                class="iowd-report-block">
                <table>
                    <tr>
                        <td>
                            <b><?php _e("Images count", IOWD_PREFIX); ?>
                                :</b>
                        </td>
                        <td>
                            <b><?php echo $total_count; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b><?php _e("Original size", IOWD_PREFIX); ?>
                                :</b>
                        </td>
                        <td>
                            <b><?php echo IOWD_Util::format_bytes($total_orig_size); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b><?php _e("Final size", IOWD_PREFIX); ?>
                                :</b>
                        </td>
                        <td>
                            <b><?php echo IOWD_Util::format_bytes($total_size); ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b><?php _e("Reduced by", IOWD_PREFIX); ?>
                                :</b>
                        </td>
                        <td>
                            <b>
                                <?php
                                $total_reduced = $total_orig_size - $total_size;
                                $total_reduced_persent = $total_orig_size ? number_format((($total_reduced / $total_orig_size) * 100), 2) : "";
                                echo IOWD_Util::format_bytes($total_reduced) . " ( " . $total_reduced_persent . "%)";
                                ?>
                                <b>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <?php
    echo $trs;
    if ($limit < $total_count) {
        ?>

        <div
            class="iowd-report-more-cont">
            <div
                class="iowd-more"
                data-limit="<?php echo $limit; ?>">
                <?php _e("Load more", IOWD_PREFIX); ?>
            </div>
        </div>
        <?php
    }
} else {
    echo "<p style='text-align:center'>" . __("No data", IOWD_PREFIX) . "</p>";
}
?>



