<?php

use CRM_NYSS_Inbox_ExtensionUtil as E;

class CRM_NYSS_Inbox_BAO_InboxMessages extends CRM_NYSS_Inbox_DAO_InboxMessages {
  static function statusOptions() {
    return [
      CRM_NYSS_Inbox_BAO_Inbox::STATUS_UNMATCHED => 'Unmatched',
      CRM_NYSS_Inbox_BAO_Inbox::STATUS_MATCHED => 'Matched',
      CRM_NYSS_Inbox_BAO_Inbox::STATUS_CLEARED => 'Cleared',
      CRM_NYSS_Inbox_BAO_Inbox::STATUS_DELETED => 'Deleted',
      CRM_NYSS_Inbox_BAO_Inbox::STATUS_UNPROCESSED => 'Unprocessed',
    ];
  }
}
