<?php
// Autoloader data for Api4 explorer.
$vars = [
  'operators' => \CRM_Core_DAO::acceptedSQLOperators(),
];
\Civi::resources()->addVars('api4', $vars);
return [
  'js' => [
    'ang/api4Explorer.js',
    'ang/api4Explorer/*.js',
    'ang/api4Explorer/*/*.js',
    'lib/*.js',
  ],
  'css' => [
    'css/explorer.css',
  ],
  'partials' => [
    'ang/api4Explorer',
  ],
  'basePages' => [],
  'requires' => ['crmUi', 'crmUtil', 'ngRoute', 'crmRouteBinder', 'ui.sortable', 'api4'],
];
