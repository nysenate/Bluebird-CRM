<?php

require_once 'navigation.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function navigation_civicrm_config(&$config) {
  _navigation_civix_civicrm_config($config);

  CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.navigation', 'js/navigation.js');
  CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.navigation', 'css/navigation.css');
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function navigation_civicrm_xmlMenu(&$files) {
  _navigation_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function navigation_civicrm_install() {
  _navigation_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function navigation_civicrm_uninstall() {
  _navigation_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function navigation_civicrm_enable() {
  _navigation_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function navigation_civicrm_disable() {
  _navigation_civix_civicrm_disable();
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
function navigation_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _navigation_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function navigation_civicrm_managed(&$entities) {
  _navigation_civix_civicrm_managed($entities);
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
function navigation_civicrm_caseTypes(&$caseTypes) {
  _navigation_civix_civicrm_caseTypes($caseTypes);
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
function navigation_civicrm_angularModules(&$angularModules) {
_navigation_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function navigation_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _navigation_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function navigation_civicrm_navigationMenu(&$params) {
  //CRM_Core_Error::debug_var('navigationMenu params',$params);

  //5260
  //find report parents
  $reportNav = array();
  foreach ($params as $navID => $navDetails) {
    //get reports menu so we can retain user-added items
    switch ($navDetails['attributes']['name']) {
      case 'Reports':
        $reportNav = $navDetails;
        break;

      default:
    }

    unset($params[$navID]);
  }

  $params[0] = array(
    'attributes' => array(
      'label' => '',
      'name' => 'Home',
      'url' => 'civicrm/dashboard?reset=1',
      'icon' => 'fa-home crm-i',
      'permission' => 'access CiviCRM',
      'operator' => 'AND',
      'separator' => 0,
      'parentID' => null,
      'navID' => 0,
      'active' => 1
    ),
    'child' => array(),
  );

  $params[1000] = _buildCreateMenu(1000);

  //build Custom Search menu
  $params[2000] = _buildSearchMenu(2000);

  //move Report menu
  $params[2500] = _buildReportsMenu(2500, $reportNav);

  //build Manage menu
  $params[3000] = _buildManageMenu(3000);

  //build Mass Email menu
  $params[4000] = _buildEmailMenu(4000);

  //build Inbox menu
  $params[5000] = _buildInboxMenu(5000);

  //move Administer menu
  $params[5500] = _buildAdminMenu(5500);

  //create Help menu 11965
  $params[6000] = _buildHelpMenu(6000);

  $params[7000] = [
    'attributes' => [
      'label' => 'Log Out',
      'name' => 'log_out',
      'url' => 'civicrm/logout?reset=1',
      'permission' => 'access CiviCRM',
      'operator' => 'AND',
      'separator' => 0,
      'parentID' => null,
      'navID' => 7000,
      'active' => 1,
    ],
  ];

  //CRM_Core_Error::debug_var('navigationMenu params (after)',$params);
}

function _buildReportsMenu($navID, $reportNav) {
  //Civi::log()->debug('', array('reportNav' => $reportNav));

  $nav = array(
    'attributes' => array(
      'label' => 'Reports',
      'name' => 'reports',
      'url' => null,
      'permission' => null,
      'operator' => 'AND',
      'separator' => 0,
      'parentID' => null,
      'navID' => $navID,
      'active' => 1
    ),
    'child' => array(
      //top level items
      $navID + 1 => array(
        'attributes' => array(
          'label' => 'Reports Listing',
          'name' => 'reports_listing',
          'url' => 'civicrm/report/list?reset=1',
          'permission' => 'access CiviReport',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $navID,
          'navID' => $navID + 1,
          'active' => 1,
        ),
      ),
      $navID + 2 => array(
        'attributes' => array(
          'label' => 'My Reports',
          'name' => 'my_reports',
          'url' => 'civicrm/report/list?myreports=1&reset=1',
          'permission' => 'access CiviReport',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $navID,
          'navID' => $navID + 2,
          'active' => 1,
        ),
      ),
      $navID + 3 => array(
        'attributes' => array(
          'label' => 'Create Reports from Templates',
          'name' => 'create_reports_from_templates',
          'url' => 'civicrm/admin/report/template/list?reset=1',
          'permission' => 'administer Reports',
          'operator' => 'AND',
          'separator' => 1,
          'parentID' => $navID,
          'navID' => $navID + 3,
          'active' => 1,
        ),
      ),

      //standard reports
      $navID + 11 => array(
        'attributes' => array(
          'label' => 'District Stats',
          'name' => 'district_stats',
          'url' => 'civicrm/districtstats',
          'permission' => 'access CiviReport',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $navID,
          'navID' => $navID + 11,
          'active' => 1,
        ),
      ),
      //7260 add websignup reports
      $navID + 12 => array(
        'attributes' => array(
          'label' => 'Web Signup Reports',
          'name' => 'web_signup_reports',
          'url' => 'signupreports',
          'permission' => 'access CiviReport',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID' => $navID,
          'navID' => $navID + 12,
          'active' => 1,
        ),
      ),
      //5260 add changelog proofing report
      $navID + 13 => array(
        'attributes' => array(
          'label' => 'Changelog Proofing Report',
          'name' => 'changelog_proofing_report',
          'url' => 'civicrm/nyss/proofingreport?reset=1',
          'permission' => 'access CiviReport',
          'operator' => 'AND',
          'separator' => 1,
          'parentID' => $navID,
          'navID' => $navID + 13,
          'active' => 1,
        ),
      ),
    ),
  );

  //extract user-added nav items
  unset($reportNav['attributes']);
  $navRemove = array('District Stats', 'Reports Listing', 'Create Reports from Templates');
  foreach ($reportNav['child'] as $k => $item) {
    if (in_array($item['attributes']['name'], $navRemove)) {
      unset($reportNav['child'][$k]);
    }
  }

  $nav['child'] += $reportNav['child'];

  return $nav;
}

/*
 * given our "starting" ID, construct the manage menu items
 * return the complete array to be added to the main navigation array
 */
function _buildManageMenu($manageID) {
  $manage = array(
    'attributes' => array(
      'label'      => 'Manage',
      'name'       => 'Manage',
      'url'        => null,
      'permission' => null,
      'operator'   => 'AND',
      'separator'  => 0,
      'parentID'   => null,
      'navID'      => $manageID,
      'active'     => 1
    ),
    'child' => array(
      $manageID+1 => array(
        'attributes' => array(
          'label' => 'BOE/3rd Party Import',
          'name' => 'BOE Import',
          'url' => 'importData',
          'permission' => 'access CiviCRM,import print production',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+1,
          'active' => 1,
        ),
      ),
      $manageID+2 => array(
        'attributes' => Array(
          'label' => 'Site Maintenance',
          'name' => 'Site Maintenance',
          'url' => 'admin/config/development/maintenance',
          'permission' => 'access CiviCRM,administer site configuration',
          'operator' => 'AND',
          'separator' => 1,
          'parentID' => $manageID,
          'navID' => $manageID+2,
          'active' => 1,
        ),
      ),
      $manageID+3 => array(
        'attributes' => Array(
          'label' => 'Backup/Restore',
          'name' => 'Backup/Restore',
          'url' => 'backupdata',
          'permission' => 'administer CiviCRM,export print production files',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+3,
          'active' => 1,
        ),
      ),
      $manageID+4 => array(
        'attributes' => Array(
          'label' => 'Load Sample Data',
          'name' => 'Load Sample Data',
          'url' => 'civicrm/nyss/loadsampledata?reset=1',
          'permission' => 'administer CiviCRM',
          'operator' => 'OR',
          'separator' => 1,
          'parentID' => $manageID,
          'navID' => $manageID+4,
          'active' => 1,
        ),
      ),
      $manageID+5 => array(
        'attributes' => Array(
          'label' => 'Import Contacts',
          'name' => 'Import Contacts',
          'url' => 'civicrm/import/contact?reset=1',
          'permission' => 'import contacts',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+5,
          'active' => 1,
        ),
      ),
      $manageID+6 => array(
        'attributes' => Array(
          'label' => 'Import Activities',
          'name' => 'Import Activities',
          'url' => 'civicrm/import/activity?reset=1',
          'permission' => 'import contacts',
          'operator' => 'AND',
          'separator' => 1,
          'parentID' => $manageID,
          'navID' => $manageID+6,
          'active' => 1,
        ),
      ),
      $manageID+7 => array(
        'attributes' => Array(
          'label' => 'Manage Groups',
          'name' => 'Manage Groups',
          'url' => 'civicrm/group?reset=1',
          'permission' => 'edit groups',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+7,
          'active' => 1,
        ),
      ),
      $manageID+8 => array(
        'attributes' => Array(
          'label' => 'Manage Tags',
          'name' => 'Manage Tags',
          'url' => 'civicrm/tag?reset=1',
          'permission' => 'administer CiviCRM,manage tags',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+8,
          'active' => 1,
        ),
      ),
      $manageID+9 => array(
        'attributes' => Array(
          'label' => 'Manage Users',
          'name' => 'Manage Users',
          'url' => 'admin/people',
          'permission' => 'administer CiviCRM,administer district',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+9,
          'active' => 1,
        ),
      ),
      //6552
      $manageID+10 => array(
        'attributes' => Array(
          'label' => 'Export Permissions',
          'name' => 'Export Permissions',
          'url' => 'civicrm/nyss/exportpermissions',
          'permission' => 'administer CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+10,
          'active' => 1,
        ),
      ),
      $manageID+11 => array(
        'attributes' => Array(
          'label' => 'Merge Duplicate Contacts',
          'name' => 'Merge Duplicate Contacts',
          'url' => 'civicrm/contact/deduperules?reset=1',
          'permission' => 'merge duplicate contacts',
          'operator' => 'OR',
          'separator' => 2,
          'parentID' => $manageID,
          'navID' => $manageID+11,
          'active' => 1,
        ),
      ),
      $manageID+12 => array(
        'attributes' => Array(
          'label' => 'Case Dashboard',
          'name' => 'Case Dashboard',
          'url' => 'civicrm/case?reset=1',
          'permission' => 'access all cases and activities',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+12,
          'active' => 1,
        ),
      ),
      $manageID+13 => array(
        'attributes' => Array(
          'label' => 'Import/Export Mappings',
          'name' => 'Import/Export Mappings',
          'url' => 'civicrm/admin/mapping?reset=1',
          'permission' => 'administer CiviCRM,administer district',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+13,
          'active' => 1,
        ),
      ),
      //5230 add duplicate address removal tool
      $manageID+14 => array(
        'attributes' => Array(
          'label' => 'Duplicate Address Removal',
          'name' => 'Duplicate Address Removal',
          'url' => 'civicrm/dedupe/dupeaddress?reset=1',
          'permission' => 'export print production files',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+14,
          'active' => 1,
        ),
      ),
      //delete trashed tool
      $manageID+15 => array(
        'attributes' => Array(
          'label' => 'Delete Trashed Contacts',
          'name' => 'Delete Trashed Contacts',
          'url' => 'civicrm/nyss/deletetrashed?reset=1',
          'permission' => 'administer CiviCRM,export print production files',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+15,
          'active' => 1,
        ),
      ),
      $manageID+16 => array(
        'attributes' => Array(
          'label' => 'Manage Images',
          'name' => 'Manage Images',
          'url' => 'sites/all/modules/civicrm/packages/kcfinder/browse.php?cms=civicrm&type=images&langCode=en',
          'permission' => 'create mailings, schedule mailings, administer CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $manageID,
          'navID' => $manageID+16,
          'active' => 1,
          'target' => '_blank',
        ),
      ),
    ),
  );

  return $manage;
}//_buildManageMenu

/*
 * given our "starting" ID, construct the mass email menu items
 * return the complete array to be added to the main navigation array
 */
function _buildEmailMenu($emailID) {
  //6799 first retrieve mailing report IDs; prefer reserved, lower IDs
  $rptIDs = array();
  $sql = "
    SELECT id, report_id
    FROM civicrm_report_instance
    WHERE report_id LIKE 'mailing/%'
    ORDER BY is_reserved DESC, id DESC
  ";
  $rpts = CRM_Core_DAO::executeQuery($sql);
  while ($rpts->fetch()) {
    $type = str_replace(['mailing/', 'Mailing/'], '', $rpts->report_id);
    $rptIDs[$type] = $rpts->id;
  }

  $email = [
    'attributes' => [
      'label' => 'Mass Email',
      'name' => 'Mass Email',
      'url' => null,
      'permission' => 'access CiviCRM',
      'operator' => 'AND',
      'separator' => 0,
      'parentID' => null,
      'navID' => $emailID,
      'active' => 1
    ],
    'child' => [
      $emailID+1 => [
        'attributes' => [
          'label' => 'New Mass Email',
          'name' => 'New Mass Email',
          'url' => 'civicrm/mailing/send?reset=1',
          'permission' => 'access CiviMail,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+1,
          'active' => 1,
        ],
      ],
      $emailID+2 => [
        'attributes' => [
          'label' => 'Draft and Unscheduled Emails',
          'name' => 'Draft and Unscheduled Emails',
          'url' => 'civicrm/mailing/browse/unscheduled?reset=1&scheduled=false',
          'permission' => 'access CiviMail,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+2,
          'active' => 1,
        ],
      ],
      $emailID+3 => [
        'attributes' => [
          'label' => 'Scheduled and Sent Emails',
          'name' => 'Scheduled and Sent Emails',
          'url' => 'civicrm/mailing/browse/scheduled?reset=1&scheduled=true',
          'permission' => 'access CiviMail,approve mailings,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+3,
          'active' => 1,
        ],
      ],
      $emailID+4 => [
        'attributes' => [
          'label' => 'Find Mailings',
          'name' => 'Find Mailings',
          'url' => 'civicrm/mailing/browse?reset=1',
          'permission' => 'access CiviMail,approve mailings,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+4,
          'active' => 1,
        ],
      ],
      $emailID+5 => [
        'attributes' => [
          'label' => 'Mailing Templates',
          'name' => 'Mailing Templates',
          'url' => 'civicrm/a/#/mosaico-template',
          'permission' => 'access CiviMail,approve mailings,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+5,
          'active' => 1,
        ],
      ],
      $emailID+6 => [
        'attributes' => [
          'label' => 'New Mass Email (legacy)',
          'name' => 'New Mass Email (legacy)',
          'url' => 'civicrm/a/#/mailing/new/traditional',
          'permission' => 'access CiviMail,approve mailings,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+6,
          'active' => 1,
        ],
      ],
      $emailID+7 => [
        'attributes' => [
          'label' => 'Archived Emails',
          'name' => 'Archived Emails',
          'url' => 'civicrm/mailing/browse/archived?reset=1',
          'permission' => 'create mailings,schedule mailings,access CiviMail',
          'operator' => 'OR',
          'separator' => 1,
          'parentID' => $emailID,
          'navID' => $emailID+7,
          'active' => 1,
        ],
      ],
      $emailID+8 => [
        'attributes' => [
          'label' => 'Mass Email Summary Report',
          'name' => 'Mass Email Summary Report',
          'url' => "civicrm/report/instance/{$rptIDs['summary']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+8,
          'active' => 1,
        ],
      ],
      $emailID+9 => [
        'attributes' => [
          'label' => 'Mass Email Bounce Report',
          'name' => 'Mass Email Bounce Report',
          'url' => "civicrm/report/instance/{$rptIDs['bounce']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+9,
          'active' => 1,
        ],
      ],
      $emailID+10 => [
        'attributes' => [
          'label' => 'Mass Email Opened Report',
          'name' => 'Mass Email Opened Report',
          'url' => "civicrm/report/instance/{$rptIDs['opened']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+10,
          'active' => 1,
        ],
      ],
      $emailID+11 => [
        'attributes' => [
          'label' => 'Mass Email Clickthrough Report',
          'name' => 'Mass Email Clickthrough Report',
          'url' => "civicrm/report/instance/{$rptIDs['clicks']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+11,
          'active' => 1,
        ],
      ],
      $emailID+12 => [
        'attributes' => [
          'label' => 'Mass Email Detail Report',
          'name' => 'Mass Email Detail Report',
          'url' => "civicrm/report/instance/{$rptIDs['detail']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+12,
          'active' => 1,
        ],
      ],
    ],
  ];

  return $email;
}//_buildManageMenu

/*
 * given our "starting" ID, construct the inbox menu items
 * return the complete array to be added to the main navigation array
 */
function _buildInboxMenu($inboxNavID) {
  $inbox = array(
    'attributes' => array(
      'label'      => 'Inbox',
      'name'       => 'Inbox',
      'url'        => null,
      'permission' => 'access inbox polling',
      'operator'   => 'AND',
      'separator'  => 0,
      'parentID'   => null,
      'navID'      => $inboxNavID,
      'active'     => 1
    ),
    'child' => array(
      $inboxNavID+1 => array(
        'attributes' => array(
          'label'      => 'Unmatched Messages',
          'name'       => 'Unmatched Messages',
          'url'        => 'civicrm/nyss/inbox/unmatched?reset=1',
          'permission' => 'access inbox polling',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $inboxNavID,
          'navID'      => $inboxNavID+1,
          'active'     => 1
        ),
      ),
      $inboxNavID+2 => array(
        'attributes' => array(
          'label'      => 'Matched Messages',
          'name'       => 'Matched Messages',
          'url'        => 'civicrm/nyss/inbox/matched?reset=1',
          'permission' => 'access inbox polling',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $inboxNavID,
          'navID'      => $inboxNavID+2,
          'active'     => 1
        ),
      ),
      $inboxNavID+3 => array(
        'attributes' => array(
          'label'      => 'Reports',
          'name'       => 'Reports',
          'url'        => 'civicrm/nyss/inbox/report',
          'permission' => 'access inbox polling',
          'operator'   => 'AND',
          'separator'  => 2,
          'parentID'   => $inboxNavID,
          'navID'      => $inboxNavID+3,
          'active'     => 1
        ),
      ),
    ),
  );

  return $inbox;
}//_buildInboxMenu

function _buildSearchMenu($searchNavID) {
  $search = array(
    'attributes' => array(
      'label'      => 'Search',
      'name'       => 'Search',
      'url'        => null,
      'permission' => 'access CiviCRM',
      'operator'   => 'AND',
      'separator'  => 0,
      'parentID'   => null,
      'navID'      => $searchNavID,
      'active'     => 1
    ),
    'child' => array(
      $searchNavID+1 => array(
        'attributes' => array(
          'label'      => 'Advanced Search',
          'name'       => 'Advanced Search',
          'url'        => 'civicrm/contact/search/advanced?reset=1',
          'permission' => 'access CiviCRM',
          'operator'   => 'OR',
          'separator'  => 1,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+1,
          'active'     => 1
        ),
      ),
      $searchNavID+2 => array(
        'attributes' => array(
          'label'      => 'Find Cases',
          'name'       => 'Find Cases',
          'url'        => 'civicrm/case/search?reset=1',
          'permission' => 'access my cases and activities,access all cases and activities',
          'operator'   => 'OR',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+2,
          'active'     => 1
        ),
      ),
      $searchNavID+3 => array(
        'attributes' => array(
          'label'      => 'Find Activities',
          'name'       => 'Find Activities',
          'url'        => 'civicrm/activity/search?reset=1',
          'permission' => 'view all activities',
          'operator'   => 'AND',
          'separator'  => 1,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+3,
          'active'     => 1
        ),
      ),
      $searchNavID+4 => array(
        'attributes' => array(
          'label'      => 'Full-text Search',
          'name'       => 'Full-text Search',
          'url'        => 'civicrm/contact/search/custom?csid=15&reset=1',
          'permission' => 'access CiviCRM',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+4,
          'active'     => 1
        ),
      ),
      $searchNavID+5 => array(
        'attributes' => array(
          'label'      => 'Search Builder',
          'name'       => 'Search Builder',
          'url'        => 'civicrm/contact/search/builder?reset=1',
          'permission' => 'access CiviCRM',
          'operator'   => 'AND',
          'separator'  => 1,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+5,
          'active'     => 1
        ),
      ),
      $searchNavID+6 => array(
        'attributes' => array(
          'label'      => 'Proximity Search',
          'name'       => 'Proximity Search',
          'url'        => 'civicrm/contact/search/custom?reset=1&csid=6',
          'permission' => 'access CiviCRM',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+6,
          'active'     => 1
        ),
      ),
      $searchNavID+7 => array(
        'attributes' => array(
          'label'      => 'Birthday Search',
          'name'       => 'Birthday Search',
          'url'        => 'civicrm/contact/search/custom?reset=1&csid=16',
          'permission' => 'access CiviCRM',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+7,
          'active'     => 1
        ),
      ),
      $searchNavID+8 => array(
        'attributes' => array(
          'label'      => 'Include/Exclude Search',
          'name'       => 'Include/Exclude Search',
          'url'        => 'civicrm/contact/search/custom?csid=4&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+8,
          'active'     => 1
        ),
      ),
      $searchNavID+9 => array(
        'attributes' => array(
          'label'      => 'Tag/Group Changelog Search',
          'name'       => 'Tag/Group Changelog Search',
          'url'        => 'civicrm/contact/search/custom?csid=17&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+9,
          'active'     => 1
        ),
      ),
      $searchNavID+10 => array(
        'attributes' => array(
          'label'      => 'Tag Count Search',
          'name'       => 'Tag Count Search',
          'url'        => 'civicrm/contact/search/custom?csid=19&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+10,
          'active'     => 1
        ),
      ),
      $searchNavID+11 => array(
        'attributes' => array(
          'label'      => 'Web Activity Search',
          'name'       => 'Web Activity Search',
          'url'        => 'civicrm/contact/search/custom?csid=18&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+11,
          'active'     => 1
        ),
      ),
      $searchNavID+12 => array(
        'attributes' => array(
          'label'      => 'Tag Demographic Search',
          'name'       => 'Tag Demographic Search',
          'url'        => 'civicrm/contact/search/custom?csid=20&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+12,
          'active'     => 1
        ),
      ),
    ),
  );

  return $search;
} //_buildSearchMenu()


//rebuild admin menu
function _buildAdminMenu($nyssBaseID) {
  $mailingCatID = CRM_Core_DAO::singleValueQuery("
    SELECT id FROM civicrm_option_group WHERE name = 'mailing_categories'
  ");

  $adminNav = array(
    'attributes' => array(
      'label' => 'Administer',
      'name' => 'Administer',
      'url' => null,
      'permission' => 'view debug output',
      'operator' => 'AND',
      'separator' => 0,
      'parentID' => null,
      'navID' => $nyssBaseID,
      'active' => 1
    ),
    'child' => array(
      $nyssBaseID+1 => array(
        'attributes' => array(
          'label' => 'Administration Console',
          'name' => 'Administration Console',
          'url' => 'civicrm/admin?reset=1',
          'permission' => 'view debug output',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+1,
          'active' => 1
        ),
      ),
      $nyssBaseID+2 => array(
        'attributes' => array(
          'label' => 'NYSS Manage Mailing Categories',
          'name' => 'NYSS Manage Mailing Categories',
          'url' => "civicrm/admin/optionValue?gid={$mailingCatID}&reset=1",
          'permission' => 'view debug output',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+2,
          'active' => 1
        ),
      ),
      $nyssBaseID+3 => array(
        'attributes' => array(
          'label' => 'CiviCRM System Status',
          'name' => 'CiviCRM System Status',
          'url' => 'civicrm/a/#/status',
          'permission' => 'view debug output',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+3,
          'active' => 1
        ),
      ),
      $nyssBaseID+4 => array(
        'attributes' => array(
          'label' => 'Manage Extensions',
          'name' => 'Manage Extensions',
          'url' => 'civicrm/admin/extensions?reset=1',
          'permission' => 'view debug output',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+4,
          'active' => 1
        ),
      ),
    ),
  );

  //CRM_Core_Error::debug_var('adminNav', $adminNav);
  return $adminNav;
} //_buildAdminMenu()

//11965
function _buildHelpMenu($nyssBaseID) {
  $nav = array(
    'attributes' => array(
      'label' => 'Help',
      'name' => 'Help',
      'url' => null,
      'permission' => 'access CiviCRM',
      'operator' => 'AND',
      'separator' => 0,
      'parentID' => null,
      'navID' => $nyssBaseID,
      'active' => 1
    ),
    'child' => array(
      $nyssBaseID+1 => array(
        'attributes' => array(
          'label' => 'Introduction',
          'name' => 'Introduction',
          'url' => 'sites/all/docs/bluebird_intro.pdf',
          'permission' => 'access CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+1,
          'active' => 1,
          'target' => '_blank',
        ),
      ),
      $nyssBaseID+2 => array(
        'attributes' => array(
          'label' => 'Inbound Email',
          'name' => 'Inbound Email',
          'url' => 'sites/all/docs/bluebird_inbound_email.pdf',
          'permission' => 'access CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+2,
          'active' => 1,
          'target' => '_blank',
        ),
      ),
      $nyssBaseID+3 => array(
        'attributes' => array(
          'label' => 'Mass Email',
          'name' => 'Mass Email',
          'url' => 'sites/all/docs/bluebird_mass_email.pdf',
          'permission' => 'access CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+3,
          'active' => 1,
          'target' => '_blank',
        ),
      ),
      $nyssBaseID+4 => array(
        'attributes' => array(
          'label' => 'Postal Mailings',
          'name' => 'Postal Mailings',
          'url' => 'sites/all/docs/bluebird_postal_mail.pdf',
          'permission' => 'access CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+4,
          'active' => 1,
          'target' => '_blank',
        ),
      ),
      $nyssBaseID+5 => [
        'attributes' => [
          'label' => 'Bluebird News',
          'name' => 'Bluebird News',
          'url' => 'https://senateonline.nysenate.gov/BluebirdNews.nsf',
          'permission' => 'access CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+5,
          'active' => 1,
          'target' => '_blank',
        ],
      ],
    ),
  );

  //CRM_Core_Error::debug_var('adminNav', $adminNav);
  return $nav;
} //_buildHelpMenu()

function _buildCreateMenu($nyssBaseID) {
  $nav = array(
    'attributes' => array(
      'label' => 'Create',
      'name' => 'Create',
      'url' => null,
      'permission' => 'access CiviCRM',
      'operator' => 'AND',
      'separator' => 0,
      'parentID' => null,
      'navID' => $nyssBaseID,
      'active' => 1
    ),
    'child' => array(
      $nyssBaseID+1 => array(
        'attributes' => array(
          'label' => 'New Individual',
          'name' => 'New Individual',
          'url' => 'civicrm/contact/add?reset=1&ct=Individual',
          'permission' => 'add contacts',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+1,
          'active' => 1
        ),
      ),
      $nyssBaseID+2 => array(
        'attributes' => array(
          'label' => 'New Household',
          'name' => 'New Household',
          'url' => 'civicrm/contact/add?reset=1&ct=Household',
          'permission' => 'add contacts',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+2,
          'active' => 1
        ),
      ),
      $nyssBaseID+3 => array(
        'attributes' => array(
          'label' => 'New Organization',
          'name' => 'New Organization',
          'url' => 'civicrm/contact/add?reset=1&ct=Organization',
          'permission' => 'add contacts',
          'operator' => 'OR',
          'separator' => 1,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+3,
          'active' => 1
        ),
      ),
      $nyssBaseID+4 => array(
        'attributes' => array(
          'label' => 'New Activity',
          'name' => 'New Activity',
          'url' => 'civicrm/activity?reset=1&action=add&context=standalone',
          'permission' => 'view all activities',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+4,
          'active' => 1
        ),
      ),
      $nyssBaseID+5 => array(
        'attributes' => array(
          'label' => 'New Case',
          'name' => 'New Case',
          'url' => 'civicrm/case/add?reset=1&action=add&atype=13&context=standalone',
          'permission' => 'access all cases and activities',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+5,
          'active' => 1
        ),
      ),
      $nyssBaseID+6 => array(
        'attributes' => array(
          'label' => 'New Email',
          'name' => 'New Email',
          'url' => 'civicrm/activity/email/add?atype=3&action=add&reset=1&context=standalone',
          'permission' => 'add contacts',
          'operator' => 'OR',
          'separator' => 1,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+6,
          'active' => 1
        ),
      ),
      $nyssBaseID+7 => array(
        'attributes' => array(
          'label' => 'New Group',
          'name' => 'New Group',
          'url' => 'civicrm/group/add?reset=1',
          'permission' => 'edit groups',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+7,
          'active' => 1
        ),
      ),
    ),
  );

  //CRM_Core_Error::debug_var('adminNav', $adminNav);
  return $nav;
} //_buildHelpMenu()

