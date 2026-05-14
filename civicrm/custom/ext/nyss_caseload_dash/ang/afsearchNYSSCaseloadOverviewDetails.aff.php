<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Caseload - District Details'),
  //'placement' => [
  //  'dashboard_dashlet',
  //],
  'js' => [
    'ang/afsearchNYSSCaseloadOverviewDetails/*.js',
  ],
  'icon' => 'fa-list-alt',
  'requires' => ['crmNyssCaseloadDash'],
  //'server_route' => 'civicrm/nyss/caseload/overview',
];
