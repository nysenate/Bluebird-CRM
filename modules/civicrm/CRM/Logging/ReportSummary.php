<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Logging_ReportSummary extends CRM_Report_Form {
  protected $cid;

  protected $_logTables =
    array(
          'log_civicrm_contact' =>
          array( 'fk'  => 'id',
                 ),
          'log_civicrm_email' =>
          array( 'fk'  => 'contact_id',
                 'log_type' => 'Contact',
                 ),
          'log_civicrm_phone' =>
          array( 'fk'  => 'contact_id',
                 'log_type' => 'Contact',
                 ),
          'log_civicrm_address' =>
          array( 'fk'  => 'contact_id',
                 'log_type' => 'Contact',
                 ),
          'log_civicrm_note' =>
          array( 'fk'  => 'entity_id',
                 'entity_table' => true,
                 'dao' => 'CRM_Core_DAO_Note',
                 'dao_column'  => 'subject',
                 ),
          'log_civicrm_group_contact' =>
          array( 'fk'  => 'contact_id',
                 'dao' => 'CRM_Contact_DAO_Group',
                 'dao_column'    => 'title',
                 'entity_column' => 'group_id',
                 'action_column' => 'status',
                 'log_type'      => 'Group',
                 ),
          'log_civicrm_entity_tag' =>
          array( 'fk'  => 'entity_id',
                 'dao' => 'CRM_Core_DAO_Tag',
                 'dao_column'    => 'name',
                 'entity_column' => 'tag_id',
                 'entity_table'  => true
                 ),
          'log_civicrm_relationship' =>
          array( 'fk'  => 'contact_id_a',
                 'entity_column' => 'relationship_type_id',
                 'dao' => 'CRM_Contact_DAO_RelationshipType',
                 'dao_column' => 'label_a_b',
                 ),
           );

  protected $loggingDB; function __construct() {
    // don’t display the ‘Add these Contacts to Group’ button
    $this->_add2groupSupported = FALSE;

    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $this->loggingDB = $dsn['database'];

    // used for redirect back to contact summary
    $this->cid = CRM_Utils_Request::retrieve('cid', 'Integer', CRM_Core_DAO::$_nullObject);

    parent::__construct();
  }

  function groupBy() {
    $this->_groupBy = 'GROUP BY log_conn_id, log_user_id, EXTRACT(DAY_MICROSECOND FROM log_date)';
  }

  function orderBy() {
    $this->_orderBy = 'ORDER BY log_date DESC';
  }

  function select() {
    $select = array();
    $this->_columnHeaders = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) or CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display'] = CRM_Utils_Array::value('no_display', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
          }
        }
      }
    }
    $this->_select = 'SELECT ' . implode(', ', $select) . ' ';
  }

  function where() {
    parent::where();

    list($offset, $rowCount) = $this->limit();
    $this->_limit = NULL;

    $tempClause    = ($offset && $rowCount) ? "AND temp.id BETWEEN $offset AND $rowCount" : null;
    $this->_where .= " AND (log_action != 'Initialization') {$tempClause}";
  }

  function postProcess() {
    $this->beginPostProcess();
    $rows = array();
    // temp table to hold all altered contact-ids
    $sql = "
CREATE TEMPORARY TABLE
       civicrm_temp_civireport_logsummary ( id int PRIMARY KEY AUTO_INCREMENT,
                                            contact_id int, UNIQUE UI_id (contact_id) ) ENGINE=HEAP";
    CRM_Core_DAO::executeQuery($sql);

    $logDateClause = $this->dateClause('log_date',
                                       CRM_Utils_Array::value("log_date_relative",  $this->_params),
                                       CRM_Utils_Array::value("log_date_from",      $this->_params),
                                       CRM_Utils_Array::value("log_date_to",        $this->_params),
                                       CRM_Utils_Type::T_DATE,
                                       CRM_Utils_Array::value("log_date_from_time", $this->_params),
                                       CRM_Utils_Array::value("log_date_to_time",   $this->_params));
    $logDateClause = $logDateClause ? "AND {$logDateClause}" : null;

    list($offset, $rowCount) = $this->limit();
    $this->_limit = NULL;

    foreach ( $this->_logTables as $entity => $detail ) {
      $clause = CRM_Utils_Array::value('entity_table', $detail);
      $clause = $clause ? "entity_table = 'civicrm_contact' AND" : null;
      $sql    = "
INSERT IGNORE INTO civicrm_temp_civireport_logsummary ( contact_id )
  SELECT DISTINCT {$detail['fk']} FROM `{$this->loggingDB}`.{$entity}
  WHERE {$clause} log_action != 'Initialization' {$logDateClause} LIMIT {$rowCount}";
      CRM_Core_DAO::executeQuery($sql);
    }

    $logTypes = CRM_Utils_Array::value('log_type_value', $this->_params);
    unset($this->_params['log_type_value']);
    if ( empty($logTypes) ) {
      foreach ( array_keys($this->_logTables) as  $table ) {
        $type = $this->getLogType($table);
        $logTypes[$type] = $type;
      }
    }

    foreach ( $this->_logTables as $entity => $detail ) {
      if ((in_array($this->getLogType($entity), $logTypes) &&
           CRM_Utils_Array::value('log_type_op', $this->_params) == 'in') ||
          (!in_array($this->getLogType($entity), $logTypes) &&
           CRM_Utils_Array::value('log_type_op', $this->_params) == 'notin')) {
        $this->from( $entity );
        $sql = $this->buildQuery(false);
        $sql = str_replace("entity_log_civireport.log_type as", "'{$entity}' as", $sql);
        $this->buildRows($sql, $rows);
      }
    }

    // format result set.
    $this->formatDisplay($rows);

    // assign variables to templates
    $this->doTemplateAssignment($rows);

    // do print / pdf / instance stuff if needed
    $this->endPostProcess($rows);
  }

  function getLogType( $entity ) {
    if (CRM_Utils_Array::value('log_type', $this->_logTables[$entity])) {
      return $this->_logTables[$entity]['log_type'];
    }
    $logType = ucfirst(substr($entity, strrpos($entity, '_') + 1));
    return $logType;
  }

  function getEntityValue( $id, $entity ) {
    if (CRM_Utils_Array::value('dao', $this->_logTables[$entity])) {
      if (CRM_Utils_Array::value('entity_column', $this->_logTables[$entity])) {
        $sql = "select {$this->_logTables[$entity]['entity_column']} from `{$this->loggingDB}`.{$entity} where id = %1";
        $entityID = CRM_Core_DAO::singleValueQuery($sql, array(1 => array($id, 'Integer')));
      } else {
        $entityID = $id;
      }
      return CRM_Core_DAO::getFieldValue($this->_logTables[$entity]['dao'], $entityID, $this->_logTables[$entity]['dao_column']);
    }
    return null;
  }

  function getEntityAction( $id, $connId, $entity ) {
    if (CRM_Utils_Array::value('action_column', $this->_logTables[$entity])) {
      $sql = "select {$this->_logTables[$entity]['action_column']} from `{$this->loggingDB}`.{$entity} where id = %1 AND log_conn_id = %2";
      return CRM_Core_DAO::singleValueQuery($sql, array(1 => array($id, 'Integer'), 2 => array($connId, 'Integer')));
    }
    return null;
  }
}
