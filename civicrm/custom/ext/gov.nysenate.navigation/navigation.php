<?php

require_once 'navigation.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function navigation_civicrm_config(&$config) {
  _navigation_civix_civicrm_config($config);
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
  //first find parents
  $reportNavID = $adminNavID = '';
  $reportNav = $adminNav = array();
  foreach ($params as $navID => $navDetails) {
    //unset all but the reports and administer menus
    switch ($navDetails['attributes']['label']) {
      case 'Reports':
        $reportNavID = $navID;
        $reportNav = $navDetails;
        break;
      case 'Administer':
        $adminNavID = $navID;
        $adminNav = $navDetails;
        break;
      default:
    }
    unset($params[$navID]);
  }

  //get max key
  $maxKey = (!empty($params)) ? max(array_keys($params)) : 0;

  //build home menu item
  $params[1] = array(
    'attributes' => array(
      'label'      => 'Home',
      'name'       => 'Home',
      'url'        => 'civicrm/dashboard&reset=1',
      'permission' => 'access CiviCRM',
      'operator'   => 'AND',
      'separator'  => 0,
      'parentID'   => null,
      'navID'      => 1,
      'active'     => 1
    ),
    'child' => array(),
  );

  $params[2] = array(
    'attributes' => array(
      'label'      => 'Advanced Search',
      'name'       => 'Advanced Search',
      'url'        => 'civicrm/contact/search/advanced?reset=1',
      'permission' => 'access CiviCRM',
      'operator'   => 'AND',
      'separator'  => 0,
      'parentID'   => null,
      'navID'      => 2,
      'active'     => 1
    ),
    'child' => array(),
  );

  //build Custom Search menu
  $params[3] = _buildSearchMenu(2);

  //move Report menu
  $params[$reportNavID] = $reportNav;

  //NYSS-7260 add websignup reports
  $params[$reportNavID]['child'][$maxKey+1] = array(
    'attributes' => array(
      'label'      => 'Web Signup Reports',
      'name'       => 'Web Signup Reports',
      'url'        => 'signupreports',
      'permission' => 'access CiviReport',
      'operator'   => 'AND',
      'separator'  => 0,
      'parentID'   => $reportNavID,
      'navID'      => $maxKey+1,
      'active'     => 1
    ),
    'child' => array(),
  );
  //5260 add changelog proofing report
  $params[$reportNavID]['child'][$maxKey+2] = array(
    'attributes' => array(
      'label'      => 'Changelog Proofing Report',
      'name'       => 'Changelog Proofing Report',
      'url'        => 'civicrm/nyss/proofingreport?reset=1',
      'permission' => 'access CiviReport',
      'operator'   => 'AND',
      'separator'  => 0,
      'parentID'   => $reportNavID,
      'navID'      => $maxKey+2,
      'active'     => 1
    ),
    'child' => array(),
  );

  //build Manage menu
  $params[1000] = _buildManageMenu(1000);

  //build Mass Email menu
  $params[2000] = _buildEmailMenu(2000);

  //build Inbox menu
  $params[3000] = _buildInboxMenu(3000);

  //move Administer menu
  $params[$adminNavID] = _buildAdminMenu($adminNavID);

  //create Help menu 11965
  $params[4000] = _buildHelpMenu(4000);

  //CRM_Core_Error::debug_var('navigationMenu params (after)',$params);
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
    $type = str_replace(array('mailing/', 'Mailing/'), '', $rpts->report_id);
    $rptIDs[$type] = $rpts->id;
  }

  $email = array(
    'attributes' => array(
      'label'      => 'Mass Email',
      'name'       => 'Mass Email',
      'url'        => null,
      'permission' => 'access CiviCRM',
      'operator'   => 'AND',
      'separator'  => 0,
      'parentID'   => null,
      'navID'      => $emailID,
      'active'     => 1
    ),
    'child' => array(
      $emailID+1 => array(
        'attributes' => array(
          'label' => 'New Mass Email',
          'name' => 'New Mass Email',
          'url' => 'civicrm/mailing/send?reset=1',
          'permission' => 'access CiviMail,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+1,
          'active' => 1,
        ),
        'child' => array(),
      ),
      $emailID+2 => array(
        'attributes' => Array(
          'label' => 'Draft and Unscheduled Emails',
          'name' => 'Draft and Unscheduled Emails',
          'url' => 'civicrm/mailing/browse/unscheduled?reset=1&scheduled=false',
          'permission' => 'access CiviMail,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+2,
          'active' => 1,
        ),
        'child' => array(),
      ),
      $emailID+3 => array(
        'attributes' => Array(
          'label' => 'Scheduled and Sent Emails',
          'name' => 'Scheduled and Sent Emails',
          'url' => 'civicrm/mailing/browse/scheduled?reset=1&scheduled=true',
          'permission' => 'access CiviMail,approve mailings,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+3,
          'active' => 1,
        ),
        'child' => array(),
      ),
      $emailID+4 => array(
        'attributes' => Array(
          'label' => 'Find Mailings',
          'name' => 'Find Mailings',
          'url' => 'civicrm/mailing/browse?reset=1',
          'permission' => 'access CiviMail,approve mailings,create mailings,schedule mailings',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+4,
          'active' => 1,
        ),
        'child' => array(),
      ),
      $emailID+5 => array(
        'attributes' => Array(
          'label' => 'Archived Emails',
          'name' => 'Archived Emails',
          'url' => 'civicrm/mailing/browse/archived?reset=1',
          'permission' => 'create mailings,schedule mailings,access CiviMail',
          'operator' => 'OR',
          'separator' => 1,
          'parentID' => $emailID,
          'navID' => $emailID+5,
          'active' => 1,
        ),
        'child' => array(),
      ),
      $emailID+6 => array(
        'attributes' => Array(
          'label' => 'Mass Email Summary Report',
          'name' => 'Mass Email Summary Report',
          'url' => "civicrm/report/instance/{$rptIDs['summary']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+6,
          'active' => 1,
        ),
        'child' => array(),
      ),
      $emailID+7 => array(
        'attributes' => Array(
          'label' => 'Mass Email Bounce Report',
          'name' => 'Mass Email Bounce Report',
          'url' => "civicrm/report/instance/{$rptIDs['bounce']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'AND',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+7,
          'active' => 1,
        ),
        'child' => array(),
      ),
      $emailID+8 => array(
        'attributes' => Array(
          'label' => 'Mass Email Opened Report',
          'name' => 'Mass Email Opened Report',
          'url' => "civicrm/report/instance/{$rptIDs['opened']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+8,
          'active' => 1,
        ),
        'child' => array(),
      ),
      $emailID+9 => array(
        'attributes' => Array(
          'label' => 'Mass Email Clickthrough Report',
          'name' => 'Mass Email Clickthrough Report',
          'url' => "civicrm/report/instance/{$rptIDs['clicks']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+9,
          'active' => 1,
        ),
        'child' => array(),
      ),
      $emailID+10 => array(
        'attributes' => Array(
          'label' => 'Mass Email Detail Report',
          'name' => 'Mass Email Detail Report',
          'url' => "civicrm/report/instance/{$rptIDs['detail']}?reset=1",
          'permission' => 'access CiviReport',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $emailID,
          'navID' => $emailID+10,
          'active' => 1,
        ),
        'child' => array(),
      ),
    ),
  );

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
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
      ),
    ),
  );

  return $inbox;
}//_buildInboxMenu

