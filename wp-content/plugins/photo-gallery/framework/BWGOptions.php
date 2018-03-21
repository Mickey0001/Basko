<?php
defined('ABSPATH') || die('Access Denied');

class WD_BWG_Options {
  
  public $images_directory = null;

  public $masonry = 'vertical';
  public $mosaic = 'vertical';
  public $resizable_mosaic = 0;
  public $mosaic_total_width = 100;
  public $image_column_number = 5;
  public $images_per_page = 30;
  public $thumb_width = 180;
  public $thumb_height = 90;
  public $upload_thumb_width = 500;
  public $upload_thumb_height = 500;
  public $image_enable_page = 1;
  public $image_title_show_hover = 'none';
  public $ecommerce_icon_show_hover = 'none';
  public $show_gallery_description = 0;

  public $album_column_number = 5;
  public $albums_per_page = 30;
  public $album_title_show_hover = 'hover';
  public $album_thumb_width = 120;
  public $album_thumb_height = 90;
  public $album_enable_page = 1;
  public $extended_album_height = 150;
  public $extended_album_description_enable = 1;

  public $image_browser_width = 800;
  public $image_browser_title_enable = 1;
  public $image_browser_description_enable = 1;

  public $blog_style_width = 800;
  public $blog_style_title_enable = 1;
  public $blog_style_images_per_page = 5;
  public $blog_style_enable_page = 1;
  public $blog_style_description_enable = 0;

  public $slideshow_type = 'fade';
  public $slideshow_interval = 5;
  public $slideshow_width = 800;
  public $slideshow_height = 500;
  public $slideshow_enable_autoplay = 0;
  public $slideshow_enable_shuffle = 0;
  public $slideshow_enable_ctrl = 1;
  public $slideshow_enable_filmstrip = 0;
  public $slideshow_filmstrip_height = 90;
  public $slideshow_enable_title = 0;
  public $slideshow_title_position = 'top-right';
  public $slideshow_enable_description = 0;
  public $slideshow_description_position = 'bottom-right';
  public $slideshow_enable_music = 0;
  public $slideshow_audio_url = '';
  public $slideshow_effect_duration = 1;

  public $popup_width = 800;
  public $popup_height = 500;
  public $popup_type = 'fade';
  public $popup_interval = 5;
  public $popup_enable_filmstrip = 0;
  public $popup_filmstrip_height = 70;
  public $popup_enable_ctrl_btn = 1;
  public $popup_enable_fullscreen = 1;
  public $popup_enable_comment = 1;
  public $popup_enable_email = 0;
  public $popup_enable_captcha = 0;
  public $popup_enable_download = 0;
  public $popup_enable_fullsize_image = 0;
  public $popup_enable_facebook = 1;
  public $popup_enable_twitter = 1;
  public $popup_enable_google = 1;
  public $popup_enable_ecommerce = 1;
  public $popup_effect_duration = 1;

  public $watermark_type = 'none';
  public $watermark_position = 'bottom-left';
  public $watermark_width = 90;
  public $watermark_height = 90;
  public $watermark_url = '';
  public $watermark_text = 'web-dorado.com';
  public $watermark_link = 'https://web-dorado.com';
  public $watermark_font_size = 20;
  public $watermark_font = 'segoe ui';
  public $watermark_color = 'FFFFFF';
  public $watermark_opacity = 30;

  public $built_in_watermark_type = 'none';
  public $built_in_watermark_position = 'middle-center';
  public $built_in_watermark_size = 15;
  public $built_in_watermark_url = '';
  public $built_in_watermark_text = 'web-dorado.com';
  public $built_in_watermark_font_size = 20;
  public $built_in_watermark_font = 'arial';
  public $built_in_watermark_color = 'FFFFFF';
  public $built_in_watermark_opacity = 30;

