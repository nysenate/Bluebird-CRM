<?php

require_once 'CRM/Core/Page.php';

class CRM_NYSS_Inbox_Page_Unmatched extends CRM_Core_Page {
  public function run() {
    CRM_NYSS_Inbox_BAO_Inbox::addResources();

    $this->assign('title', 'Unmatched Messages');
    $this->assign('toggleAll', "<input class='select-all' type='checkbox'>");

    $controller = new CRM_Core_Controller_Simple('CRM_NYSS_Inbox_Form_MessageFilter',
      ts('Message Filter'), NULL
    );
    $controller->setEmbedded(TRUE);
    $controller->run();

    parent::run();
  }

  static function getUnmatched() {
    //Civi::log()->debug('getUnmatched', array('$_GET' => $_GET));

    $requiredParameters = array();
    $optionalParameters = array(
      'range' => 'Integer',
      'term' => 'String',
    );
    $params = CRM_Core_Page_AJAX::defaultSortAndPagerParams();
    $params += CRM_Core_Page_AJAX::validateParams($requiredParameters, $optionalParameters);
    //Civi::log()->debug('getUnmatched', array('params' => $params));

    //get unmatched records
    $unmatched = CRM_NYSS_Inbox_BAO_Inbox::getMessages($params, 'unmatched');
    /*Civi::log()->debug('getUnmatched', array(
      'unmatched' => $unmatched,
      'json' => json_encode($unmatched),
    ));*/

    CRM_Utils_JSON::output($unmatched);
  }
}
