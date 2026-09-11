<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Caseload - District Totals'),
  'placement' => [
    'dashboard_dashlet',
  ],
  'icon' => 'fa-list-alt',
  //'server_route' => 'civicrm/nyss/caseload/overview',
  'permission' => ['access caseload dashboard'],
];
