<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('My Caseload'),
  'placement' => [
    'dashboard_dashlet',
  ],
  'icon' => 'fa-list-alt',
  'requires' => ['crmNyssCaseloadDash'],
  'permission' => [
    'access CiviCRM', 'access all cases and activities',
  ],
];
