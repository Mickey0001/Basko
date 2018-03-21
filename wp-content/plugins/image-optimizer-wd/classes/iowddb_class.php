<?php

if (!defined('ABSPATH')) {
    exit;
}

class IOWDDB_Class
{
    public $errors = array();
    private $table_name;


    /**
     * function for create table
     *
     * @param  string $table_name
     * @param  array  $fields array( 0=> array("field_name" => "name", "field_type" => "type", "null" => "NOT NULL", "ai" => "0"))
     * @param  string $primary_key
     *
     * @return void
     */
    public function create_table($table_name, $fields = array(), $primary_key = "id")
    {
        global $wpdb;
        $query_str = array();
        foreach ($fields as $field) {
            $single_field = $field["field_name"] . " " . $field["field_type"] . " " . $field["null"];
            if ($field["ai"] == 1) {
                $single_field .= " AUTO_INCREMENT";
            }
            $query_str[] = $single_field;
        }

        $fields = implode(",", $query_str);

        $query = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . $table_name . "` (
            " . $fields . ",
            PRIMARY KEY (`" . $primary_key . "`)
            ) DEFAULT CHARSET=utf8;";

        $wpdb->query($query);
        if ($wpdb->last_error) {
            array_push($this->errors, $wpdb->last_error);
        }
    }


    /**
     * function for getting single row by field name
     *
     * @param  array  $values = array("field_name" => field_value, ..)
     * @param  string $compare
     * @param  string $operator
     *
     * @return object
     */

    public function get_row_by_field($values = array(), $compare = "=", $operator = "and")
    {
        $row = false;
        global $wpdb;
        if ($this->table_name) {
            $where = array();
            if ($values) {
                foreach ($values as $key => $val) {
                    $where[] = $key . $compare . "'" . $val . "'";
                }
            }
            $where = count($where) > 0 ? " WHERE " . implode(" " . $operator . " ", $where) : "";
            $query = "SELECT * FROM " . $wpdb->prefix . $this->table_name . $where;

            $row = $wpdb->get_row($query);
        }

        return $row;
    }

    /**
     * function for setting table name
     *
     * @param  string $table_name
     *
     * @return void
     */
    public function set_table_name($table_name)
    {
        $this->table_name = $table_name;
    }

}



