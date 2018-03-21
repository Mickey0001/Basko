<?php
class BWGModelWidgetFrontEnd {
  public function get_tags_data($count) {
    global $wpdb;
    $rows = $wpdb->get_results('SELECT image.thumb_url as thumb_url, image.id as image_id, tags.name, tags.slug, tags.term_id, image.filetype FROM ' . $wpdb->prefix . 'terms AS tags INNER JOIN ' . $wpdb->prefix . 'term_taxonomy AS taxonomy ON taxonomy.term_id=tags.term_id INNER JOIN (SELECT image.thumb_url, tag.tag_id, image.id, image.filetype FROM ' . $wpdb->prefix . 'bwg_image AS image INNER JOIN ' . $wpdb->prefix . 'bwg_image_tag AS tag ON image.id=tag.image_id ORDER BY RAND()) AS image ON image.tag_id=tags.term_id WHERE taxonomy.taxonomy="bwg_tag" GROUP BY tags.term_id' . ($count ? ' LIMIT ' . $count : ""));
    foreach ( $rows as $row ) {
      $row->permalink = WDWLibrary::get_custom_post_permalink(array( 'slug' => $row->slug, 'post_type' => 'tag' ));
    }

    return $rows;
  }
}