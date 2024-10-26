<?php
return [
  [
    'name' => 'OptionGroup_mailing_template_category',
    'entity' => 'OptionGroup',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'mailing_template_category',
        'title' => 'Mailing Template Category',
        'description' => NULL,
        'data_type' => NULL,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'is_locked' => FALSE,
      ],
      'match' => ['name'],
    ],
  ],
];
