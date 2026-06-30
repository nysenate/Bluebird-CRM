<?php
use CRM_NYSS_CaseloadDash_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Caseload - Case Manager Details'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/nyss/caseload/case-manager',
  'permission' => ['access caseload dashboard'],
];
