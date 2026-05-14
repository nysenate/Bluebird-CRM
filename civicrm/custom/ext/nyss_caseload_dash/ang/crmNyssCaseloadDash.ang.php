<?php

// Angular module crmNyssCaseloadDash.
// @see https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
return [
  'js' => [
    'ang/crmNyssCaseloadDash.js',
    'ang/crmNyssCaseloadDash/*.js',
    'ang/crmNyssCaseloadDash/*/*.js',
  ],
  'css' => [
    'ang/crmNyssCaseloadDash.css',
  ],
  'partials' => [
    'ang/crmNyssCaseloadDash',
  ],
  'requires' => ['crmUi', 'crmUtil', 'ngRoute'],
  'settings' => [],
];
