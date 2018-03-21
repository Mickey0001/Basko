<?php
if (!defined('ABSPATH')) {
    exit;
}

?>

<div
    class="iowd">
    <h2><?php _e("REPORT", IOWD_PREFIX); ?></h2>
    <div
        class="iowd_reports_container">
        <div
            class="iowd-bulk">
            <button
                class="iowd-clear-history iowd-clear-history-bulk"><?php _e("Clear history", IOWD_PREFIX); ?></button>
        </div>
        <div
            class="iowd_reports_filters">
            <table>
                <tr>
                    <td><?php _e("File name", IOWD_PREFIX); ?>
                        :&nbsp;</td>
                    <td><?php _e("Date range", IOWD_PREFIX); ?>
                        :&nbsp;</td>
                    <td><?php _e("Size range", IOWD_PREFIX); ?>
                        :&nbsp;</td>
                    <td><?php _e("File type", IOWD_PREFIX); ?>
                        :&nbsp;</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <input
                            type="text"
                            placeholder="<?php _e("Filter by name", IOWD_PREFIX); ?>"
                            class="iowd-elem-250 iowd-filter-elem"
                            name="path"
                            value="<?php echo isset($_POST["path"]) ? $_POST["path"] : ""; ?>">
                    </td>
                    <td>
                        <input
                            type="text"
                            id="start_date"
                            name="start_date"
                            value="<?php echo isset($_POST["start_date"]) ? $_POST["start_date"] : ""; ?>"
                            class="iowd-elem-110 iowd-filter-elem">
                        <input
                            class="calendar_button "
                            type="reset"
                            onclick="return showCalendar('start_date','%Y-%m-%d');"
                            value=""/>
                        <label>
                            &nbsp;-&nbsp;</label>
                        <input
                            type="text"
                            id="end_date"
                            name="end_date"
                            value="<?php echo isset($_POST["end_date"]) ? $_POST["end_date"] : ""; ?>"
                            class="iowd-elem-110 iowd-filter-elem">
                        <input
                            class="calendar_button"
                            type="reset"
                            onclick="return showCalendar('end_date','%Y-%m-%d');"
                            value=""/>
                    </td>
                    <td>
                        <select
                            id="size_from"
                            name="size_from"
                            class="iowd-filter-elem">
                            <option
                                value=""><?php _e("Larger than", IOWD_PREFIX); ?></option>
                            <?php
                            foreach ($data["sizes"] as $key => $value) {
                                $size_from = isset($_POST["size_from"]) ? $_POST["size_from"] : "";
                                echo '<option value="' . $key . '" ' . selected($size_from, $key, false) . '>' . $value . '</option>';
                            }
                            ?>
                        </select>
                        <label>
                            &nbsp;-&nbsp;</label>
                        <select
                            id="size_to"
                            name="size_to"
                            class="iowd-filter-elem">
                            <option
                                value=""><?php _e("Smaller than", IOWD_PREFIX); ?></option>
                            <?php
                            foreach ($data["sizes"] as $key => $value) {
                                $size_to = isset($_POST["size_to"]) ? $_POST["size_to"] : "";
                                echo '<option value="' . $key . '" ' . selected($size_to, $key, false) . '>' . $value . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <select
                            name="type"
                            id="type"
                            class="iowd-filter-elem">
                            <?php
                            $type = isset($_POST["type"]) ? $_POST["type"] : "";
                            foreach ($file_types as $file_type => $file_type1) {
                                $selected = $type == $file_type ? "selected" : "";
                                echo '<option value="' . $file_type . '" ' . $selected . '>' . $file_type1 . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <img
                            src="<?php echo IOWD_URL_IMG; ?>/search.png"
                            class="iowd_report_search"/>
                    </td>
                    <td>
                        <img
                            src="<?php echo IOWD_URL_IMG; ?>/reset.png"
                            class="iowd_report_reset"/>
                    </td>
                </tr>

            </table>
        </div>
        <div
            class="iowd_reports">
            <img
                src="<?php echo IOWD_URL_IMG . "/spinner.gif"; ?>"
                class="iowd-report-loader">
            <div
                class="iowd_reports_table"
                id="iowd_reports_table">
                <div
                    class="iowd_reports_table_tbody">
                    <?php require_once IOWD_DIR_VIEWS . '/iowd_report_tbody_display.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>



