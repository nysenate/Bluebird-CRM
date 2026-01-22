<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'nyss_print_export.civix.php';
// phpcs:enable

use CRM_NyssPrintExport_ExtensionUtil as E;

if (!defined('NYSS_PRINT_EXPORT_REPORT')) {
    define('NYSS_PRINT_EXPORT_REPORT', 1000);
}

if (!defined('NYSS_DISTRICT_EXPORT_REPORT')) {
    define('NYSS_DISTRICT_EXPORT_REPORT', 1001);
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function nyss_print_export_civicrm_config(\CRM_Core_Config $config): void {
  _nyss_print_export_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function nyss_print_export_civicrm_install(): void {
  _nyss_print_export_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function nyss_print_export_civicrm_enable(): void {
  _nyss_print_export_civix_civicrm_enable();
}

#[CRM_NYSS_Attribute_IssueRef('17701')]
function nyss_print_export_civicrm_searchTasks(string $objectName, array &$tasks){
    if ($objectName === 'contact'){
        if (CRM_Core_Permission::check('export print production files')) {
            if (! array_key_exists(NYSS_PRINT_EXPORT_REPORT, $tasks)) {
                $tasks[NYSS_PRINT_EXPORT_REPORT] = [
                    'title' => E::ts('Export for Print Production'),
                    'class' => ['CRM_NYSS_PrintExport_Form_Task_PrintExportReport',
                        'CRM_NYSS_PrintExport_Form_Task_PrintExportResult'
                    ],

                ];
            }
            if (! array_key_exists(NYSS_DISTRICT_EXPORT_REPORT, $tasks)) {
                $tasks[NYSS_DISTRICT_EXPORT_REPORT] = [
                    'title' => ts('Export District for Merge/Purge'),
                    'class' => 'CRM_NYSS_PrintExport_Form_Task_DistrictExport',
                    'result' => true
                ];
            }
        }
    }
}
