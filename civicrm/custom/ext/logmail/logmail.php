<?php

require_once 'logmail.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function logmail_civicrm_config(&$config) {
  _logmail_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function logmail_civicrm_xmlMenu(&$files) {
  _logmail_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function logmail_civicrm_install() {
  _logmail_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function logmail_civicrm_uninstall() {
  _logmail_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function logmail_civicrm_enable() {
  _logmail_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function logmail_civicrm_disable() {
  _logmail_civix_civicrm_disable();
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
function logmail_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _logmail_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function logmail_civicrm_managed(&$entities) {
  _logmail_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function logmail_civicrm_caseTypes(&$caseTypes) {
  _logmail_civix_civicrm_caseTypes($caseTypes);
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
function logmail_civicrm_angularModules(&$angularModules) {
_logmail_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function logmail_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _logmail_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function logmail_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function logmail_civicrm_navigationMenu(&$menu) {
  _logmail_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'logmail')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _logmail_civix_navigationMenu($menu);
} // */

function logmail_civicrm_navigationMenu(&$params) {
  $navId = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");
  if (is_integer($navId)) {
    $navId++;
  }

  $adminID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation',
    'Administer', 'id', 'name');
  $params[$adminID]['child'][$navId] = [
    'attributes' => [
      'label' => ts('Logmail Settings'),
      'name' => 'Logmail Settings',
      'url' => 'civicrm/admin/setting/logmail',
      'permission' => 'administer CiviCRM',
      'operator' => 'AND',
      'separator' => 1,
      'parentID' => $adminID,
      'navID' => $navId,
      'active' => 1
    ],
  ];
}

function logmail_civicrm_check(&$messages) {
  if (Civi::settings()->get('logmail_enable')) {
    $messages[] = new CRM_Utils_Check_Message(
      'logmail_active',
      ts('All emails are currently getting logged to file. No emails will be delivered.'),
      ts('Email Logging Enabled'),
      \Psr\Log\LogLevel::CRITICAL,
      'fa-envelope'
    );
  }
}

function logmail_civicrm_alterMailer(&$mailer, $driver, $params) {
  if (Civi::settings()->get('logmail_enable')) {
    $mailer = new LogMailDriver();
  }
}

class LogMailDriver {
  /**
   * Send an email
   */
  function send($recipients, $headers, $body) {
    // Write mail out to a log file instead of delivering it if setting enabled
    if (Civi::settings()->get('logmail_enable')) {
      if (Civi::settings()->get('logmail_file')) {
        $data = [
          'recipients' => $recipients,
          'headers' => $headers,
          'body' => $body,
        ];

        $config = CRM_Core_Config::singleton();
        $logFile = "{$config->configAndLogDir}Logmail.log";
        $out = print_r($data, TRUE);
        $out = date('Y-m-d H:i:s') . " = {$out}";
        file_put_contents($logFile, $out, FILE_APPEND);
      }
      else {
        //do nothing; discard email
      }
    }
  }
}
