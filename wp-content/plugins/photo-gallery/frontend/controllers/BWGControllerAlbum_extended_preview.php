<?php

class BWGControllerAlbum_extended_preview {

  public function __construct() { }

  public function execute($params = array(), $from_shortcode = 0, $bwg = 0) {
    $this->display($params, $from_shortcode, $bwg);
  }

  public function display($params, $from_shortcode = 0, $bwg = 0) {
    require_once WD_BWG_DIR . "/frontend/models/BWGModelAlbum_extended_preview.php";
    $model = new BWGModelAlbum_extended_preview();

    require_once WD_BWG_DIR . "/frontend/views/BWGViewAlbum_extended_preview.php";
    $view = new BWGViewAlbum_extended_preview($model);
    $view->display($params, $from_shortcode, $bwg);
  }
}