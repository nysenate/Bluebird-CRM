<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('My Caseload'),
  'placement' => [
    'dashboard_dashlet',
  ],
  'icon' => 'fa-list-alt',
  'permission' => [
    'access CiviCRM','access my cases and activities',
  ],
];
