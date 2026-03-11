<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'nyss_greeting.civix.php';
// phpcs:enable

use CRM_NyssGreeting_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function nyss_greeting_civicrm_config(\CRM_Core_Config $config): void {
  _nyss_greeting_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function nyss_greeting_civicrm_install(): void {
  _nyss_greeting_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function nyss_greeting_civicrm_enable(): void {
  _nyss_greeting_civix_civicrm_enable();
}

function nyss_greeting_civicrm_pageRun(&$page) {
    if (get_class($page) === 'CRM_Queue_Page_Runner') {
        $qrid = CRM_Utils_Request::retrieve('qrid', 'String');
        if ($qrid === 'generate-greetings'){
            $page->assign('myVariable', 'myValue');
        }
    }
}
