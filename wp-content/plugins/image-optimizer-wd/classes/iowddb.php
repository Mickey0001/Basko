<?php

if (!defined('ABSPATH')) {
    exit;
}
require_once IOWD_DIR_CLASSES . "/iowddb_class.php";

class IOWDDB extends IOWDDB_Class
{

    public function create_iowd_images_table()
    {
        $fields = array(
            array(
                "field_name" => "`id`",
                "field_type" => "int(16)",
                "null"       => "not null",
                "ai"         => 1,
            ),
            array(
                "field_name" => "`post_id`",
                "field_type" => "varchar(256)",
                "null"       => "not null",
                "ai"         => 0,
            ),

            array(
                "field_name" => "`size`",
                "field_type" => "varchar(256)",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`path`",
                "field_type" => "varchar(256)",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`image_size`",
                "field_type" => "int(16)",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`image_orig_size`",
                "field_type" => "int(16)",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`updated_date`",
                "field_type" => "datetime",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`status`",
                "field_type" => "varchar(256)",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`media`",
                "field_type" => "int(16)",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`already_optimized`",
                "field_type" => "varchar(256)",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`converted`",
                "field_type" => "int(16)",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`resized`",
                "field_type" => "int(16)",
                "null"       => "not null",
                "ai"         => 0,
            ),
            array(
                "field_name" => "`deleted`",
                "field_type" => "int(16)",
                "null"       => "not null",
                "ai"         => 0,
            ),

        );

        $this->create_table("iowd_images", $fields);
    }

    public function update()
    {
       $options = json_decode(get_option(IOWD_PREFIX . "_options"), true);

       if(!in_array("optimize_gallery", $options)){
           $options["optimize_gallery"] = "0";
           update_option(IOWD_PREFIX . "_options",json_encode($options));
       }
    }


}



