<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2017
 * $Id$
 */
class CRM_Logging_ReportSummary extends CRM_Report_Form {
  protected $cid;

  protected $_logTables = array();

  protected $loggingDB;

  /**
   * The log table currently being processed.
   *
   * @var string
   */
  protected $currentLogTable;

  /**
   * Class constructor.
   */
  public function __construct() {
    // don’t display the ‘Add these Contacts to Group’ button
    $this->_add2groupSupported = FALSE;

    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $this->loggingDB = $dsn['database'];

    // used for redirect back to contact summary
    $this->cid = CRM_Utils_Request::retrieve('cid', 'Integer');

    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $sourceID = CRM_Utils_Array::key('Activity Source', $activityContacts);
    $assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
    $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);

    /*
     * NYSS #7893 
     * _bracketInfo = array of tables used to look up bracketed information on change log summary
     *    array (
     *      'bracket_field'  => the field containing the information to be included
     *      'entity_field'   => used to create "WHERE <entity_field> = <log_id>"
     *      'entity_table'   => table to query instead of the original log table
     *      'bracket_lookup' => if populated, bracket_field is used as the desired key from this array
     *    )
     */
    $this->_bracketInfo = 
      array(
        'log_civicrm_note' =>
          array(
            'bracket_field'  => 'subject',
          ),
        'log_civicrm_note_comment' =>
          array(
            'entity_field'   => 'entity_id',
            'entity_table'   => 'log_civicrm_note',
            'bracket_field'  => 'subject',
          ),
        'log_civicrm_group_contact' =>
          array(
            'entity_field'   => 'group_id',
            'entity_table'   => 'log_civicrm_group',
            'bracket_field'  => 'title',
          ),
        'log_civicrm_entity_tag' =>
          array(
            'entity_field'   => 'tag_id',
            'entity_table'   => 'log_civicrm_tag',
            'bracket_field'  => 'name',
          ),
        'log_civicrm_relationship' =>
          array(
            'entity_field'   => 'relationship_type_id',
            'entity_table'   => 'log_civicrm_relationship_type',
            'bracket_field'  => 'label_a_b',
          ),
        'log_civicrm_activity' =>
          array(
            'bracket_field'  => 'activity_type_id',
            'bracket_lookup' => CRM_Core_PseudoConstant::activityType(TRUE, TRUE, FALSE, 'label', TRUE),
          ),
        'log_civicrm_activity_contact' =>
          array(
            'entity_field'   => 'activity_id',
            'entity_table'   => 'log_civicrm_activity',
            'bracket_field'  => 'activity_type_id',
            'bracket_lookup' => CRM_Core_PseudoConstant::activityType(TRUE, TRUE, FALSE, 'label', TRUE),
          ),
        'log_civicrm_case' =>
          array(
            'bracket_field'  => 'case_type_id',
            'bracket_lookup' => CRM_Case_PseudoConstant::caseType('title', FALSE),
          ),
        'log_civicrm_case_contact' =>
          array(
            'entity_field'   => 'case_id',
            'entity_table'   => 'log_civicrm_case',
            'bracket_field'  => 'case_type_id',
            'bracket_lookup' => CRM_Case_PseudoConstant::caseType('title', FALSE),
          ),
      );

