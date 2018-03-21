<?php

class BWGControllerTags_bwg {
  public function __construct() {
  }

  public function execute() {
    $task = WDWLibrary::get('task');
    $id = WDWLibrary::get('current_id', 0);
    if ( $task != '' ) {
      if ( !WDWLibrary::verify_nonce('tags_bwg') ) {
        die('Sorry, your nonce did not verify.');
      }
    }
    $message = WDWLibrary::get('message');
    echo WDWLibrary::message_id($message);
    if ( method_exists($this, $task) ) {
      $this->$task($id);
    }
    else {
      $this->display();
    }
  }

  public function display() {
    require_once WD_BWG_DIR . "/admin/models/BWGModelTags_bwg.php";
    $model = new BWGModelTags_bwg();

    require_once WD_BWG_DIR . "/admin/views/BWGViewTags_bwg.php";
    $view = new BWGViewTags_bwg($model);
    $view->display();
  }

  public function save() {
    $message = $this->save_tag();
    $page = WDWLibrary::get('page');
    $query_url = wp_nonce_url( admin_url('admin.php'), 'tags_bwg', 'bwg_nonce' );
    $query_url = add_query_arg(array('page' => $page, 'task' => 'display', 'message' => $message), $query_url);
    WDWLibrary::spider_redirect($query_url);
  } 
  
