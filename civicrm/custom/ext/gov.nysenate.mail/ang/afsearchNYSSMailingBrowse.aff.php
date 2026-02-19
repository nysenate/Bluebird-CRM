<?php

use CRM_NYSS_Mail_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Mailings'),
  'server_route' => 'civicrm/nyss/mailing',
  'permission' => ['access CiviMail', 'create mailings', 'schedule mailings'],
  'permission_operator' => 'OR',
];