    /* NYSS #7893 this should now be obsolete.  Left in place for any other classes that extend ReportSummary */
    $this->_logTables = array(
      'log_civicrm_contact' => array(
        'fk' => 'id',
      ),
      'log_civicrm_email' => array(
        'fk' => 'contact_id',
        'log_type' => 'Contact',
      ),
      'log_civicrm_phone' => array(
        'fk' => 'contact_id',
        'log_type' => 'Contact',
      ),
      'log_civicrm_address' => array(
        'fk' => 'contact_id',
        'log_type' => 'Contact',
      ),
      'log_civicrm_note' => array(
        'fk' => 'entity_id',
        'entity_table' => TRUE,
        'bracket_info' => array(
          'table' => 'log_civicrm_note',
          'column' => 'subject',
        ),
      ),
      'log_civicrm_note_comment' => array(
        'fk' => 'entity_id',
        'table_name' => 'log_civicrm_note',
        'joins' => array(
          'table' => 'log_civicrm_note',
          'join' => "entity_log_civireport.entity_id = fk_table.id AND entity_log_civireport.entity_table = 'civicrm_note'",
        ),
        'entity_table' => TRUE,
        'bracket_info' => array(
          'table' => 'log_civicrm_note',
          'column' => 'subject',
        ),
      ),
      'log_civicrm_group_contact' => array(
        'fk' => 'contact_id',
        'bracket_info' => array(
          'entity_column' => 'group_id',
          'table' => 'log_civicrm_group',
          'column' => 'title',
        ),
        'action_column' => 'status',
        'log_type' => 'Group',
      ),
      'log_civicrm_entity_tag' => array(
        'fk' => 'entity_id',
        'bracket_info' => array(
          'entity_column' => 'tag_id',
          'table' => 'log_civicrm_tag',
          'column' => 'name',
        ),
        'entity_table' => TRUE,
      ),
      'log_civicrm_relationship' => array(
        'fk' => 'contact_id_a',
        'bracket_info' => array(
          'entity_column' => 'relationship_type_id',
          'table' => 'log_civicrm_relationship_type',
          'column' => 'label_a_b',
        ),
      ),
      'log_civicrm_activity_contact' => array(
        'fk' => 'contact_id',
        'table_name' => 'log_civicrm_activity_contact',
        'log_type' => 'Activity',
        'field' => 'activity_id',
        'extra_joins' => array(
          'table' => 'log_civicrm_activity',
          'join' => 'extra_table.id = entity_log_civireport.activity_id',
        ),
      ),
      'log_civicrm_activity_for_assignee' => array(
        'fk' => 'contact_id',
        'table_name' => 'log_civicrm_activity',
        'joins' => array(
          'table' => 'log_civicrm_activity_contact',
          'join' => "entity_log_civireport.id = fk_table.activity_id AND fk_table.record_type_id = {$assigneeID}"
        ),
        'bracket_info' => array(
          'entity_column' => 'activity_type_id',
          'options' => CRM_Core_PseudoConstant::activityType(TRUE, TRUE, FALSE, 'label', TRUE)
        ),
        'log_type' => 'Activity',
      ),
      'log_civicrm_activity_for_source' => array(
        'fk' => 'contact_id',
        // reproduce fix from NYSS #3461
        'table_name' => 'log_civicrm_activity',
        'joins' => array(
          'table' => 'log_civicrm_activity_contact',
          'join' => "entity_log_civireport.id = fk_table.activity_id AND fk_table.record_type_id = {$sourceID}"
        ),
        'bracket_info' => array(
          'entity_column' => 'activity_type_id',
          'options' => CRM_Core_PseudoConstant::activityType(TRUE, TRUE, FALSE, 'label', TRUE)
        ),
        'log_type' => 'Activity',
      ),
      'log_civicrm_case' => array(
        'fk' => 'contact_id',
        'joins' => array(
          'table' => 'log_civicrm_case_contact',
          'join' => 'entity_log_civireport.id = fk_table.case_id',
        ),
        'bracket_info' => array(
          'entity_column' => 'case_type_id',
          'options' => CRM_Case_BAO_Case::buildOptions('case_type_id', 'search'),
        ),
      ),
    );

    $logging = new CRM_Logging_Schema();

    // build _logTables for contact custom tables
    $customTables = $logging->entityCustomDataLogTables('Contact');
    foreach ($customTables as $table) {
      $this->_logTables[$table] = array(
        'fk' => 'entity_id',
        'log_type' => 'Contact',
      );
    }

    // build _logTables for address custom tables
    $customTables = $logging->entityCustomDataLogTables('Address');
    foreach ($customTables as $table) {
      $this->_logTables[$table] = array(
        // For join of fk_table with contact table.
        'fk' => 'contact_id',
        'joins' => array(
          // fk_table
          'table' => 'log_civicrm_address',
          'join' => 'entity_log_civireport.entity_id = fk_table.id',
        ),
        'log_type' => 'Contact',
      );
    }

    // Allow log tables to be extended via report hooks.
    CRM_Report_BAO_Hook::singleton()->alterLogTables($this, $this->_logTables);

