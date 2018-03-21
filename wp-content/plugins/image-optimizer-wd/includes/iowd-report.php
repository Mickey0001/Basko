<?php
if (!defined('ABSPATH')) {
    exit;
}

class IOWD_Report
{
    private $filter_path = "";
    private $filter_start_date = "";
    private $filter_end_date = "";
    private $filter_size_from = "";
    private $filter_size_to = "";
    private $filter_type = "";
    private $limit = 10;

    public function __construct()
    {
        if (isset($_POST["path"]) && $_POST["path"] != "") {
            $this->filter_path = $_POST["path"];
        }
        if (isset($_POST["start_date"]) && $_POST["start_date"] != "") {
            $this->filter_start_date = $_POST["start_date"];
        }
        if (isset($_POST["end_date"]) && $_POST["end_date"] != "") {
            $this->filter_end_date = $_POST["end_date"];
        }
        if (isset($_POST["size_from"]) && $_POST["size_from"] != "") {
            $this->filter_size_from = $_POST["size_from"];
        }
        if (isset($_POST["size_to"]) && $_POST["size_to"] != "") {
            $this->filter_size_to = $_POST["size_to"];
        }
        if (isset($_POST["type"]) && $_POST["type"] != "") {
            $this->filter_type = $_POST["type"];
        }

        if (isset($_POST["limit"]) && $_POST["limit"] != "") {
            $this->limit = $_POST["limit"];
        }
    }

    public function display()
    {
        // get options
        $options = json_decode(get_option(IOWD_PREFIX . "_options"), true);

        // report data
        $data = $this->report_data();
        $report_data = $data["rows"];
        $total_count = $data["total_count"];
        $total_orig_size = $data["total_image_orig_size"];
        $total_size = $data["total_image_size"];

        $limit = $data["limit"];

        $file_types = array("" => "-" . __("Select", IOWD_PREFIX) . "-", "jpg" => "jpg", "png" => "png", "gif" => "gif", "pdf" => "pdf");

        // require view template
        require_once(IOWD_DIR_VIEWS . '/iowd_report_display.php');
    }


    public function report_data()
    {
        global $wpdb;
        $where = array();
        if ($this->filter_path) {
            $where[] = " path LIKE '%" . $this->filter_path . "%' ";
        }
        if ($this->filter_start_date) {
            $where[] = " updated_date >= '" . $this->filter_start_date . "' ";
        }
        if ($this->filter_end_date) {
            $where[] = " updated_date <= '" . $this->filter_end_date . "' ";
        }
        if ($this->filter_size_from) {
            $where[] = " image_size >= " . (int)$this->filter_size_from . " ";
        }
        if ($this->filter_size_to) {
            $where[] = " image_size <= " . (int)$this->filter_size_to . " ";
        }
        if ($this->filter_type) {
            if ($this->filter_type == "jpg") {
                $where[] = " ( LOWER(SUBSTRING_INDEX(path,'.',-1)) = 'jpg' OR   LOWER(SUBSTRING_INDEX(path,'.',-1)) = 'jpeg' )";
            } else {
                $where[] = " LOWER(SUBSTRING_INDEX(path,'.',-1)) = '" . $this->filter_type . "' ";
            }

        }
        $where = count($where) > 0 ? " AND " . implode(" AND ", $where) : "";

        $rows = $wpdb->get_results("SELECT  id, image_orig_size, image_size, path, post_id, DATE(updated_date) AS date  FROM " . $wpdb->prefix . "iowd_images WHERE deleted=0 " . $where . "  ORDER BY id DESC LIMIT 0, " . $this->limit);

        $total_data = $wpdb->get_row("SELECT  COUNT(id) AS  count, SUM(image_orig_size) AS total_image_orig_size, SUM(image_size) AS total_image_size  FROM " . $wpdb->prefix . "iowd_images WHERE deleted=0 " . $where);

        $count = $total_data ? $total_data->count : 0;
        $total_image_orig_size = $total_data ? $total_data->total_image_orig_size : 0;
        $total_image_size = $total_data ? $total_data->total_image_size : 0;

        $sizes = array(
            "500000"   => "0.5MB",
            "1000000"  => "1MB",
            "2000000"  => "2MB",
            "4000000"  => "4MB",
            "8000000"  => "8MB",
            "14000000" => "14MB",
        );

        $data = array(
            "rows"                  => $rows,
            "total_count"           => $count,
            "total_image_orig_size" => $total_image_orig_size,
            "total_image_size"      => $total_image_size,
            "limit"                 => $this->limit,
            "sizes"                 => $sizes,
        );

        return $data;

    }


}


