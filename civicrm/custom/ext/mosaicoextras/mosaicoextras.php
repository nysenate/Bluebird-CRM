<?php

require_once 'mosaicoextras.civix.php';

use CRM_Mosaicoextras_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function mosaicoextras_civicrm_config(&$config) {
  _mosaicoextras_civix_civicrm_config($config);
  Civi::dispatcher()->addListener('civi.api.authorize', ['CRM_Mosaicoextras_APIWrapper', 'authorize'], -100);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function mosaicoextras_civicrm_install() {
  _mosaicoextras_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function mosaicoextras_civicrm_enable() {
  _mosaicoextras_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function mosaicoextras_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function mosaicoextras_civicrm_navigationMenu(&$menu) {
//  _mosaicoextras_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _mosaicoextras_civix_navigationMenu($menu);
//}

/**
 * Implements hook_civicrm_mosaicoConfig().
 *
 * @link https://docs.civicrm.org/mosaico/en/latest/api/#hook_civicrm_mosaicoconfig
 */
function mosaicoextras_civicrm_mosaicoConfig(&$config) {
  $res = CRM_Core_Resources::singleton();

  $config['tinymceConfigFull']['plugins'] = [Civi::settings()->get('mosaico_plugins')];
  $config['tinymceConfigFull']['toolbar1'] = Civi::settings()->get('mosaico_toolbar');

  // Add mailto plugin
  $config['tinymceConfig']['external_plugins']['mailto'] = $res->getUrl('mosaicoextras', 'js/tinymce-plugins/mailto/plugin.min.js', 1);
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_permission/
 */
function mosaicoextras_civicrm_permission(&$permissions) {
  $permissions['delete Mosaico templates'] = [
    'label' => E::ts('MosaicoExtras: delete Mosaico templates'),
    'description' => E::ts('Grants the necessary API permissions to access mosaico templates and delete without Administer CiviCRM permission'),
  ];
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterAPIPermissions/
 */
function mosaicoextras_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  if ($entity == 'mosaico_template' and $action == 'delete') {
    if (CRM_Core_Permission::check('delete Mosaico templates')) {
      $params['check_permissions'] = FALSE;
    }
  }
}
