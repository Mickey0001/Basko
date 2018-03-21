<?php
class BWGModelGalleryBox {
  public function get_comment_rows_data($image_id) {
    global $wpdb;
    $row = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'bwg_image_comment WHERE image_id="%d" AND published=1 ORDER BY `id` DESC', $image_id));
    return $row;
  }

  public function get_image_rows_data($gallery_id, $bwg, $sort_by, $order_by = 'asc', $tag = 0) {
    global $wpdb;
    if ($sort_by == 'size' || $sort_by == 'resolution') {
      $sort_by = ' CAST(image.' . $sort_by . ' AS SIGNED) ';
    }
    elseif ($sort_by == 'random' || $sort_by == 'RAND()') {
      $sort_by = 'RAND()';
    }
    elseif (($sort_by != 'alt') && ($sort_by != 'date') && ($sort_by != 'filetype') && ($sort_by != 'filename')) {
      $sort_by = 'image.`order`';
    }
    if (strtolower($order_by) != 'asc') {
      $order_by = 'desc';
    }
    $bwg_random_seed = isset($_SESSION['bwg_random_seed_'. $bwg]) ? $_SESSION['bwg_random_seed_'. $bwg] : '';
    $filter_tags = (isset($_REQUEST['filter_tag_'. $bwg]) && $_REQUEST['filter_tag_'. $bwg]) ? explode(",", $_REQUEST['filter_tag_'. $bwg]) : array();
    $filter_search_name = (isset($_REQUEST['filter_search_name_'. $bwg])) ? esc_html($_REQUEST['filter_search_name_'. $bwg]) : '';
    $where = '';
    if ($filter_search_name != '') {
     $where = ' AND (image.alt LIKE "%%' . $filter_search_name . '%%" OR image.description LIKE "%%' . $filter_search_name . '%%")';
    }

    $where .= ($gallery_id ? ' AND image.gallery_id = "' . $gallery_id . '" ' : '') . ($tag ? ' AND tag.tag_id = "' . $tag . '" ' : '');
    $join = $tag ? 'LEFT JOIN ' . $wpdb->prefix . 'bwg_image_tag as tag ON image.id=tag.image_id' : '';

    if ($filter_tags){
      $join .= ' LEFT JOIN (SELECT GROUP_CONCAT(tag_id SEPARATOR ",") AS tags_combined, image_id FROM  ' . $wpdb->prefix . 'bwg_image_tag' . ($gallery_id ? ' WHERE gallery_id="' . $gallery_id . '"' : '') . ' GROUP BY image_id) AS tags ON image.id=tags.image_id';
      $where .= ' AND CONCAT(",", tags.tags_combined, ",") REGEXP ",(' . implode("|", $filter_tags) . ')," ';
    }

    $row = $wpdb->get_results('SELECT image.*, rates.rate FROM ' . $wpdb->prefix . 'bwg_image as image LEFT JOIN (SELECT rate, image_id FROM ' . $wpdb->prefix . 'bwg_image_rate WHERE ip="%s") as rates ON image.id=rates.image_id ' . $join . ' WHERE image.published=1 ' . $where . ' ORDER BY ' . str_replace('RAND()', 'RAND(' . $bwg_random_seed . ')', $sort_by) . ' ' . $order_by);

    return $row;
  }
}