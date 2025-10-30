<?php
use CRM_NYSS_Case_ExtensionUtil as E;

// NYSS #17214
return [
  'type' => 'search',
  'title' => E::ts('Search Cases'),
  'icon' => 'fa-magnifying-glass',
  'server_route' => 'civicrm/case/search/bb',
  'permission' => [
    'access CiviCRM',
    'access my cases and activities',
    'access all cases and activities',
  ],
];
