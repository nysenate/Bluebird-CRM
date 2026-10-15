<?php

/**
 * Thin AJAX entry point for civicrm/nyss/inbox/clearmsgs.
 *
 * @see CRM_NYSS_Inbox_BAO_Inbox::clearMessages()
 */
class CRM_NYSS_Inbox_Page_ClearMessages extends CRM_Core_Page {
  public function run() {
    $ids = $_REQUEST['ids'] ?? [];
    CRM_NYSS_Inbox_BAO_Inbox::clearMessages($ids);
    CRM_Utils_System::civiExit();
  }
}
