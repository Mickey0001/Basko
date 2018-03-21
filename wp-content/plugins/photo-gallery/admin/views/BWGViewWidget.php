<?php
class BWGViewWidget {

  private $model;

  public function __construct($model) {
	$this->model = $model;
  }

  public function display() {
  }

  function widget($args, $instance) {
    extract($args);
	global $wd_bwg_options;
    $title = (!empty($instance['title']) ? $instance['title'] : "");
    $type  = (!empty($instance['type']) ? $instance['type'] : "gallery");
    $gallery_id = (!empty($instance['gallery_id']) ? $instance['gallery_id'] : 0);
    $album_id = (!empty($instance['album_id']) ? $instance['album_id'] : 0);
    $theme_id = (!empty($instance['theme_id']) ? $instance['theme_id'] : 0);
    $show  = (!empty($instance['show']) ? $instance['show'] : "random");
	$sort_by = 'order';
	if ($show == 'random') {
		$sort_by = 'RAND()';
	}
	$order_by = 'ASC';
	if ($show == 'last') {
		$order_by = 'DESC';
	}

	$count  = (!empty($instance['count']) ? $instance['count'] : $wd_bwg_options->image_column_number);
    $width  = (!empty($instance['width']) ? $instance['width'] : $wd_bwg_options->thumb_width);
    $height = (!empty($instance['height']) ? $instance['height'] : $wd_bwg_options->thumb_height);

	// Before widget.
    echo $before_widget;
    // Title of widget.
    if ($title) {
      echo $before_title . $title . $after_title;
    }
    // Widget output.
	$params = array (
				'from' => 'widget',
				'theme_id' => $theme_id,
				'sort_by'  => $sort_by,
				'order_by' => $order_by,
				'image_enable_page' => 0
			);
    if ($type == 'gallery') {
		require_once(WD_BWG_DIR . '/frontend/controllers/BWGControllerThumbnails.php');
		$controller_class = 'BWGControllerThumbnails';
		$params['gallery_id'] 	 = $gallery_id;
		$params['thumb_width'] 	 = $width;
		$params['thumb_height']  = $height;
		$params['image_column_number'] = $count;
		$params['images_per_page'] = $count;
		$params['gallery_type']  = 'thumbnails';
    }
    else {
      require_once(WD_BWG_DIR . '/frontend/controllers/BWGControllerAlbum_compact_preview.php');
      $controller_class = 'BWGControllerAlbum_compact_preview';
		$params['album_id'] = $album_id;
		$params['compuct_albums_per_page'] = $count;
		$params['compuct_album_thumb_width']   = $width;
		$params['compuct_album_thumb_height']  = $height;
		$params['compuct_album_image_thumb_width']   = $width;
		$params['compuct_album_image_thumb_height']  = $height;
		$params['compuct_album_view_type']  = 'thumbnail';
		$params['gallery_type']  = 'album_compact_preview';
		$params['compuct_album_enable_page'] = 0;
    }
    $controller = new $controller_class();
    global $bwg;
	$pairs = WDWLibrary::get_shortcode_option_params( $params );
    $controller->execute($pairs, 1, $bwg);
    $bwg++;
    // After widget.
    echo $after_widget;
  }
  
