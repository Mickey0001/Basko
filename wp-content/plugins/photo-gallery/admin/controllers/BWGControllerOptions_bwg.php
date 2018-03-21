<?php

class BWGControllerOptions_bwg {

  public function execute() {
    $task = ((isset($_POST['task'])) ? esc_html(stripslashes($_POST['task'])) : '');
    
    if($task != ''){
      if(!WDWLibrary::verify_nonce('options_bwg')){
        die('Sorry, your nonce did not verify.');
      }
    }

    if (method_exists($this, $task)) {
      $this->$task();
    }
    else {
      $this->display();
    }
  }

  public function display() {
    require_once WD_BWG_DIR . "/admin/models/BWGModelOptions_bwg.php";
    $model = new BWGModelOptions_bwg();

    require_once WD_BWG_DIR . "/admin/views/BWGViewOptions_bwg.php";
    $view = new BWGViewOptions_bwg($model);
    $view->display();
  }
  
  public function reset() {
    require_once WD_BWG_DIR . "/admin/models/BWGModelOptions_bwg.php";
    $model = new BWGModelOptions_bwg();

    require_once WD_BWG_DIR . "/admin/views/BWGViewOptions_bwg.php";
    $view = new BWGViewOptions_bwg($model);
    echo WDWLibrary::message('Changes must be saved.', 'wd_error');
    $view->display(true);
  }

  public function save() {
    $this->save_db();
    $this->display();
  }
  
  public function save_db() {
    $row = new WD_BWG_Options();
    if (isset($_POST['old_images_directory'])) {
      $row->old_images_directory = esc_html(stripslashes($_POST['old_images_directory']));
    }
    if (isset($_POST['images_directory'])) {
      $row->images_directory = esc_html(stripslashes($_POST['images_directory']));
      if (!is_dir(ABSPATH . $row->images_directory) || (is_dir(ABSPATH . $row->images_directory . '/photo-gallery') && $row->old_images_directory && $row->old_images_directory != $row->images_directory)) {
        if (!is_dir(ABSPATH . $row->images_directory)) {
          echo WDWLibrary::message('Uploads directory doesn\'t exist. Old value is restored.', 'wd_error');
        }
        else {
          echo WDWLibrary::message('Warning: "photo-gallery" folder already exists in uploads directory. Old value is restored.', 'wd_error');
        }
        if ($row->old_images_directory) {
          $row->images_directory = $row->old_images_directory;
        }
        else {
          $upload_dir = wp_upload_dir();
          if (!is_dir($upload_dir['basedir'] . '/photo-gallery')) {
            mkdir($upload_dir['basedir'] . '/photo-gallery', 0777);
          }
          $row->images_directory = str_replace(ABSPATH, '', $upload_dir['basedir']);
        }
      }
    }
    else {
      $upload_dir = wp_upload_dir();
      if (!is_dir($upload_dir['basedir'] . '/photo-gallery')) {
        mkdir($upload_dir['basedir'] . '/photo-gallery', 0777);
      }
      $row->images_directory = str_replace(ABSPATH, '', $upload_dir['basedir']);
    }

    foreach ($row as $name => $value) {
      if ($name == 'autoupdate_interval') {
        $autoupdate_interval = (isset($_POST['autoupdate_interval_hour']) && isset($_POST['autoupdate_interval_min']) ? ((int) $_POST['autoupdate_interval_hour'] * 60 + (int) $_POST['autoupdate_interval_min']) : null);
        /*minimum autoupdate interval is 1 min*/
        $row->autoupdate_interval = isset($autoupdate_interval) && $autoupdate_interval >= 1 ? $autoupdate_interval : 1;
      }
      else if ($name != 'images_directory' && isset($_POST[$name])) {
        $row->$name = esc_html(stripslashes($_POST[$name]));
      }
    }

    $save = update_option('wd_bwg_options', json_encode($row), 'no');

    if (isset($_POST['watermark']) && $_POST['watermark'] == "image_set_watermark") {
      $this->image_set_watermark();
    }

    if ($save) {
      if ($row->old_images_directory && $row->old_images_directory != $row->images_directory) {
        rename(ABSPATH . $row->old_images_directory . '/photo-gallery', ABSPATH . $row->images_directory . '/photo-gallery');
      }
      if (!is_dir(ABSPATH . $row->images_directory . '/photo-gallery')) {
        mkdir(ABSPATH . $row->images_directory . '/photo-gallery', 0777);
      }
      if (isset($_POST['recreate']) && $_POST['recreate'] == "resize_image_thumb") {
        $this->resize_image_thumb();
        echo WDWLibrary::message(__('All thumbnails are successfully recreated.', 'bwg_back'), 'wd_updated');
      }
      else {
        echo WDWLibrary::message(__('Item Succesfully Saved.', 'bwg_back'), 'wd_updated');
      }
    }
  }

  public function image_set_watermark() {
    WDWLibrary::bwg_image_set_watermark(0);
  }
  
  public function image_recover_all() {
    WDWLibrary::bwg_image_recover_all(0);
    $this->display();
  }
  
   public function resize_image_thumb() {
    global $WD_BWG_UPLOAD_DIR;
    global $wpdb;
    global $wd_bwg_options;
    $img_ids = $wpdb->get_results('SELECT id, thumb_url FROM ' . $wpdb->prefix . 'bwg_image');
    foreach ($img_ids as $img_id) {
        $file_path = str_replace("thumb", ".original", htmlspecialchars_decode(ABSPATH . $WD_BWG_UPLOAD_DIR . $img_id->thumb_url, ENT_COMPAT | ENT_QUOTES));
	      $new_file_path = htmlspecialchars_decode(ABSPATH . $WD_BWG_UPLOAD_DIR . $img_id->thumb_url, ENT_COMPAT | ENT_QUOTES);
        list($img_width, $img_height, $type) = @getimagesize(htmlspecialchars_decode($file_path, ENT_COMPAT | ENT_QUOTES));
        if (!$img_width || !$img_height) {
          continue;
        }
        $max_width = $wd_bwg_options->upload_thumb_width;
        $max_height = $wd_bwg_options->upload_thumb_height;
        $scale = min(
          $max_width / $img_width,
          $max_height / $img_height
        );
        @ini_set('memory_limit', '-1');
        if (!function_exists('imagecreatetruecolor')) {
          error_log('Function not found: imagecreatetruecolor');
          return FALSE;
        }
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $dst_x = 0;
        $dst_y = 0;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch ($type) {
          case 2:
            $src_img = @imagecreatefromjpeg($file_path);
            $write_image = 'imagejpeg';
            $image_quality = $wd_bwg_options->jpeg_quality;
            break;
          case 1:
            @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
            $src_img = @imagecreatefromgif($file_path);
            $write_image = 'imagegif';
            $image_quality = null;
            break;
          case 3:
            @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
            @imagealphablending($new_img, false);
            @imagesavealpha($new_img, true);
            $src_img = @imagecreatefrompng($file_path);
            $write_image = 'imagepng';
            $image_quality = $wd_bwg_options->png_quality;
            break;
          default:
            $src_img = null;
            break;
        }
        $success = $src_img && @imagecopyresampled(
          $new_img,
          $src_img,
          $dst_x,
          $dst_y,
          0,
          0,
          $new_width,
          $new_height,
          $img_width,
          $img_height
        ) && $write_image($new_img, $new_file_path, $image_quality);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        @ini_restore('memory_limit');
	  }
  }
}