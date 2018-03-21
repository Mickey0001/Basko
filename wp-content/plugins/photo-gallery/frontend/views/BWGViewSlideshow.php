<?php
class BWGViewSlideshow {
  public function display($params, $from_shortcode = 0, $bwg = 0) {
    global $WD_BWG_UPLOAD_DIR;
    global $wd_bwg_options;
    require_once(WD_BWG_DIR . '/framework/WDWLibrary.php');
    require_once(WD_BWG_DIR . '/framework/WDWLibraryEmbed.php');
    $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$theme_id = $params['theme_id'];
	$gallery_id = $params['gallery_id'];
	$order_by = $params['order_by'];
	$sort_by = $params['sort_by'];
	$slideshow_effect = $params['slideshow_effect'];
	$enable_slideshow_autoplay = $params['enable_slideshow_autoplay'];
	$enable_slideshow_shuffle = $params['enable_slideshow_shuffle'];
	$enable_slideshow_ctrl = $params['enable_slideshow_ctrl'];
	$enable_slideshow_filmstrip = FALSE;
	$slideshow_filmstrip_height = 0;
	$slideshow_filmstrip_width = 0;

	$enable_image_title = $params['slideshow_enable_title'];
	$slideshow_title_position = explode('-', $params['slideshow_title_position']);
	$enable_image_description = $params['slideshow_enable_description'];
	$slideshow_description_position = explode('-', $params['slideshow_description_position']);
	$enable_slideshow_music = $params['enable_slideshow_music'];
	$slideshow_music_url = $params['slideshow_music_url'];

	$image_width = $params['slideshow_width'];
	$image_height = $params['slideshow_height'];
	$slideshow_interval = $params['slideshow_interval'];

	$watermark_type = $params['watermark_type'];
	$watermark_text = $params['watermark_text'];
	$watermark_font_size = $params['watermark_font_size'];
	$watermark_font = $params['watermark_font'];
	$watermark_color = $params['watermark_color'];
	$watermark_opacity = $params['watermark_opacity'];
	$watermark_position = explode('-', $params['watermark_position']);
	$watermark_link = $params['watermark_link'];
	$watermark_url = $params['watermark_url'];
	$watermark_width = $params['watermark_width'];
	$watermark_height = $params['watermark_height'];
	$slideshow_effect_duration = $params['slideshow_effect_duration'];
	
	$image_right_click = isset($wd_bwg_options->image_right_click) ? $wd_bwg_options->image_right_click : 0;

	$theme_row = WDWLibrary::get_theme_row_data($theme_id);
	if (!$theme_row) {
		echo WDWLibrary::message(__('There is no theme selected or the theme was deleted.', 'bwg'), 'wd_error');
		return;
	}

	$gallery_row = WDWLibrary::get_gallery_row_data($gallery_id);
    if (!$gallery_row && $params["tag"] == 0) {
      echo WDWLibrary::message(__('There is no gallery selected or the gallery was deleted.', 'bwg'), 'wd_error');
      return;
    }
    $image_rows = WDWLibrary::get_image_rows_data($gallery_id, $bwg, 'slideshow', '', $params['tag'], '', '', $sort_by, $order_by);
    $image_rows = $image_rows['images'];
    $images_count = count($image_rows);
    if (!$images_count) {
      if ($params['tag']) {
        echo WDWLibrary::message(__('There are no images.', 'bwg'), 'wd_error');
        return;
      }
      else {
        echo WDWLibrary::message(__('There are no images in this gallery.', 'bwg'), 'wd_error');
        return;
      }
    }
    $current_image_id = ($image_rows ? $image_rows[0]->id : 0);
    $play_pause_button_display = 'undefined';
    $filmstrip_thumb_margin = $theme_row->slideshow_filmstrip_thumb_margin;
    $margins_split = explode(" ", $filmstrip_thumb_margin);
    $temp_iterator = 1;
    if (isset($margins_split[$temp_iterator])) {
      $filmstrip_thumb_margin_right = (int) $margins_split[$temp_iterator];
      if (isset($margins_split[$temp_iterator + 2])) {
        $filmstrip_thumb_margin_left = (int) $margins_split[$temp_iterator + 2];
      }
      else {
        $filmstrip_thumb_margin_left = $filmstrip_thumb_margin_right;
      }
    }
    elseif (isset($margins_split[0])) {
      $filmstrip_thumb_margin_right = (int) $margins_split[0];
      $filmstrip_thumb_margin_left = $filmstrip_thumb_margin_right;
    }
    $filmstrip_thumb_margin_hor = $filmstrip_thumb_margin_right + $filmstrip_thumb_margin_left;
    $inline_style = $this->inline_styles($bwg, $theme_row, $params, $image_width, $image_height, $slideshow_filmstrip_height, $wd_bwg_options, $filmstrip_thumb_margin_hor, $slideshow_filmstrip_width, $image_rows, $watermark_position, $slideshow_title_position, $slideshow_description_position, $watermark_height, $watermark_width, $watermark_opacity, $watermark_font_size, $watermark_font, $watermark_color, $enable_slideshow_filmstrip);
    if ($wd_bwg_options->use_inline_stiles_and_scripts) {
      wp_enqueue_style('bwg_frontend');
      wp_add_inline_style('bwg_frontend', $inline_style);
      wp_enqueue_style('bwg_font-awesome');
      wp_enqueue_style('bwg_mCustomScrollbar');
      $google_fonts = WDWLibrary::get_google_fonts();
      wp_enqueue_style('bwg_googlefonts');
      if ($params['thumb_click_action'] == 'open_lightbox') {
        if (!wp_script_is('bwg_mCustomScrollbar', 'done')) {
          wp_print_scripts('bwg_mCustomScrollbar');
        }
        if (!wp_script_is('jquery-fullscreen', 'done')) {
          wp_print_scripts('jquery-fullscreen');
        }
        if (!wp_script_is('bwg_gallery_box', 'done')) {
          wp_print_scripts('bwg_gallery_box');
        }
      }
      if (!wp_script_is('bwg_frontend', 'done')) {
        wp_print_scripts('bwg_frontend');
      }
      if (!wp_script_is('bwg_jquery_mobile', 'done')) {
        wp_print_scripts('bwg_jquery_mobile');
      }
    }
    else {
      echo '<style>' . $inline_style . '</style>';
    }
    ?>
    <script>
      var data_<?php echo $bwg; ?> = [];
      var event_stack_<?php echo $bwg; ?> = [];
      <?php
      foreach ($image_rows as $key => $image_row) {
        if ($image_row->id == $current_image_id) {
          $current_image_alt = $image_row->alt;
          $current_image_description = str_replace(array("\r\n", "\n", "\r"), esc_html('<br />'), $image_row->description);
        }
        ?>
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"] = [];
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["id"] = "<?php echo $image_row->id; ?>";
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["alt"] = "<?php echo str_replace(array("\r\n", "\n", "\r"), esc_html('<br />'), $image_row->alt); ?>";
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["description"] = "<?php echo str_replace(array("\r\n", "\n", "\r"), esc_html('<br />'), $image_row->description); ?>";
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["filetype"] = "<?php echo $image_row->filetype; ?>";
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["filename"] = "<?php echo $image_row->filename; ?>";
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["image_url"] = "<?php echo $image_row->image_url; ?>";
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["thumb_url"] = "<?php echo $image_row->thumb_url; ?>";
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["date"] = "<?php echo $image_row->date; ?>";
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["is_embed"] = "<?php echo (preg_match('/EMBED/',$image_row->filetype)==1 ? true :false); ?>";
        data_<?php echo $bwg; ?>["<?php echo $key; ?>"]["is_embed_video"] = "<?php echo (((preg_match('/EMBED/',$image_row->filetype)==1) && (preg_match('/_VIDEO/',$image_row->filetype)==1)) ? true :false); ?>";
        <?php
      }
      ?>    
    </script>
    <div id="bwg_container1_<?php echo $bwg; ?>">
      <div id="bwg_container2_<?php echo $bwg; ?>">
        <div class="bwg_slideshow_image_wrap_<?php echo $bwg; ?>">
          <div id="ajax_loading_<?php echo $bwg; ?>" style="text-align: center; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999;">
            <img src="<?php echo WD_BWG_URL . '/images/ajax_loader.gif'; ?>" style="width: 30px; border: medium none; visibility: visible;">
          </div>
          <?php
          $current_pos = 0;
          if (!$enable_slideshow_filmstrip) {
            ?>
            <div class="bwg_slideshow_dots_container_<?php echo $bwg; ?>">
              <div class="bwg_slideshow_dots_thumbnails_<?php echo $bwg; ?>">
                <?php
                foreach ($image_rows as $key => $image_row) {
                  if ($image_row->id == $current_image_id) {
                    $current_pos = $key * ($slideshow_filmstrip_width + 2);
                    $current_key = $key;
                  }
                ?>
                <span id="bwg_dots_<?php echo $key; ?>_<?php echo $bwg; ?>" class="bwg_slideshow_dots_<?php echo $bwg; ?> <?php echo (($image_row->id == $current_image_id) ? 'bwg_slideshow_dots_active_' . $bwg : 'bwg_slideshow_dots_deactive_' . $bwg); ?>" onclick="bwg_change_image_<?php echo $bwg; ?>(parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()), '<?php echo $key; ?>', data_<?php echo $bwg; ?>)" image_id="<?php echo $image_row->id; ?>" image_key="<?php echo $key; ?>"></span>
                <?php
                }
                ?>
              </div>
            </div>
            <?php
          }
          ?>
          <div id="bwg_slideshow_image_container_<?php echo $bwg; ?>" class="bwg_slideshow_image_container_<?php echo $bwg; ?>">        
            <div class="bwg_slide_container_<?php echo $bwg; ?>">
              <div class="bwg_slide_bg_<?php echo $bwg; ?>">
                <div class="bwg_slider_<?php echo $bwg; ?>">
                <?php
                foreach ($image_rows as $key => $image_row) {
                  $is_embed = preg_match('/EMBED/',$image_row->filetype)==1 ? true :false;
                  $is_embed_video = ($is_embed && preg_match('/_VIDEO/',$image_row->filetype)==1) ? true :false;
                  $is_embed_instagram_post = preg_match('/INSTAGRAM_POST/',$image_row->filetype)==1 ? true :false;
                  if ($image_row->id == $current_image_id) {
                    $current_key = $key;
                    ?>
                    <span class="bwg_slideshow_image_spun_<?php echo $bwg; ?>" id="image_id_<?php echo $bwg; ?>_<?php echo $image_row->id; ?>">
                      <span class="bwg_slideshow_image_spun1_<?php echo $bwg; ?>">
                        <span class="bwg_slideshow_image_spun2_<?php echo $bwg; ?>">
                          <?php 
                            if (!$is_embed) {
                          ?>
                            <a <?php echo ($params['thumb_click_action'] == 'open_lightbox' ? (' class="bwg_lightbox_' . $bwg . '"' . ($wd_bwg_options->enable_seo ? ' href="' . ($is_embed ? $image_row->thumb_url : site_url() . '/' . $WD_BWG_UPLOAD_DIR . $image_row->image_url) . '"' : '') . ' data-image-id="' . $image_row->id . '"') : ($params['thumb_click_action'] == 'redirect_to_url' && $image_row->redirect_url ? 'href="' . $image_row->redirect_url . '" target="' .  ($params['thumb_link_target'] ? '_blank' : '')  . '"' : '')) ?>>
                            <img id="bwg_slideshow_image_<?php echo $bwg; ?>" class="bwg_slideshow_image_<?php echo $bwg; ?>" src="<?php echo site_url() . '/' . $WD_BWG_UPLOAD_DIR . $image_row->image_url; ?>" image_id="<?php echo $image_row->id; ?>" alt="<?php echo $image_row->alt; ?>"/>
                            </a>
                            <?php 
                            }
                            else{  /*$is_embed*/?>
                            <span id="bwg_slideshow_image_<?php echo $bwg; ?>" class="bwg_slideshow_embed_<?php echo $bwg; ?>" image_id="<?php echo $image_row->id; ?>">
                            <?php
                              if($is_embed_instagram_post) {
                                $post_width = $image_width;
                                $post_height = $image_height;
                                if ($post_height < $post_width + 88) {
                                  $post_width = $post_height - 88;
                                }
                                else {
                                  $post_height = $post_width + 88;
                                }
                                
                                $instagram_post_width = $post_width;
                                $instagram_post_height = $post_height;
                                $image_resolution = explode(' x ', $image_row->resolution);
                                if (is_array($image_resolution)) {
                                  $instagram_post_width = $image_resolution[0];
                                  $instagram_post_height = explode(' ', $image_resolution[1]);
                                  $instagram_post_height = $instagram_post_height[0];
                                }
                                
                                WDWLibraryEmbed::display_embed($image_row->filetype, $image_row->filename, array('class' => "bwg_embed_frame_" . $bwg, 'data-width' => $instagram_post_width, 'data-height' => $instagram_post_height, 'frameborder' => "0", 'style' => "width:" . $post_width . "px; height:" . $post_height . "px; vertical-align:middle; display:inline-block; position:relative;"));
                              }
                              else{
                              WDWLibraryEmbed::display_embed($image_row->filetype, $image_row->filename, array('class'=>"bwg_embed_frame_".$bwg, 'frameborder'=>"0", 'allowfullscreen'=>"allowfullscreen", 'style'=>"width:inherit; height:inherit; vertical-align:middle; display:table-cell;"));
                              }
                              ?>         
                            </span>
                            <?php
                            }
                          ?>
                        </span>
                      </span>
                    </span>
                    <span class="bwg_slideshow_image_second_spun_<?php echo $bwg; ?>">
                    </span>
                    <input type="hidden" id="bwg_current_image_key_<?php echo $bwg; ?>" value="<?php echo $key; ?>" />
                    <?php
                    break;
                  }
                  else {
                    ?>
                    <span class="bwg_slideshow_image_second_spun_<?php echo $bwg; ?>" id="image_id_<?php echo $bwg; ?>_<?php echo $image_row->id; ?>">
                      <span class="bwg_slideshow_image_spun1_<?php echo $bwg; ?>">
                        <span class="bwg_slideshow_image_spun2_<?php echo $bwg; ?>">
                          <?php 
                            if (!$is_embed) {
                            ?>
                            <a <?php echo ($params['thumb_click_action'] == 'open_lightbox' ? (' class="bwg_lightbox_' . $bwg . '"' . ($wd_bwg_options->enable_seo ? ' href="' . ($is_embed ? $image_row->thumb_url : site_url() . '/' . $WD_BWG_UPLOAD_DIR . $image_row->image_url) . '"' : '') . ' data-image-id="' . $image_row->id . '"') : ($params['thumb_click_action'] == 'redirect_to_url' && $image_row->redirect_url ? 'href="' . $image_row->redirect_url . '" target="' .  ($params['thumb_link_target'] ? '_blank' : '')  . '"' : '')) ?>>
                            <img class="bwg_slideshow_image_<?php echo $bwg; ?>" src="<?php echo site_url() . '/' . $WD_BWG_UPLOAD_DIR . $image_row->image_url; ?>" alt="<?php echo $image_row->alt; ?>"/>
                            </a>
                          <?php
                            }
                            else {   /*$is_embed*/ ?>
                            <span class="bwg_slideshow_embed_<?php echo $bwg; ?>">
                                <?php
                              if($is_embed_instagram_post){
                                $post_width = $image_width;
                                $post_height = $image_height;
                                if($post_height < $post_width + 88) {
                                  $post_width = $post_height - 88;
                                }
                                else{
                                  $post_height = $post_width + 88;
                                }
                                
                                $instagram_post_width = $post_width;
                                $instagram_post_height = $post_height;
                                $image_resolution = explode(' x ', $image_row->resolution);
                                if (is_array($image_resolution)) {
                                  $instagram_post_width = $image_resolution[0];
                                  $instagram_post_height = explode(' ', $image_resolution[1]);
                                  $instagram_post_height = $instagram_post_height[0];
                                }
                                WDWLibraryEmbed::display_embed($image_row->filetype, $image_row->filename, array('class'=>"bwg_embed_frame_".$bwg, 'data-width' => $instagram_post_width, 'data-height' => $instagram_post_height, 'frameborder'=>"0", 'style'=>"width:".$post_width."px; height:".$post_height."px; vertical-align:middle; display:inline-block; position:relative;"));
                              }
                              else{
                              WDWLibraryEmbed::display_embed($image_row->filetype, $image_row->filename, array('class'=>"bwg_embed_frame_".$bwg, 'frameborder'=>"0", 'allowfullscreen'=>"allowfullscreen", 'style'=>"width:inherit; height:inherit; vertical-align:middle; display:table-cell;"));
                              }
                              ?>                        
                            </span>
                            <?php 
                            }
                          ?>
                        </span>
                      </span>
                    </span>
                    <?php
                  }
                }
                ?>
                </div>
              </div>
            </div>
            <?php
              if ($enable_slideshow_ctrl) {
                ?>
              <a id="spider_slideshow_left_<?php echo $bwg; ?>" onclick="bwg_change_image_<?php echo $bwg; ?>(parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()), (parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()) + data_<?php echo $bwg; ?>.length - bwg_iterator_<?php echo $bwg; ?>()) % data_<?php echo $bwg; ?>.length, data_<?php echo $bwg; ?>); return false;"><span id="spider_slideshow_left-ico_<?php echo $bwg; ?>"><span><i class="bwg_slideshow_prev_btn_<?php echo $bwg; ?> fa <?php echo $theme_row->slideshow_rl_btn_style; ?>-left"></i></span></span></a>
              <span id="bwg_slideshow_play_pause_<?php echo $bwg; ?>" style="display: <?php echo $play_pause_button_display; ?>;"><span><span id="bwg_slideshow_play_pause-ico_<?php echo $bwg; ?>"><i class="bwg_ctrl_btn_<?php echo $bwg; ?> bwg_slideshow_play_pause_<?php echo $bwg; ?> fa fa-play"></i></span></span></span>
              <a id="spider_slideshow_right_<?php echo $bwg; ?>" onclick="bwg_change_image_<?php echo $bwg; ?>(parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()), (parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()) + bwg_iterator_<?php echo $bwg; ?>()) % data_<?php echo $bwg; ?>.length, data_<?php echo $bwg; ?>); return false;"><span id="spider_slideshow_right-ico_<?php echo $bwg; ?>"><span><i class="bwg_slideshow_next_btn_<?php echo $bwg; ?> fa <?php echo $theme_row->slideshow_rl_btn_style; ?>-right"></i></span></span></a>
              <?php
              }
            ?>
          </div>
          <?php
          if ($watermark_type != 'none') {
          ?>
          <div class="bwg_slideshow_image_container_<?php echo $bwg; ?>" style="position: absolute;">
            <div class="bwg_slideshow_watermark_container_<?php echo $bwg; ?>">
              <div style="display:table; margin:0 auto;">
                <span class="bwg_slideshow_watermark_spun_<?php echo $bwg; ?>" id="bwg_slideshow_watermark_container_<?php echo $bwg; ?>">
                  <?php
                  if ($watermark_type == 'image') {
                  ?>
                  <a href="<?php echo urldecode($watermark_link); ?>" target="_blank">
                    <img class="bwg_slideshow_watermark_image_<?php echo $bwg; ?> bwg_slideshow_watermark_<?php echo $bwg; ?>" src="<?php echo urldecode($watermark_url); ?>" />
                  </a>
                  <?php
                  }
                  elseif ($watermark_type == 'text') {
                  ?>
                  <a class="bwg_none_selectable_<?php echo $bwg; ?> bwg_slideshow_watermark_text_<?php echo $bwg; ?> bwg_slideshow_watermark_<?php echo $bwg; ?>" target="_blank" href="<?php echo urldecode($watermark_link); ?>"><?php echo $watermark_text; ?></a>
                  <?php
                  }
                  ?>
                </span>
              </div>
            </div>
          </div>      
          <?php
          }
          if ($enable_image_title) {
          ?>
          <div class="bwg_slideshow_image_container_<?php echo $bwg; ?>" style="position: absolute;">
            <div class="bwg_slideshow_watermark_container_<?php echo $bwg; ?>">
              <div style="display:table; margin:0 auto;">
                <span class="bwg_slideshow_title_spun_<?php echo $bwg; ?>">
                  <div class="bwg_slideshow_title_text_<?php echo $bwg; ?>" style="<?php if (!$current_image_alt) echo 'display:none;'; ?>">
                    <?php echo html_entity_decode($current_image_alt); ?>
                  </div>
                </span>
              </div>
            </div>
          </div>
          <?php 
          }
          if ($enable_image_description) {
          ?>
          <div class="bwg_slideshow_image_container_<?php echo $bwg; ?>" style="position: absolute;">
            <div class="bwg_slideshow_watermark_container_<?php echo $bwg; ?>">
              <div style="display:table; margin:0 auto;">
                <span class="bwg_slideshow_description_spun_<?php echo $bwg; ?>">
                  <div class="bwg_slideshow_description_text_<?php echo $bwg; ?>" style="<?php if (!$current_image_description) echo 'display:none;'; ?>">
                    <?php echo html_entity_decode(str_replace("\r\n", esc_html('<br />'), $current_image_description)); ?>
                  </div>
                </span>
              </div>
            </div>
          </div>
          <?php 
          }
          if ($enable_slideshow_music) {
            ?>
            <audio id="bwg_audio_<?php echo $bwg; ?>" src="<?php echo site_url() . '/' . $WD_BWG_UPLOAD_DIR . $slideshow_music_url ?>" loop volume="1.0"></audio>
            <?php 
          }
          ?>
        </div>
        <div id="bwg_spider_popup_loading_<?php echo $bwg; ?>" class="bwg_spider_popup_loading"></div>
        <div id="spider_popup_overlay_<?php echo $bwg; ?>" class="spider_popup_overlay" onclick="spider_destroypopup(1000)"></div>
      </div>
    </div>
    <script language="javascript" type="text/javascript" src="<?php echo WD_BWG_URL . '/js/bwg_embed.js?ver=' . wd_bwg_version(); ?>"></script>
    <?php
    $params_array = array(
      'action' => 'GalleryBox',
      'current_view' => $bwg,
      'gallery_id' => $params['gallery_id'],
      'theme_id' => $params['theme_id'],
      'open_with_fullscreen' => $params['popup_fullscreen'],
      'open_with_autoplay' => $params['popup_autoplay'],
      'image_width' => $params['popup_width'],
      'image_height' => $params['popup_height'],
      'image_effect' => $params['popup_effect'],
      'wd_sor' => $sort_by,
      'wd_ord' => $order_by,
      'enable_image_filmstrip' => $params['popup_enable_filmstrip'],
      'image_filmstrip_height' => $params['popup_filmstrip_height'],
      'enable_image_ctrl_btn' => $params['popup_enable_ctrl_btn'],
      'enable_image_fullscreen' => $params['popup_enable_fullscreen'],
      'popup_enable_info' => $params['popup_enable_info'],
      'popup_info_always_show' => $params['popup_info_always_show'],
      'popup_info_full_width' => $params['popup_info_full_width'],
      'popup_hit_counter' => $params['popup_hit_counter'],
      'popup_enable_rate' => $params['popup_enable_rate'],
      'slideshow_interval' => $params['popup_interval'],
      'enable_comment_social' => $params['popup_enable_comment'],
      'enable_image_facebook' => $params['popup_enable_facebook'],
      'enable_image_twitter' => $params['popup_enable_twitter'],
      'enable_image_google' => $params['popup_enable_google'],
      'enable_image_pinterest' => $params['popup_enable_pinterest'],
      'enable_image_tumblr' => $params['popup_enable_tumblr'],
      'watermark_type' => $params['watermark_type'],
      'slideshow_effect_duration' => isset($params['popup_effect_duration']) ? $params['popup_effect_duration'] : 1,
      'tags' => (isset($params['tag']) ? $params['tag'] : 0),
      'current_url' => urlencode($current_url),
    );
    if ($params['watermark_type'] != 'none') {
      $params_array['watermark_link'] = $params['watermark_link'];
      $params_array['watermark_opacity'] = $params['watermark_opacity'];
      $params_array['watermark_position'] = $params['watermark_position'];
    }
    if ($params['watermark_type'] == 'text') {
      $params_array['watermark_text'] = $params['watermark_text'];
      $params_array['watermark_font_size'] = $params['watermark_font_size'];
      $params_array['watermark_font'] = $params['watermark_font'];
      $params_array['watermark_color'] = $params['watermark_color'];
    }
    elseif ($params['watermark_type'] == 'image') {
      $params_array['watermark_url'] = $params['watermark_url'];
      $params_array['watermark_width'] = $params['watermark_width'];
      $params_array['watermark_height'] = $params['watermark_height'];
    }
    ?>
	<script>
      var bwg_trans_in_progress_<?php echo $bwg; ?> = false;
      var bwg_transition_duration_<?php echo $bwg; ?> = <?php echo (($slideshow_interval < 4) && ($slideshow_interval != 0)) ? ($slideshow_interval * 1000) / 4 : ($slideshow_effect_duration * 1000); ?>;
      var bwg_playInterval_<?php echo $bwg; ?>;
      /* Stop autoplay.*/
      window.clearInterval(bwg_playInterval_<?php echo $bwg; ?>);
      /* Set watermark container size.*/
      function bwg_change_watermark_container_<?php echo $bwg; ?>() {
        jQuery(".bwg_slider_<?php echo $bwg; ?>").children().each(function() {
          if (jQuery(this).css("zIndex") == 2) {
            /* For images.*/
            var bwg_current_image_span = jQuery(this).find("img");
            if (bwg_current_image_span.length) {
              if (bwg_current_image_span.prop('complete')) {
                var width = bwg_current_image_span.width();
                var height = bwg_current_image_span.height();
                bwg_change_each_watermark_container_<?php echo $bwg; ?>(width, height);
              }
              else {
                bwg_current_image_span.on("load", function () {
                  var width = bwg_current_image_span.width();
                  var height = bwg_current_image_span.height();
                  bwg_change_each_watermark_container_<?php echo $bwg; ?>(width, height);
                });
              }
            }
            else {
              /* For embeds and videos.*/
              bwg_current_image_span = jQuery(this).find("iframe");
              if (!bwg_current_image_span.length) {
                bwg_current_image_span = jQuery(this).find("video");
              }
              var width = bwg_current_image_span.width();
              var height = bwg_current_image_span.height();
              bwg_change_each_watermark_container_<?php echo $bwg; ?>(width, height);
            }
          }
        });
      }
      /* Set each watermark container size.*/
      function bwg_change_each_watermark_container_<?php echo $bwg; ?>(width, height) {
        jQuery(".bwg_slideshow_watermark_spun_<?php echo $bwg; ?>").width(width);
        jQuery(".bwg_slideshow_watermark_spun_<?php echo $bwg; ?>").height(height);
        jQuery(".bwg_slideshow_title_spun_<?php echo $bwg; ?>").width(width);
        jQuery(".bwg_slideshow_title_spun_<?php echo $bwg; ?>").height(height);
        jQuery(".bwg_slideshow_description_spun_<?php echo $bwg; ?>").width(width);
        jQuery(".bwg_slideshow_description_spun_<?php echo $bwg; ?>").height(height);
        jQuery(".bwg_slideshow_watermark_<?php echo $bwg; ?>").css({display: ''});
        if (jQuery.trim(jQuery(".bwg_slideshow_title_text_<?php echo $bwg; ?>").text())) {
          jQuery(".bwg_slideshow_title_text_<?php echo $bwg; ?>").css({display: ''});
        }
        if (jQuery.trim(jQuery(".bwg_slideshow_description_text_<?php echo $bwg; ?>").text())) {
          jQuery(".bwg_slideshow_description_text_<?php echo $bwg; ?>").css({display: ''});
        }
      }
      var bwg_current_key_<?php echo $bwg; ?> = '<?php echo (isset($current_key) ? $current_key : ''); ?>';
      var bwg_current_filmstrip_pos_<?php echo $bwg; ?> = <?php echo $current_pos; ?>;
      /* Set filmstrip initial position.*/
      function bwg_set_filmstrip_pos_<?php echo $bwg; ?>(filmStripWidth) {
        var selectedImagePos = -bwg_current_filmstrip_pos_<?php echo $bwg; ?> - (jQuery(".bwg_slideshow_filmstrip_thumbnail_<?php echo $bwg; ?>").width() + <?php echo $filmstrip_thumb_margin_hor; ?>) / 2;
        var imagesContainerLeft = Math.min(0, Math.max(filmStripWidth - jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").width(), selectedImagePos + filmStripWidth / 2));
        jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").animate({
            left: imagesContainerLeft
          }, {
            duration: 500,
            complete: function () { bwg_filmstrip_arrows_<?php echo $bwg; ?>(); }
          });
      }
      function bwg_move_filmstrip_<?php echo $bwg; ?>() {
        var image_left = jQuery(".bwg_slideshow_thumb_active_<?php echo $bwg; ?>").position().left;
        var image_right = jQuery(".bwg_slideshow_thumb_active_<?php echo $bwg; ?>").position().left + jQuery(".bwg_slideshow_thumb_active_<?php echo $bwg; ?>").outerWidth(true);
        var bwg_filmstrip_width = jQuery(".bwg_slideshow_filmstrip_<?php echo $bwg; ?>").outerWidth(true);
        var bwg_filmstrip_thumbnails_width = jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").outerWidth(true);
        var long_filmstrip_cont_left = jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left;
        var long_filmstrip_cont_right = Math.abs(jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left) + bwg_filmstrip_width;
        if (bwg_filmstrip_width > bwg_filmstrip_thumbnails_width) {
          return;
        }
        if (image_left < Math.abs(long_filmstrip_cont_left)) {
          jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").animate({
            left: -image_left
          }, {
            duration: 500,
            complete: function () { bwg_filmstrip_arrows_<?php echo $bwg; ?>(); }
          });
        }
        else if (image_right > long_filmstrip_cont_right) {
          jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").animate({
            left: -(image_right - bwg_filmstrip_width)
          }, {
            duration: 500,
            complete: function () { bwg_filmstrip_arrows_<?php echo $bwg; ?>(); }
          });
        }
      }
      function bwg_move_dots_<?php echo $bwg; ?>() {
        var image_left = jQuery(".bwg_slideshow_dots_active_<?php echo $bwg; ?>").position().left;
        var image_right = jQuery(".bwg_slideshow_dots_active_<?php echo $bwg; ?>").position().left + jQuery(".bwg_slideshow_dots_active_<?php echo $bwg; ?>").outerWidth(true);
        var bwg_dots_width = jQuery(".bwg_slideshow_dots_container_<?php echo $bwg; ?>").outerWidth(true);
        var bwg_dots_thumbnails_width = jQuery(".bwg_slideshow_dots_thumbnails_<?php echo $bwg; ?>").outerWidth(false);
        var long_filmstrip_cont_left = jQuery(".bwg_slideshow_dots_thumbnails_<?php echo $bwg; ?>").position().left;
        var long_filmstrip_cont_right = Math.abs(jQuery(".bwg_slideshow_dots_thumbnails_<?php echo $bwg; ?>").position().left) + bwg_dots_width;
        if (bwg_dots_width > bwg_dots_thumbnails_width) {
          return;
        }
        if (image_left < Math.abs(long_filmstrip_cont_left)) {
          jQuery(".bwg_slideshow_dots_thumbnails_<?php echo $bwg; ?>").animate({
            left: -image_left
          }, {
            duration: 500,
            complete: function () {  }
          });
        }
        else if (image_right > long_filmstrip_cont_right) {
          jQuery(".bwg_slideshow_dots_thumbnails_<?php echo $bwg; ?>").animate({
            left: -(image_right - bwg_dots_width)
          }, {
            duration: 500,
            complete: function () {  }
          });
        }
      }
      /* Show/hide filmstrip arrows.*/
      function bwg_filmstrip_arrows_<?php echo $bwg; ?>() {
        if (jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").width() < jQuery(".bwg_slideshow_filmstrip_<?php echo $bwg; ?>").width()) {
          jQuery(".bwg_slideshow_filmstrip_left_<?php echo $bwg; ?>").hide();
          jQuery(".bwg_slideshow_filmstrip_right_<?php echo $bwg; ?>").hide();
        }
        else {
          jQuery(".bwg_slideshow_filmstrip_left_<?php echo $bwg; ?>").show();
          jQuery(".bwg_slideshow_filmstrip_right_<?php echo $bwg; ?>").show();
        }
      }
      function bwg_testBrowser_cssTransitions_<?php echo $bwg; ?>() {
        return bwg_testDom_<?php echo $bwg; ?>('Transition');
      }
      function bwg_testBrowser_cssTransforms3d_<?php echo $bwg; ?>() {
        return bwg_testDom_<?php echo $bwg; ?>('Perspective');
      }
      function bwg_testDom_<?php echo $bwg; ?>(prop) {
        /* Browser vendor CSS prefixes.*/
        var browserVendors = ['', '-webkit-', '-moz-', '-ms-', '-o-', '-khtml-'];
        /* Browser vendor DOM prefixes.*/
        var domPrefixes = ['', 'Webkit', 'Moz', 'ms', 'O', 'Khtml'];
        var i = domPrefixes.length;
        while (i--) {
          if (typeof document.body.style[domPrefixes[i] + prop] !== 'undefined') {
            return true;
          }
        }
        return false;
      }
      function bwg_cube_<?php echo $bwg; ?>(tz, ntx, nty, nrx, nry, wrx, wry, current_image_class, next_image_class, direction) {
        /* If browser does not support 3d transforms/CSS transitions.*/
        if (!bwg_testBrowser_cssTransitions_<?php echo $bwg; ?>()) {
          return bwg_fallback_<?php echo $bwg; ?>(current_image_class, next_image_class, direction);
        }
        if (!bwg_testBrowser_cssTransforms3d_<?php echo $bwg; ?>()) {
          return bwg_fallback3d_<?php echo $bwg; ?>(current_image_class, next_image_class, direction);
        }
        bwg_trans_in_progress_<?php echo $bwg; ?> = true;
        /* Set active thumbnail.*/
        jQuery(".bwg_slideshow_filmstrip_thumbnail_<?php echo $bwg; ?>").removeClass("bwg_slideshow_thumb_active_<?php echo $bwg; ?>").addClass("bwg_slideshow_thumb_deactive_<?php echo $bwg; ?>");
        jQuery("#bwg_filmstrip_thumbnail_" + bwg_current_key_<?php echo $bwg; ?> + "_<?php echo $bwg; ?>").removeClass("bwg_slideshow_thumb_deactive_<?php echo $bwg; ?>").addClass("bwg_slideshow_thumb_active_<?php echo $bwg; ?>");
        jQuery(".bwg_slideshow_dots_<?php echo $bwg; ?>").removeClass("bwg_slideshow_dots_active_<?php echo $bwg; ?>").addClass("bwg_slideshow_dots_deactive_<?php echo $bwg; ?>");
        jQuery("#bwg_dots_" + bwg_current_key_<?php echo $bwg; ?> + "_<?php echo $bwg; ?>").removeClass("bwg_slideshow_dots_deactive_<?php echo $bwg; ?>").addClass("bwg_slideshow_dots_active_<?php echo $bwg; ?>");
        jQuery(".bwg_slide_bg_<?php echo $bwg; ?>").css('perspective', 1000);
        jQuery(current_image_class).css({
          transform : 'translateZ(' + tz + 'px)',
          backfaceVisibility : 'hidden'
        });
        jQuery(next_image_class).css({
          opacity : 1,
          filter: 'Alpha(opacity=100)',
          zIndex: 2,
          backfaceVisibility : 'hidden',
          transform : 'translateY(' + nty + 'px) translateX(' + ntx + 'px) rotateY('+ nry +'deg) rotateX('+ nrx +'deg)'
        });
        jQuery(".bwg_slider_<?php echo $bwg; ?>").css({
          transform: 'translateZ(-' + tz + 'px)',
          transformStyle: 'preserve-3d'
        });
        /* Execution steps.*/
        setTimeout(function () {
          jQuery(".bwg_slider_<?php echo $bwg; ?>").css({
            transition: 'all ' + bwg_transition_duration_<?php echo $bwg; ?> + 'ms ease-in-out',
            transform: 'translateZ(-' + tz + 'px) rotateX('+ wrx +'deg) rotateY('+ wry +'deg)'
          });
        }, 20);
        /* After transition.*/
        jQuery(".bwg_slider_<?php echo $bwg; ?>").one('webkitTransitionEnd transitionend otransitionend oTransitionEnd mstransitionend', jQuery.proxy(bwg_after_trans));
        function bwg_after_trans() {
          /*if (bwg_from_focus_<?php echo $bwg; ?>) {
            bwg_from_focus_<?php echo $bwg; ?> = false;
            return;
          }*/
          jQuery(current_image_class).removeAttr('style');
          jQuery(next_image_class).removeAttr('style');
          jQuery(".bwg_slider_<?php echo $bwg; ?>").removeAttr('style');
          jQuery(current_image_class).css({'opacity' : 0, filter: 'Alpha(opacity=0)', 'z-index': 1});
          jQuery(next_image_class).css({'opacity' : 1, filter: 'Alpha(opacity=100)', 'z-index' : 2});
          bwg_change_watermark_container_<?php echo $bwg; ?>();
          bwg_trans_in_progress_<?php echo $bwg; ?> = false;
          if (typeof event_stack_<?php echo $bwg; ?> !== 'undefined') {
            if (event_stack_<?php echo $bwg; ?>.length > 0) {
              key = event_stack_<?php echo $bwg; ?>[0].split("-");
              event_stack_<?php echo $bwg; ?>.shift();
              bwg_change_image_<?php echo $bwg; ?>(key[0], key[1], data_<?php echo $bwg; ?>, true);
            }
          }
        }
      }
      function bwg_cubeH_<?php echo $bwg; ?>(current_image_class, next_image_class, direction) {
        /* Set to half of image width.*/
        var dimension = jQuery(current_image_class).width() / 2;
        if (direction == 'right') {
          bwg_cube_<?php echo $bwg; ?>(dimension, dimension, 0, 0, 90, 0, -90, current_image_class, next_image_class, direction);
        }
        else if (direction == 'left') {
          bwg_cube_<?php echo $bwg; ?>(dimension, -dimension, 0, 0, -90, 0, 90, current_image_class, next_image_class, direction);
        }
      }
      /* For browsers that does not support transitions.*/
      function bwg_fallback_<?php echo $bwg; ?>(current_image_class, next_image_class, direction) {
        bwg_fade_<?php echo $bwg; ?>(current_image_class, next_image_class, direction);
      }
      /* For browsers that support transitions, but not 3d transforms (only used if primary transition makes use of 3d-transforms).*/
      function bwg_fallback3d_<?php echo $bwg; ?>(current_image_class, next_image_class, direction) {
        bwg_sliceV_<?php echo $bwg; ?>(current_image_class, next_image_class, direction);
      }
      function bwg_none_<?php echo $bwg; ?>(current_image_class, next_image_class, direction) {
        jQuery(current_image_class).css({'opacity' : 0, 'z-index': 1});
        jQuery(next_image_class).css({'opacity' : 1, 'z-index' : 2});
        bwg_change_watermark_container_<?php echo $bwg; ?>();
        /* Set active thumbnail.*/
        jQuery(".bwg_slideshow_filmstrip_thumbnail_<?php echo $bwg; ?>").removeClass("bwg_slideshow_thumb_active_<?php echo $bwg; ?>").addClass("bwg_slideshow_thumb_deactive_<?php echo $bwg; ?>");
        jQuery("#bwg_filmstrip_thumbnail_" + bwg_current_key_<?php echo $bwg; ?> + "_<?php echo $bwg; ?>").removeClass("bwg_slideshow_thumb_deactive_<?php echo $bwg; ?>").addClass("bwg_slideshow_thumb_active_<?php echo $bwg; ?>");
        jQuery(".bwg_slideshow_dots_<?php echo $bwg; ?>").removeClass("bwg_slideshow_dots_active_<?php echo $bwg; ?>").addClass("bwg_slideshow_dots_deactive_<?php echo $bwg; ?>");
        jQuery("#bwg_dots_" + bwg_current_key_<?php echo $bwg; ?> + "_<?php echo $bwg; ?>").removeClass("bwg_slideshow_dots_deactive_<?php echo $bwg; ?>").addClass("bwg_slideshow_dots_active_<?php echo $bwg; ?>");
      }
      function bwg_fade_<?php echo $bwg; ?>(current_image_class, next_image_class, direction) {
        /* Set active thumbnail.*/
        jQuery(".bwg_slideshow_filmstrip_thumbnail_<?php echo $bwg; ?>").removeClass("bwg_slideshow_thumb_active_<?php echo $bwg; ?>").addClass("bwg_slideshow_thumb_deactive_<?php echo $bwg; ?>");
        jQuery("#bwg_filmstrip_thumbnail_" + bwg_current_key_<?php echo $bwg; ?> + "_<?php echo $bwg; ?>").removeClass("bwg_slideshow_thumb_deactive_<?php echo $bwg; ?>").addClass("bwg_slideshow_thumb_active_<?php echo $bwg; ?>");
        jQuery(".bwg_slideshow_dots_<?php echo $bwg; ?>").removeClass("bwg_slideshow_dots_active_<?php echo $bwg; ?>").addClass("bwg_slideshow_dots_deactive_<?php echo $bwg; ?>");
        jQuery("#bwg_dots_" + bwg_current_key_<?php echo $bwg; ?> + "_<?php echo $bwg; ?>").removeClass("bwg_slideshow_dots_deactive_<?php echo $bwg; ?>").addClass("bwg_slideshow_dots_active_<?php echo $bwg; ?>");
        if (bwg_testBrowser_cssTransitions_<?php echo $bwg; ?>()) {
          jQuery(next_image_class).css('transition', 'opacity ' + bwg_transition_duration_<?php echo $bwg; ?> + 'ms linear');
          jQuery(current_image_class).css({'opacity' : 0, 'z-index': 1});
          jQuery(next_image_class).css({'opacity' : 1, 'z-index' : 2});
          bwg_change_watermark_container_<?php echo $bwg; ?>();
        }
        else {
          jQuery(current_image_class).animate({'opacity' : 0, 'z-index' : 1}, bwg_transition_duration_<?php echo $bwg; ?>);
          jQuery(next_image_class).animate({
              'opacity' : 1,
              'z-index': 2
            }, {
              duration: bwg_transition_duration_<?php echo $bwg; ?>,
              complete: function () { bwg_change_watermark_container_<?php echo $bwg; ?>(); }
            });
          /* For IE.*/
          jQuery(current_image_class).fadeTo(bwg_transition_duration_<?php echo $bwg; ?>, 0);
          jQuery(next_image_class).fadeTo(bwg_transition_duration_<?php echo $bwg; ?>, 1);
        }
      }
      function bwg_grid_<?php echo $bwg; ?>(cols, rows, ro, tx, ty, sc, op, current_image_class, next_image_class, direction) {
        /* If browser does not support CSS transitions.*/
        if (!bwg_testBrowser_cssTransitions_<?php echo $bwg; ?>()) {
          return bwg_fallback_<?php echo $bwg; ?>(current_image_class, next_image_class, direction);
        }
        bwg_trans_in_progress_<?php echo $bwg; ?> = true;
        /* Set active thumbnail.*/
        jQuery(".bwg_slideshow_filmstrip_thumbnail_<?php echo $bwg; ?>").removeClass("bwg_slideshow_thumb_active_<?php echo $bwg; ?>").addClass("bwg_slideshow_thumb_deactive_<?php echo $bwg; ?>");
        jQuery("#bwg_filmstrip_thumbnail_" + bwg_current_key_<?php echo $bwg; ?> + "_<?php echo $bwg; ?>").removeClass("bwg_slideshow_thumb_deactive_<?php echo $bwg; ?>").addClass("bwg_slideshow_thumb_active_<?php echo $bwg; ?>");
        jQuery(".bwg_slideshow_dots_<?php echo $bwg; ?>").removeClass("bwg_slideshow_dots_active_<?php echo $bwg; ?>").addClass("bwg_slideshow_dots_deactive_<?php echo $bwg; ?>");
        jQuery("#bwg_dots_" + bwg_current_key_<?php echo $bwg; ?> + "_<?php echo $bwg; ?>").removeClass("bwg_slideshow_dots_deactive_<?php echo $bwg; ?>").addClass("bwg_slideshow_dots_active_<?php echo $bwg; ?>");
        /* The time (in ms) added to/subtracted from the delay total for each new gridlet.*/
        var count = (bwg_transition_duration_<?php echo $bwg; ?>) / (cols + rows);
        /* Gridlet creator (divisions of the image grid, positioned with background-images to replicate the look of an entire slide image when assembled)*/
        function bwg_gridlet(width, height, top, img_top, left, img_left, src, imgWidth, imgHeight, c, r) {
          var delay = (c + r) * count;
          /* Return a gridlet elem with styles for specific transition.*/
          return jQuery('<span class="bwg_gridlet_<?php echo $bwg; ?>" />').css({
            display : "block",
            width : width,
            height : height,
            top : top,
            left : left,
            backgroundImage : 'url("' + src + '")',
            backgroundColor: jQuery(".bwg_slideshow_image_wrap_<?php echo $bwg; ?>").css("background-color"),
            /*backgroundColor: rgba(0, 0, 0, 0),*/
            backgroundRepeat: 'no-repeat',
            backgroundPosition : img_left + 'px ' + img_top + 'px',
            backgroundSize : imgWidth + 'px ' + imgHeight + 'px',
            transition : 'all ' + bwg_transition_duration_<?php echo $bwg; ?> + 'ms ease-in-out ' + delay + 'ms',
            transform : 'none'
          });
        }
        /* Get the current slide's image.*/
        var cur_img = jQuery(current_image_class).find('img');
        /* Create a grid to hold the gridlets.*/
        var grid = jQuery('<span style="display: block;" />').addClass('bwg_grid_<?php echo $bwg; ?>');
        /* Prepend the grid to the next slide (i.e. so it's above the slide image).*/
        jQuery(current_image_class).prepend(grid);
        /* vars to calculate positioning/size of gridlets*/
        var cont = jQuery(".bwg_slide_bg_<?php echo $bwg; ?>");
        var imgWidth = cur_img.width();
        var imgHeight = cur_img.height();
        var contWidth = cont.width(),
            contHeight = cont.height(),
            imgSrc = cur_img.attr('src'),/*.replace('/thumb', ''),*/
            colWidth = Math.floor(contWidth / cols),
            rowHeight = Math.floor(contHeight / rows),
            colRemainder = contWidth - (cols * colWidth),
            colAdd = Math.ceil(colRemainder / cols),
            rowRemainder = contHeight - (rows * rowHeight),
            rowAdd = Math.ceil(rowRemainder / rows),
            leftDist = 0,
            img_leftDist = (jQuery(".bwg_slide_bg_<?php echo $bwg; ?>").width() - cur_img.width()) / 2;
        /* tx/ty args can be passed as 'auto'/'min-auto' (meaning use slide width/height or negative slide width/height).*/
        tx = tx === 'auto' ? contWidth : tx;
        tx = tx === 'min-auto' ? - contWidth : tx;
        ty = ty === 'auto' ? contHeight : ty;
        ty = ty === 'min-auto' ? - contHeight : ty;
        /* Loop through cols*/
        for (var i = 0; i < cols; i++) {
          var topDist = 0,
              img_topDst = (jQuery(".bwg_slide_bg_<?php echo $bwg; ?>").height() - cur_img.height()) / 2,
              newColWidth = colWidth;
          /* If imgWidth (px) does not divide cleanly into the specified number of cols, adjust individual col widths to create correct total.*/
          if (colRemainder > 0) {
            var add = colRemainder >= colAdd ? colAdd : colRemainder;
            newColWidth += add;
            colRemainder -= add;
          }
          /* Nested loop to create row gridlets for each col.*/
          for (var j = 0; j < rows; j++)  {
            var newRowHeight = rowHeight,
                newRowRemainder = rowRemainder;
            /* If contHeight (px) does not divide cleanly into the specified number of rows, adjust individual row heights to create correct total.*/
            if (newRowRemainder > 0) {
              add = newRowRemainder >= rowAdd ? rowAdd : rowRemainder;
              newRowHeight += add;
              newRowRemainder -= add;
            }
            /* Create & append gridlet to grid.*/
            grid.append(bwg_gridlet(newColWidth, newRowHeight, topDist, img_topDst, leftDist, img_leftDist, imgSrc, imgWidth, imgHeight, i, j));
            topDist += newRowHeight;
            img_topDst -= newRowHeight;
          }
          img_leftDist -= newColWidth;
          leftDist += newColWidth;
        }
        /* Set event listener on last gridlet to finish transitioning.*/
        var last_gridlet = grid.children().last();
        /* Show grid & hide the image it replaces.*/
        grid.show();
        cur_img.css('opacity', 0);
        /* Add identifying classes to corner gridlets (useful if applying border radius).*/
        grid.children().first().addClass('rs-top-left');
        grid.children().last().addClass('rs-bottom-right');
        grid.children().eq(rows - 1).addClass('rs-bottom-left');
        grid.children().eq(- rows).addClass('rs-top-right');
        /* Execution steps.*/
        setTimeout(function () {
          grid.children().css({
            opacity: op,
            transform: 'rotate('+ ro +'deg) translateX('+ tx +'px) translateY('+ ty +'px) scale('+ sc +')'
          });
        }, 1);
        jQuery(next_image_class).css('opacity', 1);
        /* After transition.*/
        jQuery(last_gridlet).one('webkitTransitionEnd transitionend otransitionend oTransitionEnd mstransitionend', jQuery.proxy(bwg_after_trans));
        function bwg_after_trans() {
          /*if (bwg_from_focus_<?php echo $bwg; ?>) {
            bwg_from_focus_<?php echo $bwg; ?> = false;
            return;
          }*/
          jQuery(current_image_class).css({'opacity' : 0, 'z-index': 1});
          jQuery(next_image_class).css({'opacity' : 1, 'z-index' : 2});
          cur_img.css('opacity', 1);
          bwg_change_watermark_container_<?php echo $bwg; ?>();
          grid.remove();
          bwg_trans_in_progress_<?php echo $bwg; ?> = false;
          if (typeof event_stack_<?php echo $bwg; ?> !== 'undefined') {
            if (event_stack_<?php echo $bwg; ?>.length > 0) {
              key = event_stack_<?php echo $bwg; ?>[0].split("-");
              event_stack_<?php echo $bwg; ?>.shift();
              bwg_change_image_<?php echo $bwg; ?>(key[0], key[1], data_<?php echo $bwg; ?>, true);
            }
          }
        }
      }
      function bwg_sliceV_<?php echo $bwg; ?>(current_image_class, next_image_class, direction) {
        if (direction == 'right') {
          var translateY = 'min-auto';
        }
        else if (direction == 'left') {
          var translateY = 'auto';
        }
        bwg_grid_<?php echo $bwg; ?>(10, 1, 0, 0, translateY, 1, 0, current_image_class, next_image_class, direction);
      }
      function bwg_scaleOut_<?php echo $bwg; ?>(current_image_class, next_image_class, direction) {
        bwg_grid_<?php echo $bwg; ?>(1, 1, 0, 0, 0, 1.5, 0, current_image_class, next_image_class, direction);
      }
      function bwg_blindH_<?php echo $bwg; ?>(current_image_class, next_image_class, direction) {
        bwg_grid_<?php echo $bwg; ?>(10, 1, 0, 0, 0, .7, 0, current_image_class, next_image_class);
      }
      function bwg_iterator_<?php echo $bwg; ?>() {
        var iterator = 1;
        if (<?php echo $enable_slideshow_shuffle; ?>) {
          iterator = Math.floor((data_<?php echo $bwg; ?>.length - 1) * Math.random() + 1);
        }
        return iterator;
      }
      function bwg_change_image_<?php echo $bwg; ?>(current_key, key, data_<?php echo $bwg; ?>, from_effect) {
        /* Pause videos.*/
        jQuery("#bwg_slideshow_image_container_<?php echo $bwg; ?>").find("iframe").each(function () {
          jQuery(this)[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
          jQuery(this)[0].contentWindow.postMessage('{ "method": "pause" }', "*");
          jQuery(this)[0].contentWindow.postMessage('pause', '*');
        });
        if (data_<?php echo $bwg; ?>[key]) {
          if (jQuery('.bwg_ctrl_btn_<?php echo $bwg; ?>').hasClass('fa-pause')) {
            bwg_play_<?php echo $bwg; ?>();
          }
          if (!from_effect) {
            /* Change image key.*/
            jQuery("#bwg_current_image_key_<?php echo $bwg; ?>").val(key);
            if (current_key == '-1') { /* Filmstrip.*/
              current_key = jQuery(".bwg_slideshow_thumb_active_<?php echo $bwg; ?>").children("img").attr("image_key");
            }
            else if (current_key == '-2') { /* Dots.*/
              current_key = jQuery(".bwg_slideshow_dots_active_<?php echo $bwg; ?>").attr("image_key");
            }
          }
          if (bwg_trans_in_progress_<?php echo $bwg; ?>) {
            event_stack_<?php echo $bwg; ?>.push(current_key + '-' + key);
            return;
          }
          var direction = 'right';
          if (bwg_current_key_<?php echo $bwg; ?> > key) {
            var direction = 'left';
          }
          else if (bwg_current_key_<?php echo $bwg; ?> == key) {
            return;
          }
          jQuery(".bwg_slideshow_watermark_<?php echo $bwg; ?>").css({display: 'none'});
          jQuery(".bwg_slideshow_title_text_<?php echo $bwg; ?>").css({display: 'none'});
          jQuery(".bwg_slideshow_description_text_<?php echo $bwg; ?>").css({display: 'none'});
          /* Set active thumbnail position.*/
          bwg_current_filmstrip_pos_<?php echo $bwg; ?> = key * (jQuery(".bwg_slideshow_filmstrip_thumbnail_<?php echo $bwg; ?>").width() + 2 + 2 * <?php echo $theme_row->lightbox_filmstrip_thumb_border_width; ?>);
          bwg_current_key_<?php echo $bwg; ?> = key;
          /* Change image id, title, description.*/
          jQuery("#bwg_slideshow_image_<?php echo $bwg; ?>").attr('image_id', data_<?php echo $bwg; ?>[key]["id"]);
          jQuery(".bwg_slideshow_title_text_<?php echo $bwg; ?>").html(jQuery('<span style="display: block;" />').html(data_<?php echo $bwg; ?>[key]["alt"]).text());
          jQuery(".bwg_slideshow_description_text_<?php echo $bwg; ?>").html(jQuery('<span style="display: block;" />').html(data_<?php echo $bwg; ?>[key]["description"]).text());
          /*var current_image_class = "#image_id_<?php echo $bwg; ?>_" + data_<?php echo $bwg; ?>[current_key]["id"];
          var next_image_class = "#image_id_<?php echo $bwg; ?>_" + data_<?php echo $bwg; ?>[key]["id"];*/
          var current_image_class = jQuery(".bwg_slideshow_image_spun_<?php echo $bwg; ?>").css("zIndex") == 2 ? ".bwg_slideshow_image_spun_<?php echo $bwg; ?>" : ".bwg_slideshow_image_second_spun_<?php echo $bwg; ?>";
          var next_image_class = current_image_class == ".bwg_slideshow_image_second_spun_<?php echo $bwg; ?>" ? ".bwg_slideshow_image_spun_<?php echo $bwg; ?>" : ".bwg_slideshow_image_second_spun_<?php echo $bwg; ?>";
          var is_embed = data_<?php echo $bwg; ?>[key]['filetype'].indexOf("EMBED_") > -1 ? true : false;
          var is_embed_instagram_post = data_<?php echo $bwg; ?>[key]['filetype'].indexOf('INSTAGRAM_POST') > -1 ? true :false;
          var cur_height = jQuery(current_image_class).height();
          var cur_width = jQuery(current_image_class).width();
          var innhtml = '<span class="bwg_slideshow_image_spun1_<?php echo $bwg; ?>"><span class="bwg_slideshow_image_spun2_<?php echo $bwg; ?>" style="display:table-cell;">';
          if (!is_embed) {
            <?php
              if ($params['thumb_click_action'] != 'do_nothing' ) {
            ?>
                innhtml += '<a' + <?php if ($params['thumb_click_action'] == 'open_lightbox') { echo '\' class="bwg_lightbox_' . $bwg . '" data-image-id="\' + data_' . $bwg . '[key][\'id\'] + \'"'; }  else { echo ($params['thumb_click_action'] == 'redirect_to_url' && 'data_' . $bwg . '[key][\'redirect_url\']') ? '\' href="\'+ data_' . $bwg . '[key][\'redirect_url\']+\'" target="' . ($params['thumb_link_target'] ? '_blank' : '')  . '"' : '';} ?> '+'>';
            <?php    
              }
            ?>
              innhtml += '<img style="max-height: ' + cur_height + 'px !important; max-width: ' + cur_width + 'px !important; display:inline-block;" class="bwg_slideshow_image_<?php echo $bwg; ?>" id="bwg_slideshow_image_<?php echo $bwg; ?>" src="<?php echo site_url() . '/' . $WD_BWG_UPLOAD_DIR; ?>' + jQuery('<span style="display: block;" />').html(data_<?php echo $bwg; ?>[key]["image_url"]).text() + '" alt="' + data_<?php echo $bwg; ?>[key]["alt"] + '" image_id="' + data_<?php echo $bwg; ?>[key]["id"] + '" />';
          }
          else { /*is_embed*/
            innhtml += '<span style="height: ' + cur_height + 'px; width: ' + cur_width + 'px;" class="bwg_popup_embed bwg_popup_watermark">';
            if (is_embed_instagram_post) {
              var post_width = 0;
              var post_height = 0;
              if (cur_height < cur_width + 88) {
                post_height = cur_height;
                post_width = post_height - 88;
              }
              else {
                post_width = cur_width;
                post_height = post_width + 88;
              }
              innhtml += spider_display_embed(data_<?php echo $bwg; ?>[key]['filetype'], data_<?php echo $bwg; ?>[key]['filename'], {class:"bwg_embed_frame", 'data-width': data_<?php echo $bwg; ?>[key]['image_width'], 'data-height': data_<?php echo $bwg; ?>[key]['image_height'], frameborder: "0", allowfullscreen: "allowfullscreen", style: "width:" + post_width + "px; height:" + post_height + "px; vertical-align:middle; display:inline-block; position:relative;"});
            }
            else {
              innhtml += spider_display_embed(data_<?php echo $bwg; ?>[key]['filetype'], data_<?php echo $bwg; ?>[key]['filename'], {class:"bwg_embed_frame", frameborder:"0", allowfullscreen:"allowfullscreen", style:"width:inherit; height:inherit; vertical-align:middle; display:table-cell;" });
            }
            innhtml += "</span>";
          }
          innhtml += '</span></span>';
          jQuery(next_image_class).html(innhtml);
          if ('<?php echo $params['thumb_click_action']; ?>' == 'open_lightbox' || <?php echo $image_right_click; ?>) {
            bwg_document_ready_<?php echo $bwg; ?>();
          }
          <?php
          if ($wd_bwg_options->preload_images) {
            echo "bwg_preload_images_" .  $bwg . "(key);";
          }
          ?>
          bwg_<?php echo $slideshow_effect; ?>_<?php echo $bwg; ?>(current_image_class, next_image_class, direction);
          <?php
          if ($enable_slideshow_filmstrip) {
            ?>
            bwg_move_filmstrip_<?php echo $bwg; ?>();
            <?php
          }
          else {            
            ?>
            bwg_move_dots_<?php echo $bwg; ?>();
            <?php
          }
          ?>
          if (data_<?php echo $bwg; ?>[key]["is_embed_video"]) {
            jQuery("#bwg_slideshow_play_pause_<?php echo $bwg; ?>").css({display: 'none'});
          }
          else {
            jQuery("#bwg_slideshow_play_pause_<?php echo $bwg; ?>").css({display: ''});            
          }
        }
      }
      function bwg_preload_images_<?php echo $bwg; ?>(key) {
        count = <?php echo (int) $wd_bwg_options->preload_images_count / 2; ?>;
        var count_all = data_<?php echo $bwg; ?>.length;
        if (count_all < <?php echo $wd_bwg_options->preload_images_count; ?>) {
          count = 0;
        }
        if (count != 0) {
          for (var i = key - count; i < key + count; i++) {
            var index = parseInt((i + count_all) % count_all);
            var is_embed = data_<?php echo $bwg; ?>[index]['filetype'].indexOf("EMBED_") > -1 ? true : false;
            if (typeof data_<?php echo $bwg; ?>[index] != "undefined") {
              if (!is_embed) {
                jQuery("<img/>").attr("src", '<?php echo site_url() . '/' . $WD_BWG_UPLOAD_DIR; ?>' + jQuery('<span style="display: block;" />').html(data_<?php echo $bwg; ?>[index]["image_url"]).text());
              }
            }
          }
        }
        else {
          for (var i = 0; i < data_<?php echo $bwg; ?>.length; i++) {
            var is_embed = data_<?php echo $bwg; ?>[i]['filetype'].indexOf("EMBED_") > -1 ? true : false;
            if (typeof data_<?php echo $bwg; ?>[i] != "undefined") {
              if (!is_embed) {
                jQuery("<img/>").attr("src", '<?php echo site_url() . '/' . $WD_BWG_UPLOAD_DIR; ?>' + jQuery('<span style="display: block;" />').html(data_<?php echo $bwg; ?>[i]["image_url"]).text());
              }
            }
          }
        }
      }
      function bwg_popup_resize_<?php echo $bwg; ?>() {
        var parent_width = jQuery(".bwg_slideshow_image_wrap_<?php echo $bwg; ?>").parent().width();
        if (parent_width >= <?php echo $image_width; ?>) {
          jQuery(".bwg_slideshow_image_wrap_<?php echo $bwg; ?>").css({width: <?php echo $image_width; ?>});
          jQuery(".bwg_slideshow_image_wrap_<?php echo $bwg; ?>").css({height: <?php echo $image_height; ?>});
          jQuery(".bwg_slideshow_image_container_<?php echo $bwg; ?>").css({width: <?php echo $image_width; ?>});
          jQuery(".bwg_slideshow_image_container_<?php echo $bwg; ?>").css({height: (<?php echo $image_height - $slideshow_filmstrip_height; ?>)});
          jQuery(".bwg_slideshow_image_<?php echo $bwg; ?>").css({
            cssText: "max-width: <?php echo $image_width; ?>px !important; max-height: <?php echo $image_height - $slideshow_filmstrip_height; ?>px !important;"
          });
          jQuery(".bwg_slideshow_embed_<?php echo $bwg; ?>").css({
            cssText: "width: <?php echo $image_width; ?>px !important; height: <?php echo $image_height - $slideshow_filmstrip_height; ?>px !important;"
          });
          bwg_resize_instagram_post_<?php echo $bwg?>();
          /* Set watermark container size.*/
          bwg_change_watermark_container_<?php echo $bwg; ?>();
          jQuery(".bwg_slideshow_filmstrip_container_<?php echo $bwg; ?>").css({width: <?php echo $image_width; ?>});
          jQuery(".bwg_slideshow_filmstrip_<?php echo $bwg; ?>").css({width: (<?php echo $image_width; ?> - 40)});
          jQuery(".bwg_slideshow_dots_container_<?php echo $bwg; ?>").css({width: <?php echo $image_width; ?>});
          jQuery("#bwg_slideshow_play_pause-ico_<?php echo $bwg; ?>").css({fontSize: (<?php echo $theme_row->slideshow_play_pause_btn_size; ?>)});
          jQuery(".bwg_slideshow_watermark_image_<?php echo $bwg; ?>").css({maxWidth: <?php echo $watermark_width; ?>, maxHeight: <?php echo $watermark_height; ?>});
          jQuery(".bwg_slideshow_watermark_text_<?php echo $bwg; ?>, .bwg_slideshow_watermark_text_<?php echo $bwg; ?>:hover").css({fontSize: (<?php echo $watermark_font_size; ?>)});
          jQuery(".bwg_slideshow_title_text_<?php echo $bwg; ?>").css({fontSize: (<?php echo $theme_row->slideshow_title_font_size * 2; ?>)});
          jQuery(".bwg_slideshow_description_text_<?php echo $bwg; ?>").css({fontSize: (<?php echo $theme_row->slideshow_description_font_size * 2; ?>)});
        }
        else {
          jQuery(".bwg_slideshow_image_wrap_<?php echo $bwg; ?>").css({width: (parent_width)});
          jQuery(".bwg_slideshow_image_wrap_<?php echo $bwg; ?>").css({height: ((parent_width) * <?php echo $image_height / $image_width ?>)});
          jQuery(".bwg_slideshow_image_container_<?php echo $bwg; ?>").css({width: (parent_width)});
          jQuery(".bwg_slideshow_image_container_<?php echo $bwg; ?>").css({height: ((parent_width) * <?php echo $image_height / $image_width ?> - <?php echo $slideshow_filmstrip_height; ?>)});
          jQuery(".bwg_slideshow_image_<?php echo $bwg; ?>").css({
            cssText: "max-width: " + parent_width + "px !important; max-height: " + (parent_width * (<?php echo $image_height / $image_width ?>) - <?php echo $slideshow_filmstrip_height; ?> - 1) + "px !important;"
          });
          jQuery(".bwg_slideshow_embed_<?php echo $bwg; ?>").css({
            cssText: "width: " + parent_width + "px !important; height: " + (parent_width * (<?php echo $image_height / $image_width ?>) - <?php echo $slideshow_filmstrip_height; ?> - 1) + "px !important;"
          });
          bwg_resize_instagram_post_<?php echo $bwg?>();
          /* Set watermark container size.*/
          bwg_change_watermark_container_<?php echo $bwg; ?>();
          jQuery(".bwg_slideshow_filmstrip_container_<?php echo $bwg; ?>").css({width: (parent_width)});
          jQuery(".bwg_slideshow_filmstrip_<?php echo $bwg; ?>").css({width: (parent_width - 40)});
          jQuery(".bwg_slideshow_dots_container_<?php echo $bwg; ?>").css({width: (parent_width)});
          jQuery("#bwg_slideshow_play_pause-ico_<?php echo $bwg; ?>").css({fontSize: ((parent_width) * <?php echo $theme_row->slideshow_play_pause_btn_size / $image_width; ?>)});
          jQuery(".bwg_slideshow_watermark_image_<?php echo $bwg; ?>").css({maxWidth: ((parent_width) * <?php echo $watermark_width / $image_width; ?>), maxHeight: ((parent_width) * <?php echo $watermark_height / $image_width; ?>)});
          jQuery(".bwg_slideshow_watermark_text_<?php echo $bwg; ?>, .bwg_slideshow_watermark_text_<?php echo $bwg; ?>:hover").css({fontSize: ((parent_width) * <?php echo $watermark_font_size / $image_width; ?>)});
          jQuery(".bwg_slideshow_title_text_<?php echo $bwg; ?>").css({fontSize: ((parent_width) * <?php echo 2 * $theme_row->slideshow_title_font_size / $image_width; ?>)});
          jQuery(".bwg_slideshow_description_text_<?php echo $bwg; ?>").css({fontSize: ((parent_width) * <?php echo 2 * $theme_row->slideshow_description_font_size / $image_width; ?>)});
          jQuery(".bwg_slideshow_image_<?php echo $bwg; ?>").css({'display':'inline-block'});
        }
        if (data_<?php echo $bwg; ?>[parseInt(jQuery("#bwg_current_image_key_<?php echo $bwg; ?>").val())]["is_embed_video"]) {
          jQuery("#bwg_slideshow_play_pause_<?php echo $bwg; ?>").css({display: 'none'});
        }
        else {
          jQuery("#bwg_slideshow_play_pause_<?php echo $bwg; ?>").css({display: ''});            
        }
      }
      jQuery(document).ready(function() {
        bwg_popup_resize_<?php echo $bwg; ?>();
      });
      jQuery(document).ready(function () {
        jQuery('#ajax_loading_<?php echo $bwg; ?>').hide();
      	<?php
        if ($image_right_click) {
          ?>
          /* Disable right click.*/
          jQuery('div[id^="bwg_container"]').bind("contextmenu", function () {
            return false;
          });
          jQuery('div[id^="bwg_container"]').css('webkitTouchCallout','none');
          <?php
        }
        ?>
        if (typeof jQuery().swiperight !== 'undefined') {
          if (jQuery.isFunction(jQuery().swiperight)) {
            jQuery('#bwg_container1_<?php echo $bwg; ?>').swiperight(function () {
              bwg_change_image_<?php echo $bwg; ?>(parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()), (parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()) - bwg_iterator_<?php echo $bwg; ?>()) >= 0 ? (parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()) - bwg_iterator_<?php echo $bwg; ?>()) % data_<?php echo $bwg; ?>.length : data_<?php echo $bwg; ?>.length - 1, data_<?php echo $bwg; ?>);
              return false;
            });
          }
        }
        if (typeof jQuery().swipeleft !== 'undefined') {
          if (jQuery.isFunction(jQuery().swipeleft)) {
            jQuery('#bwg_container1_<?php echo $bwg; ?>').swipeleft(function () {
              bwg_change_image_<?php echo $bwg; ?>(parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()), (parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()) + bwg_iterator_<?php echo $bwg; ?>()) % data_<?php echo $bwg; ?>.length, data_<?php echo $bwg; ?>);
              return false;
            });
          }
        }
        var isMobile = (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()));
        var bwg_click = isMobile ? 'touchend' : 'click';
        bwg_popup_resize_<?php echo $bwg; ?>();
        jQuery("#bwg_container1_<?php echo $bwg; ?>").css({visibility: 'visible'});
        jQuery(".bwg_slideshow_watermark_<?php echo $bwg; ?>").css({display: 'none'});
        jQuery(".bwg_slideshow_title_text_<?php echo $bwg; ?>").css({display: 'none'});
        jQuery(".bwg_slideshow_description_text_<?php echo $bwg; ?>").css({display: 'none'});
        setTimeout(function () {
          bwg_change_watermark_container_<?php echo $bwg; ?>();
        }, 500);
        /* Set image container height.*/
        jQuery(".bwg_slideshow_image_container_<?php echo $bwg; ?>").height(jQuery(".bwg_slideshow_image_wrap_<?php echo $bwg; ?>").height() - <?php echo $slideshow_filmstrip_height; ?>);
        var mousewheelevt = (/Firefox/i.test(navigator.userAgent)) ? "DOMMouseScroll" : "mousewheel"; /* FF doesn't recognize mousewheel as of FF3.x */
        jQuery('.bwg_slideshow_filmstrip_<?php echo $bwg; ?>').bind(mousewheelevt, function(e) {
          var evt = window.event || e; /* Equalize event object.*/
          evt = evt.originalEvent ? evt.originalEvent : evt; /* Convert to originalEvent if possible.*/
          var delta = evt.detail ? evt.detail*(-40) : evt.wheelDelta; /* Check for detail first, because it is used by Opera and FF.*/
          if (delta > 0) {
            /* Scroll up.*/
            jQuery(".bwg_slideshow_filmstrip_left_<?php echo $bwg; ?>").trigger("click");
          }
          else {
            /* Scroll down.*/
            jQuery(".bwg_slideshow_filmstrip_right_<?php echo $bwg; ?>").trigger("click");
          }
          return false;
        });
        jQuery(".bwg_slideshow_filmstrip_right_<?php echo $bwg; ?>").on('click', function () {
          jQuery( ".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>" ).stop(true, false);
          if (jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left >= -(jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").width() - jQuery(".bwg_slideshow_filmstrip_<?php echo $bwg; ?>").width())) {
            jQuery(".bwg_slideshow_filmstrip_left_<?php echo $bwg; ?>").css({opacity: 1, filter: "Alpha(opacity=100)"});
            if (jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left < -(jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").width() - jQuery(".bwg_slideshow_filmstrip_<?php echo $bwg; ?>").width() - <?php echo $filmstrip_thumb_margin_hor + $slideshow_filmstrip_width; ?>)) {
              jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").animate({left: -(jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").width() - jQuery(".bwg_slideshow_filmstrip_<?php echo $bwg; ?>").width())}, 500, 'linear');
            }
            else {
              jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").animate({left: (jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left - <?php echo $filmstrip_thumb_margin_hor + $slideshow_filmstrip_width; ?>)}, 500, 'linear');
            }
          }
          /* Disable right arrow.*/
          window.setTimeout(function(){
            if (jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left == -(jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").width() - jQuery(".bwg_slideshow_filmstrip_<?php echo $bwg; ?>").width())) {
              jQuery(".bwg_slideshow_filmstrip_right_<?php echo $bwg; ?>").css({opacity: 0.3, filter: "Alpha(opacity=30)"});
            }
          }, 500);
        });
        jQuery(".bwg_slideshow_filmstrip_left_<?php echo $bwg; ?>").on('click', function () {
          jQuery( ".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>" ).stop(true, false);
          if (jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left < 0) {
            jQuery(".bwg_slideshow_filmstrip_right_<?php echo $bwg; ?>").css({opacity: 1, filter: "Alpha(opacity=100)"});
            if (jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left > - <?php echo $filmstrip_thumb_margin_hor + $slideshow_filmstrip_width; ?>) {
              jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").animate({left: 0}, 500, 'linear');
            }
            else {
              jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").animate({left: (jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left + <?php echo $filmstrip_thumb_margin_hor + $slideshow_filmstrip_width; ?>)}, 500, 'linear');
            }
          }
          /* Disable left arrow.*/
          window.setTimeout(function(){
            if (jQuery(".bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?>").position().left == 0) {
              jQuery(".bwg_slideshow_filmstrip_left_<?php echo $bwg; ?>").css({opacity: 0.3, filter: "Alpha(opacity=30)"});
            }
          }, 500);
        });
        /* Set filmstrip initial position.*/
        bwg_set_filmstrip_pos_<?php echo $bwg; ?>(jQuery(".bwg_slideshow_filmstrip_<?php echo $bwg; ?>").width());
        /* Play/pause.*/
        jQuery("#bwg_slideshow_play_pause_<?php echo $bwg; ?>").on('click', function () {
          if (jQuery(".bwg_ctrl_btn_<?php echo $bwg; ?>").hasClass("fa-play")) {
            bwg_play_<?php echo $bwg; ?>();
            jQuery(".bwg_slideshow_play_pause_<?php echo $bwg; ?>").attr("title", "<?php echo __('Pause', 'bwg'); ?>");
            jQuery(".bwg_slideshow_play_pause_<?php echo $bwg; ?>").attr("class", "bwg_ctrl_btn_<?php echo $bwg; ?> bwg_slideshow_play_pause_<?php echo $bwg; ?> fa fa-pause");
            if (<?php echo $enable_slideshow_music ?>) {
              document.getElementById("bwg_audio_<?php echo $bwg; ?>").play();
            }
          }
          else {
            /* Pause.*/
            window.clearInterval(bwg_playInterval_<?php echo $bwg; ?>);
            jQuery(".bwg_slideshow_play_pause_<?php echo $bwg; ?>").attr("title", "<?php echo __('Play', 'bwg'); ?>");
            jQuery(".bwg_slideshow_play_pause_<?php echo $bwg; ?>").attr("class", "bwg_ctrl_btn_<?php echo $bwg; ?> bwg_slideshow_play_pause_<?php echo $bwg; ?> fa fa-play");
            if (<?php echo $enable_slideshow_music ?>) {
              document.getElementById("bwg_audio_<?php echo $bwg; ?>").pause();
            }
          }
        });
        if (<?php echo $enable_slideshow_autoplay; ?>) {
          bwg_play_<?php echo $bwg; ?>();
          jQuery(".bwg_slideshow_play_pause_<?php echo $bwg; ?>").attr("title", "<?php echo __('Pause', 'bwg'); ?>");
          jQuery(".bwg_slideshow_play_pause_<?php echo $bwg; ?>").attr("class", "bwg_ctrl_btn_<?php echo $bwg; ?> bwg_slideshow_play_pause_<?php echo $bwg; ?> fa fa-pause");
          if (<?php echo $enable_slideshow_music ?>) {
            document.getElementById("bwg_audio_<?php echo $bwg; ?>").play();
          }
        }
        <?php
        if ($wd_bwg_options->preload_images) {
          echo "bwg_preload_images_" .  $bwg . "(parseInt(jQuery('#bwg_current_image_key_" . $bwg . "').val()));";
        }
        ?>
        jQuery(".bwg_slideshow_image_<?php echo $bwg; ?>").removeAttr("width");
        jQuery(".bwg_slideshow_image_<?php echo $bwg; ?>").removeAttr("height");
      });
      function bwg_resize_instagram_post_<?php echo $bwg?>() {
        if (jQuery('.inner_instagram_iframe_bwg_embed_frame_<?php echo $bwg?>').length) {
          var post_width = jQuery('.bwg_slideshow_embed_<?php echo $bwg?>').width();
          var post_height = jQuery('.bwg_slideshow_embed_<?php echo $bwg?>').height();
          jQuery('.inner_instagram_iframe_bwg_embed_frame_<?php echo $bwg?>').each(function() {
            var parent_container = jQuery(this).parent();
            if (post_height / (parseInt(parent_container.attr('data-height')) + 96) < post_width / parseInt(parent_container.attr('data-width'))) {
              parent_container.height(post_height);
              parent_container.width((parent_container.height() - 96) * parent_container.attr('data-width') / parent_container.attr('data-height') + 16);
            }
            else {
              parent_container.width(post_width);
              parent_container.height((parent_container.width() - 16) * parent_container.attr('data-height') / parent_container.attr('data-width') + 96);
            }
          });
        }
        bwg_change_watermark_container_<?php echo $bwg; ?>();
      }
      function bwg_play_<?php echo $bwg; ?>() {
        window.clearInterval(bwg_playInterval_<?php echo $bwg; ?>);
        /* Play.*/
        bwg_playInterval_<?php echo $bwg; ?> = setInterval(function () {
          var iterator = 1;
          if (<?php echo $enable_slideshow_shuffle; ?>) {
            iterator = Math.floor((data_<?php echo $bwg; ?>.length - 1) * Math.random() + 1);
          }
          bwg_change_image_<?php echo $bwg; ?>(parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()), (parseInt(jQuery('#bwg_current_image_key_<?php echo $bwg; ?>').val()) + iterator) % data_<?php echo $bwg; ?>.length, data_<?php echo $bwg; ?>)
        }, '<?php echo $slideshow_interval * 1000; ?>');
      }
      jQuery(window).focus(function() {
        /* event_stack_<?php echo $bwg; ?> = [];*/
        if (!jQuery(".bwg_ctrl_btn_<?php echo $bwg; ?>").hasClass("fa-play")) {
          bwg_play_<?php echo $bwg; ?>();
        }
        var i_<?php echo $bwg; ?> = 0;
        jQuery(".bwg_slider_<?php echo $bwg; ?>").children("span").each(function () {
          if (jQuery(this).css('opacity') == 1) {
            jQuery("#bwg_current_image_key_<?php echo $bwg; ?>").val(i_<?php echo $bwg; ?>);
          }
          i_<?php echo $bwg; ?>++;
        });
      });
      jQuery(window).blur(function() {
        event_stack_<?php echo $bwg; ?> = [];
        window.clearInterval(bwg_playInterval_<?php echo $bwg; ?>);
      });
      function bwg_gallery_box_<?php echo $bwg; ?>(image_id, openEcommerce) {
        if(typeof openEcommerce == undefined){
          openEcommerce = false;
        }
        var ecommerce = openEcommerce == true ? "&open_ecommerce=1" : "";
        var filterTags = jQuery("#bwg_tags_id_bwg_standart_thumbnails_<?php echo $bwg; ?>" ).val() ? jQuery("#bwg_tags_id_bwg_standart_thumbnails_<?php echo $bwg; ?>" ).val() : 0;
        var filtersearchname = jQuery("#bwg_search_input_<?php echo $bwg; ?>" ).val() ? "&filter_search_name_<?php echo $bwg; ?>=" + jQuery("#bwg_search_input_<?php echo $bwg; ?>" ).val() : '';
        spider_createpopup('<?php echo addslashes(add_query_arg($params_array, admin_url('admin-ajax.php'))); ?>&image_id=' + image_id + "&filter_tag_<?php echo $bwg; ?>=" +  filterTags + ecommerce + filtersearchname, '<?php echo $bwg; ?>', '<?php echo $params['popup_width']; ?>', '<?php echo $params['popup_height']; ?>', 1, 'testpopup', 5, "<?php echo $theme_row->lightbox_ctrl_btn_pos ;?>");
      }
      var bwg_hash = window.location.hash.substring(1);
        if (bwg_hash) {
          if (bwg_hash.indexOf("bwg") != "-1") {
            bwg_hash_array = bwg_hash.replace("bwg", "").split("/");
            if(bwg_hash_array[0] == "<?php echo $params_array['gallery_id']; ?>"){
              bwg_gallery_box_<?php echo $bwg; ?>(bwg_hash_array[1]);
            }
          }
        }
       function bwg_document_ready_<?php echo $bwg; ?>() {
        var bwg_touch_flag = false;
        jQuery(".bwg_lightbox_<?php echo $bwg; ?>").on("click", function () {
          if (!bwg_touch_flag) {
            bwg_touch_flag = true;
            setTimeout(function(){ bwg_touch_flag = false; }, 100);
            bwg_gallery_box_<?php echo $bwg; ?>(jQuery(this).attr("data-image-id"));
            return false;
          }
        });
        jQuery(".bwg_lightbox_<?php echo $bwg; ?> .bwg_ecommerce").on("click", function (event) {
          event.stopPropagation();
          if (!bwg_touch_flag) {
            bwg_touch_flag = true;
            setTimeout(function(){ bwg_touch_flag = false; }, 100);
			      var image_id = jQuery(this).closest(".bwg_lightbox_<?php echo $bwg; ?>").attr("data-image-id");
            bwg_gallery_box_<?php echo $bwg; ?>(image_id, true);
            return false;
          }
        });       
        <?php 
        if ($image_right_click) {
          ?>
          /* Disable right click.*/
          jQuery('div[id^="bwg_container"]').bind("contextmenu", function () {
            return false;
          });
          jQuery('div[id^="bwg_container"]').css('webkitTouchCallout','none');
          <?php
        }
        ?>
      }
      jQuery(document).ready(function () {
        bwg_document_ready_<?php echo $bwg; ?>();
      });
    </script>
    <?php
    if ($from_shortcode) {
      return;
    }
    else {
      die();
    }
  }

  private function inline_styles($bwg, $theme_row, $params, $image_width, $image_height, $slideshow_filmstrip_height, $wd_bwg_options, $filmstrip_thumb_margin_hor, $slideshow_filmstrip_width, $image_rows, $watermark_position, $slideshow_title_position, $slideshow_description_position, $watermark_height, $watermark_width, $watermark_opacity, $watermark_font_size, $watermark_font, $watermark_color, $enable_slideshow_filmstrip) {
    ob_start();
    ?>
    #bwg_container1_<?php echo $bwg; ?> {
      visibility: hidden;
    }
      #bwg_container1_<?php echo $bwg; ?> * {
        -moz-user-select: none;
        -khtml-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_image_wrap_<?php echo $bwg; ?> * {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        /*backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        -moz-backface-visibility: hidden;
        -ms-backface-visibility: hidden;*/
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_image_wrap_<?php echo $bwg; ?> {
        background-color: #<?php echo $theme_row->slideshow_cont_bg_color; ?>;
        border-collapse: collapse;
        display: table;
        position: relative;
        text-align: center;
        width: <?php echo $image_width; ?>px;
        height: <?php echo $image_height; ?>px;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_image_<?php echo $bwg; ?> {
        padding: 0 !important;
        margin: 0 !important;
        float: none !important;
        max-width: <?php echo $image_width; ?>px;
        max-height: <?php echo $image_height - $slideshow_filmstrip_height; ?>px;
        vertical-align: middle;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_embed_<?php echo $bwg; ?> {
        padding: 0 !important;
        margin: 0 !important;
        float: none !important;
        width: <?php echo $image_width; ?>px;
        height: <?php echo $image_height - $slideshow_filmstrip_height; ?>px;
        vertical-align: middle;
        display: inline-block;
        text-align: center;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_watermark_<?php echo $bwg; ?> {
        position: relative;
        z-index: 15;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #bwg_slideshow_play_pause_<?php echo $bwg; ?> {
        background: transparent url("<?php echo WD_BWG_URL . '/images/blank.gif'; ?>") repeat scroll 0 0;
        bottom: 0;
        cursor: pointer;
        display: table;
        height: inherit;
        outline: medium none;
        position: absolute;
        width: 30%;
        left: 35%;
        z-index: 13;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #bwg_slideshow_play_pause_<?php echo $bwg; ?>:hover #bwg_slideshow_play_pause-ico_<?php echo $bwg; ?> {
        display: inline-block !important;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #bwg_slideshow_play_pause_<?php echo $bwg; ?>:hover span {
        position: relative;
        z-index: 13;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #bwg_slideshow_play_pause_<?php echo $bwg; ?> span {
        display: table-cell;
        text-align: center;
        vertical-align: middle;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #bwg_slideshow_play_pause-ico_<?php echo $bwg; ?> {  
        display: none !important;
        color: #<?php echo $theme_row->slideshow_rl_btn_color; ?>;        
        font-size: <?php echo $theme_row->slideshow_play_pause_btn_size; ?>px;
        cursor: pointer;
        position: relative;
        z-index: 13;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #bwg_slideshow_play_pause-ico_<?php echo $bwg; ?>:hover {  
        color: #<?php echo $theme_row->slideshow_close_rl_btn_hover_color; ?>;
        display: inline-block;
        position: relative;
        z-index: 13;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_left_<?php echo $bwg; ?>,
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_right_<?php echo $bwg; ?> {
        background: transparent url("<?php echo WD_BWG_URL . '/images/blank.gif'; ?>") repeat scroll 0 0;
        bottom: 35%;
        cursor: pointer;
        display: inline;
        height: 30%;
        outline: medium none;
        position: absolute;
        width: 35%;
        /*z-index: 10130;*/
        z-index: 13;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_left_<?php echo $bwg; ?> {
        left: 0;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_right_<?php echo $bwg; ?> {
        right: 0;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_left_<?php echo $bwg; ?>:hover,
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_right_<?php echo $bwg; ?>:hover {
        visibility: visible;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_left_<?php echo $bwg; ?>:hover span {
        left: 20px;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_right_<?php echo $bwg; ?>:hover span {
        left: auto;
        right: 20px;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_left-ico_<?php echo $bwg; ?> span,
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_right-ico_<?php echo $bwg; ?> span {
        display: table-cell;
        text-align: center;
        vertical-align: middle;
        z-index: 13;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_left-ico_<?php echo $bwg; ?>,
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_right-ico_<?php echo $bwg; ?> {
        background-color: #<?php echo $theme_row->slideshow_rl_btn_bg_color; ?>;
        border-radius: <?php echo $theme_row->slideshow_rl_btn_border_radius; ?>;
        border: <?php echo $theme_row->slideshow_rl_btn_border_width; ?>px <?php echo $theme_row->slideshow_rl_btn_border_style; ?> #<?php echo $theme_row->slideshow_rl_btn_border_color; ?>;
        box-shadow: <?php echo $theme_row->slideshow_rl_btn_box_shadow; ?>;
        color: #<?php echo $theme_row->slideshow_rl_btn_color; ?>;
        height: <?php echo $theme_row->slideshow_rl_btn_height; ?>px;
        font-size: <?php echo $theme_row->slideshow_rl_btn_size; ?>px;
        width: <?php echo $theme_row->slideshow_rl_btn_width; ?>px;
        z-index: 13;
        -moz-box-sizing: content-box;
        box-sizing: content-box;
        cursor: pointer;
        display: table;
        line-height: 0;
        margin-top: -15px;
        position: absolute;
        top: 50%;
        /*z-index: 10135;*/
        opacity: <?php echo number_format($theme_row->slideshow_close_btn_transparent / 100, 2, ".", ""); ?>;
        filter: Alpha(opacity=<?php echo $theme_row->slideshow_close_btn_transparent; ?>);
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_left-ico_<?php echo $bwg; ?>:hover,
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_slideshow_right-ico_<?php echo $bwg; ?>:hover {
        color: #<?php echo $theme_row->slideshow_close_rl_btn_hover_color; ?>;
        cursor: pointer;
      }
      <?php
  if($wd_bwg_options->autohide_slideshow_navigation){?>
	  #spider_slideshow_left-ico_<?php echo $bwg; ?>{
		   left: -9999px;
	  }
	#spider_slideshow_right-ico_<?php echo $bwg; ?>{
		left: -9999px;
	 }
		
 <?php }
       else{ ?>
	    #spider_slideshow_left-ico_<?php echo $bwg; ?>{
		   left: 20px;
		}
	   #spider_slideshow_right-ico_<?php echo $bwg; ?>{
		   left: auto;
           right: 20px;
		}
	  <?php } ?>
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_image_container_<?php echo $bwg; ?> {
        display: table;
        position: absolute;
        text-align: center;
        <?php echo $theme_row->slideshow_filmstrip_pos; ?>: <?php echo $slideshow_filmstrip_height; ?>px;
        vertical-align: middle;
        width: <?php echo $image_width; ?>px;
        height: <?php echo $image_height; ?>px;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_filmstrip_container_<?php echo $bwg; ?> {
        display: table;
        height: <?php echo $slideshow_filmstrip_height; ?>px;
        position: absolute;
        width: <?php echo $image_width; ?>px;
        /*z-index: 10105;*/
        <?php echo $theme_row->slideshow_filmstrip_pos; ?>: 0;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_filmstrip_<?php echo $bwg; ?> {
        left: 20px;
        overflow: hidden;
        position: absolute;
        width: <?php echo $image_width - 40; ?>px;
        /*z-index: 10106;*/
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_filmstrip_thumbnails_<?php echo $bwg; ?> {
        height: <?php echo $slideshow_filmstrip_height; ?>px;
        left: 0px;
        margin: 0 auto;
        overflow: hidden;
        position: relative;
        width: <?php echo ($slideshow_filmstrip_width + $filmstrip_thumb_margin_hor) * count($image_rows); ?>px;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_filmstrip_thumbnail_<?php echo $bwg; ?> {
        position: relative;
        background: none;
        border: <?php echo $theme_row->slideshow_filmstrip_thumb_border_width; ?>px <?php echo $theme_row->slideshow_filmstrip_thumb_border_style; ?> #<?php echo $theme_row->slideshow_filmstrip_thumb_border_color; ?>;
        border-radius: <?php echo $theme_row->slideshow_filmstrip_thumb_border_radius; ?>;
        cursor: pointer;
        float: left;
        height: <?php echo $slideshow_filmstrip_height; ?>px;
        margin: <?php echo $theme_row->slideshow_filmstrip_thumb_margin; ?>;
        width: <?php echo $slideshow_filmstrip_width; ?>px;
        overflow: hidden;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_thumb_active_<?php echo $bwg; ?> {
        opacity: 1;
        filter: Alpha(opacity=100);
        border: <?php echo $theme_row->slideshow_filmstrip_thumb_active_border_width; ?>px solid #<?php echo $theme_row->slideshow_filmstrip_thumb_active_border_color; ?>;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_thumb_deactive_<?php echo $bwg; ?> {
        opacity: <?php echo number_format($theme_row->slideshow_filmstrip_thumb_deactive_transparent / 100, 2, ".", ""); ?>;
        filter: Alpha(opacity=<?php echo $theme_row->slideshow_filmstrip_thumb_deactive_transparent; ?>);
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_filmstrip_thumbnail_img_<?php echo $bwg; ?> {
        display: block;
        opacity: 1;
        filter: Alpha(opacity=100);
        padding: 0 !important;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_filmstrip_left_<?php echo $bwg; ?> {
        background-color: #<?php echo $theme_row->slideshow_filmstrip_rl_bg_color; ?>;
        cursor: pointer;
        display: table-cell;
        vertical-align: middle;
        width: 20px;
        /*z-index: 10106;*/
        left: 0;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_filmstrip_right_<?php echo $bwg; ?> {
        background-color: #<?php echo $theme_row->slideshow_filmstrip_rl_bg_color; ?>;
        cursor: pointer;
        right: 0;
        width: 20px;
        display: table-cell;
        vertical-align: middle;
        /*z-index: 10106;*/
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_filmstrip_left_<?php echo $bwg; ?> i,
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_filmstrip_right_<?php echo $bwg; ?> i {
        color: #<?php echo $theme_row->slideshow_filmstrip_rl_btn_color; ?>;
        font-size: <?php echo $theme_row->slideshow_filmstrip_rl_btn_size; ?>px;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_none_selectable_<?php echo $bwg; ?> {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_watermark_container_<?php echo $bwg; ?> {
        display: table-cell;
        margin: 0 auto;
        position: relative;
        vertical-align: middle;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_watermark_spun_<?php echo $bwg; ?> {
        display: table-cell;
        overflow: hidden;
        position: relative;
        text-align: <?php echo $watermark_position[1]; ?>;
        vertical-align: <?php echo $watermark_position[0]; ?>;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_title_spun_<?php echo $bwg; ?> {
        display: table-cell;
        overflow: hidden;
        position: relative;
        text-align: <?php echo $slideshow_title_position[1]; ?>;
        vertical-align: <?php echo $slideshow_title_position[0]; ?>;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_description_spun_<?php echo $bwg; ?> {
        display: table-cell;
        overflow: hidden;
        position: relative;
        text-align: <?php echo $slideshow_description_position[1]; ?>;
        vertical-align: <?php echo $slideshow_description_position[0]; ?>;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_watermark_image_<?php echo $bwg; ?> {
        padding: 0 !important;
        float: none !important;
        margin: 4px !important;
        max-height: <?php echo $watermark_height; ?>px;
        max-width: <?php echo $watermark_width; ?>px;
        opacity: <?php echo number_format($watermark_opacity / 100, 2, ".", ""); ?>;
        filter: Alpha(opacity=<?php echo $watermark_opacity; ?>);
        position: relative;
        z-index: 15;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_watermark_text_<?php echo $bwg; ?>,
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_watermark_text_<?php echo $bwg; ?>:hover {
        text-decoration: none;
        margin: 4px;
        font-size: <?php echo $watermark_font_size; ?>px;
        font-family: <?php echo $watermark_font; ?>;
        color: #<?php echo $watermark_color; ?> !important;
        opacity: <?php echo number_format($watermark_opacity / 100, 2, ".", ""); ?>;
        filter: Alpha(opacity=<?php echo $watermark_opacity; ?>);
        position: relative;
        z-index: 15;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_title_text_<?php echo $bwg; ?> {
        text-decoration: none;
        font-size: <?php echo $theme_row->slideshow_title_font_size; ?>px;
        font-family: <?php echo $theme_row->slideshow_title_font; ?>;
        color: #<?php echo $theme_row->slideshow_title_color; ?> !important;
        opacity: <?php echo number_format($theme_row->slideshow_title_opacity / 100, 2, ".", ""); ?>;
        filter: Alpha(opacity=<?php echo $theme_row->slideshow_title_opacity; ?>);
        position: relative;
        z-index: 11;
        border-radius: <?php echo $theme_row->slideshow_title_border_radius; ?>;
        background-color: #<?php echo $theme_row->slideshow_title_background_color; ?>;
        padding: <?php echo $theme_row->slideshow_title_padding; ?>;
        <?php if($params['slideshow_title_full_width']) { ?>
        width: 100%;
        <?php } else { ?>
        margin: 5px;
        <?php } ?>
        display: inline-block;
        word-wrap: break-word;
        word-break: break-word;
        <?php if (!$enable_slideshow_filmstrip && $slideshow_title_position[0] == $theme_row->slideshow_filmstrip_pos) echo $theme_row->slideshow_filmstrip_pos . ':' . ($theme_row->slideshow_dots_height + 4) . 'px;'; ?>
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_description_text_<?php echo $bwg; ?> {
        text-decoration: none;
        font-size: <?php echo $theme_row->slideshow_description_font_size; ?>px;
        font-family: <?php echo $theme_row->slideshow_description_font; ?>;
        color: #<?php echo $theme_row->slideshow_description_color; ?> !important;
        opacity: <?php echo number_format($theme_row->slideshow_description_opacity / 100, 2, ".", ""); ?>;
        filter: Alpha(opacity=<?php echo $theme_row->slideshow_description_opacity; ?>);
        position: relative;
        z-index: 15;
        border-radius: <?php echo $theme_row->slideshow_description_border_radius; ?>;
        background-color: #<?php echo $theme_row->slideshow_description_background_color; ?>;
        padding: <?php echo $theme_row->slideshow_description_padding; ?>;
        margin: 5px;
        display: inline-block;
        word-wrap: break-word;
        word-break: break-word;
        <?php if (!$enable_slideshow_filmstrip && $slideshow_description_position[0] == $theme_row->slideshow_filmstrip_pos) echo $theme_row->slideshow_filmstrip_pos . ':' . ($theme_row->slideshow_dots_height + 4) . 'px;'; ?>        
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_description_text_<?php echo $bwg; ?> * {
        text-decoration: none;
        color: #<?php echo $theme_row->slideshow_description_color; ?> !important;                
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slide_container_<?php echo $bwg; ?> {
        display: table-cell;
        margin: 0 auto;
        position: absolute;
        vertical-align: middle;
        width: 100%;
        height: 100%;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slide_bg_<?php echo $bwg; ?> {
        margin: 0 auto;
        width: inherit;
        height: inherit;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slider_<?php echo $bwg; ?> {
        height: inherit;
        width: inherit;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_image_spun_<?php echo $bwg; ?> {
        width: inherit;
        height: inherit;
        display: table-cell;
        filter: Alpha(opacity=100);
        opacity: 1;
        position: absolute;
        vertical-align: middle;
        z-index: 2;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_image_second_spun_<?php echo $bwg; ?> {
        width: inherit;
        height: inherit;
        display: table-cell;
        filter: Alpha(opacity=0);
        opacity: 0;
        position: absolute;
        vertical-align: middle;
        z-index: 1;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_grid_<?php echo $bwg; ?> {
        display: none;
        height: 100%;
        overflow: hidden;
        position: absolute;
        width: 100%;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_gridlet_<?php echo $bwg; ?> {
        opacity: 1;
        filter: Alpha(opacity=100);
        position: absolute;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_dots_<?php echo $bwg; ?> {
        display: inline-block;
        position: relative;
        width: <?php echo $theme_row->slideshow_dots_width; ?>px;
        height: <?php echo $theme_row->slideshow_dots_height; ?>px;
        border-radius: <?php echo $theme_row->slideshow_dots_border_radius; ?>;
        background: #<?php echo $theme_row->slideshow_dots_background_color; ?>;
        margin: <?php echo $theme_row->slideshow_dots_margin; ?>px;
        cursor: pointer;
        overflow: hidden;
        z-index: 17;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_dots_container_<?php echo $bwg; ?> {
        display: block;
        overflow: hidden;
        position: absolute;
        width: <?php echo $image_width; ?>px;
        <?php echo $theme_row->slideshow_filmstrip_pos; ?>: 0;
        z-index: 17;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_dots_thumbnails_<?php echo $bwg; ?> {
        left: 0px;
        font-size: 0;
        margin: 0 auto;
        overflow: hidden;
        position: relative;
        height: <?php echo ($theme_row->slideshow_dots_height + $theme_row->slideshow_dots_margin * 2); ?>px;
        width: <?php echo ($theme_row->slideshow_dots_width + $theme_row->slideshow_dots_margin * 2) * count($image_rows); ?>px;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_dots_active_<?php echo $bwg; ?> {
        background: #<?php echo $theme_row->slideshow_dots_active_background_color; ?>;
        opacity: 1;
        filter: Alpha(opacity=100);
        border: <?php echo $theme_row->slideshow_dots_active_border_width; ?>px solid #<?php echo $theme_row->slideshow_dots_active_border_color; ?>;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_dots_deactive_<?php echo $bwg; ?> {
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_image_spun1_<?php echo $bwg; ?> {
        display: table; 
        width: inherit; 
        height: inherit;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> .bwg_slideshow_image_spun2_<?php echo $bwg; ?> {
        display: table-cell; 
        vertical-align: middle; 
        text-align: center;
      }
      #bwg_container1_<?php echo $bwg; ?> #bwg_container2_<?php echo $bwg; ?> #spider_popup_overlay_<?php echo $bwg; ?> {
        background-color: #<?php echo $theme_row->lightbox_overlay_bg_color; ?>;
        opacity: <?php echo number_format($theme_row->lightbox_overlay_bg_transparent / 100, 2, ".", ""); ?>;
        filter: Alpha(opacity=<?php echo $theme_row->lightbox_overlay_bg_transparent; ?>);
      }
    <?php
    return ob_get_clean();
  }
}