  // Widget Control Panel.
  function form($instance, $id_title, $name_title, $id_type, $name_type, $id_show, $name_show, $id_gallery_id, $name_gallery_id, $id_album_id, $name_album_id, $id_count, $name_count, $id_width, $name_width, $id_height, $name_height, $id_theme_id, $name_theme_id) {
		$defaults = array(
			'title' => 'Photo Gallery',
			'type' => 'gallery',
			'gallery_id' => 0,
			'album_id' => 0,
			'show' => 'random',
			'count' => 4,
			'width' => 100,
			'height' => 100,
			'theme_id' => 0,
		);
		
    $instance = wp_parse_args((array) $instance, $defaults);
    $gallery_rows = $this->model->get_gallery_rows_data();
    $album_rows = $this->model->get_album_rows_data();
    $theme_rows = $this->model->get_theme_rows_data();
    ?>
    <script>
      function bwg_change_type(event, obj) {
        var div = jQuery(obj).closest("div");
        if (jQuery(jQuery(div).find(".sel_gallery")[0]).prop("checked")) {
          jQuery(jQuery(div).find("#p_galleries")).css("display", "");
          jQuery(jQuery(div).find("#p_albums")).css("display", "none");
          jQuery(obj).nextAll(".bwg_hidden").first().attr("value", "gallery");
        }
        else {
          jQuery(jQuery(div).find("#p_galleries")).css("display", "none");
          jQuery(jQuery(div).find("#p_albums")).css("display", "");
          jQuery(obj).nextAll(".bwg_hidden").first().attr("value", "album");
        }
      }
    </script>
    <p>
      <label for="<?php echo $id_title; ?>"><?php _e("Title:", 'bwg_back'); ?></label>
      <input class="widefat" id="<?php echo $id_title; ?>" name="<?php echo $name_title; ?>'" type="text" value="<?php echo $instance['title']; ?>"/>
    </p>
    <p>
      <input type="radio" name="<?php echo $name_type; ?>" id="<?php echo $id_type . "_1"; ?>" value="gallery" class="sel_gallery" onclick="bwg_change_type(event, this)" <?php if ($instance['type'] == "gallery") echo 'checked="checked"'; ?> /><label for="<?php echo $id_type . "_1"; ?>"><?php _e("Gallery", 'bwg_back'); ?></label>
      <input type="radio" name="<?php echo $name_type; ?>" id="<?php echo $id_type . "_2"; ?>" value="album" class="sel_album" onclick="bwg_change_type(event, this)" <?php if ($instance['type'] == "album") echo 'checked="checked"'; ?> /><label for="<?php echo $id_type . "_2"; ?>"><?php _e("Album", 'bwg_back'); ?></label>
      <input type="hidden" name="<?php echo $name_type; ?>" id="<?php echo $id_type; ?>" value="<?php echo $instance['type']; ?>" class="bwg_hidden" />
    </p>
    <p id="p_galleries" style="display:<?php echo ($instance['type'] == "gallery") ? "" : "none" ?>;">
      <select name="<?php echo $name_gallery_id; ?>" id="<?php echo $id_gallery_id; ?>" class="widefat">
        <option value="0"><?php _e("Select Gallery", 'bwg_back'); ?></option>
        <?php
        foreach ($gallery_rows as $gallery_row) {
          ?>
          <option value="<?php echo $gallery_row->id; ?>" <?php echo (($instance['gallery_id'] == $gallery_row->id) ? 'selected="selected"' : ''); ?>><?php echo $gallery_row->name; ?></option>
          <?php
        }
        ?>
      </select>
    </p>
    <p id="p_albums" style="display:<?php echo ($instance['type'] == "album") ? "" : "none" ?>;">
      <select name="<?php echo $name_album_id; ?>" id="<?php echo $id_album_id; ?>" class="widefat">
        <option value="0"><?php _e("Select Album", 'bwg_back'); ?></option>
        <?php
        foreach ($album_rows as $album_row) {
          ?>
          <option value="<?php echo $album_row->id; ?>" <?php echo (($instance['album_id'] == $album_row->id) ? 'selected="selected"' : ''); ?>><?php echo $album_row->name; ?></option>
          <?php
        }
        ?>
      </select>
    </p>    
    <p>
      <input type="radio" name="<?php echo $name_show; ?>" id="<?php echo $id_show . "_1"; ?>" value="random" <?php if ($instance['show'] == "random") echo 'checked="checked"'; ?> onclick='jQuery(this).nextAll(".bwg_hidden").first().attr("value", "random");' /><label for="<?php echo $id_show . "_1"; ?>"><?php _e("Random", 'bwg_back'); ?></label>
      <input type="radio" name="<?php echo $name_show; ?>" id="<?php echo $id_show . "_2"; ?>" value="first" <?php if ($instance['show'] == "first") echo 'checked="checked"'; ?> onclick='jQuery(this).nextAll(".bwg_hidden").first().attr("value", "first");' /><label for="<?php echo $id_show . "_2"; ?>"><?php _e("First", 'bwg_back'); ?></label>
      <input type="radio" name="<?php echo $name_show; ?>" id="<?php echo $id_show . "_3"; ?>" value="last" <?php if ($instance['show'] == "last") echo 'checked="checked"'; ?> onclick='jQuery(this).nextAll(".bwg_hidden").first().attr("value", "last");' /><label for="<?php echo $id_show . "_3"; ?>"><?php _e("Last", 'bwg_back'); ?></label>
      <input type="hidden" name="<?php echo $name_show; ?>" id="<?php echo $id_show; ?>" value="<?php echo $instance['show']; ?>" class="bwg_hidden" />
    </p>
    <p>
      <label for="<?php echo $id_count; ?>"><?php _e("Count:", 'bwg_back'); ?></label>
      <input class="widefat" style="width:25%;" id="<?php echo $id_count; ?>" name="<?php echo $name_count; ?>'" type="text" value="<?php echo $instance['count']; ?>"/>
    </p>
    <p>
      <label for="<?php echo $id_width; ?>"><?php _e("Dimensions:", 'bwg_back'); ?></label>
      <input class="widefat" style="width:25%;" id="<?php echo $id_width; ?>" name="<?php echo $name_width; ?>'" type="text" value="<?php echo $instance['width']; ?>"/> x 
      <input class="widefat" style="width:25%;" id="<?php echo $id_height; ?>" name="<?php echo $name_height; ?>'" type="text" value="<?php echo $instance['height']; ?>"/> px
    </p>
    <p>
      <select name="<?php echo $name_theme_id; ?>" id="<?php echo $id_theme_id; ?>" class="widefat">
        <?php
        foreach ($theme_rows as $theme_row) {
          ?>
          <option value="<?php echo $theme_row->id; ?>" <?php echo (($instance['theme_id'] == $theme_row->id || $theme_row->default_theme == 1) ? 'selected="selected"' : ''); ?>><?php echo $theme_row->name; ?></option>
          <?php
        }
        ?>
      </select>
    </p> 
    <?php
  }
  
  ////////////////////////////////////////////////////////////////////////////////////////
  // Getters & Setters                                                                  //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Private Methods                                                                    //
  ////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////
  // Listeners                                                                          //
  ////////////////////////////////////////////////////////////////////////////////////////
}