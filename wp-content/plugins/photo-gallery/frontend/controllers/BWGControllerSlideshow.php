<?php
class BWGControllerSlideshow {

  public function __construct() {
  }

  public function execute($params = array(), $from_shortcode = 0, $bwg = 0) {
    $this->display($params, $from_shortcode, $bwg);
  }

  public function display($params, $from_shortcode = 0, $bwg = 0) {
    require_once WD_BWG_DIR . "/frontend/models/BWGModelSlideshow.php";
    $model = new BWGModelSlideshow();

    require_once WD_BWG_DIR . "/frontend/views/BWGViewSlideshow.php";
    $view = new BWGViewSlideshow($model);
    $view->display($params, $from_shortcode, $bwg);
  }
}