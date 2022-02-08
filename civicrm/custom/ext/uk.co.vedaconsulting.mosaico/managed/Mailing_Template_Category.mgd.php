<?php
return [
  [
    'name' => 'OptionGroup_mailing_template_category',
    'entity' => 'OptionGroup',
    'cleanup' => 'unused',
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
    ],
  ],
  [
    'name' => 'OptionGroup_mailing_template_category_newsletter',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'mailing_template_category',
        'label' => 'Newsletter',
        'value' => '1',
        'name' => 'newsletter',
        'grouping' => NULL,
        'filter' => 0,
        'is_default' => TRUE,
        'weight' => 1,
        'description' => NULL,
        'is_optgroup' => FALSE,
        'is_reserved' => FALSE,
        'is_active' => TRUE,
        'icon' => NULL,
        'color' => NULL,
        'component_id' => NULL,
        'visibility_id' => NULL,
        'domain_id' => NULL,
      ],
    ],
  ],
];
