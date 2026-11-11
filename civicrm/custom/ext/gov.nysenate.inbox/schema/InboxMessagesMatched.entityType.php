<?php
use CRM_NYSS_Inbox_ExtensionUtil as E;

return [
  'name' => 'InboxMessagesMatched',
  'table' => 'nyss_inbox_messages_matched',
  'class' => 'CRM_NYSS_Inbox_DAO_InboxMessagesMatched',
  'getInfo' => fn() => [
    'title' => E::ts('Inbox Messages Matched'),
    'title_plural' => E::ts('Inbox Messages Matcheds'),
    'description' => E::ts('Links messages with matched contacts and activities'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'idx_row_id' => [
      'fields' => [
        'row_id' => TRUE,
      ],
    ],
    'idx_message_id' => [
      'fields' => [
        'message_id' => TRUE,
      ],
    ],
    'idx_matched_id' => [
      'fields' => [
        'matched_id' => TRUE,
      ],
    ],
    'idx_activity_id' => [
      'fields' => [
        'activity_id' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique InboxMessagesMatched ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'row_id' => [
      'title' => E::ts('Row ID'),
      'sql_type' => 'int',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to inbox row ID'),
      'entity_reference' => [
        'entity' => 'InboxMessages',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'message_id' => [
      'title' => E::ts('Message ID'),
      'sql_type' => 'int',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to inbox message ID'),
      'entity_reference' => [
        'entity' => 'InboxMessages',
        'key' => 'message_id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'matched_id' => [
      'title' => E::ts('Matched ID'),
      'sql_type' => 'int',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to matched contact ID'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'activity_id' => [
      'title' => E::ts('Activity ID'),
      'sql_type' => 'int',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to activity ID'),
      'entity_reference' => [
        'entity' => 'Activity',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
  ],
];
