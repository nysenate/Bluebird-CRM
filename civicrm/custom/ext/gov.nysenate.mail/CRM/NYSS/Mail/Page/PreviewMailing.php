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
      //Civi::log()->debug(__FUNCTION__, ['$mailing' => $mailing]);
    }
    catch (CiviCRM_API3_Exception $e) {}

    $this->assign('content', $mailing['values']['body_html']);

    // Suppress "Bootstrap theme not found" error that was introduced in CiviCore 6.13
    // as part of the "Bootstrap3 Everywhere PR" (https://github.com/civicrm/civicrm-core/pull/34890)
    CRM_Core_Region::instance('page-header')->add([
      'markup' => '<style>#bootstrap-theme { display: none !important; }</style>',
      'weight' => 100,
    ]);

    parent::run();
  }

}
