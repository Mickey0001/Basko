<?php
class BWGControllerThumbnails {

  public function __construct() {
  }

  public function execute($params = array(), $from_shortcode = 0, $bwg = 0) {
	$this->display($params, $from_shortcode, $bwg);
  }

  public function display($params, $from_shortcode = 0, $bwg = 0) {
    require_once WD_BWG_DIR . "/frontend/models/BWGModelThumbnails.php";
    $model = new BWGModelThumbnails();

    require_once WD_BWG_DIR . "/frontend/views/BWGViewThumbnails.php";
    $view = new BWGViewThumbnails($model);
    $view->display($params, $from_shortcode, $bwg);
  }
}