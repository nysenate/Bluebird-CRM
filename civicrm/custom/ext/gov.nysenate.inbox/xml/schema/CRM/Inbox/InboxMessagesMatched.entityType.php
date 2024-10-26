<?php
// This file declares a new entity type. For more details, see "hook_civicrm_entityTypes" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
return [
  [
    'name' => 'InboxMessagesMatched',
    'class' => 'CRM_NYSS_Inbox_DAO_InboxMessagesMatched',
    'table' => 'nyss_inbox_messages_matched',
  ],
];
