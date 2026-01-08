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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function logmail_civicrm_install() {
  _logmail_civix_civicrm_install();
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
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

 // */

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
