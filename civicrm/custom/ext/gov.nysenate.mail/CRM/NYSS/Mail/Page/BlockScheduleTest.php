<?php
use CRM_NYSS_Mail_ExtensionUtil as E;

class CRM_NYSS_Mail_Page_BlockScheduleTest extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('Send Test Email'));

    $mailingId = CRM_Utils_Request::retrieve('id', 'Positive');
    Civi::resources()->addVars('NYSS', ['mailingId' => $mailingId]);

    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.mail', 'js/BlockScheduleTest.js');

    parent::run();
  }

}
