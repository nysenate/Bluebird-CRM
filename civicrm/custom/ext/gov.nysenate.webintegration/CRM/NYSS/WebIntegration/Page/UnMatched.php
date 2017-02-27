<?php

require_once 'CRM/Core/Page.php';

class CRM_NYSS_WebIntegration_Page_UnMatched extends CRM_Core_Page {
  public function run() {
    $cid = CRM_Core_Session::getLoggedInContactID();
    $aid = civicrm_api3('option_value', 'getvalue', array(
      'option_group_name' => 'activity_type',
      'name' => 'website_message',
      'return' => 'value',
    ));

    $this->assign('cid', $cid);
    $this->assign('aid', $aid);

    CRM_Core_Resources::singleton()->addVars('NYSS', array(
      'unmatched_cid' => $cid,
      'unmatched_aid' => $aid,
    ));

    parent::run();
  }
}
