<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'nyss_caseload_dash.civix.php';
// phpcs:enable

use CRM_NyssCaseloadDash_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function nyss_caseload_dash_civicrm_config(\CRM_Core_Config $config): void {
  _nyss_caseload_dash_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function nyss_caseload_dash_civicrm_install(): void {
  _nyss_caseload_dash_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function nyss_caseload_dash_civicrm_enable(): void {
  _nyss_caseload_dash_civix_civicrm_enable();
}

function nyss_caseload_dash_civicrm_pageRun(&$page) {
    //print_r($page->url);
    //array_sear
    $pageName = get_class($page);
    if ($pageName == 'CRM_Contact_Page_DashBoard' ||
        array_search('caseload',$page->urlPath)) // TODO: create page and reference it
    {
        Civi::resources()->addStyleFile('nyss_caseload_dash', 'css/app.css');
    }

}
