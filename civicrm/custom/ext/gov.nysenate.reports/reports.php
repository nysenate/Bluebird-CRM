<?php

require_once 'reports.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function reports_civicrm_config(&$config) {
  _reports_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function reports_civicrm_xmlMenu(&$files) {
  _reports_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function reports_civicrm_install() {
  _reports_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function reports_civicrm_uninstall() {
  _reports_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function reports_civicrm_enable() {
  _reports_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function reports_civicrm_disable() {
  _reports_civix_civicrm_disable();
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
function reports_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _reports_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function reports_civicrm_managed(&$entities) {
  _reports_civix_civicrm_managed($entities);
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
function reports_civicrm_caseTypes(&$caseTypes) {
  _reports_civix_civicrm_caseTypes($caseTypes);
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
function reports_civicrm_angularModules(&$angularModules) {
_reports_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function reports_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _reports_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function reports_civicrm_queryObjects(&$queryObjects, $type) {
  if ($type == 'Report') {
    $queryObjects[] = new CRM_NYSS_Reports_BAO_Query();
  }
}

function reports_civicrm_alterReportVar($varType, &$var, &$object) {
  /*Civi::log()->debug('alterReportVar', array(
    'varType' => $varType,
    'var' => $var,
    //'object' => $object,
  ));*/

  $class = get_class($object);
  switch ($varType) {
    case 'columns':
      switch ($class) {
        case 'CRM_Report_Form_Case_Detail':
          _reports_CaseDetail_col($var, $object);
          break;

        default:
      }
      break;

    case 'sql':
      switch ($class) {
        case 'CRM_Report_Form_Contact_LoggingSummary':
          _reports_LoggingSummary_sql($var, $object);
          break;

        case 'CRM_Report_Form_Case_Detail':
          _reports_CaseDetail_sql($var, $object);
          break;

        default:
      }
      break;

    case 'rows':
      break;

    default:
  }
}

/*
 * 12173 see also CRM_NYSS_Reports_BAO_Query
 * this ensures tag/group logs aren't duplicated as contact entries
 * we need to do this because we trigger a modified_date update for those
 * objects which causes them to show up as contact updates
 */
function _reports_LoggingSummary_sql(&$var, &$object) {
  if ($var->getVar('currentLogTable') == 'log_civicrm_contact') {
    $from = $var->getVar('_from');
    $where = $var->getVar('_where');

    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $loggingDB = $dsn['database'];

    //change extra_join to LEFT JOIN
    $from = str_replace("INNER JOIN `{$loggingDB}`.log_civicrm_entity_tag",
      "LEFT JOIN `{$loggingDB}`.log_civicrm_entity_tag", $from);
    $from = str_replace("INNER JOIN `{$loggingDB}`.log_civicrm_group_contact",
      "LEFT JOIN `{$loggingDB}`.log_civicrm_group_contact", $from);
    $var->setVar('_from', $from);

    $where .= " AND extra_table.id IS NULL AND extra_table_2.id IS NULL";
    $var->setVar('_where', $where);
  }
}

function _reports_CaseDetail_col(&$var, &$object) {
  $var['civicrm_tag'] = [
    'dao' => 'CRM_Core_DAO_Tag',
    'fields' => [
      'id' => [
        'required' => TRUE,
        'no_display' => TRUE,
      ],
      'name' => [
        'title' => ts('Tag Name'),
        'default' => TRUE,
        'no_repeat' => TRUE,
      ],
    ],
    'group_bys' => [
      'tag_name' => [
        'name' => 'id',
        'title' => ts('Tag'),
        'default' => TRUE,
      ],
    ],
    'order_bys' => [
      'name' => [
        'title' => ts('Tag Name'),
      ],
    ],
    'grouping' => 'case-fields',
  ];
}

function _reports_CaseDetail_sql(&$var, &$object) {
  /*Civi::log()->debug('alterReportVar', array(
    'var' => $var,
    //'object' => $object,
  ));*/

  $from = $var->getVar('_from');
  $from .= "
    LEFT JOIN civicrm_entity_tag
      ON civicrm_entity_tag.entity_id = {$var->getVar('_aliases')['civicrm_case']}.id
      AND civicrm_entity_tag.entity_table LIKE '%civicrm_case%'
    LEFT JOIN civicrm_tag tag_civireport
      ON civicrm_entity_tag.tag_id = tag_civireport.id
  ";

  $var->setVar('_from', $from);

  $selectClauses = &$var->_selectClauses;
  foreach ($selectClauses as &$clause) {
    switch ($clause) {
      case 'tag_civireport.id as civicrm_tag_id':
        $clause = 'GROUP_CONCAT(DISTINCT tag_civireport.id) as civicrm_tag_id';
        break;
      case 'tag_civireport.name as civicrm_tag_name':
        $clause = 'GROUP_CONCAT(DISTINCT tag_civireport.name SEPARATOR ", ") as civicrm_tag_name';
        break;
      default:
    }
  }

  $select = $var->_select;
  $select = str_replace(', tag_civireport.id as civicrm_tag_id',
    ', GROUP_CONCAT(DISTINCT tag_civireport.id) as civicrm_tag_id', $select);
  $select = str_replace(', tag_civireport.name as civicrm_tag_name',
    ', GROUP_CONCAT(DISTINCT tag_civireport.name SEPARATOR ", ") as civicrm_tag_name', $select);
  $var->_select = $select;

  $groupBy = $var->_groupBy;
  $var->_groupBy = str_replace(', tag_civireport.id', '', $groupBy);
}
