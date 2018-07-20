<?php

require_once 'slicknav.civix.php';

/**
 * Adds js/css for the slicknav menu
 *
 * @param $list
 * @param $region
 */
function slicknav_civicrm_coreResourceList($list, $region) {
  $config = CRM_Core_Config::singleton();
  //check if logged in user has access CiviCRM permission and build menu
  $buildNavigation = !CRM_Core_Config::isUpgradeMode() && CRM_Core_Permission::check('access CiviCRM');
  if (defined('CIVICRM_DISABLE_DEFAULT_MENU') || $config->userFrameworkFrontend) {
    $buildNavigation = FALSE;
  }
  if ($buildNavigation && $region == 'html-header') {
    $contactID = CRM_Core_Session::getLoggedInContactID();
    if ($contactID) {
      CRM_Core_Resources::singleton()->addScriptFile('com.aghstrategies.slicknav', 'slicknav/dist/jquery.slicknav.min.js', 0, 'html-header');
      CRM_Core_Resources::singleton()->addStyleFile('com.aghstrategies.slicknav', 'slicknav/dist/slicknav.min.css', 0, 'html-header');
      CRM_Core_Resources::singleton()->addStyleFile('com.aghstrategies.slicknav', 'css/civislicknav.css', 1, 'html-header');

      // These params force the browser to refresh the js file when switching user, domain, or language
      if (is_callable(array('CRM_Core_I18n', 'getLocale'))) {
        $tsLocale = CRM_Core_I18n::getLocale();
      }
      // 4.6 compatibility
      else {
        global $tsLocale;
      }
      $domain = CRM_Core_Config::domainID();
      $key = CRM_Core_BAO_Navigation::getCacheKey($contactID);
      $src = CRM_Utils_System::url("civicrm/ajax/responsiveadminmenu/$contactID/$tsLocale/$domain/$key", 1, 'html-header');
      CRM_Core_Resources::singleton()->addScriptUrl($src);
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function slicknav_civicrm_config(&$config) {
  _slicknav_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function slicknav_civicrm_xmlMenu(&$files) {
  _slicknav_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function slicknav_civicrm_install() {
  _slicknav_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function slicknav_civicrm_uninstall() {
  _slicknav_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function slicknav_civicrm_enable() {
  _slicknav_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function slicknav_civicrm_disable() {
  _slicknav_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function slicknav_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _slicknav_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function slicknav_civicrm_managed(&$entities) {
  _slicknav_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function slicknav_civicrm_caseTypes(&$caseTypes) {
  _slicknav_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function slicknav_civicrm_angularModules(&$angularModules) {
_slicknav_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function slicknav_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _slicknav_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function slicknav_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function slicknav_civicrm_navigationMenu(&$menu) {
  _slicknav_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'com.aghstrategies.slicknav')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _slicknav_civix_navigationMenu($menu);
} // */
