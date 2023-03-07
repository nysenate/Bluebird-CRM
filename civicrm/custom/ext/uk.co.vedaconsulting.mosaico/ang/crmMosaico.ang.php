<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules

$canRead = Civi::service('civi_api_kernel')->runAuthorize('MosaicoTemplate', 'get', ['version' => 3, 'check_permissions' => 1]);
if (!$canRead) {
  return [];
}

$result = [
  'requires' => ['crmUi', 'crmUtil', 'ngRoute', 'crmMailing', 'crmDialog'],
  'js' =>
  [
    0 => 'ang/crmMosaico.js',
    1 => 'ang/crmMosaico/*.js',
    2 => 'ang/crmMosaico/*/*.js',
  ],
  'css' => ['css/mosaico-bootstrap.css'],
  'bundles' => ['bootstrap3'],
  'partials' => [
    'ang/crmMosaico',
  ],
  'settings' =>
  [
    'canDelete' => Civi::service('civi_api_kernel')->runAuthorize('MosaicoTemplate', 'delete', ['version' => 3, 'check_permissions' => 1]),
    // If there are any navbars that we should try to avoid, include them
    // in these jQuery selectors.
    'topNav' => '#civicrm-menu',
    'drupalNav' => '#toolbar',
    'joomlaNav' => '.com_civicrm > .navbar',
    'leftNav' => '.wp-admin #adminmenu',
    'variantsPct' => CRM_Mosaico_AbDemux::DEFAULT_AB_PERCENTAGE,
  ],
];
return $result;
