<?php
use CRM_NYSS_Mail_ExtensionUtil as E;

class CRM_NYSS_Mail_Page_MosaicoTemplatePrint extends CRM_Core_Page {

  public function run() {
    $templateId = CRM_Utils_Request::retrieve('id', 'Positive');
    if (empty($templateId)) {
      CRM_Core_Error::statusBounce('No template was selected.', CRM_Utils_System::url('civicrm/mosaico-template-list'));
    }

    try {
      $template = civicrm_api3('MosaicoTemplate', 'getsingle', ['id' => $templateId]);
      //Civi::log()->debug(__METHOD__, ['template' => $template]);

      CRM_Utils_System::setTitle($template['title']);
      $this->assign('templateHtml', $template['html']);
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::statusBounce('Unable to retrieve template.', CRM_Utils_System::url('civicrm/mosaico-template-list'));
    }

    parent::run();
  }

}
