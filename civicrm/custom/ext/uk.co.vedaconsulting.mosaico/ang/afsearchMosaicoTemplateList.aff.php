<?php
use CRM_Mosaico_ExtensionUtil as E;

return [
  'type' => 'search',
  'requires' => ['crmMosaico'],
  'title' => E::ts('Mosaico Templates'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/mosaico-template-list',
  'permission' => [
    'edit message templates',
    'edit user driven message templates',
  ],
  'permission_operator' => 'OR',
];
