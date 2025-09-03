<?php
use CRM_Mosaico_ExtensionUtil as E;

return [
  'name' => 'MosaicoTemplate',
  'table' => 'civicrm_mosaico_template',
  'class' => 'CRM_Mosaico_DAO_MosaicoTemplate',
  'getInfo' => fn() => [
    'title' => E::ts('Mosaico Template'),
    'title_plural' => E::ts('Mosaico Templates'),
    'description' => E::ts('Standalone Mosaico Template'),
    'log' => TRUE,
    'label_field' => 'title',
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique Template ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'title' => [
      'title' => E::ts('Title'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Title'),
    ],
    'base' => [
      'title' => E::ts('Base Template'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Select',
      'description' => E::ts('Name of the Mosaico base template (e.g. versafix-1)'),
      'pseudoconstant' => [
        'callback' => 'CRM_Mosaico_BAO_MosaicoTemplate::getBaseTemplateOptions',
      ],
    ],
    'html' => [
      'title' => E::ts('HTML'),
      'sql_type' => 'longtext',
      'input_type' => 'TextArea',
      'description' => E::ts('Fully renderd HTML'),
    ],
    'metadata' => [
      'title' => E::ts('metadata'),
      'sql_type' => 'longtext',
      'input_type' => 'TextArea',
      'description' => E::ts('Mosaico metadata (JSON)'),
    ],
    'content' => [
      'title' => E::ts('Content'),
      'sql_type' => 'longtext',
      'input_type' => 'TextArea',
      'description' => E::ts('Mosaico content (JSON)'),
    ],
    'msg_tpl_id' => [
      'title' => E::ts('message template ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => E::ts('FK to civicrm_msg_template.'),
      'pseudoconstant' => [
        'table' => 'civicrm_msg_template',
        'key_column' => 'id',
        'label_column' => 'msg_title',
      ],
      'entity_reference' => [
        'entity' => 'MessageTemplate',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'category_id' => [
      'title' => E::ts('Category ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => E::ts('ID of the category this mailing template is currently belongs. Foreign key to civicrm_option_value.'),
      'input_attrs' => [
        'label' => E::ts('Category'),
      ],
      'pseudoconstant' => [
        'option_group_name' => 'mailing_template_category',
      ],
    ],
    'domain_id' => [
      'title' => E::ts('Domain ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => E::ts('Domain ID this message template belongs to.'),
      'pseudoconstant' => [
        'table' => 'civicrm_domain',
        'key_column' => 'id',
        'label_column' => 'name',
      ],
      'entity_reference' => [
        'entity' => 'Domain',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
  ],
];
