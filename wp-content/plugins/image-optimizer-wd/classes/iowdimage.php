<?php

if (!defined('ABSPATH')) {
    exit;
}

class IOWDImage
{

    private $image = null;
    private $image_path = null;
    private $image_full_path = null;
    private $mime_type = null;
    private $image_name = null;
    private $width = null;
    private $height = null;

    /**
     * function for setting image path
     *
     * @param  string $image_path
     *
     * @return void
     */
    public function load_image($image_path)
    {

        $this->image_full_path = $image_path;
        $this->mime_type = pathinfo($image_path, PATHINFO_EXTENSION);

        $this->image_path = str_replace(basename($image_path), "", $image_path);
        $this->image_name = str_replace("." . $this->mime_type, "", basename($image_path));


        list($img_width, $img_height) = @getimagesize(htmlspecialchars_decode($image_path, ENT_COMPAT | ENT_QUOTES));

        $this->width = $img_width;
        $this->height = $img_height;

        switch ($this->mime_type) {
            case "jpg" :
            case "jpeg" :
                $this->image = @imagecreatefromjpeg($image_path);
                break;
            case "png" :
                $this->image = @imagecreatefrompng($image_path);
                break;

            case "gif" :
                $this->image = @imagecreatefromgif($image_path);
                break;
        }
    }

    /**
     * function for setting image path
     *
     * @return void
     */
    public function convert_to_png()
    {
        if ($this->image) {
            imagepng($this->image, $this->image_path . $this->image_name . '.png');
            @imagedestroy($this->image);
        }

    }

    /**
     * function for setting image path
     *
     * @return void
     */
    public function convert_to_jpg()
    {
        if ($this->image) {
            $output = $this->image;

            if ($this->mime_type == "png") {
                list($width, $height) = getimagesize($this->image_full_path);
                $output = imagecreatetruecolor($width, $height);
                $white = imagecolorallocate($output, 255, 255, 255);
                imagefilledrectangle($output, 0, 0, $width, $height, $white);
                imagecopy($output, $this->image, 0, 0, 0, 0, $width, $height);
            }

            imagejpeg($output, $this->image_path . $this->image_name . '.jpg');
            @imagedestroy($output);
        }
    }

    /**
     * function for setting image path
     *
     * @return void
     */
    public function convert_to_webp()
    {
        if ($this->image) {
            $output = $this->image;

            if ($this->mime_type == "png") {
                list($width, $height) = getimagesize($this->image_full_path);
                $output = imagecreatetruecolor($width, $height);
                $white = imagecolorallocate($output, 255, 255, 255);
                imagefilledrectangle($output, 0, 0, $width, $height, $white);
                imagecopy($output, $this->image, 0, 0, 0, 0, $width, $height);
            }
            @imagewebp($output, $this->image_path . $this->image_name . '.webp');
            @imagedestroy($output);
        }
    }

    /**
     * function for setting image path
     *
     * @param  string $width
     * @param  string $height
     *
     * @return void
     */
    public function resize($width, $height)
    {
        $ratio = $this->width / $this->height;
        $max_width = $width;
        $max_height = $width / $ratio;

        ini_set('memory_limit', '-1');

        if (($this->width / $this->height) >= ($max_width / $max_height)) {
            $new_width = $this->width / ($this->height / $max_height);
            $new_height = $max_height;
        } else {
            $new_width = $max_width;
            $new_height = $this->height / ($this->width / $max_width);
        }

        $dst_x = 0 - ($new_width - $max_width) / 2;
        $dst_y = 0 - ($new_height - $max_height) / 2;

        $new_img = @imagecreatetruecolor($max_width, $max_height);

        switch ($this->mime_type) {
            case "jpg":
            case "jpeg":
                $write_image = 'imagejpeg';
                $image_quality = 75;
                $extension = ".jpeg";
                break;

            case "gif":
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $write_image = 'imagegif';
                $image_quality = null;
                $extension = ".gif";
                break;

            case "png":
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $write_image = 'imagepng';
                $image_quality = 9;
                $extension = ".png";
                break;


        }
        $success = $this->image && @imagecopyresampled($new_img, $this->image, $dst_x, $dst_y, 0, 0, $new_width, $new_height, $this->width, $this->height) && $write_image($new_img, $this->image_full_path, $image_quality);

        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($this->image);
        @imagedestroy($new_img);
        ini_restore('memory_limit');

    }



}



