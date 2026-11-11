<?php
use CRM_NYSS_Inbox_ExtensionUtil as E;

return [
  'name' => 'InboxMessages',
  'table' => 'nyss_inbox_messages',
  'class' => 'CRM_NYSS_Inbox_DAO_InboxMessages',
  'getInfo' => fn() => [
    'title' => E::ts('Inbox Messages'),
    'title_plural' => E::ts('Inbox Messageses'),
    'description' => E::ts('Metadata re: the inbound email'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'idx_sender_email' => [
      'fields' => [
        'sender_email' => TRUE,
      ],
    ],
    'idx_status' => [
      'fields' => [
        'status' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique InboxMessages ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'message_id' => [
      'title' => E::ts('Message ID'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'description' => E::ts('Email message ID'),
    ],
    'imap_id' => [
      'title' => E::ts('Imap ID'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'description' => E::ts('IMAP message ID'),
    ],
    'sender_name' => [
      'title' => E::ts('Sender Name'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Email from name'),
    ],
    'sender_email' => [
      'title' => E::ts('Sender Email'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Email from email'),
    ],
    'subject' => [
      'title' => E::ts('Subject'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'body' => [
      'title' => E::ts('Email Body'),
      'sql_type' => 'longtext',
      'input_type' => 'TextArea',
    ],
    'forwarder' => [
      'title' => E::ts('Forwarder Email'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Individual who forwarded the email'),
    ],
    'status' => [
      'title' => E::ts('Processing Status'),
      'sql_type' => 'int',
      'input_type' => 'Select',
      'description' => E::ts('See constants in BAO'),
      'pseudoconstant' => [
        'callback' => 'CRM_NYSS_Inbox_BAO_InboxMessages::statusOptions',
      ],
    ],
    'matcher' => [
      'title' => E::ts('Contact ID of individual who matched the email'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'description' => E::ts('See constants in BAO'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'format' => [
      'title' => E::ts('Email Format'),
      'sql_type' => 'varchar(10)',
      'input_type' => 'Text',
      'description' => E::ts('plain/html'),
    ],
    'debug' => [
      'title' => E::ts('Debugging Details'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Additional details useful for debugging.'),
    ],
    'updated_date' => [
      'title' => E::ts('Updated Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
    ],
    'email_date' => [
      'title' => E::ts('Email Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
    ],
    'inbox_source' => [
      'title' => E::ts('Inbox Source'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Text',
      'description' => E::ts('Inbox account this email was processed from.'),
    ],
  ],
];
