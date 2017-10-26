<?php

require_once 'CRM/Core/Page.php';

class CRM_NYSS_WebIntegration_Page_UnMatched extends CRM_Core_Page {
  public function run() {
    $cid = CRM_Core_Session::getLoggedInContactID();

    $activity_direct = civicrm_api3('option_value', 'getvalue', array(
      'option_group_name' => 'activity_type',
      'name' => 'website_direct_message',
      'return' => 'value',
    ));
    $activity_contextual = civicrm_api3('option_value', 'getvalue', array(
      'option_group_name' => 'activity_type',
      'name' => 'website_contextual_message',
      'return' => 'value',
    ));

    $this->assign('cid', $cid);
    $this->assign('activity_direct', $activity_direct);
    $this->assign('activity_contextual', $activity_contextual);

    CRM_Core_Resources::singleton()->addVars('NYSS', array(
      'unmatched_cid' => $cid,
      'unmatched_activity_direct' => $activity_direct,
      'unmatched_activity_contextual' => $activity_contextual,
    ));
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.webintegration', 'js/inbox_msgs.js');

    parent::run();
  }
}
