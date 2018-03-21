<?php
class BWGViewAlbums_bwg {
  
  private $model;

  public function __construct($model) {
    $this->model = $model;
  }
  public function display() {
    global $WD_BWG_UPLOAD_DIR;
    $rows_data = $this->model->get_rows_data();
    $page_nav = $this->model->page_nav();
    $search_value = ((isset($_POST['search_value'])) ? esc_html(stripslashes($_POST['search_value'])) : '');
    $search_select_value = ((isset($_POST['search_select_value'])) ? (int) $_POST['search_select_value'] : 0);
    $asc_or_desc = ((isset($_POST['asc_or_desc'])) ? esc_html(stripslashes($_POST['asc_or_desc'])) : 'asc');
    $order_by = (isset($_POST['order_by']) ? esc_html(stripslashes($_POST['order_by'])) : 'order');
    $order_class = 'manage-column column-title sorted ' . $asc_or_desc;
    $ids_string = '';
    $per_page = $this->model->per_page();
    $pager = 0;
    $album_button_array = array(
      'publish_all' => __('Publish', 'bwg_back'),
      'unpublish_all' => __('Unpublish', 'bwg_back'),
      'delete_all' => __('Delete', 'bwg_back')
    );
    ?>
    <form class="wrap bwg_form" id="albums_form" method="post" action="admin.php?page=albums_bwg" style="width: 98%; float: left;">
    <?php wp_nonce_field( 'albums_bwg', 'bwg_nonce' ); ?>
      <div>
        <span class="album-icon"></span>
        <h2>
          <?php _e('Albums', 'bwg_back'); ?>
          <a href="" class="add-new-h2" onclick="spider_set_input_value('task', 'add');
                                                 spider_form_submit(event, 'albums_form')"><?php _e('Add new', 'bwg_back'); ?></a>
        </h2>
      </div>
      <div id="draganddrop" class="wd_updated" style="display:none;"><strong><p><?php _e('Changes made in this table should be saved.', 'bwg_back'); ?></p></strong></div>
      <?php WDWLibrary::search(__('Name', 'bwg_back'), $search_value, 'albums_form',''); ?>
      <div class="tablenav top buttons_div buttons_div_left">
        <span class="wd-btn wd-btn-primary-gray bwg_check_all non_selectable" onclick="spider_check_all_items()">
          <input type="checkbox" id="check_all_items" name="check_all_items" onclick="spider_check_all_items_checkbox()" style="margin: 0; vertical-align: middle;" />
          <span style="vertical-align: middle;"><?php echo __('Select All', 'bwg_back'); ?></span>
        </span>
        <select class='select_icon bulk_action'>
          <option value=""><?php _e('Bulk Actions', 'bwg_back'); ?></option>
          <?php 
          foreach ($album_button_array as $key => $value) {
            ?>
          <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
            <?php
          }
          ?>
        </select>
        <input class="wd-btn wd-btn-primary wd-btn-icon wd-btn-apply" type="button" title="<?php _e("Apply", "bwg_back"); ?>" onclick="if (!bwg_bulk_actions('.bulk_action', 'album_page')) {return false;}" value="<?php _e("Apply", "bwg_back"); ?>" />
        <?php WDWLibrary::html_page_nav($page_nav['total'], $pager++, $page_nav['limit'], 'albums_form', $per_page); ?>
      </div>
      <table class="wp-list-table widefat fixed pages">
        <thead>
        <th class="sortable check-column table_small_col manage-column <?php if ($order_by == 'order') {echo $order_class;} ?>" style="margin: 0px auto 5px 10px; width: 77px; vertical-align:middle;">
          <a id="show_hide_weights" class="bwg_order_column" onclick="spider_show_hide_weights();" value="<?php echo __('Hide order column', 'bwg_back'); ?>" ></a>
          <a id="show_order_button" class="bwg_save_order" title="<?php echo __('Save Order', 'bwg_back'); ?>"  onclick="spider_set_input_value('task', 'save_order');spider_form_submit(event, 'albums_form')" value="<?php echo __('Save Order', 'bwg_back'); ?>" ></a>
            <a id="th_order"  onclick="spider_set_input_value('task', '');
                        spider_set_input_value('order_by', 'order');
                        spider_set_input_value('asc_or_desc', '<?php echo ((isset($_POST['asc_or_desc']) && isset($_POST['order_by']) && (esc_html(stripslashes($_POST['order_by'])) == 'order') && esc_html(stripslashes($_POST['asc_or_desc'])) == 'asc') ? 'desc' : 'asc'); ?>');
                        spider_form_submit(event, 'albums_form')" href="">
             <span class="sorting-indicator"></span>
            </a>
          </th>
          <th class="sortable manage-column column-cb check-column table_small_col" style="padding-top:14px !important;" ><input id="check_all" onclick="spider_check_all(this)" type="checkbox" style="margin:0;" /></th>
          <th class="sortable table_th_middle table_small_col <?php if ($order_by == 'id') {echo $order_class;} ?>">
            <a onclick="spider_set_input_value('task', '');
                        spider_set_input_value('order_by', 'id');
                        spider_set_input_value('asc_or_desc', '<?php echo ((isset($_POST['asc_or_desc']) && isset($_POST['order_by']) && (esc_html(stripslashes($_POST['order_by'])) == 'id') && esc_html(stripslashes($_POST['asc_or_desc'])) == 'asc') ? 'desc' : 'asc'); ?>');
                        spider_form_submit(event, 'albums_form')" href="">
              <span>ID</span><span class="sorting-indicator"></span>
            </a>
          </th> 
          <th class="table_extra_large_col"><?php echo __('Thumbnail', 'bwg_back'); ?></th>          
          <th class="sortable table_extra_large_col <?php if ($order_by == 'name') {echo $order_class;} ?>">
            <a onclick="spider_set_input_value('task', '');
                        spider_set_input_value('order_by', 'name');
                        spider_set_input_value('asc_or_desc', '<?php echo ((isset($_POST['asc_or_desc']) && isset($_POST['order_by']) && (esc_html(stripslashes($_POST['order_by'])) == 'name') && esc_html(stripslashes($_POST['asc_or_desc'])) == 'asc') ? 'desc' : 'asc'); ?>');
                        spider_form_submit(event, 'albums_form')" href="">
              <span><?php echo __('Name', 'bwg_back'); ?></span><span class="sorting-indicator"></span>
            </a>
          </th>
          <th class="sortable table_extra_large_col<?php if ($order_by == 'slug') {echo $order_class;} ?>">
            <a onclick="spider_set_input_value('task', '');
                        spider_set_input_value('order_by', 'slug');
                        spider_set_input_value('asc_or_desc', '<?php echo ((isset($_POST['asc_or_desc']) && isset($_POST['order_by']) && (esc_html(stripslashes($_POST['order_by'])) == 'slug') && esc_html(stripslashes($_POST['asc_or_desc'])) == 'asc') ? 'desc' : 'asc'); ?>');
                        spider_form_submit(event, 'albums_form')" href="">
              <span><?php echo __('Slug', 'bwg_back'); ?></span><span class="sorting-indicator"></span>
            </a>
          </th>
          <th class="sortable table_extra_large_col <?php if ($order_by == 'display_name') {echo $order_class;} ?>">
            <a onclick="spider_set_input_value('task', '');
                        spider_set_input_value('order_by', 'display_name');
                        spider_set_input_value('asc_or_desc', '<?php echo ((isset($_POST['asc_or_desc']) && isset($_POST['order_by']) && (esc_html(stripslashes($_POST['order_by'])) == 'display_name') && esc_html(stripslashes($_POST['asc_or_desc'])) == 'asc') ? 'desc' : 'asc'); ?>');
                        spider_form_submit(event, 'albums_form')" href="">
              <span><?php echo __('Author', 'bwg_back'); ?></span><span class="sorting-indicator"></span>
            </a>
          </th>
          <th class="sortable table_big_col <?php if ($order_by == 'published') {echo $order_class;} ?>" style="padding-left:20px;">
            <a onclick="spider_set_input_value('task', '');
                        spider_set_input_value('order_by', 'published');
                        spider_set_input_value('asc_or_desc', '<?php echo ((isset($_POST['asc_or_desc']) && isset($_POST['order_by']) && (esc_html(stripslashes($_POST['order_by'])) == 'published') && esc_html(stripslashes($_POST['asc_or_desc'])) == 'asc') ? 'desc' : 'asc'); ?>');
                        spider_form_submit(event, 'albums_form')" href="">
              <span><?php echo __('Published', 'bwg_back'); ?></span><span class="sorting-indicator"></span>
            </a>
          </th>
          <th class="table_small_col"><?php echo __('Edit', 'bwg_back'); ?></th>
          <th class="table_small_col"><?php echo __('Delete', 'bwg_back'); ?></th>
        </thead>
        <tbody id="tbody_arr">
          <?php
          if ($rows_data) {
            foreach ($rows_data as $row_data) {
              $alternate = (!isset($alternate) || $alternate == 'class="alternate"') ? '' : 'class="alternate"';
              $published_image = (($row_data->published) ? 'publish-blue' : 'unpublish-red');
              $published = (($row_data->published) ? 'unpublish' : 'publish');
              $unpublished = ((!$row_data->published) ? 'Unpublish' : 'Publish');
              if ($row_data->preview_image == '') {
                $preview_image = WD_BWG_URL . '/images/no-image.png';
              }
              else {
                $preview_image = site_url() . '/' . $WD_BWG_UPLOAD_DIR . $row_data->preview_image;
              }
              ?>
              <tr id="tr_<?php echo $row_data->id; ?>" <?php echo $alternate; ?>>
                <td class="spider_order table_medium_col"><input id="order_input_<?php echo $row_data->id; ?>" name="order_input_<?php echo $row_data->id; ?>" type="text" size="1" value="<?php echo $row_data->order; ?>" /></td>
                <td class="connectedSortable handles table_small_col"><div class="bwg_img_handle handle" style="margin:5px auto 0 auto;" title="Drag to re-order"></div></td>
                <td class="table_small_col check-column"><input id="check_<?php echo $row_data->id; ?>" name="check_<?php echo $row_data->id; ?>" onclick="spider_check_all(this)" type="checkbox" /></td>
                <td class="table_small_col"><?php echo $row_data->id; ?></td>   
                <td class="table_extra_large_col">
                  <img title="<?php echo $row_data->name; ?>" style="border: 1px solid #CCCCCC; max-width:60px; max-height:60px;" src="<?php echo $preview_image; ?>">
                </td>
                <td><a onclick="spider_set_input_value('task', 'edit');
                                spider_set_input_value('current_id', '<?php echo $row_data->id; ?>');
                                spider_form_submit(event, 'albums_form')" href="" title="<?php echo __('Edit', 'bwg_back'); ?>"><?php echo $row_data->name; ?></a></td>
                <td><?php echo $row_data->slug; ?></td> 
                <td><?php echo get_userdata($row_data->author)->display_name; ?></td>
                <td class="table_big_col publish_icon"><a style="background-image:url('<?php echo WD_BWG_URL . '/images/icons/' . $published_image . '.png'; ?>'); background-repeat: no-repeat; display: inline-block; width: 18px; height: 22px;margin: 3px; vertical-align: middle;background-size: contain;" title="<?php echo $unpublished; ?>" onclick="spider_set_input_value('task', '<?php echo $published; ?>');spider_set_input_value('current_id', '<?php echo $row_data->id; ?>');spider_form_submit(event, 'albums_form')" href=""></a></td>
                <td class="table_big_col"><a class="bwg_img_edit" title="<?php echo __('Edit', 'bwg_back'); ?>" onclick="spider_set_input_value('task', 'edit');
                                                      spider_set_input_value('current_id', '<?php echo $row_data->id; ?>');
                                                      spider_form_submit(event, 'albums_form')" href=""></a></td>
                <td class="table_big_col"><a class="bwg_img_remove" title="<?php echo __('Delete', 'bwg_back'); ?>" onclick="if(confirm('Do you want to delete selected items?')){ spider_set_input_value('task', 'delete');
                                                                                                                                spider_set_input_value('current_id', '<?php echo $row_data->id; ?>');
                                                                                                                                spider_form_submit(event, 'albums_form')}
                                                                                                                              else {
                                                                                                                                return false;
                                                                                                                              }" href=""></a></td>
              </tr>
              <?php
              $ids_string .= $row_data->id . ',';
            }
          }
          ?>
        </tbody>
      </table>
      <div class="tablenav bottom">
        <?php
        WDWLibrary::html_page_nav($page_nav['total'],$pager++, $page_nav['limit'], 'albums_form', $per_page);
        ?>
      </div>
      <input id="task" name="task" type="hidden" value="" />
      <input id="current_id" name="current_id" type="hidden" value="" />
      <input id="ids_string" name="ids_string" type="hidden" value="<?php echo $ids_string; ?>" />
      <input id="asc_or_desc" name="asc_or_desc" type="hidden" value="asc" />
      <input id="order_by" name="order_by" type="hidden" value="<?php echo $order_by; ?>" />
      <script>
        window.onload = spider_show_hide_weights;
      </script>
    </form>
    <?php
  }

  public function edit($id) {
    global $WD_BWG_UPLOAD_DIR;
    global $wd_bwg_options;
    $row = $this->model->get_row_data($id);
    $page_title = (($id != 0) ? __('Edit album ',"bwg_back") . $row->name : __('Create new album',"bwg_back"));
    $per_page = $this->model->per_page();
    $enable_wp_editor = isset($wd_bwg_options->enable_wp_editor) ? $wd_bwg_options->enable_wp_editor : 1;
    ?>
    <script>
      function bwg_add_preview_image(files) {
        document.getElementById("preview_image").value = files[0]['thumb_url'];
        document.getElementById("button_preview_image").style.display = "none";
        document.getElementById("delete_preview_image").style.display = "inline-block";
        if (document.getElementById("img_preview_image")) {
          document.getElementById("img_preview_image").src = files[0]['reliative_url'];
          document.getElementById("img_preview_image").style.display = "inline-block";
        }
      }

      function bwg_add_items(trackIds, titles, types) {
        jQuery(document).trigger("onAddAlbum");
        var tbody = document.getElementById('tbody_albums_galleries');
        var counter = 0;
        for(i = 0; i < trackIds.length; i++) {          
          tr = document.createElement('tr');
          tr.setAttribute('id', "tr_0:" + types[i] + ":" + trackIds[i]);
          tr.setAttribute('style', 'height:35px');
          
          var td_drag = document.createElement('td');
          td_drag.setAttribute('class','connectedSortable table_small_col');
          td_drag.setAttribute('title','Drag to re-order');
          
          var div_drag = document.createElement('div');
          div_drag.setAttribute('class', 'bwg_img_handle handle');
          div_drag.setAttribute('style','margin-left:10px;');
          td_drag.appendChild(div_drag);
          tr.appendChild(td_drag);          
          
          var td_title = document.createElement('td');
          td_title.setAttribute('style', 'max-width:420px;min-width:400px;');
          td_title.innerHTML = (types[i] == '1' ? 'Album: ' : 'Gallery: ') + titles[i];
          
          tr.appendChild(td_title);
          
          var td_delete = document.createElement('td');
          td_delete.setAttribute('class', 'table_small_col');
          
          var span_del = document.createElement('span');
          span_del.setAttribute('class', 'spider_delete_img');
          span_del.setAttribute('onclick', 'spider_remove_row("tbody_albums_galleries", event, this);');
          
          td_delete.appendChild(span_del);
          tr.appendChild(td_delete);
          
          tbody.appendChild(tr);
          counter++;
        }
        if (counter) {
          document.getElementById("table_albums_galleries").style.display = "block";
        }
        spider_sortt('tbody_albums_galleries');
        tb_remove();
      }
    </script>
    <form class="wrap bwg_form" method="post" action="admin.php?page=albums_bwg" style="width: 98%; float: left;">
      <?php wp_nonce_field( 'albums_bwg', 'bwg_nonce' ); ?>
      <div>
        <span class="album-icon"></span>
        <h2><?php echo $page_title; ?></h2>
      </div>
      <div style="float:right;">
        <input id="save_albums" class="wd-btn wd-btn-primary wd-btn-icon wd-btn-save" type="submit" onclick="if(spider_check_required('name', 'Name')){return false;};spider_set_input_value('task', 'save')" value="<?php echo __('Save', 'bwg_back'); ?>" />
        <input class="wd-btn wd-btn-primary wd-btn-icon wd-btn-apply" type="submit" onclick="if(spider_check_required('name', 'Name')){return false;};spider_set_input_value('task', 'apply')" value="<?php echo __('Apply', 'bwg_back'); ?>" />
        <input class="wd-btn wd-btn-primary wd-btn-icon wd-btn-cancel" type="submit" onclick="spider_set_input_value('task', 'cancel')" value="<?php echo __('Cancel', 'bwg_back'); ?>" />
      </div>
      <table style="clear:both;">
        <tbody>
          <tr>
            <td class="spider_label"><label for="name"><?php _e('Name:', 'bwg_back'); ?> <span style="color:#FF0000;">*</span> </label></td>
            <td><input type="text" id="name" name="name" value="<?php echo $row->name; ?>" size="39" class="bwg_requried"/></td>
          </tr>
          <tr>
            <td class="spider_label"><label for="slug"><?php _e('Slug:', 'bwg_back'); ?> </label></td>
            <td><input type="text" id="slug" name="slug" value="<?php echo $row->slug; ?>" size="39" /><input type="hidden" id="old_slug" name="old_slug" value="<?php echo $row->slug; ?>" size="39" /></td>
          </tr>
          <tr>
            <td class="spider_label"><label for="description"><?php _e('Description:', 'bwg_back'); ?> </label></td>
            <td>
              <div style="width:500px;">
              <?php
              if (user_can_richedit() && $enable_wp_editor) {
                wp_editor($row->description, 'description', array('teeny' => TRUE, 'textarea_name' => 'description', 'media_buttons' => FALSE, 'textarea_rows' => 5));
              }
              else {
              ?>
              <textarea cols="36" rows="5" id="description" name="description" style="resize:vertical">
                <?php echo $row->description; ?>
              </textarea>
              <?php
              }
              ?>
              </div>
            </td>
          </tr>
          <tr>
            <td class="spider_label"><label><?php _e('Author:', 'bwg_back'); ?> </label></td>
            <td><?php echo get_userdata($row->author)->display_name; ?></td>
          </tr>
          <tr>
            <td class="spider_label"><label for="published1"><?php _e('Published:', 'bwg_back'); ?> </label></td>
            <td>
              <input type="radio" class="inputbox" id="published0" name="published" <?php echo (($row->published) ? '' : 'checked="checked"'); ?> value="0" >
              <label for="published0"><?php _e('No', 'bwg_back'); ?></label>
              <input type="radio" class="inputbox" id="published1" name="published" <?php echo (($row->published) ? 'checked="checked"' : ''); ?> value="1" >
              <label for="published1"><?php _e('Yes', 'bwg_back'); ?></label>
            </td>
          </tr>
          <tr>
            <td class="spider_label"><label for="url"><?php _e('Preview image:', 'bwg_back'); ?> </label></td>
            <td>
            <?php 
            $query_url =  add_query_arg(array('action' => 'addImages', 'width' => '700', 'height' => '550', 'extensions' => 'jpg,jpeg,png,gif', 'callback' => 'bwg_add_preview_image'), admin_url('admin-ajax.php'));
            $query_url = wp_nonce_url( $query_url, 'addImages', 'bwg_nonce' );
            $query_url =  add_query_arg(array('TB_iframe' => '1'), $query_url );
            

            ?>
              <a href="<?php echo $query_url; ?>"
                 id="button_preview_image"
                 class="wd-preview-image-btn thickbox thickbox-preview"
                 title="<?php echo __('Add Preview Image', 'bwg_back'); ?>"
                 onclick="return false;"
                 style="display:none;">
                
              </a>
              <input type="hidden" id="preview_image" name="preview_image" value="<?php echo $row->preview_image; ?>" style="display:inline-block;"/>
              <img id="img_preview_image"
                   style="max-height:90px; max-width:120px; vertical-align:middle;"
                   src="<?php echo site_url() . '/' . $WD_BWG_UPLOAD_DIR . $row->preview_image; ?>">
              <span id="delete_preview_image" class="spider_delete_img"
                    onclick="spider_remove_url('button_preview_image', 'preview_image', 'delete_preview_image', 'img_preview_image')"></span>
            </td>
          </tr>
          <tr>
            <td class="spider_label"><label for="content-add_media"><?php _e('Albums And Galleries:', 'bwg_back'); ?> </label></td>
            <td>
            <?php 
              $query_url = add_query_arg(array('action' => 'addAlbumsGalleries', 'album_id' => $id, 'width' => '700', 'height' => '550', 'bwg_items_per_page'=>$per_page ), admin_url('admin-ajax.php'));
              $query_url = wp_nonce_url( $query_url, 'addAlbumsGalleries', 'bwg_nonce' );
              $query_url = add_query_arg(array('TB_iframe' => '1'), $query_url);

              
            ?>
              <a href="<?php echo $query_url; ?>" class="wd-btn wd-btn-primary wd-btn-icon wd-btn-add thickbox thickbox-preview" id="content-add_media" title="<?php echo __("Add Images", 'bwg_back'); ?>" onclick="return false;">
                <?php _e('Add Albums/Galleries', 'bwg_back'); ?>
              </a>              
              <?php $albums_galleries = $this->model->get_albums_galleries_rows_data($id) ?>
              <table id="table_albums_galleries" class="widefat spider_table" <?php echo (($albums_galleries) ? '' : 'style="display:none;"'); ?>>          
                <tbody id="tbody_albums_galleries">
                  <?php
                  if ($albums_galleries) {
                    $hidden = "";
                    foreach($albums_galleries as $alb_gal) {
                      if ($alb_gal) {
                        ?>
                        <tr id="tr_<?php echo $alb_gal->id . ":" . $alb_gal->is_album . ":" . $alb_gal->alb_gal_id ?>" style="height:35px;">
                          <td class="connectedSortable table_small_col" title="<?php _e('Drag to re-order', 'bwg_back'); ?>"><div class="handle"></div></td>
                          <td style="max-width:420px; min-width:400px;"><?php echo ($alb_gal->is_album ? 'Album: ' : 'Gallery: ') . $alb_gal->name; ?></td>
                          <td class="table_small_col">
                            <span class="spider_delete_img" onclick="spider_remove_row('tbody_albums_galleries', event, this)"/>
                          </td>
                        </tr>
                        <?php
                        $hidden .= $alb_gal->id . ":" . $alb_gal->is_album . ":" . $alb_gal->alb_gal_id . ",";
                      }
                    }
                  }
                  ?>
                </tbody>
              </table>
              <input type="hidden" value="<?php echo isset($hidden) ? $hidden : ''; ?>" id="albums_galleries" name="albums_galleries"/>
            </td>
          </tr>          
        </tbody>
      </table>
      <input id="task" name="task" type="hidden" value="" />
      <input id="current_id" name="current_id" type="hidden" value="<?php echo $row->id; ?>" />
      <script>
        jQuery(window).load(function() {
          spider_reorder_items('tbody_albums_galleries');
        });
        <?php
        if ($row->preview_image == '') {
          ?>
          spider_remove_url('button_preview_image', 'preview_image', 'delete_preview_image', 'img_preview_image');
          <?php
        }
        ?>
      </script>
    </form>
    <?php
  }  
 
}