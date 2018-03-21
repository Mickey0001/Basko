<?php
if (!defined('ABSPATH')) {
    exit;
}

class IOWD_Util
{


    public static function format_bytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        if ($bytes == 0) {
            return '0 B';
        }

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));

        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);


        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    public static function is_transparent_png($file)
    {
        //4 checks for greyscale + alpha and RGB + alpha
        if ((ord(file_get_contents($file, false, null, 25, 1)) & 4)) {
            return true;
        }
        $contents = file_get_contents( $file );

        if ( stripos( $contents, 'PLTE' ) !== false && stripos( $contents, 'tRNS' ) !== false ) {
            return true;
        }
        return false;

    }

}