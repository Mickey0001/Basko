<?php
if (!defined('ABSPATH')) {
    exit;
}

class IOWD_Log
{

    private static $log_path = IOWD_DIR . "/log/";

    public static function error_log($log_content)
    {
        self::write_log($log_content, "ERROR");
    }


    private static function write_log($log, $type)
    {
        $log_content = "[" . date("Y-m-d H:i:s") . "] " ;
        if($type){
            $log_content .=  "[" . $type . "] ";
        }
        $log_content .= $log . '/n';
        file_put_contents(self::$log_path . date("Ymd") . '.txt', $log_content);
    }


}