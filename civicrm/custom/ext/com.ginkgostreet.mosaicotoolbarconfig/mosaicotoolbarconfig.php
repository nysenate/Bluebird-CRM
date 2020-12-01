<?php

require_once 'mosaicotoolbarconfig.civix.php';
use CRM_mosaicotoolbarconfig_ExtensionUtil as E;

define('CIVICRM_MOSAICO_PLUGINS', 'link hr paste lists textcolor code civicrmtoken');
define('CIVICRM_MOSAICO_TOOLBAR', 'bold italic forecolor backcolor hr styleselect removeformat | civicrmtoken | link unlink | pastetext code');

/**
 * implements hook_civicrm_buildForm()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm/
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 * @return void
 */
function mosaicotoolbarconfig_civicrm_buildForm($formName, &$form) {

  if ($formName == 'CRM_Mosaico_Form_MosaicoAdmin') {
    CRM_Core_Resources::singleton()->addVars('mosaico', array(
      'plugins' => Civi::settings()->get('mosaico_plugins') ?: CIVICRM_MOSAICO_PLUGINS,
      'toolbar' => Civi::settings()->get('mosaico_toolbar') ?: CIVICRM_MOSAICO_TOOLBAR,
    ));
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => __DIR__ . '/templates/Mosaicotoolbarconfig/settings.tpl',
    ));
  }
}

/**
 * implements hook_civicrm_postProcess()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postProcess/
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 * @return void
 */
function mosaicotoolbarconfig_civicrm_postProcess($formName, &$form) {

  if ($formName == 'CRM_Mosaico_Form_MosaicoAdmin') {
    Civi::settings()->set('mosaico_plugins', $_POST['mosaico_plugins']);
    Civi::settings()->set('mosaico_toolbar', $_POST['mosaico_toolbar']);
  }
}

/**
 * implements hook_civicrm_mosaicoConfig()
 *
 * @see https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico/pull/272/files
 *
 * @param array $config
 * @return void
 */
function mosaicotoolbarconfig_civicrm_mosaicoConfig(&$config) {
  $config['tinymceConfigFull']['plugins'] = array(Civi::settings()->get('mosaico_plugins') ?: CIVICRM_MOSAICO_PLUGINS);
  $config['tinymceConfigFull']['toolbar1'] = Civi::settings()->get('mosaico_toolbar') ?: CIVICRM_MOSAICO_TOOLBAR;
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mosaicotoolbarconfig_civicrm_config(&$config) {
  _mosaicotoolbarconfig_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mosaicotoolbarconfig_civicrm_xmlMenu(&$files) {
  _mosaicotoolbarconfig_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mosaicotoolbarconfig_civicrm_install() {
  _mosaicotoolbarconfig_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function mosaicotoolbarconfig_civicrm_postInstall() {
  _mosaicotoolbarconfig_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mosaicotoolbarconfig_civicrm_uninstall() {
  _mosaicotoolbarconfig_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mosaicotoolbarconfig_civicrm_enable() {
  _mosaicotoolbarconfig_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mosaicotoolbarconfig_civicrm_disable() {
  _mosaicotoolbarconfig_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mosaicotoolbarconfig_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mosaicotoolbarconfig_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mosaicotoolbarconfig_civicrm_managed(&$entities) {
  _mosaicotoolbarconfig_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mosaicotoolbarconfig_civicrm_caseTypes(&$caseTypes) {
  _mosaicotoolbarconfig_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function mosaicotoolbarconfig_civicrm_angularModules(&$angularModules) {
  _mosaicotoolbarconfig_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mosaicotoolbarconfig_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mosaicotoolbarconfig_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function mosaicotoolbarconfig_civicrm_entityTypes(&$entityTypes) {
  _mosaicotoolbarconfig_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function mosaicotoolbarconfig_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function mosaicotoolbarconfig_civicrm_navigationMenu(&$menu) {
  _mosaicotoolbarconfig_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _mosaicotoolbarconfig_civix_navigationMenu($menu);
} // */