    parent::__construct();
  }

  public function groupBy() {
    $this->_groupBy = 'GROUP BY entity_log_civireport.log_conn_id, entity_log_civireport.log_user_id, EXTRACT(DAY_MICROSECOND FROM entity_log_civireport.log_date), entity_log_civireport.id';
  }

  /**
   * Adjust query for the activity_contact table.
   *
   * As this is just a join table the ID we REALLY care about is the activity id.
   *
   * @param string $tableName
   * @param string $tableKey
   * @param string $fieldName
   * @param string $field
   *
   * @return string
   */
  public function selectClause(&$tableName, $tableKey, &$fieldName, &$field) {
    if ($this->currentLogTable == 'log_civicrm_activity_contact' && $fieldName == 'id') {
      $alias = "{$tableName}_{$fieldName}";
      $select[] = "{$tableName}.activity_id as $alias";
      $this->_selectAliases[] = $alias;
      return "activity_id";
    }
    if ($fieldName == 'log_grouping') {
      if ($this->currentLogTable != 'log_civicrm_activity_contact') {
        return 1;
      }
      $mergeActivityID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Contact Merged');
      return " IF (entity_log_civireport.log_action = 'Insert' AND extra_table.activity_type_id = $mergeActivityID , GROUP_CONCAT(entity_log_civireport.contact_id), 1) ";
    }
  }

  public function where() {
    // reset where clause as its called multiple times, every time insert sql is built.
    $this->_whereClauses = array();

    parent::where();
    /* NYSS #7893 removed to reflect pre-filtered summary/detail entries */
    /*$this->_where .= " AND (entity_log_civireport.log_action != 'Initialization')";*/
  }

  public function postProcess() {
    $this->beginPostProcess();
    $rows = array();

    $tempColumns = "id int(10),  log_civicrm_entity_log_grouping varchar(32)";
    if (!empty($this->_params['fields']['log_action'])) {
      $tempColumns .= ", log_action varchar(64)";
    }
    $tempColumns .= ", log_type varchar(64), log_user_id int(10), log_date timestamp";
    if (!empty($this->_params['fields']['altered_contact'])) {
      $tempColumns .= ", altered_contact varchar(128)";
    }
    $tempColumns .= ", altered_contact_id int(10), log_conn_id varchar(17), is_deleted tinyint(4)";
    if (!empty($this->_params['fields']['display_name'])) {
      $tempColumns .= ", display_name varchar(128)";
    }

    // temp table to hold all altered contact-ids
    $sql = "CREATE TEMPORARY TABLE civicrm_temp_civireport_logsummary ( {$tempColumns} ) ENGINE=HEAP";
    CRM_Core_DAO::executeQuery($sql);

    $logTypes = CRM_Utils_Array::value('log_type_value', $this->_params);
    unset($this->_params['log_type_value']);
    if (empty($logTypes)) {
      foreach (array_keys($this->_logTables) as $table) {
        $type = $this->getLogType($table);
        $logTypes[$type] = $type;
      }
    }

    $logTypeTableClause = '(1)';
    if ($logTypeTableValue = CRM_Utils_Array::value("log_type_table_value", $this->_params)) {
      $logTypeTableClause = $this->whereClause($this->_columns['log_civicrm_entity']['filters']['log_type_table'],
        $this->_params['log_type_table_op'], $logTypeTableValue, NULL, NULL);
      unset($this->_params['log_type_table_value']);
    }

    foreach ($this->_logTables as $entity => $detail) {
      if ((in_array($this->getLogType($entity), $logTypes) &&
          CRM_Utils_Array::value('log_type_op', $this->_params) == 'in') ||
        (!in_array($this->getLogType($entity), $logTypes) &&
          CRM_Utils_Array::value('log_type_op', $this->_params) == 'notin')
      ) {
        $this->currentLogTable = $entity;
        $sql = $this->buildQuery(FALSE);
        $sql = str_replace("entity_log_civireport.log_type as", "'{$entity}' as", $sql);
        $sql = "INSERT IGNORE INTO civicrm_temp_civireport_logsummary {$sql}";
        CRM_Core_DAO::executeQuery($sql);
      }
    }

    $this->currentLogTable = '';

    // add computed log_type column so that we can do a group by after that, which will help
    // alterDisplay() counts sync with pager counts
    $sql = "SELECT DISTINCT log_type FROM civicrm_temp_civireport_logsummary";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $replaceWith = array();
    while ($dao->fetch()) {
      $type = $this->getLogType($dao->log_type);
      if (!array_key_exists($type, $replaceWith)) {
        $replaceWith[$type] = array();
      }
      $replaceWith[$type][] = $dao->log_type;
    }
    foreach ($replaceWith as $type => $tables) {
      if (!empty($tables)) {
        $replaceWith[$type] = implode("','", $tables);
      }
    }

    $sql = "ALTER TABLE civicrm_temp_civireport_logsummary ADD COLUMN log_civicrm_entity_log_type_label varchar(64)";
    CRM_Core_DAO::executeQuery($sql);
    foreach ($replaceWith as $type => $in) {
      $sql = "UPDATE civicrm_temp_civireport_logsummary SET log_civicrm_entity_log_type_label='{$type}', log_date=log_date WHERE log_type IN('$in')";
      CRM_Core_DAO::executeQuery($sql);
    }

    // note the group by columns are same as that used in alterDisplay as $newRows - $key
    $this->limit();
    $sql = "{$this->_select}
FROM civicrm_temp_civireport_logsummary entity_log_civireport
WHERE {$logTypeTableClause}
GROUP BY log_civicrm_entity_log_date, log_civicrm_entity_log_type_label, log_civicrm_entity_log_conn_id, log_civicrm_entity_log_user_id, log_civicrm_entity_altered_contact_id, log_civicrm_entity_log_grouping
ORDER BY log_civicrm_entity_log_date DESC {$this->_limit}";
    $sql = str_replace('modified_contact_civireport.display_name', 'entity_log_civireport.altered_contact', $sql);
    $sql = str_replace('modified_contact_civireport.id', 'entity_log_civireport.altered_contact_id', $sql);
    $sql = str_replace(array(
      'modified_contact_civireport.',
      'altered_by_contact_civireport.',
    ), 'entity_log_civireport.', $sql);
    $this->buildRows($sql, $rows);

    // format result set.
    $this->formatDisplay($rows);

    // assign variables to templates
    $this->doTemplateAssignment($rows);

    // do print / pdf / instance stuff if needed
    $this->endPostProcess($rows);
  }

  /**
   * Get log type.
   *
   * @param string $entity
   *
   * @return string
   */
  public function getLogType($entity) {
    if (!empty($this->_logTables[$entity]['log_type'])) {
      return $this->_logTables[$entity]['log_type'];
    }
    $logType = ucfirst(substr($entity, strrpos($entity, '_') + 1));
    return $logType;
  }

