<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules

return [
  'js' =>
  [
    0 => 'ang/crmbWizard.js',
    1 => 'ang/crmbWizard/*.js',
    2 => 'ang/crmbWizard/*/*.js',
  ],
  // Hmm, shouldn't high-level components have separate CSS files?
  'css' => ['css/mosaico-bootstrap.css'],
  'bundles' => ['bootstrap3'],
  'partials' =>
  [
    0 => 'ang/crmbWizard',
  ],
];
