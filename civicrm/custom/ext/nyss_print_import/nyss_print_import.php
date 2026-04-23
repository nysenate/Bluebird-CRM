<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'nyss_print_import.civix.php';
// phpcs:enable

use CRM_NyssPrintImport_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function nyss_print_import_civicrm_config(\CRM_Core_Config $config): void {
  _nyss_print_import_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function nyss_print_import_civicrm_install(): void {
  _nyss_print_import_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function nyss_print_import_civicrm_enable(): void {
  _nyss_print_import_civix_civicrm_enable();
}