/* obsolete with NYSS 7893 */
  /**
   * Get entity value.
   *
   * @param int $id
   * @param $entity
   * @param $logDate
   *
   * @return mixed|null|string
   */
/*
  public function getEntityValue($id, $entity, $logDate) {
    // NYSS 7893 new bracket info process 
    static $value_cache = array();

    // Initialize the return 
    $ret = array();
    $timerfull = CRM_Utils_DebugTimer::create('getEntityValue detail query');
    // get detail row(s) from nyss_changelog_detail 
    $sql = "SELECT log_id, log_table_name FROM nyss_changelog_detail WHERE log_change_seq = %1 GROUP BY log_id";
    $rows = CRM_Core_DAO::executeQuery($sql, array(1=>array($change_id,'Integer')));
    $timerfull->log("first query done:\n$sql");

    // cycle through each detail row 
    // for each detail row, use _bracketInfo to find the original entity 
    while ($rows->fetch()) {
      $timerkey = 'getEntityValue row='.$rows->log_table_name.'-'.$rows->log_id;
      $timer = CRM_Utils_DebugTimer::create($timerkey);
      // initialize the "found" value
      $bracketValue = NULL;

      // easy references 
      $id = $rows->log_id;
      $logTable = $rows->log_table_name;
      $logDate = CRM_Utils_Date::isoToMysql($change_date);
      
      // check for cache
      $cache_key = "{$logTable}_{$id}";
      if (array_key_exists($cache_key,$value_cache)) {
        error_log('using cache');
        $bracketValue = $value_cache[$cache_key];
      } else {
      
        // make sure an entry exists, and it contains (minimum) the 'bracket_field' key
        $this_table = CRM_Utils_Array::value($logTable, $this->_bracketInfo);
        if (is_array($this_table) && ($bracket_field = CRM_Utils_Array::value('bracket_field',$this_table))) { 
          // discover which method to use
          // 1. no lookup
          // 2. normal lookup
          // 3. pseudo lookup
          
          // default method #1
          $qtable = $logTable;
          if (array_key_exists('entity_field',$this_table) && array_key_exists('entity_table',$this_table)) {
            $qfield = CRM_Utils_Array::value('entity_field',$this_table);
          } else {
            $qfield = $bracket_field;
          }
          $sql = "SELECT `{$qfield}` FROM `{$this->loggingDB}`.`{$qtable}` " .
                 "WHERE `id` = %2 AND `log_date` <= %1 ORDER BY `log_date` DESC LIMIT 1;";
          $params =array(1 => array($logDate,'Timestamp'), 2 => array($id, 'Integer'));
          error_log("running for row sql: $sql\nparams: " .str_replace(array("\n",' '),'', var_export($params,1)));
          $bracketValue = CRM_Core_DAO::singleValueQuery($sql, $params);
          
          // at this point, $bracketValue holds:
          // method 1: the final value
          // method 2: the key to a second lookup for the final value
          // method 3: the key to a pseudo lookup for the final value
          if (array_key_exists('entity_field',$this_table) && array_key_exists('entity_table',$this_table)) {
            // using method 2, do the second lookup
            $qtable = CRM_Utils_Array::value('entity_table',$this_table);
            $qfield = $bracket_field;
            $sql = "SELECT `{$qfield}` FROM `{$this->loggingDB}`.`{$qtable}` " .
                   "WHERE `id` = %2 AND `log_date` <= %1 ORDER BY `log_date` DESC LIMIT 1;";
            $params =array(1 => array($logDate,'Timestamp'), 2 => array($bracketValue, 'Integer'));
          error_log("running second row sql: $sql\nparams: " .str_replace(array("\n",' '),'', var_export($params,1)));
            $bracketValue = CRM_Core_DAO::singleValueQuery($sql, $params);
          }
          
          // special formatting of entityID for "Case" objects, since it uses a separator 
          if ($logTable == 'log_civicrm_case' || $logTable == 'log_civicrm_case_contact') {
            $bracketValue = explode(CRM_Case_BAO_Case::VALUE_SEPARATOR, $bracketValue);
            $bracketValue = CRM_Utils_Array::value(1, $bracketValue);
          }
          
        }
        
        // and finally, method #3, pseudo lookup
        if ($bracketValue && ($lookup = CRM_Utils_Array::value('bracket_lookup',$this_table))) {
          $bracketValue = CRM_Utils_Array::value($bracketValue, $lookup);
        }
  
        if ($bracketValue) {
          if (!in_array($bracketValue,$ret)) { $ret[] = $bracketValue; }
          $key = "{$logTable}_{$id}";
          $value_cache[$key] = $bracketValue;
        }
      }
      $timer->log('row done');
    }
    
    // convert the return from an array to a string, or NULL if nothing was found 
    $ret = count($ret) ? implode(',',$ret) : NULL;
    $timerfull->log('returning');
    return $ret;
  } */

  /**
   * Get entity action.
   *
   * @param int $id
   * @param int $connId
   * @param $entity
   * @param $oldAction
   *
   * @return null|string
   */
  public function getEntityAction($id, $connId, $entity, $oldAction) {
    if (!empty($this->_logTables[$entity]['action_column'])) {
      $sql = "select {$this->_logTables[$entity]['action_column']} from `{$this->loggingDB}`.{$entity} where id = %1 AND log_conn_id = %2";
      $newAction = CRM_Core_DAO::singleValueQuery($sql, array(
        1 => array($id, 'Integer'),
        2 => array($connId, 'String'),
      ));

      switch ($entity) {
        case 'log_civicrm_group_contact':
          if ($oldAction !== 'Update') {
            $newAction = $oldAction;
          }
          if ($oldAction == 'Insert') {
            $newAction = 'Added';
          }
          break;
      }
      return $newAction;
    }
    return NULL;
  }

}
