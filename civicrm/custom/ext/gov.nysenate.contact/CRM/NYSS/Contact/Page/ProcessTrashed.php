<?php

/**
 * Thin AJAX entry point for civicrm/nyss/processtrashed.
 *
 * @see CRM_NYSS_Contact_BAO::processTrashed()
 */
class CRM_NYSS_Contact_Page_ProcessTrashed extends CRM_Core_Page {
  public function run() {
    $params = [
      'modified_date' => CRM_Utils_Request::retrieveValue('modified_date', 'String'),
      'dryrun' => CRM_Utils_Request::retrieveValue('dryrun', 'Boolean', FALSE),
    ];

    CRM_NYSS_Contact_BAO::processTrashed($params);

    // processTrashed() outputs progress messages. (sidenote- Business logic probably shouldn't do that. Worth refactoring)
    // civiExit() is needed in this case to avoid a full CiviCRM page draw and allow the progess messages to live on their own.
    CRM_Utils_System::civiExit();
  }
}
