<?php

/**
 * Thin AJAX entry point for civicrm/nyss/inbox/deletemsgs.
 *
 * @see CRM_NYSS_Inbox_BAO_Inbox::deleteMessages()
 */
class CRM_NYSS_Inbox_Page_DeleteMessages extends CRM_Core_Page {
  public function run() {
    $ids = $_REQUEST['ids'] ?? [];
    $ret = CRM_NYSS_Inbox_BAO_Inbox::deleteMessages($ids);

    // sendJSONResponse() echoes the JSON response and calls civiExit()
    // itself, so the business logic no longer needs to own that.
    CRM_Utils_System::sendJSONResponse($ret);
  }
}
