<?php

/**
 * Thin AJAX entry point for civicrm/nyss/subscription/admin/process.
 *
 * Registered as a bare "_Page" class (not "Class::method") so that
 * CRM_Core_Invoke::findCallback() routes it through the normal Page
 * dispatch instead of the Civi\Core\Resolver path, which would otherwise
 * inject a GuzzleHttp\Psr7\ServerRequest as the sole argument. The only
 * live caller is the "Save Subscription Settings" dialog button in
 * nyssSubscriptions.tpl, which POSTs eid/mailing_categories_list directly
 * here (it bypasses CRM_NYSS_Subscription_Form_Admin's own submit/postProcess
 * entirely).
 *
 * @see CRM_NYSS_Subscription_Form_Admin::_processSubscriptions()
 */
class CRM_NYSS_Subscription_Page_ProcessSubscriptions extends CRM_Core_Page {
  public function run() {
    $ret = CRM_NYSS_Subscription_Form_Admin::_processSubscriptions($_REQUEST);
    CRM_Utils_System::sendJSONResponse($ret ?? []);
  }
}
