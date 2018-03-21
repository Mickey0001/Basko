<?php

class BWGControllerGalleries_bwg {
  public function execute() {
    $task = ((isset($_POST['task'])) ? esc_html(stripslashes($_POST['task'])) : '');
    $id = ((isset($_POST['current_id'])) ? esc_html(stripslashes($_POST['current_id'])) : 0);
    if ($task != '') {
      if (!WDWLibrary::verify_nonce('galleries_bwg')) {
        die('Sorry, your nonce did not verify.');
      }
    }

    if (method_exists($this, $task)) {
      $this->$task($id);
    }
    else {
      $this->display();
    }
  }

  public function display() {
    require_once WD_BWG_DIR . "/admin/models/BWGModelGalleries_bwg.php";
    $model = new BWGModelGalleries_bwg();

    require_once WD_BWG_DIR . "/admin/views/BWGViewGalleries_bwg.php";
    $view = new BWGViewGalleries_bwg($model);
    $this->delete_unknown_images();
    $view->display();
  }

  public function add() {
    require_once WD_BWG_DIR . "/admin/models/BWGModelGalleries_bwg.php";
    $model = new BWGModelGalleries_bwg();

    require_once WD_BWG_DIR . "/admin/views/BWGViewGalleries_bwg.php";
    $view = new BWGViewGalleries_bwg($model);
    $view->edit(0);
  }

  public function edit() {
    require_once WD_BWG_DIR . "/admin/models/BWGModelGalleries_bwg.php";
    $model = new BWGModelGalleries_bwg();

    require_once WD_BWG_DIR . "/admin/views/BWGViewGalleries_bwg.php";
    $view = new BWGViewGalleries_bwg($model);
    $id = ((isset($_POST['current_id']) && esc_html(stripslashes($_POST['current_id'])) != '') ? esc_html(stripslashes($_POST['current_id'])) : 0);
    $view->edit($id);
  }

