<?php

if (!defined('ABSPATH')) {
    exit;
}
require_once(IOWD_DIR . '/vendor/autoload.php');
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class IOWD_Amazon_S3
{
    private $cacert_path = null;

    public $errors = array();
    public $sub_folder_name = null;
    public $folder_name = null;
    public $credentials = null;
    public $s3_client = null;
    private $region = 'us-west-2';
    private $bucket = 'iotwbucket';

    public function __construct($id = '')
    {
        $this->cacert_path = IOWD_DIR . '/cacert-2017-06-07.pem';

        $this->sub_folder_name = md5("TWlop45&&Kn1)@" . time());
        $this->folder_name = md5(site_url());

        $credentials = get_option("iowd_crd_" . $id);
        $config = array(
            'version'     => 'latest',
            'region'      => $this->region,
            'credentials' => $credentials,
            'http'        => array(
                'verify' => $this->cacert_path,
            ),
        );
        $this->credentials = $credentials;

        try {
            $this->s3_client = new S3Client($config);
        } catch (\Exception $e) {

        }
    }

    public function upload($file_key)
    {
        try {
            @ini_set('max_execution_time', 3600);
            clearstatcache();
            $arg = array(
                'Bucket'     => $this->bucket,
                'Key'        => $this->folder_name . "/" . $this->sub_folder_name . "/" . basename($file_key),
                'SourceFile' => $file_key,
            );

            $response = $this->s3_client->putObjectAsync($arg);

            return $response;

        } catch (S3Exception $e) {
            IOWD_Log::error_log($e->getMessage());
        }

    }


}