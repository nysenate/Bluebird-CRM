<?php
use CRM_NYSS_Activity_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('My Activities'),
  'placement' => [
    'dashboard_dashlet',
  ],
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/my-activities',
  'permission' => [
    'access CiviCRM',
    'access Contact Dashboard',
  ],
  'permission_operator' => 'OR',
];
