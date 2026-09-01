<?php
use CRM_NYSS_Navigation_ExtensionUtil as E;

// Restores the core-standard navigation items, 'Mailings' (top level) and 'CiviMail' (under Administer).
// These were renamed and/or removed from Bluebird a long time ago (See scripts/Old/v122_mail.sh)
// However, the Mosaico managed navigation items depend on them -- using them as parents for their own nav items.
// This was silently tolerated until CiviCRM Core version 6.16. But as of 6.16, the missing parents now result
// in an error, which was noticed during cache flushes.
return [
  [
    'name' => 'Navigation_Mailings',
    'entity' => 'Navigation',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Mailings',
        'label' => E::ts('Mailings'),
        'permission' => [
          'access CiviMail',
          'create mailings',
          'approve mailings',
          'schedule mailings',
          'send SMS',
        ],
        'permission_operator' => 'OR',
        'parent_id' => NULL,
        'is_active' => TRUE,
        'weight' => 50,
        'icon' => 'crm-i fa-envelope-o',
      ],
      'match' => [
        'domain_id',
        'name',
      ],
    ],
  ],
  [
    'name' => 'Navigation_CiviMail',
    'entity' => 'Navigation',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'CiviMail',
        'label' => E::ts('CiviMail'),
        'permission' => [
          'access CiviMail',
          'administer CiviCRM',
        ],
        'permission_operator' => 'AND',
        'parent_id.name' => 'Administer',
        'is_active' => TRUE,
        'weight' => 14,
      ],
      'match' => [
        'domain_id',
        'name',
      ],
    ],
  ],
];
