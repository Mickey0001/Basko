<?php
class BWGControllerImage_browser {

  public function __construct() {
  }

  public function execute($params = array(), $from_shortcode = 0, $bwg = 0) {
    $this->display($params, $from_shortcode, $bwg);
  }

  public function display($params, $from_shortcode = 0, $bwg = 0) {
    require_once WD_BWG_DIR . "/frontend/models/BWGModelImage_browser.php";
    $model = new BWGModelImage_browser();

    require_once WD_BWG_DIR . "/frontend/views/BWGViewImage_browser.php";
    $view = new BWGViewImage_browser($model);

    $view->display($params, $from_shortcode, $bwg);
  }
}