<?php
use CRM_NYSS_Inbox_ExtensionUtil as E;

return [
  'name' => 'InboxAttachments',
  'table' => 'nyss_inbox_attachments',
  'class' => 'CRM_NYSS_Inbox_DAO_InboxAttachments',
  'getInfo' => fn() => [
    'title' => E::ts('Inbox Attachments'),
    'title_plural' => E::ts('Inbox Attachmentses'),
    'description' => E::ts('Store metadata re: email attachments.'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique InboxAttachments ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'email_id' => [
      'title' => E::ts('Email ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to InboxMessages'),
      'entity_reference' => [
        'entity' => 'InboxMessages',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'file_name' => [
      'title' => E::ts('File Name'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('File name'),
    ],
    'file_full' => [
      'title' => E::ts('File Full Path'),
      'sql_type' => 'varchar(512)',
      'input_type' => 'Text',
      'description' => E::ts('Full path to file'),
    ],
    'rejection' => [
      'title' => E::ts('Rejection Details'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Details if attachment cannot be processed.'),
      'default' => NULL,
    ],
    'mime_type' => [
      'title' => E::ts('Mime Type'),
      'sql_type' => 'varchar(128)',
      'input_type' => 'Text',
    ],
    'size' => [
      'title' => E::ts('File Size'),
      'sql_type' => 'int',
      'input_type' => 'Text',
    ],
    'ext' => [
      'title' => E::ts('File Extension'),
      'sql_type' => 'varchar(10)',
      'input_type' => 'Text',
    ],
  ],
];
