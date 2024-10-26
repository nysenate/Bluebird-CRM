<?php

use CRM_Mosaico_ExtensionUtil as E;

return [
  [
    'name' => 'Navigation_mosaico_traditional_mailing',
    'entity' => 'Navigation',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('New Mailing (Traditional)'),
        'name' => 'traditional_mailing',
        'url' => 'civicrm/a#/mailing/new/traditional',
        'icon' => '',
        'permission' => [
          'access CiviMail',
          'create mailings',
        ],
        'permission_operator' => 'OR',
        'parent_id.name' => 'Mailings',
        'has_separator' => 1,
        'weight' => 2,
      ],
      'match' => [
        'domain_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'Navigation_mosaico_message_templates',
    'entity' => 'Navigation',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('Mosaico Templates'),
        'name' => 'mosaico_templates',
        'url' => 'civicrm/mosaico-template-list',
        'icon' => '',
        'permission' => [
          'edit message templates',
        ],
        'permission_operator' => 'OR',
        'parent_id.name' => 'Mailings',
        'weight' => 8,
      ],
      'match' => [
        'domain_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'Navigation_mosaico_settings',
    'entity' => 'Navigation',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('Mosaico Settings'),
        'name' => 'mosaico_settings',
        'url' => 'civicrm/admin/setting/mosaico',
        'icon' => '',
        'permission' => [
          'administer CiviCRM',
        ],
        'permission_operator' => 'OR',
        'parent_id.name' => 'CiviMail',
        'weight' => 100,
      ],
      'match' => [
        'domain_id',
        'name',
      ],
    ],
  ],
];
