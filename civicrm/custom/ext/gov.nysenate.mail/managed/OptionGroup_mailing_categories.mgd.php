<?php
use CRM_NYSS_Mail_ExtensionUtil as E;

return [
  [
    'name' => 'OptionGroup_mailing_categories',
    'entity' => 'OptionGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'mailing_categories',
        'title' => E::ts('Mailing Categories'),
        'description' => E::ts('Mailing Categories'),
        'option_value_fields' => ['name', 'label', 'description'],
      ],
      'match' => ['name'],
    ],
  ],
    [
        'name' => 'OptionGroup_mailing_categories_OptionValue_General_Announcements',
        'entity' => 'OptionValue',
        'cleanup' => 'unused',
        'update' => 'always',
        'params' => [
            'version' => 4,
            'values' => [
                'option_group_id:name' => 'mailing_categories',
                'label' => E::ts('General Announcements'),
                'value' => '8',
                'name' => 'General Announcements',
                'is_default' => TRUE,
                'description' => E::ts(''),
                'is_active' => TRUE,
            ],
            'match' => [
                'option_group_id',
                'name',
                'value',
            ],
        ],
    ],
  [
    'name' => 'OptionGroup_mailing_categories_OptionValue_Budget_Updates',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id:name' => 'mailing_categories',
        'label' => E::ts('Budget Updates'),
        'value' => '1',
        'name' => 'Budget Updates',
        'is_default' => FALSE,
        'description' => E::ts(''),
        'is_active' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_mailing_categories_OptionValue_Community_and_Special_Event_Notices',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id:name' => 'mailing_categories',
        'label' => E::ts('Community and Special Event Notices'),
        'value' => '2',
        'name' => 'Community and Special Event Notices',
        'is_default' => FALSE,
        'description' => E::ts(''),
        'is_active' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_mailing_categories_OptionValue_Emergency_and_Public_Safety_Alerts',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id:name' => 'mailing_categories',
        'label' => E::ts('Emergency and Public Safety Alerts'),
        'value' => '3',
        'name' => 'Emergency and Public Safety Alerts',
        'is_default' => FALSE,
        'description' => E::ts(''),
        'is_active' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_mailing_categories_OptionValue_Issue_Bill_Updates',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id:name' => 'mailing_categories',
        'label' => E::ts('Issue/Bill Updates'),
        'value' => '4',
        'name' => 'Issue/Bill Updates',
        'is_default' => FALSE,
        'description' => E::ts(''),
        'is_active' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_mailing_categories_OptionValue_Newsletters',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id:name' => 'mailing_categories',
        'label' => E::ts('Newsletters'),
        'value' => '5',
        'name' => 'Newsletters',
        'is_default' => FALSE,
        'description' => E::ts(''),
        'is_active' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_mailing_categories_OptionValue_Press_Releases',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id:name' => 'mailing_categories',
        'label' => E::ts('Press Releases'),
        'value' => '6',
        'name' => 'Press Releases',
        'is_default' => FALSE,
        'description' => E::ts(''),
        'is_active' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_mailing_categories_OptionValue_All_Other_Messages',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id:name' => 'mailing_categories',
        'label' => E::ts('All Other Messages'),
        'value' => '7',
        'name' => 'All Other Messages',
        'is_default' => FALSE,
        'description' => E::ts(''),
        'is_active' => FALSE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],

];
