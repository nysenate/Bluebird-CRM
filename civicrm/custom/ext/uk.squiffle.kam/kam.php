<?php

require_once 'kam.civix.php';

/**
 * Implements hook_civicrm_coreResourceList().
 * Adds js/css for the smartmenus menu
 *
 *  * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_coreResourceList()
 */
function kam_civicrm_coreResourceList(&$list, $region) {
  $config = CRM_Core_Config::singleton();

  // Don't load default navigation css and menu
  $cssWeDontWant = array_search('css/civicrmNavigation.css', $list);
  if ($cssWeDontWant !== FALSE) {
    unset($list[$cssWeDontWant]);
  }

  //check if logged in user has access CiviCRM permission and build menu
  $buildNavigation = !CRM_Core_Config::isUpgradeMode() && CRM_Core_Permission::check('access CiviCRM');
  if (!$buildNavigation || $config->userFrameworkFrontend) {
    return;
  }

  if ($region == 'html-header') {
    $contactID = CRM_Core_Session::getLoggedInContactID();
    $position = Civi::settings()->get('menubar_position');
    if ($contactID && $position !== 'none' && !defined('CIVICRM_DISABLE_DEFAULT_MENU')) {
      define('CIVICRM_DISABLE_DEFAULT_MENU', TRUE);
      $cms = strtolower(CRM_Core_Config::singleton()->userFramework);
      $cms = $cms === 'drupal' ? 'drupal7' : $cms;
      $path = 'packages/smartmenus-1.1.0/';
      Civi::resources()
        ->addStyleFile('uk.squiffle.kam', "css/menubar-$cms.css", -99, 'html-header')
        ->addStyleUrl(\Civi::service('asset_builder')->getUrl('sm-civicrm.css'), -98, 'html-header')
        ->addScriptFile('uk.squiffle.kam', $path . 'jquery.smartmenus.js', -99, 'html-header')
        ->addScriptFile('uk.squiffle.kam', $path . 'addons/keyboard/jquery.smartmenus.keyboard.js', -98, 'html-header')
        ->addScriptFile('uk.squiffle.kam', 'js/crm.menubar.js', -97, 'html-header');
      $list[] = [
        'config' => ['locale' => CRM_Core_I18n::getLocale()],
        'menubar' => [
          'position' => $position,
          'qfKey' => CRM_Core_Key::get('CRM_Contact_Controller_Search', TRUE),
        ],
      ];
    }
  }
}

/**
 * Implements hook_civicrm_alterContent().
 */
function kam_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  $region = CRM_Core_Region::instance('html-header');
  $resources = Civi::resources();
  // Remove backdrop.js file
  $backdropJs = $region->get($resources->getUrl('civicrm', 'js/crm.backdrop.js', TRUE));
  if ($backdropJs) {
    $override = ['scriptUrl' => NULL];
    $region->update($backdropJs['name'], $override);
  }
  // Override drupal7.js file
  $drupal7 = $region->get($resources->getUrl('civicrm', 'js/crm.drupal7.js', TRUE));
  if ($drupal7) {
    $override = ['scriptUrl' => $resources->getUrl('uk.squiffle.kam', 'js/crm.drupal7.js', TRUE)];
    $region->update($drupal7['name'], $override);
  }
  // Override drupal8.js file
  $drupal8 = $region->get($resources->getUrl('civicrm', 'js/crm.drupal8.js', TRUE));
  if ($drupal8) {
    $override = ['scriptUrl' => $resources->getUrl('uk.squiffle.kam', 'js/crm.drupal8.js', TRUE)];
    $region->update($drupal8['name'], $override);
  }
  // Override wordpress.js file
  $wordpress = $region->get($resources->getUrl('civicrm', 'js/crm.wordpress.js', TRUE));
  if ($wordpress) {
    $override = ['scriptUrl' => $resources->getUrl('uk.squiffle.kam', 'js/crm.wordpress.js', TRUE)];
    $region->update($wordpress['name'], $override);
  }
  // Override core joomla.css file
  $joomlaCss = $region->get($resources->getUrl('civicrm', 'css/joomla.css', TRUE));
  if ($joomlaCss) {
    $override = ['styleUrl' => $resources->getUrl('uk.squiffle.kam', 'css/core-joomla.css', TRUE)];
    $region->update($joomlaCss['name'], $override);
  }
}

/**
 * Implements hook_civicrm_buildAsset().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildAsset
 */
function kam_civicrm_buildAsset($asset, $params, &$mimetype, &$content) {
  if ($asset !== 'sm-civicrm.css') {
    return;
  }
  $path = 'packages/smartmenus-1.1.0/';
  $raw = '';
  foreach (array($path . 'css/sm-core-css.css', 'css/crm-menubar.css') as $file) {
    $raw .= file_get_contents(Civi::resources()->getPath('uk.squiffle.kam', $file));
  }
  $content = str_replace('BASE_URL', rtrim(Civi::resources()->getUrl('civicrm', '/'), '/'), $raw);
  $mimetype = 'text/css';
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function kam_civicrm_config(&$config) {
  _kam_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function kam_civicrm_xmlMenu(&$files) {
  _kam_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function kam_civicrm_install() {
  _kam_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function kam_civicrm_postInstall() {
  _kam_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function kam_civicrm_uninstall() {
  _kam_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function kam_civicrm_enable() {
  _kam_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function kam_civicrm_disable() {
  _kam_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function kam_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _kam_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function kam_civicrm_managed(&$entities) {
  _kam_civix_civicrm_managed($entities);
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
function kam_civicrm_caseTypes(&$caseTypes) {
  _kam_civix_civicrm_caseTypes($caseTypes);
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
function kam_civicrm_angularModules(&$angularModules) {
  _kam_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function kam_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _kam_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_preProcess().
 * @var CRM_Admin_Form_Preferences_Display $form
 */
function kam_civicrm_preProcess($formName, &$form) {
  if ($formName === 'CRM_Admin_Form_Preferences_Display') {
    $setting = civicrm_api3('Setting', 'getfields', ['name' => "menubar_position"])['values']['menubar_position'];
    $element = $form->add('select', 'menubar_position', $setting['title'], $setting['options']);
    $element->setValue(Civi::settings()->get('menubar_position'));
  }
}
/**
 * Implements hook_civicrm_postProcess().
 * @var CRM_Admin_Form_Preferences_Display $form
 */
function kam_civicrm_postProcess($formName, &$form) {
  if ($formName === 'CRM_Admin_Form_Preferences_Display') {
    $params = $form->controller->exportValues('Display');
    Civi::settings()->set('menubar_position', $params['menubar_position']);
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function kam_civicrm_navigationMenu(&$menu) {
  _kam_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'uk.squiffle.kam')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _kam_civix_navigationMenu($menu);
} // */
