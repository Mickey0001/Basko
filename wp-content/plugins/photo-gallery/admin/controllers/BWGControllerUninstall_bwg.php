<?php

class BWGControllerUninstall_bwg {
 
  public function __construct() {
    global $bwg_options;
    if (!class_exists("DoradoWebConfig")) {
      include_once(WD_BWG_DIR . "/wd/config.php");
    }
    $config = new DoradoWebConfig();
    $config->set_options($bwg_options);
    $deactivate_reasons = new DoradoWebDeactivate($config);
    $deactivate_reasons->submit_and_deactivate();
  }
  
  public function execute() {
    $task = ((isset($_POST['task'])) ? esc_html(stripslashes($_POST['task'])) : '');

    if($task != ''){
      if(!WDWLibrary::verify_nonce('uninstall_bwg')){
        die('Sorry, your nonce did not verify.');
      }
    }

    if (method_exists($this, $task)) {
      $this->$task();
    }
    else {
      $this->display();
    }
  }

  public function display() {
    require_once WD_BWG_DIR . "/admin/models/BWGModelUninstall_bwg.php";
    $model = new BWGModelUninstall_bwg();

    require_once WD_BWG_DIR . "/admin/views/BWGViewUninstall_bwg.php";
    $view = new BWGViewUninstall_bwg($model);
    $view->display();
  }

  public function uninstall() {
    require_once WD_BWG_DIR . "/admin/models/BWGModelUninstall_bwg.php";
    $model = new BWGModelUninstall_bwg();

    require_once WD_BWG_DIR . "/admin/views/BWGViewUninstall_bwg.php";
    $view = new BWGViewUninstall_bwg($model);
    $view->uninstall();
  }
}