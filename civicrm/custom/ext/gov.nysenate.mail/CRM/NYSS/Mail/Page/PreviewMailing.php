<?php
use CRM_NYSS_Mail_ExtensionUtil as E;

class CRM_NYSS_Mail_Page_PreviewMailing extends CRM_Core_Page {

  public function run() {
    $mailingId = CRM_Utils_Request::retrieve('id', 'Positive');
    if (!$mailingId) {
      CRM_Core_Error::statusBounce('No Mailing ID was provided.');
    }

    try {
      $mailing = civicrm_api3('Mailing', 'preview', ['id' => $mailingId]);
    }
    catch (CiviCRM_API3_Exception $e) {}

    $this->assign('content', $mailing['values']['body_html']);

    parent::run();
  }

}
