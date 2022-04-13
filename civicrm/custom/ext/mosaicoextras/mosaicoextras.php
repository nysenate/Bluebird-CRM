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
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function mosaicoextras_civicrm_xmlMenu(&$files) {
  _mosaicoextras_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function mosaicoextras_civicrm_postInstall() {
  _mosaicoextras_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function mosaicoextras_civicrm_uninstall() {
  _mosaicoextras_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function mosaicoextras_civicrm_enable() {
  _mosaicoextras_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function mosaicoextras_civicrm_disable() {
  _mosaicoextras_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function mosaicoextras_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mosaicoextras_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function mosaicoextras_civicrm_managed(&$entities) {
  _mosaicoextras_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function mosaicoextras_civicrm_caseTypes(&$caseTypes) {
  _mosaicoextras_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function mosaicoextras_civicrm_angularModules(&$angularModules) {
  _mosaicoextras_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function mosaicoextras_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mosaicoextras_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function mosaicoextras_civicrm_entityTypes(&$entityTypes) {
  _mosaicoextras_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function mosaicoextras_civicrm_themes(&$themes) {
  _mosaicoextras_civix_civicrm_themes($themes);
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
    E::ts('MosaicoExtras: delete Mosaico templates'),
    E::ts('Grants the necessary API permissions to delete mosaico templates without Administer CiviCRM'),
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
