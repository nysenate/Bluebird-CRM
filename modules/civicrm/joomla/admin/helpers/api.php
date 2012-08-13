<?php

/**
 * @version
 * @package
 * @copyright   @copyright CiviCRM LLC (c) 2004-2012
 * @license		GNU/GPL v2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');
class CivicrmHelperApi {
  function civiInit() {
    define('CIVICRM_SETTINGS_PATH', JPATH_BASE . DS . 'administrator' . DS . 'components' . DS . 'com_civicrm' . DS . 'civicrm.settings.php');
    require_once CIVICRM_SETTINGS_PATH;

    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
  }

  function civiimport($path) {
    self::civiInit();

    global $civicrm_root;
    return JLoader::import($path, $civicrm_root, '');
  }
}