function _buildSearchMenu($searchNavID) {
  $search = array(
    'attributes' => array(
      'label'      => 'Custom Search',
      'name'       => 'Custom Search',
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
          'label'      => 'Find Cases',
          'name'       => 'Find Cases',
          'url'        => 'civicrm/case/search?reset=1',
          'permission' => 'access my cases and activities,access all cases and activities',
          'operator'   => 'OR',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+1,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+2 => array(
        'attributes' => array(
          'label'      => 'Find Activities',
          'name'       => 'Find Activities',
          'url'        => 'civicrm/activity/search?reset=1',
          'permission' => 'view all activities',
          'operator'   => 'AND',
          'separator'  => 1,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+2,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+3 => array(
        'attributes' => array(
          'label'      => 'Full-text Search',
          'name'       => 'Full-text Search',
          'url'        => 'civicrm/contact/search/custom?csid=15&reset=1',
          'permission' => 'access CiviCRM',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+3,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+4 => array(
        'attributes' => array(
          'label'      => 'Search Builder',
          'name'       => 'Search Builder',
          'url'        => 'civicrm/contact/search/builder?reset=1',
          'permission' => 'access CiviCRM',
          'operator'   => 'AND',
          'separator'  => 1,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+4,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+5 => array(
        'attributes' => array(
          'label'      => 'Proximity Search',
          'name'       => 'Proximity Search',
          'url'        => 'civicrm/contact/search/custom?reset=1&csid=6',
          'permission' => 'access CiviCRM',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+5,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+6 => array(
        'attributes' => array(
          'label'      => 'Birthday Search',
          'name'       => 'Birthday Search',
          'url'        => 'civicrm/contact/search/custom?reset=1&csid=16',
          'permission' => 'access CiviCRM',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+6,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+7 => array(
        'attributes' => array(
          'label'      => 'Include/Exclude Search',
          'name'       => 'Include/Exclude Search',
          'url'        => 'civicrm/contact/search/custom?csid=4&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+7,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+8 => array(
        'attributes' => array(
          'label'      => 'Tag/Group Changelog Search',
          'name'       => 'Tag/Group Changelog Search',
          'url'        => 'civicrm/contact/search/custom?csid=17&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+8,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+9 => array(
        'attributes' => array(
          'label'      => 'Tag Count Search',
          'name'       => 'Tag Count Search',
          'url'        => 'civicrm/contact/search/custom?csid=19&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+9,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+10 => array(
        'attributes' => array(
          'label'      => 'Web Activity Search',
          'name'       => 'Web Activity Search',
          'url'        => 'civicrm/contact/search/custom?csid=18&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+10,
          'active'     => 1
        ),
        'child' => array(),
      ),
      $searchNavID+11 => array(
        'attributes' => array(
          'label'      => 'Tag Demographic Search',
          'name'       => 'Tag Demographic Search',
          'url'        => 'civicrm/contact/search/custom?csid=20&reset=1',
          'permission' => 'access CiviCRM,view all contacts',
          'operator'   => 'AND',
          'separator'  => 0,
          'parentID'   => $searchNavID,
          'navID'      => $searchNavID+11,
          'active'     => 1
        ),
        'child' => array(),
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
        'child' => array(),
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
        'child' => array(),
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
          'label' => 'Bluebird Introduction',
          'name' => 'Bluebird Introduction',
          'url' => 'http://senateonline.senate.state.ny.us/STS.nsf/($all)/834B05B0923E3D6A85257CEC00622A0E/$file/Bluebird%20Intro%20Nov%202017.pdf',
          'permission' => 'access CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+1,
          'active' => 1
        ),
        'child' => array(),
      ),
      $nyssBaseID+2 => array(
        'attributes' => array(
          'label' => 'Bluebird Managing Inbound Emails',
          'name' => 'Bluebird Managing Inbound Emails',
          'url' => 'http://senateonline.senate.state.ny.us/STS.nsf/($all)/B165F4DF4AC7CF9585257CEC00627ABA/$file/Bluebird%20Managing%20Inbound%20Emails%20Feb%202018.pdf',
          'permission' => 'access CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+2,
          'active' => 1
        ),
        'child' => array(),
      ),
      $nyssBaseID+3 => array(
        'attributes' => array(
          'label' => 'Bluebird Mass Email',
          'name' => 'Bluebird Mass Email',
          'url' => 'http://senateonline.senate.state.ny.us/STS.nsf/($all)/5C7B4FB960A91B9E85257CEC006255AB/$file/Bluebird%20Mass%20Email%20Nov%202017.pdf',
          'permission' => 'access CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+3,
          'active' => 1
        ),
        'child' => array(),
      ),
      $nyssBaseID+4 => array(
        'attributes' => array(
          'label' => 'Bluebird Postal Mailings',
          'name' => 'Bluebird Postal Mailings',
          'url' => 'http://senateonline.senate.state.ny.us/STS.nsf/($all)/D6935E6B4319E97485257CEC00628C18/$file/Bluebird%20Postal%20Mailings%20Dec%202017.pdf',
          'permission' => 'access CiviCRM',
          'operator' => 'OR',
          'separator' => 0,
          'parentID' => $nyssBaseID,
          'navID' => $nyssBaseID+4,
          'active' => 1
        ),
        'child' => array(),
      ),
    ),
  );

  //CRM_Core_Error::debug_var('adminNav', $adminNav);
  return $nav;
} //_buildHelpMenu()
