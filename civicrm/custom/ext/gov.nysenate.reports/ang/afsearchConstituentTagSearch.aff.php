<?php
use CRM_NYSS_Reports_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Constituent Tag Search'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/constituent_tag_report',
  'permission' => [
    'view all contacts',
  ],
];
