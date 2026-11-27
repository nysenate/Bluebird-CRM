<?php
use CRM_Recentmenu_ExtensionUtil as E;

return [
  [
    'name' => 'Navigation_recent_items',
    'entity' => 'Navigation',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('Recent'),
        'name' => 'recent_items',
        'url' => NULL,
        'icon' => 'crm-i fa-history',
        'permission' => ['access CiviCRM'],
        'parent_id' => NULL,
        'permission_operator' => 'AND',
        'is_active' => TRUE,
      ],
      'match' => ['name', 'domain_id'],
    ],
  ],
];