  public function bwg_get_unique_slug($slug, $id) {
    global $wpdb;
    $slug = sanitize_title($slug);
    if ($id != 0) {
      $query = $wpdb->prepare("SELECT slug FROM " . $wpdb->prefix . "terms WHERE slug = %s AND term_id != %d", $slug, $id);
    }
    else {
      $query = $wpdb->prepare("SELECT slug FROM " . $wpdb->prefix . "terms WHERE slug = %s", $slug);
    }
    if ($wpdb->get_var($query)) {
      $num = 2;
      do {
        $alt_slug = $slug . "-$num";
        $num++;
        $slug_check = $wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $wpdb->prefix . "terms WHERE slug = %s", $alt_slug));
      } while ($slug_check);
      $slug = $alt_slug;
    }
    return $slug;
  }
  
  public function bwg_get_unique_name($name, $id) {
    /*global $wpdb;
    if ($id != 0) {
      $query = $wpdb->prepare("SELECT name FROM " . $wpdb->prefix . "terms WHERE name = %s AND term_id != %d", $name, $id);
    }
    else {
      $query = $wpdb->prepare("SELECT name FROM " . $wpdb->prefix . "terms WHERE name = %s", $name);
    }
    if ($wpdb->get_var($query)) {
      $num = 2;
      do {
        $alt_name = $name . "-$num";
        $num++;
        $slug_check = $wpdb->get_var($wpdb->prepare("SELECT name FROM " . $wpdb->prefix . "terms WHERE name = %s", $alt_name));
      } while ($slug_check);
      $name = $alt_name;
    }*/
    return $name;
  }

  public function save_tag() {
    $message_id = 15;
    $name = ((isset($_POST['tagname'])) ? esc_html(stripslashes($_POST['tagname'])) : '');
    $slug = ((isset($_POST['slug']) && (esc_html($_POST['slug']) != '')) ? esc_html(stripslashes($_POST['slug'])) : $name);
    $slug = $this->bwg_get_unique_slug($slug, 0);
    $slug = sanitize_title($slug);
    if ( $name ) {
      $save = wp_insert_term($name, 'bwg_tag', array(
                                    'description' => '',
                                    'slug' => $slug,
								'parent' => 0
								)
							);
      // Create custom post (type is tag).
      $custom_post_params = array(
        'id' => $save['term_id'],
        'title' => $name,
        'slug' => $slug,
        'type' => array(
          'post_type' => 'tag',
          'mode' => '',
        ),
      );
      WDWLibrary::bwg_create_custom_post($custom_post_params);
      $message_id = 1;
      if ( isset($save->errors) ) {
        $message_id = 14;
      }
    }

    return $message_id;
  }

  function edit_tag() {
    global $wpdb;
    $flag = FALSE;
    $id = ((isset($_REQUEST['tag_id'])) ? esc_html(stripslashes($_REQUEST['tag_id'])) : '');
    $query = $wpdb->prepare("SELECT count FROM " . $wpdb->prefix . "term_taxonomy WHERE term_id=%d", $id);
    $count = $wpdb->get_var($query);
    $name = ((isset($_REQUEST['tagname'])) ? esc_html(stripslashes($_REQUEST['tagname'])) : '');
    $name = $this->bwg_get_unique_name($name, $id);
    if ( $name ) {
      $slug = ((isset($_REQUEST['slug']) && (esc_html($_REQUEST['slug']) != '')) ? esc_html(stripslashes($_REQUEST['slug'])) : $name);
      $slug = $this->bwg_get_unique_slug($slug, $id);
      $save = wp_update_term($id, 'bwg_tag', array(
        'name' => $name,
        'slug' => $slug,
      ));
	 
      // Create custom post (type is tag).
      $custom_post_params = array(
        'id' => $id,
        'title' => $name,
        'slug' => $slug,
		'old_slug' => !empty($_POST['old_slug']) ? $_POST['old_slug'] : '',
        'type' => array(
          'post_type' => 'tag',
          'mode' => '',
        ),
      );
      WDWLibrary::bwg_create_custom_post($custom_post_params);
	  if ( isset($save->errors) ) {
        echo 'The slug must be unique.';
      }
      else {
        $flag = TRUE;
      }
    }
    if ( $flag ) {
      echo $name . '.' . $slug . '.' . $count;
    }
    die();
  }

  public function edit_tags() {
    global $wpdb;
	$message_id = '';
    $flag = FALSE;
    $rows = get_terms('bwg_tag', array('orderby' => 'count', 'hide_empty' => 0));
	$terms = array();
    foreach ($rows as $row) {
      $id = $row->term_id;
      $name = ((isset($_POST['tagname' . $row->term_id])) ? esc_html(stripslashes($_POST['tagname' . $id])) : '');
      $name = $this->bwg_get_unique_name($name,  $id);
      if ($name) {
        $old_slug = ((isset($_POST['old_slug' . $row->term_id]) && (esc_html($_POST['old_slug' . $id]) != '')) ? esc_html(stripslashes($_POST['old_slug' . $id])) : '');
        $slug 	  = ((isset($_POST['slug' . $row->term_id]) && (esc_html($_POST['slug' . $id]) != '')) ? esc_html(stripslashes($_POST['slug' . $id])) : $name);
		$terms[]  = array('id' => $id, 'name' => $name, 'slug' => $slug, 'old_slug' => $old_slug);
		$slug = $this->bwg_get_unique_slug($slug, $id);
        $save = wp_update_term($id, 'bwg_tag', array('name' => $name, 'slug' => $slug));
        if (isset($save->errors)) {
          $message_id = 16;
        }
        else {
          $flag = TRUE;
        }
      }
    }

	$name = ((isset($_POST['tagname'])) ? esc_html(stripslashes($_POST['tagname'])) : '');
	$name = $this->bwg_get_unique_name($name, 0);
	$slug = ((isset($_POST['slug']) && (esc_html($_POST['slug']) != '')) ? esc_html(stripslashes($_POST['slug'])) : $name);
	$slug = $this->bwg_get_unique_slug($slug, 0);
	if ($name) {
		$save = wp_insert_term($name, 'bwg_tag', array(
								'description'=> '',
								'slug' => $slug,
								'parent' => 0)
							);
		$term = array('id' => $save['term_id'], 'name' => $name, 'old_slug' => '', 'slug' => $slug);
		$terms[count($terms)] = $term;
		$message_id = 1;
		if (isset($save->errors)) {
			$message_id = 15;
		}
    }
	if ($flag) {
      $message_id = 1;
    }
	// Create custom post (type is tag).
	if ( !empty($terms) ) {
		foreach($terms as $term){
			$custom_post_params = array(
									'id' => $term['id'],
									'title' => $term['name'],
									'slug' => $term['slug'],
									'old_slug' => $term['old_slug'],
									'type' => array(
									  'post_type' => 'tag',
									  'mode' => '',
									),
								);
			WDWLibrary::bwg_create_custom_post($custom_post_params);
		}
	}
	$page = WDWLibrary::get('page');
    $query_url = wp_nonce_url( admin_url('admin.php'), 'tags_bwg', 'bwg_nonce' );
    $query_url = add_query_arg(array('page' => $page, 'task' => 'display', 'message' => $message_id), $query_url);
    WDWLibrary::spider_redirect($query_url);
  }

  public function delete($id) {
    global $wpdb;
	$message = 2;
	$row = $wpdb->get_row( $wpdb->prepare('SELECT term_id, slug FROM ' . $wpdb->prefix . 'terms WHERE term_id="%d"', $id) );
	if ( !empty($row) ) {
		wp_delete_term($id, 'bwg_tag');
		$flag = $wpdb->query( $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE tag_id="%d"', $id) );		
		if ($flag !== FALSE) {
		  // Remove custom post (type by bwg_album).
		  WDWLibrary::bwg_remove_custom_post( array( 'slug' => $row->slug, 'post_type' => 'bwg_tag') );
		  $message = 3;
		}
	}
    $page = WDWLibrary::get('page');
    $query_url = wp_nonce_url( admin_url('admin.php'), 'tags_bwg', 'bwg_nonce' );
    $query_url = add_query_arg(array('page' => $page, 'task' => 'display', 'message' => $message), $query_url);
    WDWLibrary::spider_redirect($query_url);
  }
  
  public function delete_all() {
	$message_id = 6;
	$termids = array();
	if ( !empty($_POST['ids_string']) ){
		$ids = explode(',', $_POST['ids_string']);
		foreach ($ids as $id) {
			$keypost = 'check_' . $id;
			if ( !empty($_POST[$keypost]) ) {
				$termids[] = $id;
			}
		}
	}

	if ( !empty($termids) ){
		global $wpdb;
		$terms = $wpdb->get_results('SELECT `term_id` AS `id`, `slug` FROM ' . $wpdb->prefix . 'terms WHERE `term_id` IN (' . implode(',', $termids). ')');
		if ( !empty($terms) ) {
			$delete = false;
			foreach( $terms as $term ) {
				wp_delete_term($term->id, 'bwg_tag');
				$wpdb->query( $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'bwg_image_tag WHERE tag_id="%d"', $term->id) );
				// Remove custom post (type by bwg_tag).
				WDWLibrary::bwg_remove_custom_post( array( 'slug' => $term->slug, 'post_type' => 'bwg_tag') );
				$delete = true;
			}
			if ( $delete ) {
				$message_id = 5;
			}
		}
	}
    $page = WDWLibrary::get('page');
    $query_url = wp_nonce_url( admin_url('admin.php'), 'tags_bwg', 'bwg_nonce' );
    $query_url  = add_query_arg(array('page' => $page, 'task' => 'display', 'message' => $message_id), $query_url);
    WDWLibrary::spider_redirect($query_url);
  }
}