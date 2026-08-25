<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Caseload - Case Manager Totals'),
  'placement' => [
    'dashboard_dashlet',
  ],
  'icon' => 'fa-list-alt',
  //'server_route' => 'civicrm/nyss-case-manager-totals',
  'permission' => ['access caseload dashboard'],
];