  public function save_order_images($gallery_id) {
    global $wpdb;
    $imageids_col = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'bwg_image WHERE `gallery_id`="%d"', $gallery_id));
    if ($imageids_col) {
      foreach ($imageids_col as $imageid) {
        if (isset($_POST['order_input_' . $imageid])) {
          $order_values[$imageid] = (int) $_POST['order_input_' . $imageid];
        }
        else {
          $order_values[$imageid] = (int) $wpdb->get_var($wpdb->prepare('SELECT `order` FROM ' . $wpdb->prefix . 'bwg_image WHERE `id`="%d"', $imageid));
        }
      }
      asort($order_values);
      $i = 1;
      foreach ($order_values as $key => $order_value) {
        $wpdb->update($wpdb->prefix . 'bwg_image', array('order' => $i), array('id' => $key));
        $i++;
      }
    }
  }

  public function ajax_search() {
    if (isset($_POST['ajax_task'])) {
      // Save gallery on "apply" and "save".
      $this->save_db();
      global $wpdb;
      if (!isset($_POST['current_id']) || (esc_html(stripslashes($_POST['current_id'])) == 0) || (esc_html(stripslashes($_POST['current_id'])) == '')) {
        // If gallery saved first time (insert in db).
        $_POST['current_id'] = (int) $wpdb->get_var('SELECT MAX(`id`) FROM ' . $wpdb->prefix . 'bwg_gallery');
      }
    }
    $this->save_image_db();
    if (isset($_POST['check_all_items'])) {
      $tag_ids = (isset($_POST['added_tags_select_all']) ? esc_html(stripslashes($_POST['added_tags_select_all'])) : '');
      if ($tag_ids != '') {
          $this->save_tags_if_select_all($tag_ids);
      }
    }
    $this->save_order_images($_POST['current_id']);
    if (isset($_POST['ajax_task']) && esc_html($_POST['ajax_task']) != '') {
      $ajax_task = esc_html($_POST['ajax_task']);
      if (method_exists($this, $ajax_task)) {
        $this->$ajax_task();
      }
    }
    $this->edit();
  }
  
  public function save_tags_if_select_all($tag_ids) {
    global $wpdb;
    $gal_id = (isset($_POST['current_id']) ? (int) $_POST['current_id'] : 0);
    $image_ids = (isset($_POST['ids_string']) ? esc_html(stripslashes($_POST['ids_string'])) : '');
    $current_page_image_ids = explode(',', $image_ids);
    $tag_ids_array = explode(',', $tag_ids);
    $query_image = $wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id="%d"', $gal_id);
    $image_id_array = $wpdb->get_results($query_image);
    foreach ($image_id_array as $image_id) {
      $flag = FALSE;
      foreach ($current_page_image_ids as $current_page_image_id) { 
        if ($current_page_image_id == $image_id->id) {
          $flag = TRUE;
        }
      }
      if ($flag) {
        continue;
      }
      foreach ($tag_ids_array as $tag_id) {
        if ($tag_id) {		
          $exist_tag = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE tag_id="%d" AND image_id="%d" AND gallery_id="%d"', $tag_id,$image_id->id, $gal_id));
          if ($exist_tag == NULL) {
            $save = $wpdb->insert($wpdb->prefix . 'bwg_image_tag', array(
              'tag_id' => $tag_id,
              'image_id' => $image_id->id,
              'gallery_id' => $gal_id,
              ), array(
              '%d',
              '%d',
              '%d',
            ));	  	
            // Increase tag count in term_taxonomy table.
            $wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'term_taxonomy SET count="%d" WHERE term_id="%d"', $wpdb->get_var($wpdb->prepare('SELECT COUNT(image_id) FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE tag_id="%d"', $tag_id)), $tag_id));
          }
        }
      }
    }
  }
  
  public function recover() {
    $id = ((isset($_POST['image_current_id'])) ? esc_html(stripslashes($_POST['image_current_id'])) : 0);
    global $wd_bwg_options;
    $thumb_width = $wd_bwg_options->upload_thumb_width;
    $width = $wd_bwg_options->upload_img_width;    
    WDWLibrary::recover_image($id, $thumb_width, $width, 'gallery_page');
  }
  
  public function image_recover_all() {
    $gallery_id = ((isset($_POST['current_id'])) ? esc_html(stripslashes($_POST['current_id'])) : 0);
    WDWLibrary::bwg_image_recover_all($gallery_id);
  }
  
  public function image_publish() {
    $id = ((isset($_POST['image_current_id'])) ? esc_html(stripslashes($_POST['image_current_id'])) : 0);
    global $wpdb;
    $save = $wpdb->update($wpdb->prefix . 'bwg_image', array('published' => 1), array('id' => $id));
  }

  public function image_publish_all() {
    global $wpdb;
    $gallery_id = ((isset($_POST['current_id'])) ? esc_html(stripslashes($_POST['current_id'])) : 0);
    if (isset($_POST['check_all_items'])) {
      $wpdb->query($wpdb->prepare('UPDATE ' .  $wpdb->prefix . 'bwg_image SET published=1 WHERE gallery_id="%d"', $gallery_id));
    }
    else {
      $image_ids_col = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id="%d"', $gallery_id));
      foreach ($image_ids_col as $image_id) {
        if (isset($_POST['check_' . $image_id])) {
          $wpdb->update($wpdb->prefix . 'bwg_image', array('published' => 1), array('id' => $image_id));
        }
      }
    }
  }

  public function image_unpublish() {
    $id = ((isset($_POST['image_current_id'])) ? esc_html(stripslashes($_POST['image_current_id'])) : 0);
    global $wpdb;
    $save = $wpdb->update($wpdb->prefix . 'bwg_image', array('published' => 0), array('id' => $id));
  }

  public function image_unpublish_all() {
    global $wpdb;
    $gallery_id = ((isset($_POST['current_id'])) ? esc_html(stripslashes($_POST['current_id'])) : 0);
    if (isset($_POST['check_all_items'])) {
      $wpdb->query($wpdb->prepare('UPDATE ' .  $wpdb->prefix . 'bwg_image SET published=0 WHERE gallery_id="%d"', $gallery_id));
    }
    else {
      $image_ids_col = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id="%d"', $gallery_id));
      foreach ($image_ids_col as $image_id) {
        if (isset($_POST['check_' . $image_id])) {
          $wpdb->update($wpdb->prefix . 'bwg_image', array('published' => 0), array('id' => $image_id));
        }
      }
    }
  }

  public function image_delete() {
    $id = ((isset($_POST['image_current_id'])) ? esc_html(stripslashes($_POST['image_current_id'])) : 0);
    global $wpdb;
    $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image WHERE id="%d"', $id));
    $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image_comment WHERE image_id="%d"', $id));
    $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image_rate WHERE image_id="%d"', $id));
    $tag_ids = $wpdb->get_col($wpdb->prepare('SELECT tag_id FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE image_id="%d"', $id));
    $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE image_id="%d"', $id));
    // Increase tag count in term_taxonomy table.
    if ($tag_ids) {
      foreach ($tag_ids as $tag_id) {
        $wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'term_taxonomy SET count="%d" WHERE term_id="%d"', $wpdb->get_var($wpdb->prepare('SELECT COUNT(image_id) FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE tag_id="%d"', $tag_id)), $tag_id));
      }
    }
  }

  public function image_delete_all() {
    global $wpdb;
    $gallery_id = ((isset($_POST['current_id'])) ? esc_html(stripslashes($_POST['current_id'])) : 0);
    $image_ids_col = $wpdb->get_col($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id="%d"', $gallery_id));
    foreach ($image_ids_col as $image_id) {
      if (isset($_POST['check_' . $image_id]) || isset($_POST['check_all_items'])) {
        $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image WHERE id="%d"', $image_id));
        $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image_comment WHERE image_id="%d"', $image_id));
        $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image_rate WHERE image_id="%d"', $image_id));
        $tag_ids = $wpdb->get_col($wpdb->prepare('SELECT tag_id FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE image_id="%d"', $image_id));
        $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE image_id="%d"', $image_id));
        // Increase tag count in term_taxonomy table.
        if ($tag_ids) {
          foreach ($tag_ids as $tag_id) {
            $wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'term_taxonomy SET count="%d" WHERE term_id="%d"', $wpdb->get_var($wpdb->prepare('SELECT COUNT(image_id) FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE tag_id="%d"', $tag_id)), $tag_id));
          }
        }
      }
    }
  }

  public function image_set_watermark() {
    $gallery_id = ((isset($_POST['current_id'])) ? esc_html(stripslashes($_POST['current_id'])) : 0);
    WDWLibrary::bwg_image_set_watermark($gallery_id);
  }

  public function image_resize() {
    global $wpdb;
    global $WD_BWG_UPLOAD_DIR;
    $gallery_id = ((isset($_POST['current_id'])) ? esc_html(stripslashes($_POST['current_id'])) : 0);
    $image_width = ((isset($_POST['image_width'])) ? esc_html(stripslashes($_POST['image_width'])) : 1600);
    $image_height = ((isset($_POST['image_height'])) ? esc_html(stripslashes($_POST['image_height'])) : 1200);
    $images = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id="%d"', $gallery_id));
    foreach ($images as $image) {
      if (isset($_POST['check_' . $image->id]) || isset($_POST['check_all_items'])) {
        $this->bwg_scaled_image(ABSPATH . $WD_BWG_UPLOAD_DIR . $image->image_url, $image_width, $image_height);
      }
    }
  }

  function bwg_scaled_image($file_path, $max_width = 0, $max_height = 0, $crop = FALSE) {
    $file_path = htmlspecialchars_decode($file_path, ENT_COMPAT | ENT_QUOTES);
    global $wd_bwg_options;
    if (!function_exists('getimagesize')) {
      error_log('Function not found: getimagesize');
      return FALSE;
    }
    list($img_width, $img_height, $type) = @getimagesize($file_path);
    if (!$img_width || !$img_height) {
      return FALSE;
    }
    $scale = min(
      $max_width / $img_width,
      $max_height / $img_height
    );
    @ini_set('memory_limit', '-1');
    if (($scale >= 1) || (($max_width === 0) && ($max_height === 0))) {
      // if ($file_path !== $new_file_path) {
        // return copy($file_path, $new_file_path);
      // }
      return TRUE;
    }
    
    if (!function_exists('imagecreatetruecolor')) {
      error_log('Function not found: imagecreatetruecolor');
      return FALSE;
    }
    if (!$crop) {
      $new_width = $img_width * $scale;
      $new_height = $img_height * $scale;
      $dst_x = 0;
      $dst_y = 0;
      $new_img = @imagecreatetruecolor($new_width, $new_height);
    }
    else {
      if (($img_width / $img_height) >= ($max_width / $max_height)) {
        $new_width = $img_width / ($img_height / $max_height);
        $new_height = $max_height;
      }
      else {
        $new_width = $max_width;
        $new_height = $img_height / ($img_width / $max_width);
      }
      $dst_x = 0 - ($new_width - $max_width) / 2;
      $dst_y = 0 - ($new_height - $max_height) / 2;
      $new_img = @imagecreatetruecolor($max_width, $max_height);
    }
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
        $image_quality = NULL;
        break;
      case 3:
        @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
        @imagealphablending($new_img, FALSE);
        @imagesavealpha($new_img, TRUE);
        $src_img = @imagecreatefrompng($file_path);
        $write_image = 'imagepng';
        $image_quality = $wd_bwg_options->png_quality;
        break;
      default:
        $src_img = NULL;
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
    ) && $write_image($new_img, $file_path, $image_quality);
    // Free up memory (imagedestroy does not delete files):
    @imagedestroy($src_img);
    @imagedestroy($new_img);
    @ini_restore('memory_limit');
    return $success;
  }
  
  public function save_image_db() {
    global $wpdb;
    $gal_id = (isset($_POST['current_id']) ? (int) $_POST['current_id'] : 0);
    $image_ids = (isset($_POST['ids_string']) ? esc_html(stripslashes($_POST['ids_string'])) : '');
    $image_id_array = explode(',', $image_ids);
    if (isset($_POST['check_all_items']) && isset($_POST['bulk_edit']) && $_POST['bulk_edit'] == 1) {
      $title = ((isset($_POST['title'])) ?  esc_html(stripslashes($_POST['title'])) : '');
      $desc = ((isset($_POST['desc'])) ?  esc_html(stripslashes($_POST['desc'])) : '');
      $redirecturl = ((isset($_POST['redirecturl'])) ?  esc_html(stripslashes($_POST['redirecturl'])) : '');
      $wpdb->update($wpdb->prefix . 'bwg_image', array(
        'description' => $desc,
        'alt' => $title,
        'redirect_url' => $redirecturl), array('gallery_id' => $gal_id));      
    }
    foreach ($image_id_array as $image_id) {
      if ($image_id) {
        $filename = ((isset($_POST['input_filename_' . $image_id])) ? esc_html(stripslashes($_POST['input_filename_' . $image_id])) : '');
        $image_url = ((isset($_POST['image_url_' . $image_id])) ? esc_html(stripslashes($_POST['image_url_' . $image_id])) : '');
        $thumb_url = ((isset($_POST['thumb_url_' . $image_id])) ? esc_html(stripslashes($_POST['thumb_url_' . $image_id])) : '');
        $description = ((isset($_POST['image_description_' . $image_id])) ? esc_html((stripslashes($_POST['image_description_' . $image_id]))) : '');
        $alt = ((isset($_POST['image_alt_text_' . $image_id])) ? esc_html(stripslashes($_POST['image_alt_text_' . $image_id])) : '');
        $date = ((isset($_POST['input_date_modified_' . $image_id])) ? esc_html(stripslashes($_POST['input_date_modified_' . $image_id])) : '');
        $size = ((isset($_POST['input_size_' . $image_id])) ? esc_html(stripslashes($_POST['input_size_' . $image_id])) : '');
        $filetype = ((isset($_POST['input_filetype_' . $image_id])) ? esc_html(stripslashes($_POST['input_filetype_' . $image_id])) : '');
        $resolution = ((isset($_POST['input_resolution_' . $image_id])) ? esc_html(stripslashes($_POST['input_resolution_' . $image_id])) : '');
        $order = ((isset($_POST['order_input_' . $image_id])) ? esc_html(stripslashes($_POST['order_input_' . $image_id])) : '');
        $redirect_url = ((isset($_POST['redirect_url_' . $image_id])) ? esc_html(stripslashes($_POST['redirect_url_' . $image_id])) : '');
        $author = get_current_user_id();
        $tags_ids = ((isset($_POST['tags_' . $image_id])) ? esc_html(stripslashes($_POST['tags_' . $image_id])) : '');
        if (strpos($image_id, 'pr_') !== FALSE) {
          $save = $wpdb->insert($wpdb->prefix . 'bwg_image', array(
            'gallery_id' => $gal_id,
            'slug' => WDWLibrary::spider_replace4byte($alt),
            'filename' => $filename,
            'image_url' => $image_url,
            'thumb_url' => $thumb_url,
            'description' => WDWLibrary::spider_replace4byte($description),
            'alt' => WDWLibrary::spider_replace4byte($alt),
            'date' => $date,
            'size' => $size,
            'filetype' => $filetype,
            'resolution' => $resolution,
            'author' => $author,
            'order' => $order,
            'published' => 1,
            'comment_count' => 0,
            'avg_rating' => 0,
            'rate_count' => 0,
            'hit_count' => 0,
            'redirect_url' => $redirect_url,
          ), array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
          ));
          $new_image_id = (int) $wpdb->get_var('SELECT MAX(`id`) FROM ' . $wpdb->prefix . 'bwg_image');
          if (isset($_POST['check_' . $image_id])) {
            $_POST['check_' . $new_image_id] = 'on';
          }
          if (isset($_POST['image_current_id']) && (esc_html($_POST['image_current_id']) == $image_id)) {
            $_POST['image_current_id'] = $new_image_id;
          }
          $image_id = $new_image_id;
        }
        else {
          // Don't update image and thumbnail URLs.
          $save = $wpdb->update($wpdb->prefix . 'bwg_image', array(
            'gallery_id' => $gal_id,
            'slug' => WDWLibrary::spider_replace4byte($alt),
            'description' => WDWLibrary::spider_replace4byte($description),
            'alt' => WDWLibrary::spider_replace4byte($alt),
            'date' => $date,
            'size' => $size,
            'filetype' => $filetype,
            'resolution' => $resolution,
            'order' => $order,
            'redirect_url' => $redirect_url), array('id' => $image_id));
        }
        $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE image_id="%d" AND gallery_id="%d"', $image_id, $gal_id));
        if ($save !== FALSE) {
          $tag_id_array = explode(',', $tags_ids);
          foreach ($tag_id_array as $tag_id) {
            if ($tag_id) {
              if (strpos($tag_id, 'pr_') !== FALSE) {
                $tag_id = substr($tag_id, 3);
              }
              $save = $wpdb->insert($wpdb->prefix . 'bwg_image_tag', array(
                'tag_id' => $tag_id,
                'image_id' => $image_id,
                'gallery_id' => $gal_id,
              ), array(
                '%d',
                '%d',
                '%d',
              ));
              // Increase tag count in term_taxonomy table.
              $wpdb->query($wpdb->prepare('UPDATE ' . $wpdb->prefix . 'term_taxonomy SET count="%d" WHERE term_id="%d"', $wpdb->get_var($wpdb->prepare('SELECT COUNT(image_id) FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE tag_id="%d"', $tag_id)), $tag_id));
            }
          }
        }
      }
    }
  }

  public function save() {
    echo WDWLibrary::message(__('Item Succesfully Saved.', 'bwg_back'), 'wd_updated');
    $this->display();
  }

  public function delete_unknown_images() {
    global $wpdb;
    $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id=0');
  }

  public function bwg_get_unique_slug($slug, $id) {
    global $wpdb;
    $slug = sanitize_title($slug);
    if ($id != 0) {
      $query = $wpdb->prepare("SELECT slug FROM " . $wpdb->prefix . "bwg_gallery WHERE slug = %s AND id != %d", $slug, $id);
    }
    else {
      $query = $wpdb->prepare("SELECT slug FROM " . $wpdb->prefix . "bwg_gallery WHERE slug = %s", $slug);
    }
    if ($wpdb->get_var($query)) {
      $num = 2;
      do {
        $alt_slug = $slug . "-$num";
        $num++;
        $slug_check = $wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $wpdb->prefix . "bwg_gallery WHERE slug = %s", $alt_slug));
      } while ($slug_check);
      $slug = $alt_slug;
    }
    return $slug;
  }
  
  public function bwg_get_unique_name($name, $id) {
    global $wpdb;
    if ($id != 0) {
      $query = $wpdb->prepare("SELECT name FROM " . $wpdb->prefix . "bwg_gallery WHERE name = %s AND id != %d", $name, $id);
    }
    else {
      $query = $wpdb->prepare("SELECT name FROM " . $wpdb->prefix . "bwg_gallery WHERE name = %s", $name);
    }
    if ($wpdb->get_var($query)) {
      $num = 2;
      do {
        $alt_name = $name . "-$num";
        $num++;
        $slug_check = $wpdb->get_var($wpdb->prepare("SELECT name FROM " . $wpdb->prefix . "bwg_gallery WHERE name = %s", $alt_name));
      } while ($slug_check);
      $name = $alt_name;
    }
    return $name;
  }
  
  public function save_db() {
    global $wpdb;
    global $WD_BWG_UPLOAD_DIR;
    $id   = (isset($_POST['current_id']) ? (int) $_POST['current_id'] : 0);
    $name = ((isset($_POST['name']) && esc_html(stripslashes($_POST['name'])) != '') ? esc_html(stripslashes($_POST['name'])) : 'Gallery');
    $name = $this->bwg_get_unique_name($name, $id);
    $slug = ((isset($_POST['slug']) && esc_html(stripslashes($_POST['slug'])) != '') ? esc_html(stripslashes($_POST['slug'])) : $name);
    $slug = $this->bwg_get_unique_slug($slug, $id);
	$old_slug = WDWLibrary::get('old_slug');
    $description = (isset($_POST['description']) ? stripslashes($_POST['description']) : '');
    $preview_image = (isset($_POST['preview_image']) ? esc_html(stripslashes($_POST['preview_image'])) : '');
    $random_preview_image = '';
    if ($preview_image == '') {
      if ($id != 0) {
        $random_preview_image = $wpdb->get_var($wpdb->prepare("SELECT random_preview_image FROM " . $wpdb->prefix . "bwg_gallery WHERE id='%d'", $id));
        if ($random_preview_image == '' || !file_exists(ABSPATH . $WD_BWG_UPLOAD_DIR . $random_preview_image)) {
          $random_preview_image = $wpdb->get_var($wpdb->prepare("SELECT thumb_url FROM " . $wpdb->prefix . "bwg_image WHERE gallery_id='%d' ORDER BY `order`", $id));
        }
      }
      else {
        $i = 0;
        $random_preview_image = '';
        while (isset($_POST['thumb_url_pr_' . $i]) && isset($_POST["input_filetype_pr_" . $i])) {
          /*if ($_POST["input_filetype_pr_" . $i] == "JPG" || $_POST["input_filetype_pr_" . $i] == "PNG" || $_POST["input_filetype_pr_" . $i] == "GIF")*/ {
            $random_preview_image = esc_html(stripslashes($_POST['thumb_url_pr_' . $i]));
            break;
          }
          $i++;
        }
      }
    }

    $gallery_type = ((isset($_POST['gallery_type']) && esc_html(stripslashes($_POST['gallery_type'])) != '') ? esc_html(stripslashes($_POST['gallery_type'])) : '');
    $gallery_source = ((isset($_POST['gallery_source']) && esc_html(stripslashes($_POST['gallery_source'])) != '') ? esc_html(stripslashes($_POST['gallery_source'])) : '');
    $update_flag = ((isset($_POST['update_flag']) && esc_html(stripslashes($_POST['update_flag'])) != '') ? esc_html(stripslashes($_POST['update_flag'])) : '');
    $autogallery_image_number = (isset($_POST['autogallery_image_number']) ? (int) $_POST['autogallery_image_number'] : 12);
    $published = (isset($_POST['published']) ? (int) $_POST['published'] : 1);
    if ($id != 0) {
      $data = array(
        'name' => $name,
        'slug' => $slug,
        'description' => $description,
        'random_preview_image' => $random_preview_image,
        'gallery_type' => $gallery_type,
        'gallery_source' => $gallery_source,
        'autogallery_image_number' => $autogallery_image_number,
        'update_flag' => $update_flag,
        'published' => $published
      );
      // To prevent saving preview image wrong URL after moving the image.
      if ( file_exists(ABSPATH . $WD_BWG_UPLOAD_DIR . $preview_image) ) {
        $data['preview_image'] = $preview_image;
      }
      $save = $wpdb->update($wpdb->prefix . 'bwg_gallery', $data, array('id' => $id));
    }
    else {
      $save = $wpdb->insert($wpdb->prefix . 'bwg_gallery', array(
        'name' => $name,
        'slug' => $slug,
        'description' => $description,
        'page_link' => '',
        'preview_image' => $preview_image,
        'random_preview_image' => $random_preview_image,
        'order' => ((int) $wpdb->get_var('SELECT MAX(`order`) FROM ' . $wpdb->prefix . 'bwg_gallery')) + 1,
        'author' => get_current_user_id(),
        'gallery_type' => $gallery_type,
        'gallery_source' => $gallery_source,
        'autogallery_image_number' => $autogallery_image_number,
        'update_flag' => $update_flag,
        'published' => $published,
      ), array(
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%d',
        '%d',
        '%s',
        '%s',
        '%d',
        '%s',
        '%d',
      ));
      $id = $wpdb->insert_id;
    }
    // Create custom post (type is gallery).
    $custom_post_params = array(
      'id' => $id,
      'title' => $name,
      'slug' => $slug,
	  'old_slug' => $old_slug,
      'type' => array(
        'post_type' => 'gallery',
        'mode' => '',
      ),
    );
    WDWLibrary::bwg_create_custom_post($custom_post_params);
    if ( $save !== FALSE ) {
      echo WDWLibrary::message(__('Item Succesfully Saved.', 'bwg_back'), 'wd_updated');
    }
    else {
      echo WDWLibrary::message(__('Error. Please install plugin again.', 'bwg_back'), 'wd_error');
    }
  }

  public function save_order($flag = TRUE) {
    global $wpdb;
    $gallery_ids_col = $wpdb->get_col('SELECT id FROM ' . $wpdb->prefix . 'bwg_gallery');
    if ($gallery_ids_col) {
      foreach ($gallery_ids_col as $gallery_id) {
        if (isset($_POST['order_input_' . $gallery_id])) {
          $order_values[$gallery_id] = (int) $_POST['order_input_' . $gallery_id];
        }
        else {
          $order_values[$gallery_id] = (int) $wpdb->get_var($wpdb->prepare('SELECT `order` FROM ' . $wpdb->prefix . 'bwg_gallery WHERE `id`="%d"', $gallery_id));
        }
      }
      asort($order_values);
      $i = 1;
      foreach ($order_values as $key => $order_value) {
        $wpdb->update($wpdb->prefix . 'bwg_gallery', array('order' => $i), array('id' => $key));
        $i++;
      }
      if ($flag) {
        echo WDWLibrary::message(__('Ordering Succesfully Saved.', 'bwg_back'), 'wd_updated');
      }
    }
    $this->display();
  }

  public function delete($id) {
	global $wpdb;
	$row = $wpdb->get_row( $wpdb->prepare('SELECT id, slug FROM ' . $wpdb->prefix . 'bwg_gallery WHERE id="%d"', $id) );
	if ( !empty($row) ) {
		$query = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_gallery WHERE id="%d"', $id);
		$query_image = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id="%d"', $id);
		$query_album_gallery = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_album_gallery WHERE alb_gal_id="%d" AND is_album="%d"', $id, 0);
		if ($wpdb->query($query)) {
		  $wpdb->query($query_image);
		  $wpdb->query($query_album_gallery);
		  // Remove custom post (type by bwg_gallery).
		  WDWLibrary::bwg_remove_custom_post( array( 'slug' => $row->slug, 'post_type' => 'bwg_gallery') );
		  echo WDWLibrary::message(__('Item Succesfully Deleted.', 'bwg_back'), 'wd_updated');
		}
		else {
		   // TODO change message.
		  echo WDWLibrary::message(__('Error. Please install plugin again.', 'bwg_back'), 'wd_error');
		}
    }
    $this->display();
  }
  
  public function delete_all() {
	$message = WDWLibrary::message(__('You must select at least one item.', 'bwg_back'), 'wd_error');
	$galleryids = array();
	if ( !empty($_POST['ids_string']) ){
		$ids = explode(',', $_POST['ids_string']);
		foreach ($ids as $id) {
			$keypost = 'check_' . $id;
			if ( !empty($_POST[$keypost]) ) {
				$galleryids[] = $id;
			}
		}
	}
	if ( !empty($galleryids) ) {
		global $wpdb;
		$gallerys = $wpdb->get_results('SELECT `id`, `slug` FROM ' . $wpdb->prefix . 'bwg_gallery WHERE `id` IN (' . implode(',', $galleryids). ')');
			if ( !empty($gallerys) ) {
				$delete = false;
				foreach( $gallerys as $gallery ) {
					$wpdb->query( $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_gallery WHERE id="%d"', $gallery->id) );
					$wpdb->query( $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id="%d"', $gallery->id) );
					$wpdb->query( $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_album_gallery WHERE alb_gal_id="%d" AND is_album="%d"', $gallery->id, 0) );
					// Remove custom post (type by bwg_gallery).
					WDWLibrary::bwg_remove_custom_post( array( 'slug' => $gallery->slug, 'post_type' => 'bwg_gallery') );
					$delete = true;
				}
				if ( $delete ) {
					$message = WDWLibrary::message(__('Items Succesfully Deleted.', 'bwg_back'), 'wd_updated');
				}
			}
	}
	echo $message;
	$this->display();
  }

  public function publish($id) {
    global $wpdb;
    $save = $wpdb->update($wpdb->prefix . 'bwg_gallery', array('published' => 1), array('id' => $id));
    if ($save !== FALSE) {
      echo WDWLibrary::message(__('Item Succesfully Published.', 'bwg_back'), 'wd_updated');
    }
    else {
      echo WDWLibrary::message(__('Error. Please install plugin again.', 'bwg_back'), 'wd_error');
    }
    $this->display();
  }
  
  public function publish_all() {
    global $wpdb;
    $flag = FALSE;
    if (isset($_POST['check_all_items'])) {
      $wpdb->query('UPDATE ' .  $wpdb->prefix . 'bwg_gallery SET published=1');
      $flag = TRUE;
    }
    else {
      $gal_ids_col = $wpdb->get_col('SELECT id FROM ' . $wpdb->prefix . 'bwg_gallery');
      foreach ($gal_ids_col as $gal_id) {
        if (isset($_POST['check_' . $gal_id])) {
          $flag = TRUE;
          $wpdb->update($wpdb->prefix . 'bwg_gallery', array('published' => 1), array('id' => $gal_id));
        }
      }
    }
    if ($flag) {
      echo WDWLibrary::message(__('Items Succesfully Published.', 'bwg_back'), 'wd_updated');
    }
    else {
      echo WDWLibrary::message(__('You must select at least one item.', 'bwg_back'), 'wd_error');
    }
    $this->display();
  }

  public function unpublish($id) {
    global $wpdb;
    $save = $wpdb->update($wpdb->prefix . 'bwg_gallery', array('published' => 0), array('id' => $id));
    if ($save !== FALSE) {
      echo WDWLibrary::message(__('Item Succesfully Unpublished.', 'bwg_back'), 'wd_updated');
    }
    else {
      echo WDWLibrary::message(__('Error. Please install plugin again.', 'bwg_back'), 'wd_error');
    }
    $this->display();
  }
  
  public function unpublish_all() {
    global $wpdb;
    $flag = FALSE;
    if (isset($_POST['check_all_items'])) {
      $wpdb->query('UPDATE ' .  $wpdb->prefix . 'bwg_gallery SET published=0');
      $flag = TRUE;
    }
    else {
      $gal_ids_col = $wpdb->get_col('SELECT id FROM ' . $wpdb->prefix . 'bwg_gallery');
      foreach ($gal_ids_col as $gal_id) {
        if (isset($_POST['check_' . $gal_id])) {
          $flag = TRUE;
          $wpdb->update($wpdb->prefix . 'bwg_gallery', array('published' => 0), array('id' => $gal_id));
        }
      }
    }
    if ($flag) {
      echo WDWLibrary::message(__('Items Succesfully Unpublished.', 'bwg_back'), 'wd_updated');
    }
    else {
      echo WDWLibrary::message(__('You must select at least one item.', 'bwg_back'), 'wd_error');
    }
    $this->display();
  }
  public function resize_image_thumb() {
    global $WD_BWG_UPLOAD_DIR;
    global $wpdb;
    $flag = FALSE;
    $gallery_id = ((isset($_POST['current_id'])) ? esc_html(stripslashes($_POST['current_id'])) : 0);
    $img_ids = $wpdb->get_results($wpdb->prepare('SELECT id, thumb_url FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id="%d"', $gallery_id));
    global $wd_bwg_options;
    foreach ($img_ids as $img_id) {
      if (isset($_POST['check_' . $img_id->id]) || isset($_POST['check_all_items'])) {
	      $flag = TRUE;
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
	  if ($flag == false) {
      echo WDWLibrary::message(__('You must select at least one item.', 'bwg_back'), 'wd_error');
    }
	  else {
		  echo WDWLibrary::message(__('Thumb Succesfully Resized', 'bwg_back'), 'wd_updated');
	  }
  }

  public function rotate_left() {
    $this->rotate(90);
  }

  public function rotate_right() {
    $this->rotate(270);
  }
  
  public function rotate($edit_type) {
    global $WD_BWG_UPLOAD_DIR;
    global $wpdb;
    global $wd_bwg_options;
    $flag = FALSE;
    $gallery_id = ((isset($_POST['current_id'])) ? esc_html(stripslashes($_POST['current_id'])) : 0);
    $images_data = $wpdb->get_results($wpdb->prepare('SELECT id, image_url, thumb_url FROM ' . $wpdb->prefix . 'bwg_image WHERE gallery_id="%d"', $gallery_id));
    @ini_set('memory_limit', '-1');
    foreach ($images_data as $image_data) {
      if (isset($_POST['check_' . $image_data->id]) || isset($_POST['check_all_items'])) {
	      $flag = TRUE;
        $image_data->image_url = stripcslashes($image_data->image_url);      
        $filename = htmlspecialchars_decode(ABSPATH . $WD_BWG_UPLOAD_DIR . $image_data->image_url, ENT_COMPAT | ENT_QUOTES);
        $thumb_filename = htmlspecialchars_decode(ABSPATH . $WD_BWG_UPLOAD_DIR . $image_data->thumb_url, ENT_COMPAT | ENT_QUOTES);
        list($width_rotate, $height_rotate, $type_rotate) = getimagesize($filename);
        if ($edit_type == '270' || $edit_type == '90') {
          if ($type_rotate == 2) {
            $source = imagecreatefromjpeg($filename);
            $thumb_source = imagecreatefromjpeg($thumb_filename);
            $rotate = imagerotate($source, $edit_type, 0);
            $thumb_rotate = imagerotate($thumb_source, $edit_type, 0);
            imagejpeg($thumb_rotate, $thumb_filename, $wd_bwg_options->jpeg_quality);
            imagejpeg($rotate, $filename, $wd_bwg_options->jpeg_quality);
            imagedestroy($source);
            imagedestroy($rotate);
            imagedestroy($thumb_source);
            imagedestroy($thumb_rotate);
          }
          elseif ($type_rotate == 3) {
            $source = imagecreatefrompng($filename);
            $thumb_source = imagecreatefrompng($thumb_filename);
            imagealphablending($source, FALSE);
            imagealphablending($thumb_source, FALSE);
            imagesavealpha($source, TRUE);
            imagesavealpha($thumb_source, TRUE);
            $rotate = imagerotate($source, $edit_type, imageColorAllocateAlpha($source, 0, 0, 0, 127));
            $thumb_rotate = imagerotate($thumb_source, $edit_type, imageColorAllocateAlpha($source, 0, 0, 0, 127));
            imagealphablending($rotate, FALSE);
            imagealphablending($thumb_rotate, FALSE);
            imagesavealpha($rotate, TRUE);
            imagesavealpha($thumb_rotate, TRUE);
            imagepng($rotate, $filename, $wd_bwg_options->png_quality);
            imagepng($thumb_rotate, $thumb_filename, $wd_bwg_options->png_quality);
            imagedestroy($source);
            imagedestroy($rotate);
            imagedestroy($thumb_source);
            imagedestroy($thumb_rotate);
          }
          elseif ($type_rotate == 1) {
            $source = imagecreatefromgif($filename);
            $thumb_source = imagecreatefromgif($thumb_filename);
            imagealphablending($source, FALSE);
            imagealphablending($thumb_source, FALSE);
            imagesavealpha($source, TRUE);
            imagesavealpha($thumb_source, TRUE);
            $rotate = imagerotate($source, $edit_type, imageColorAllocateAlpha($source, 0, 0, 0, 127));
            $thumb_rotate = imagerotate($thumb_source, $edit_type, imageColorAllocateAlpha($source, 0, 0, 0, 127));
            imagealphablending($rotate, FALSE);
            imagealphablending($thumb_rotate, FALSE);
            imagesavealpha($rotate, TRUE);
            imagesavealpha($thumb_rotate, TRUE);
            imagegif($rotate, $filename);
            imagegif($thumb_rotate, $thumb_filename);
            imagedestroy($source);
            imagedestroy($rotate);
            imagedestroy($thumb_source);
            imagedestroy($thumb_rotate);
          }
        }
	    }
	  }
	  if ($flag == false) {
      echo WDWLibrary::message(__('You must select at least one item.', 'bwg_back'), 'wd_error');
    }
	  else {
		  echo WDWLibrary::message(__('Items successfully rotated.', 'bwg_back'), 'wd_updated');
	  }
  }
}