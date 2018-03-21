<?php

/**
 * Created by PhpStorm.
 * User: Araqel
 * Date: 08/04/2017
 * Time: 1:38 PM
 */

use Aws\CommandPool;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

require_once IOWD_DIR_INCLUDES . "/iowd-optimize.php";

class IOWD_Rest extends WP_REST_Controller
{
    private $version = '1';
    private $route = 'iowd';
    private $region = 'us-west-2';

    public function register_routes()
    {
        $namespace = $this->route . '/v' . $this->version;
        register_rest_route($namespace, '/images/download', array(
            array(
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'download_images'),
                'args'     => array(
                    'bucket'      => array(
                        'required' => true,
                        'type'     => 'string',
                    ),
                    'post_id'     => array(
                        'required' => true,
                    ),
                    'iteration'   => array(
                        'required' => true,
                    ),
                    'images_data' => array(
                        'required' => true
                    ),
                ),
            )
        ));

    }

    //TODO optimize async download
    //TODO check which way is faster ( i think $promies->wait())
    public function download_images(WP_REST_Request $request)
    {
        clearstatcache();
        @ini_set('max_execution_time',1300);
        $bucket = $request->get_param('bucket');
        $images_data = $request->get_param('images_data');
        $post_id = $request->get_param('post_id');
        $iteration = $request->get_param('iteration');
        $credentials = get_option("iowd_crd_" . $post_id);

        $s3args = array(
            'version'     => 'latest',
            'region'      => $this->region,
            'credentials' => $credentials,
        );

        $s3client = new S3Client($s3args);

        try {
            $commands = array();
            foreach ($images_data as $image_data) {
                if ($s3client->doesObjectExist($bucket, $image_data["path"])) {
                    $commands[] = $s3client->getCommand('getObject', array(
                        'Bucket' => $bucket,
                        'Key'    => $image_data["path"],
                        'SaveAs' => $image_data["wp_path"],
                    ));
                }
            }
            if (empty($commands) === false) {
                $pool = new CommandPool($s3client, $commands);
                $promise = $pool->promise();
                $promise->wait();
            }
            $optimizer = new IOWD_Optimize();
            $optimizer->save_data_to_db($images_data, $iteration, $post_id);

            $received_images = get_site_transient("iowd_received_images_" . $post_id);
            if (!$received_images) {
                $data_count = get_option("iowd_data_count_" . $post_id);
                $received_images = range(0, $data_count-1);
            }
            unset($received_images[$iteration]);

            if (empty($received_images) === true) {
                delete_site_transient("iowd_received_images_" . $post_id);
                $post_ids = get_transient("iowd_optimizing_post_ids");
                if (($key = array_search($post_id, $post_ids)) !== false) {
                    unset($post_ids[$key]);
                    set_transient("iowd_optimizing_post_ids", $post_ids);
                } else {
                    delete_transient("iowd_optimizing_post_ids");
                }
                delete_option("iowd_crd_" . $post_id);
                delete_option("iowd_image_temp_data_" . $post_id);
                delete_option("iowd_data_count_" . $post_id);
                if (strpos($post_id, "auto") === false) {
                    set_site_transient("iowd_done_" . $post_id, 1);
                }
            } else {
                set_site_transient("iowd_received_images_" . $post_id, $received_images);
            }


        } catch (S3Exception $e) {
            IOWD_Log::error_log($e->getMessage());

            return new WP_Error($e->getCode() == 0 ? 'amazon' : $e->getCode(), $e->getMessage());
        }

        return new WP_REST_Response(array('status' => 'ok', 'message' => 'success'), 200);
    }

    /*    public function validate_credentials($param)
        {
            if (!$this->is_array($param)) {
                return false;
            }

            $haystack = array('AccessKeyId', 'SecretAccessKey', 'SessionToken', 'Expiration');
            $niddle = array_keys($param);

            if (!array_diff($niddle, $haystack)) {
                return true;
            }

            return false;

        }

        public function is_array($param)
        {
            return is_array($param);
        }*/


}