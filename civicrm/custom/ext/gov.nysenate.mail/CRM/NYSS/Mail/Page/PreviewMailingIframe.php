<?php
use CRM_NYSS_Mail_ExtensionUtil as E;

class CRM_NYSS_Mail_Page_PreviewMailingIframe extends CRM_Core_Page {

  public function run() {
    $mailingId = CRM_Utils_Request::retrieve('id', 'Positive');
    if (!$mailingId) {
      CRM_Core_Error::statusBounce('No Mailing ID was provided.');
    }

    $previewUrl = CRM_Utils_System::url('civicrm/nyss/previewmailing', "id={$mailingId}&snippet=4");
    $this->assign('previewUrl', $previewUrl);

    parent::run();
  }

}