  public $image_right_click = 0;
  public $popup_fullscreen = 0;
  public $gallery_role = 0;
  public $album_role = 0;
  public $image_role = 0;
  public $popup_autoplay = 0;
  public $album_view_type = 'thumbnail';
  public $popup_enable_pinterest = 0;
  public $popup_enable_tumblr = 0;
  public $show_search_box = 0;
  public $search_box_width = 180;
  public $preload_images = 0;
  public $preload_images_count = 10;
  public $popup_enable_info = 1;
  public $popup_enable_rate = 0;
  public $thumb_click_action = 'open_lightbox';
  public $thumb_link_target = 1;
  public $comment_moderation = 0;
  public $popup_info_always_show = 0;
  public $popup_hit_counter = 0;
  public $showthumbs_name = 0;
  public $show_album_name = 0;
  public $show_image_counts = 0;
  public $upload_img_width = 1200;
  public $upload_img_height = 1200;
  public $play_icon = 1;
  public $show_masonry_thumb_description = 0;
  public $slideshow_title_full_width = 0;
  public $popup_info_full_width = 0;
  public $show_sort_images = 0;
  public $autoupdate_interval = 30;
  public $instagram_access_token = '';
  public $description_tb = 0;
  public $enable_seo = 1;
  public $autohide_lightbox_navigation = 1;
  public $autohide_slideshow_navigation = 1;
  public $read_metadata = 1;
  public $enable_loop = 1;
  public $enable_addthis = 0;
  public $addthis_profile_id = '';
  public $carousel_interval = 5;
  public $carousel_width = 300;
  public $carousel_height = 300;
  public $carousel_image_column_number = 5;
  public $carousel_image_par = '0.75';
  public $carousel_enable_title = 0;
  public $carousel_enable_autoplay = 0;
  public $carousel_r_width = 800;
  public $carousel_fit_containerWidth = 1;
  public $carousel_prev_next_butt = 1;
  public $carousel_play_pause_butt = 1;
  public $permissions = 'manage_options';
  public $facebook_app_id = '';
  public $facebook_app_secret = '';
  public $show_tag_box = 0;
  public $show_hide_custom_post = 0;
  public $show_hide_post_meta = 0;
  public $use_inline_stiles_and_scripts = 0;
  public $placeholder = '';
  public $gallery_download = 0;
  public $enable_wp_editor = 1;
  public $image_quality = 75;

  public function __construct($reset = false) {
    $wd_bwg_options = get_option('wd_bwg_options');
    $old_images_directory = '';
    if ($wd_bwg_options) {
      $wd_bwg_options = json_decode($wd_bwg_options);
      $old_images_directory = $wd_bwg_options->images_directory;
      if (!$reset) {
        if (isset($wd_bwg_options)) {
          foreach ($wd_bwg_options as $name => $value) {
            $this->$name = $value;
          }
        }
      }
    }
    if ($this->images_directory === null) {
      $upload_dir = wp_upload_dir();
      if (!isset($this->old_images_directory) && !is_dir($upload_dir['basedir'] . '/photo-gallery') && !$reset) {
        $this->make_directory($upload_dir['basedir']);
      }
      $this->images_directory = str_replace(ABSPATH, '', $upload_dir['basedir']);
    }
    $this->old_images_directory = $old_images_directory;
    if (!$this->watermark_url) {
      $this->watermark_url = WD_BWG_URL . '/images/watermark.png';
    }
    if (!$this->built_in_watermark_url) {
      $this->built_in_watermark_url = WD_BWG_URL . '/images/watermark.png';
    }
    if ($this->permissions != 'moderate_comments' && $this->permissions != 'publish_posts' && $this->permissions != 'edit_posts') {
      $this->permissions = 'manage_options';
    }
    $this->jpeg_quality = $this->image_quality;
    $this->png_quality = 9 - round(9 * $this->image_quality / 100);
  }

  private function make_directory($upload_dir) {
    mkdir($upload_dir . '/photo-gallery', 0777);
  }
}