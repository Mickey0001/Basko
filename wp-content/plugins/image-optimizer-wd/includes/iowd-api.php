<?php
if (!defined('ABSPATH')) {
    exit;
}

class IOWD_Api
{
    private static $api_url = null;
    private static $post_data = null;
    private static $post_headers_data = array(
        "Accept" => "application/x.optimizer.v1+json"
    );


    public static function api_request()
    {

        $data = self::$post_data;
        $hedares = self::$post_headers_data;

        $request = wp_remote_post(self::$api_url, array(
                'method'      => 'POST',
                'headers'     => $hedares,
                'body'        => $data,
            )
        );

        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            if (isset($request["body"])) {
                return json_decode($request['body'], true);
            }
        }

        return false;
    }

    public static function set_post_data($data)
    {
        self::$post_data = $data;
    }

    public static function set_post_headers_data($data)
    {
        self::$post_headers_data = array_merge(self::$post_headers_data, $data);
    }



    public static function set_api_action($api_action)
    {
        self::$api_url = IOWD_API_URL . $api_action;
    }


}