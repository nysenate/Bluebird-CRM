<?php
// Main Angular module for Mosaico

return [
  'requires' => ['crmUi', 'crmUtil', 'ngRoute', 'crmMailing', 'crmDialog'],
  'js' => [
    'ang/crmMosaico.js',
    'ang/crmMosaico/*.js',
    'ang/crmMosaico/*/*.js',
  ],
  'css' => ['css/mosaico-bootstrap.css'],
  'bundles' => ['bootstrap3'],
  'partials' => [
    'ang/crmMosaico',
  ],
  'settingsFactory' => ['CRM_Mosaico_Utils', 'getAngularSettings'],
];
