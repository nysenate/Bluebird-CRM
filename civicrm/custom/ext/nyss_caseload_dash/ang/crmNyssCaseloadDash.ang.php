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
  // doesn't really require crmMosaico, but it fails because something else somewhere up the chain is looking for it.
  // This is a cheap workaround
  'requires' => ['crmMosaico'],
  'settings' => [],
];
