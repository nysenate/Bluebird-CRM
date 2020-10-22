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
  'css' => CRM_Mosaico_Utils::isBootstrap() ? ['css/mosaico-bootstrap.css'] : [],
  'partials' =>
  [
    0 => 'ang/crmbWizard',
  ],
  'settings' =>
  [
  ],
];
