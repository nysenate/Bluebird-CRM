<?php

class CRM_NYSS_Inbox_Page_Matched extends CRM_Core_Page {
  public function run() {
    CRM_NYSS_Inbox_BAO_Inbox::addResources('matched');

    $this->assign('title', 'Matched Messages');
    $this->assign('list', 'matched');
    $this->assign('toggleAll', "<input class='select-all' type='checkbox'>");

    $controller = new CRM_Core_Controller_Simple('CRM_NYSS_Inbox_Form_MessageFilter',
      ts('Message Filter'), NULL
    );
    $controller->setEmbedded(TRUE);
    $controller->run();

    parent::run();
  }

  static function getMatched() {
    //Civi::log()->debug('getMatched', array('$_GET' => $_GET));

    $requiredParameters = array();
    $optionalParameters = array(
      'range' => 'Integer',
      'term' => 'String',
    );
    $params = CRM_Core_Page_AJAX::defaultSortAndPagerParams();
    $params += CRM_Core_Page_AJAX::validateParams($requiredParameters, $optionalParameters);

    //get matched records
    $matched = CRM_NYSS_Inbox_BAO_Inbox::getMessages($params, 'matched');
    /*Civi::log()->debug('getMatched', array('matched' => $matched));*/

    CRM_Utils_JSON::output($matched);
  }
}
