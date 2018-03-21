<?php
/**
 * Author: Rob
 * Date: 6/24/13
 * Time: 11:48 AM
 */

class FilemanagerView {
  private $controller;
  private $model;

  public function __construct($controller, $model) {
    $this->controller = $controller;
    $this->model = $model;
  }

  public function display() {
    if (isset($_GET['filemanager_msg']) && esc_html($_GET['filemanager_msg']) != '') {
      ?>
      <div id="file_manager_message" style="height:40px;">
        <div  style="background-color: #FFEBE8; border: 1px solid #CC0000; margin: 5px 15px 2px; padding: 5px 10px;">
          <strong style="font-size:14px"><?php echo esc_html(stripslashes($_GET['filemanager_msg'])); ?></strong>
        </div>
      </div>
      <?php
      $_GET['filemanager_msg'] = '';
    }
    global $wd_bwg_options;
    $file_manager_data = $this->model->get_file_manager_data();
    $items_view = $file_manager_data['session_data']['items_view'];
    $sort_by = $file_manager_data['session_data']['sort_by'];
    $sort_order = $file_manager_data['session_data']['sort_order'];
    $clipboard_task = $file_manager_data['session_data']['clipboard_task'];
    $clipboard_files = $file_manager_data['session_data']['clipboard_files'];
    $clipboard_src = $file_manager_data['session_data']['clipboard_src'];
    $clipboard_dest = $file_manager_data['session_data']['clipboard_dest'];
    wp_print_scripts('jquery');
    wp_print_scripts('jquery-ui-widget');
    wp_print_scripts('wp-pointer');
    wp_print_styles('admin-bar');
    wp_print_styles('dashicons');
    wp_print_styles('wp-admin');
    wp_print_styles('buttons');
    wp_print_styles('wp-auth-check');
    wp_print_styles('wp-pointer');
    ?>
    <script src="<?php echo WD_BWG_URL; ?>/filemanager/js/jq_uploader/jquery.iframe-transport.js"></script>
    <script src="<?php echo WD_BWG_URL; ?>/filemanager/js/jq_uploader/jquery.fileupload.js"></script>
    <script>
      var ajaxurl = "<?php echo wp_nonce_url( admin_url('admin-ajax.php'), 'addImages', 'bwg_nonce' ); ?>";
      var DS = "<?php echo addslashes('/'); ?>";

      var errorLoadingFile = "<?php echo __('File loading failed', 'bwg_back'); ?>";

      var warningRemoveItems = "<?php echo __('Are you sure you want to permanently remove selected items?', 'bwg_back'); ?>";
      var warningCancelUploads = "<?php echo __('This will cancel uploads. Continue?', 'bwg_back'); ?>";

      var messageEnterDirName = "<?php echo __('Enter directory name', 'bwg_back'); ?>";
      var messageEnterNewName = "<?php echo __('Enter new name', 'bwg_back'); ?>";
      var messageFilesUploadComplete = "<?php echo __('Processing uploaded files...', 'bwg_back'); ?>";

      var root = "<?php echo addslashes($this->controller->get_uploads_dir()); ?>";
      var dir = "<?php echo (isset($_REQUEST['dir']) ? trim(esc_html($_REQUEST['dir'])) : ''); ?>";
      var dirUrl = "<?php echo $this->controller->get_uploads_url() . (isset($_REQUEST['dir']) ? esc_html($_REQUEST['dir']) . '/' : ''); ?>";
      var callback = "<?php echo (isset($_REQUEST['callback']) ? esc_html($_REQUEST['callback']) : ''); ?>";
      var sortBy = "<?php echo $sort_by; ?>";
      var sortOrder = "<?php echo $sort_order; ?>";
      var wdb_all_files = <?php echo isset($file_manager_data["all_files"]) && json_encode($file_manager_data["all_files"]) ? json_encode($file_manager_data["all_files"]) : "''"; ?>;
      var element_load_count = <?php echo isset($file_manager_data["element_load_count"]) && json_encode($file_manager_data["element_load_count"]) ? json_encode($file_manager_data["element_load_count"]) : "''"; ?>;
    </script>
    <script src="<?php echo WD_BWG_URL; ?>/filemanager/js/default.js?ver=<?php echo wd_bwg_version(); ?>"></script>
    <link href="<?php echo WD_BWG_URL; ?>/filemanager/css/default.css?ver=<?php echo wd_bwg_version(); ?>" type="text/css" rel="stylesheet">
    <?php
    switch ($items_view) {
      case 'list':
        ?>
        <link href="<?php echo WD_BWG_URL; ?>/filemanager/css/default_view_list.css?ver=<?php echo wd_bwg_version(); ?>" type="text/css" rel="stylesheet">
        <?php
        break;
      case 'thumbs':
        ?>
        <link href="<?php echo WD_BWG_URL; ?>/filemanager/css/default_view_thumbs.css?ver=<?php echo wd_bwg_version(); ?>" type="text/css" rel="stylesheet">
        <?php
        break;
    }
    $i = 0;
    ?>
    <form id="adminForm" name="adminForm" action="" method="post">
      <?php wp_nonce_field( '', 'bwg_nonce' ); ?>
      <div id="wrapper">
        <div id="opacity_div" style="background-color: rgba(0, 0, 0, 0.2); position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99998;"></div>
        <div id="loading_div" style="text-align: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999;">
          <img src="<?php echo WD_BWG_URL . '/images/ajax_loader.gif'; ?>" class="bwg_spider_ajax_loading" style="margin-top: 200px; width:30px;">
        </div>
        <div id="file_manager">
          <div class="ctrls_bar ctrls_bar_header">
            <div class="ctrls_left header_bar">
              <a class="ctrl_bar_btn btn_up" onclick="onBtnUpClick(event, this);" title="<?php echo __('Up', 'bwg_back'); ?>"></a>
              <a class="ctrl_bar_btn btn_make_dir" onclick="onBtnMakeDirClick(event, this);" title="<?php echo __('Make a directory', 'bwg_back'); ?>"></a>
              <a class="ctrl_bar_btn btn_rename_item" onclick="onBtnRenameItemClick(event, this);" title="<?php echo __('Rename item', 'bwg_back'); ?>"></a>
              <span class="ctrl_bar_divider"></span>
              <a class="ctrl_bar_btn btn_copy" onclick="onBtnCopyClick(event, this);" title="<?php echo __('Copy', 'bwg_back'); ?>"></a>
              <a class="ctrl_bar_btn btn_cut" onclick="onBtnCutClick(event, this);" title="<?php echo __('Cut', 'bwg_back'); ?>"></a>
              <a class="ctrl_bar_btn btn_paste" onclick="onBtnPasteClick(event, this);" title="<?php echo __('Paste', 'bwg_back'); ?>"> </a>
              <a class="ctrl_bar_btn btn_remove_items" onclick="onBtnRemoveItemsClick(event, this);" title="<?php echo __('Remove items', 'bwg_back'); ?>"></a>
              <span class="ctrl_bar_divider divider_upload"></span>
            </div>
            <div class="ctrls_right">
              <a class="ctrl_bar_btn btn_view_thumbs" onclick="onBtnViewThumbsClick(event, this);" title="<?php echo __('View thumbs', 'bwg_back'); ?>"></a>
              <a class="ctrl_bar_btn btn_view_list" onclick="onBtnViewListClick(event, this);" title="<?php echo __('View list', 'bwg_back'); ?>"></a>
            </div>
            <div class="ctrls_left header_bar">
              <span class="ctrl_bar_btn">
                <a id="upload_images" class="ctrl_bar_btn wd-btn wd-btn-primary wd-btn-icon wd-btn-uplaod" onclick="onBtnShowUploaderClick(event, this);"><?php echo __('Upload files', 'bwg_back'); ?></a>
              </span>
              <span class="ctrl_bar_divider divider_search"></span>
            </div>
            <div class="ctrls_left header_bar">
              <span id="search_by_name" class="ctrl_bar_btn">
                <input type="search" placeholder="Search" class="ctrl_bar_btn search_by_name">
              </span>
            </div>
          </div>
          <div id="path">
            <?php
            foreach ($file_manager_data['path_components'] as $key => $path_component) {
              ?>
              <a <?php echo ($key == 0) ? 'title="'. __("To change upload directory go to Options page.", 'bwg_back').'"' : ''; ?> class="path_component path_dir"
                 onclick="onPathComponentClick(event, this, <?php echo $key; ?>);">
                  <?php echo str_replace('\\', '', $path_component['name']); ?></a>
              <a class="path_component path_separator"><?php echo '/'; ?></a>
              <?php
            }
            ?>
          </div>
          <div id="explorer">
            <div id="explorer_header_wrapper">
              <div id="explorer_header_container">
                <div id="explorer_header">
                  <span class="item_numbering"><?php echo $items_view == 'thumbs' ? __('Order by:', 'bwg') : '#'; ?></span>
                  <span class="item_icon"></span>
                  <span class="item_name" title="<?php _e('Click to sort by name', 'bwg'); ?>">
                    <span class="clickable" onclick="onNameHeaderClick(event, this);">
                        <?php
                        echo 'Name';
                        if ($sort_by == 'name') {
                          ?>
                          <span class="sort_order_<?php echo $sort_order; ?>"></span>
                          <?php
                        }
                        ?>
                    </span>
                  </span>
                  <span class="item_size" title="<?php _e('Click to sort by size', 'bwg'); ?>">
                    <span class="clickable" onclick="onSizeHeaderClick(event, this);">
                      <?php
                      echo 'Size';
                      if ($sort_by == 'size') {
                        ?>
                        <span class="sort_order_<?php echo $sort_order; ?>"></span>
                        <?php
                      }
                      ?>
                    </span>
                  </span>
                  <span class="item_date_modified" title="<?php _e('Click to sort by date modified', 'bwg'); ?>">
                    <span class="clickable" onclick="onDateModifiedHeaderClick(event, this);">
                      <?php
                      echo 'Date modified';
                      if ($sort_by == 'date_modified') {
                        ?>
                        <span class="sort_order_<?php echo $sort_order; ?>"></span>
                        <?php
                      }
                      ?>
                    </span>
                  </span>
                  <span class="scrollbar_filler"></span>
                </div>
              </div>
            </div>
            <div id="explorer_body_wrapper">
              <div id="explorer_body_container">
                <div id="explorer_body" data-files_count="<?php echo $file_manager_data["files_count"]; ?>">
                  <?php
                  foreach ($file_manager_data['files'] as $key => $file) {
                    $file['name'] = esc_html($file['name']);
                    $file['filename'] = esc_html($file['filename']);
                    $file['thumb'] = esc_html($file['thumb']);
                    ?>
                    <div class="explorer_item" draggable="true"
                         name="<?php echo $file['name']; ?>"
                         filename="<?php echo $file['filename']; ?>"
                         alt="<?php echo $file['alt']; ?>"
                         filethumb="<?php echo $file['thumb']; ?>"
                         filesize="<?php echo $file['size']; ?>"
                         filetype="<?php echo strtoupper($file['type']); ?>"
                         date_modified="<?php echo $file['date_modified']; ?>"
                         fileresolution="<?php echo $file['resolution']; ?>"
                         fileCredit="<?php echo isset($file['credit']) ? $file['credit'] : ''; ?>"
                         fileAperture="<?php echo isset($file['aperture']) ? $file['aperture'] : ''; ?>"
                         fileCamera="<?php echo isset($file['camera']) ? $file['camera'] : ''; ?>"
                         fileCaption="<?php echo isset($file['caption']) ? $file['caption'] : ''; ?>"
                         fileIso="<?php echo isset($file['iso']) ? $file['iso'] : ''; ?>"
                         fileOrientation="<?php echo isset($file['orientation']) ? $file['orientation'] : ''; ?>"
                         fileCopyright="<?php echo isset($file['copyright']) ? $file['copyright'] : ''; ?>"
                         onmouseover="onFileMOver(event, this);"
                         onmouseout="onFileMOut(event, this);"
                         onclick="onFileClick(event, this);"
                         ondblclick="onFileDblClick(event, this);"
                         ondragstart="onFileDragStart(event, this);"
                        <?php
                        if ($file['is_dir'] == true) {
                          ?>
                        ontouchend="onFileDblClick(event, this);"
                        ondragover="onFileDragOver(event, this);"
                        ondrop="onFileDrop(event, this);"
                          <?php
                        }
                        ?>
                        isDir="<?php echo $file['is_dir'] == true ? 'true' : 'false'; ?>">
                      <span class="item_numbering"><?php echo ++$i; ?></span>
                      <span class="item_thumb">
                        <img src="<?php echo $file['thumb']; ?>" <?php echo $key >= 24 ? 'onload="loaded()"' : ''; ?> />
                      </span>
                      <span class="item_icon">
                        <img src="<?php echo $file['icon']; ?>"/>
                      </span>
                      <span class="item_name">
                        <?php echo $file['name']; ?>
                      </span>
                      <span class="item_size">
                        <?php echo $file['size']; ?>
                      </span>
                      <span class="item_date_modified">
                        <?php echo $file['date_modified']; ?>
                      </span>
                    </div>
                    <?php
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
          <div class="ctrls_bar ctrls_bar_footer">
            <div class="ctrls_left">
              <a id="select_all_images" class="ctrl_bar_btn wd-btn wd-btn-primary wd-not-image none_select" onclick="onBtnSelectAllClick();"><?php echo __('Select All', 'bwg_back'); ?></a>
            </div>
            <div class="ctrls_right">
              <span id="file_names_span">
                <span>
                </span>
              </span>
              <?php
              $add_image_btn = (isset($_REQUEST['callback']) && esc_html($_REQUEST['callback']) == 'bwg_add_image') ? __('Add selected images to gallery', 'bwg_back') : __('Add', 'bwg_back');
              ?>
              <a id="add_selectid_img" title="<?php echo $add_image_btn; ?>" class="ctrl_bar_btn btn_open wd-btn wd-btn-primary wd-btn-icon-add wd-btn-add none_select" onclick="onBtnOpenClick(event, this);">
                <div id="bwg_img_add"><?php echo $add_image_btn; ?></div>
              </a>
              <span class="ctrl_bar_empty_devider"></span>
              <a class="ctrl_bar_btn btn_cancel wd-btn wd-btn-primary wd-btn-icon wd-btn-cancel none_select" title="<?php _e('Cancel', 'bwg_back'); ?>" onclick="onBtnCancelClick(event, this);">
                <div id="bwg_img_cancel"><?php _e('Cancel', 'bwg_back'); ?></div>
              </a>
            </div>
          </div>
        </div>
        <div id="uploader">
          <div id="uploader_bg"></div>
          <div class="ctrls_bar ctrls_bar_header">
            <div class="ctrls_left upload_thumb">
              <div class="upload_thumb thumb_full_title"><?php _e("Thumbnail Maximum Dimensions:", 'bwg_back'); ?></div>
              <div class="upload_thumb thumb_title"><?php _e("Thumbnail:", 'bwg_back'); ?></div>
              <input type="text" class="upload_thumb_dim" name="upload_thumb_width" id="upload_thumb_width" value="<?php echo $wd_bwg_options->upload_thumb_width; ?>" /> x
              <input type="text" class="upload_thumb_dim" name="upload_thumb_height" id="upload_thumb_height" value="<?php echo $wd_bwg_options->upload_thumb_height; ?>" /> px
            </div>
            <div class="ctrls_right">
              <a class="ctrl_bar_btn btn_back" onclick="onBtnBackClick(event, this);" title="<?php echo __('Back', 'bwg_back'); ?>"></a>
            </div>
            <div class="ctrls_right_img upload_thumb">
              <div class="upload_thumb thumb_full_title"><?php _e("Image Maximum Dimensions:", 'bwg_back'); ?></div>
              <div class="upload_thumb thumb_title"><?php _e("Image:", 'bwg_back'); ?></div>
              <input type="text" class="upload_thumb_dim" name="upload_img_width" id="upload_img_width" value="<?php echo $wd_bwg_options->upload_img_width; ?>" /> x
              <input type="text" class="upload_thumb_dim" name="upload_img_height" id="upload_img_height" value="<?php echo $wd_bwg_options->upload_img_height; ?>" /> px
            </div>
          </div>
          <label for="jQueryUploader">
            <div id="uploader_hitter">
              <div id="drag_message">
                <span><?php echo __('Drag files here or click the button below','bwg_back') . '<br />' . __('to upload files','bwg_back')?></span>
              </div>
              <div id="btnBrowseContainer">
              <?php
              $query_url = wp_nonce_url( admin_url('admin-ajax.php'), 'bwg_UploadHandler', 'bwg_nonce' );
              $query_url = add_query_arg(array('action' => 'bwg_UploadHandler', 'dir' => (isset($_REQUEST['dir']) ? esc_html($_REQUEST['dir']) : '') . '/'), $query_url);
              ?>
                <input id="jQueryUploader" type="file" name="files[]"
                       data-url="<?php echo $query_url; ?>"
                       multiple>
              </div>
              <script>
                jQuery("#jQueryUploader").fileupload({
                  dataType: "json",
                  dropZone: jQuery("#uploader_hitter"),
                  submit: function (e, data) {
                    jQuery("#uploader_progress_text").removeClass("uploader_text");
                    isUploading = true;
                    jQuery("#uploader_progress_bar").fadeIn();
                  },
                  progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    jQuery("#uploader_progress_text").text("Progress " + progress + "%");
                    jQuery("#uploader_progress div div").css({width: progress + "%"});
                    if (data.loaded == data.total) {
                      isUploading = false;
                      jQuery("#uploader_progress_bar").fadeOut(function () {
                        jQuery("#uploader_progress_text").text(messageFilesUploadComplete);
                        jQuery("#uploader_progress_text").addClass("uploader_text");
                      });
                      jQuery("#opacity_div").show();
                      jQuery("#loading_div").show();
                    }
                  },
                  stop: function (e, data) {
                    onBtnBackClick();
                  },
                  done: function (e, data) {
                    jQuery.each(data.result.files, function (index, file) {
                      if (file.error) {
                        alert(errorLoadingFile + ' :: ' + file.error);
                      }
                      if (file.error) {
                        jQuery("#uploaded_files ul").prepend(jQuery("<li class=uploaded_item_failed>" + "<?php echo 'Upload failed' ?> :: " + file.error + "</li>"));
                      }
                      else {
                        jQuery("#uploaded_files ul").prepend(jQuery("<li class=uploaded_item>" + file.name + " (<?php echo 'Uploaded' ?>)" + "</li>"));
                      }
                    });
                    jQuery("#opacity_div").hide();
                    jQuery("#loading_div").hide();
                  }
                });
              </script>
            </div>
          </label>
          <div id="uploaded_files">
            <ul></ul>
          </div>
          <div id="uploader_progress">
            <div id="uploader_progress_bar">
              <div></div>
            </div>
            <span id="uploader_progress_text" class="uploader_text">
              <?php echo __('No files to upload', 'bwg_back'); ?>
            </span>
          </div>
        </div>
      </div>
      <input type="hidden" name="task" value="" />
      <input type="hidden" name="extensions" value="<?php echo (isset($_REQUEST['extensions']) ? esc_html($_REQUEST['extensions']) : '*'); ?>" />
      <input type="hidden" name="callback" value="<?php echo (isset($_REQUEST['callback']) ? esc_html($_REQUEST['callback']) : ''); ?>" />
      <input type="hidden" name="sort_by" value="<?php echo $sort_by; ?>" />
      <input type="hidden" name="sort_order" value="<?php echo $sort_order; ?>" />
      <input type="hidden" name="items_view" value="<?php echo $items_view; ?>" />
      <input type="hidden" name="dir" value="<?php echo (isset($_REQUEST['dir']) ? str_replace('\\', '', ($_REQUEST['dir'])) : ''); ?>" />
      <input type="hidden" name="file_names" value="" />
      <input type="hidden" name="file_namesML" value="" />
      <input type="hidden" name="file_new_name" value="" />
      <input type="hidden" name="new_dir_name" value="" />
      <input type="hidden" name="clipboard_task" value="<?php echo $clipboard_task; ?>" />
      <input type="hidden" name="clipboard_files" value="<?php echo $clipboard_files; ?>" />
      <input type="hidden" name="clipboard_src" value="<?php echo $clipboard_src; ?>" />
      <input type="hidden" name="clipboard_dest" value="<?php echo $clipboard_dest; ?>" />
    </form>
    <?php
    include_once (WD_BWG_DIR .'/includes/bwg_pointers.php');
    new BWG_pointers();
    die();
  }
}
