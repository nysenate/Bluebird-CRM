<?php

require_once 'CRM/Core/Page.php';

class CRM_Slicknav_Page_ResponsiveAdminMenu extends CRM_Core_Page {

  public function run() {
    if (CRM_Core_Session::singleton()->get('userID')) {
      CRM_Core_Page_AJAX::setJsHeaders();
      $smarty = CRM_Core_Smarty::singleton();
      print $smarty->fetchWith('CRM/Slicknav/Page/civislicknav.js.tpl', array(
        'navigation' => CRM_Core_BAO_Navigation::buildNavigation(),
      ));
    }
    CRM_Utils_System::civiExit();
  }

}
