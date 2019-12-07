<?php

require_once 'api4.civix.php';
require_once 'api/Exception.php';

if (!is_callable('civicrm_api4')) {

    /**
     * Procedural wrapper for the OO api version 4.
     *
     * @param string $entity
     * @param string $action
     * @param array $params
     * @param string|int $index
     *   If $index is a string, the results array will be indexed by that key.
     *   If $index is an integer, only the result at that index will be returned.
     *
     * @return \Civi\Api4\Generic\Result
     * @throws \API_Exception
     * @throws \Civi\API\Exception\NotImplementedException
     */
  function civicrm_api4($entity, $action, $params = [], $index = NULL) {
    $apiCall = \Civi\Api4\Utils\ActionUtil::getAction($entity, $action);
    foreach ($params as $name => $param) {
      $setter = 'set' . ucfirst($name);
      $apiCall->$setter($param);
    }
    $result = $apiCall->execute();

    // Index results by key
    if ($index && is_string($index) && !CRM_Utils_Rule::integer($index)) {
      $result->indexBy($index);
    }
    // Return result at index
    if (CRM_Utils_Rule::integer($index)) {
      $item = $result->itemAt($index);
      if (is_null($item)) {
        throw new \API_Exception("Index $index not found in api results");
      }
      // Attempt to return a Result object if item is array, otherwise just return the item
      if (!is_array($item)) {
        return $item;
      }
      $result->exchangeArray($item);

    }
    return $result;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   */
  function api4_civicrm_container($container) {
    require_once __DIR__ . '/CRM/Api4/Services.php';
    CRM_Api4_Services::hook_container($container);
  }

}

/**
 * Implements hook_civicrm_coreResourceList().
 */
function api4_civicrm_coreResourceList(&$list, $region) {
  if ($region == 'html-header') {
    Civi::resources()->addScriptFile('org.civicrm.api4', 'js/api4.js', -9000, $region);
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function api4_civicrm_config(&$config) {
  _api4_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function api4_civicrm_xmlMenu(&$files) {
  _api4_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function api4_civicrm_install() {
  _api4_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function api4_civicrm_uninstall() {
  _api4_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function api4_civicrm_enable() {
  _api4_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function api4_civicrm_disable() {
  _api4_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function api4_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _api4_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function api4_civicrm_managed(&$entities) {
  _api4_civix_civicrm_managed($entities);
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
function api4_civicrm_angularModules(&$angularModules) {
  _api4_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function api4_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _api4_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
