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

/**
 * BAO object for crm_log table
 */
class CRM_Core_BAO_Log extends CRM_Core_DAO_Log {
  static $_processed = NULL;

  static
  function &lastModified($id, $table = 'civicrm_contact') {

    $log = new CRM_Core_DAO_Log();

    $log->entity_table = $table;
    $log->entity_id = $id;
    $log->orderBy('modified_date desc');
    $log->limit(1);
    $result = CRM_Core_DAO::$_nullObject;
    if ($log->find(TRUE)) {
      list($displayName, $contactImage) = CRM_Contact_BAO_Contact::getDisplayAndImage($log->modified_id);
      $result = array(
        'id' => $log->modified_id,
        'name' => $displayName,
        'image' => $contactImage,
        'date' => $log->modified_date,
      );
    }
    return $result;
  }

  /**
   * add log to civicrm_log table
   *
   * @param array $params  array of name-value pairs of log table.
   *
   * @static
   */
  static
  function add(&$params) {
        
    //NYSS - LCD #2365
    $session = & CRM_Core_Session::singleton();
    $jobID = $session->get('jobID');
    if ( $jobID ) {
      $params['data'] .= ', [Job: '.$jobID.']';
    }
    //NYSS end

    $log = new CRM_Core_DAO_Log();
    $log->copyValues($params);
    $log->save();
  }

  static
  function register($contactID,
    $tableName,
    $tableID,
    $userID = NULL
  ) {
    if (!self::$_processed) {
      self::$_processed = array();
    }

    if (!$userID) {
      $session = CRM_Core_Session::singleton();
      $userID = $session->get('userID');
    }

    if (!$userID) {
      $userID = $contactID;
    }

    if (!$userID) {
      return;
    }

    $log = new CRM_Core_DAO_Log();
    $log->id = NULL;

    if (isset(self::$_processed[$contactID])) {
      if (isset(self::$_processed[$contactID][$userID])) {
        $log->id = self::$_processed[$contactID][$userID];
      }
      self::$_processed[$contactID][$userID] = 1;
    }
    else {
      self::$_processed[$contactID] = array($userID => 1);
    }

    $logData = "$tableName,$tableID";

    //NYSS - LCD #2365
    $session = & CRM_Core_Session::singleton();
    $jobID = $session->get('jobID');
    if ( $jobID ) {
      $logData .= ', [Job: '.$jobID.']';
    }
    //NYSS end

    if (!$log->id) {
      $log->entity_table  = 'civicrm_contact';
      $log->entity_id     = $contactID;
      $log->modified_id   = $userID;
      $log->modified_date = date("YmdHis");
      $log->data          = $logData;
      $log->save();
    }
    else {
      $query = "
UPDATE civicrm_log
   SET data = concat( data, ':$logData' )
 WHERE id = {$log->id}
";
      CRM_Core_DAO::executeQuery($query);
    }

    self::$_processed[$contactID][$userID] = $log->id;
  }

  /**
   * Function to get log record count for a Contact
   *
   * @param int $contactId Contact ID
   *
   * @return int count of log records
   * @access public
   * @static
   */
  static
  function getContactLogCount($contactID) {
    //NYSS 4574 include activity logs in count
    $query = "SELECT count(*) FROM civicrm_log
             WHERE civicrm_log.entity_table = 'civicrm_contact' AND civicrm_log.entity_id = {$contactID}";
    $contact_log_count  = CRM_Core_DAO::singleValueQuery( $query );

    require_once 'api/v2/ActivityContact.php';
    $params = array('contact_id' => $contactID);
    $activities = civicrm_activity_contact_get($params);

    $activityIDs = array();
    $activitySubject = array();
    $bulkEmailID = CRM_Core_OptionGroup::getValue( 'activity_type', 'Bulk Email', 'name' );

    foreach ( $activities['result'] as $activityID => $activityDetail ) {
      if ( $activityDetail['activity_type_id'] != $bulkEmailID ) {
          $activityIDs[] = $activityID;
          $activitySubject[$activityID] = $activityDetail['subject'];
      }
    }
    $activityIDlist = implode(',', $activityIDs);
    $activity_log_count = 0;

    if ( !empty($activityIDlist) ) {
      $query = "SELECT count(*) as aCount
                 FROM civicrm_log
                 WHERE entity_table = 'civicrm_activity' AND entity_id IN ($activityIDlist);";
      $activity_log_count = CRM_Core_DAO::singleValueQuery( $query );
    }

    $total_log_count = 0;
    $total_log_count = $contact_log_count + $activity_log_count;
    return $total_log_count;
  }

  //NYSS 5173 calculate log records using enhanced logging
  static function getEnhancedContactLogCount( $contactID ) {

    $rptSummary = new CRM_Report_Form_Contact_LoggingSummary();
    //CRM_Core_Error::debug_var('rptSummary',$rptSummary);

    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $loggingDB = $dsn['database'];

    $bbconfig = get_bluebird_instance_config();
    $civiDB   = $bbconfig['db.civicrm.prefix'].$bbconfig['db.basename'];

    CRM_Core_DAO::executeQuery('DROP TABLE IF EXISTS civicrm_temp_logcount');
    $sql = "
      CREATE TEMPORARY TABLE civicrm_temp_logcount (
        id int(10),
        log_type varchar(64),
        log_user_id int(10),
        log_date timestamp,
        altered_contact varchar(128),
        altered_contact_id int(10),
        log_conn_id int(11),
        log_action varchar(64),
        is_deleted tinyint(4),
        display_name varchar(128),
        INDEX (id)) ENGINE = MEMORY";
    CRM_Core_DAO::executeQuery($sql);

    $logTables = $rptSummary->getVar('_logTables');

    foreach ( $logTables as $entity => $detail ) {
      //CRM_Core_Error::debug_var('entity',$entity);
      //CRM_Core_Error::debug_var('detail',$detail);

      $rptSummary->from($entity);
      $from = $rptSummary->_from;
      //CRM_Core_Error::debug_var('from',$rptSummary->_from);

      $sql = "
        SELECT entity_log_civireport.id as log_civicrm_entity_id, entity_log_civireport.log_type as log_civicrm_entity_log_type, entity_log_civireport.log_user_id as log_civicrm_entity_log_user_id, entity_log_civireport.log_date as log_civicrm_entity_log_date, modified_contact_civireport.display_name as log_civicrm_entity_altered_contact, modified_contact_civireport.id as log_civicrm_entity_altered_contact_id, entity_log_civireport.log_conn_id as log_civicrm_entity_log_conn_id, entity_log_civireport.log_action as log_civicrm_entity_log_action, modified_contact_civireport.is_deleted as log_civicrm_entity_is_deleted, altered_by_contact_civireport.display_name as altered_by_contact_display_name
        {$from}
        WHERE ( modified_contact_civireport.id = {$contactID} )
          AND (entity_log_civireport.log_action != 'Initialization')
        GROUP BY log_civicrm_entity_id, entity_log_civireport.log_conn_id, entity_log_civireport.log_user_id, EXTRACT(DAY_MICROSECOND FROM entity_log_civireport.log_date), entity_log_civireport.id
      ";
      $sql = str_replace("entity_log_civireport.log_type as", "'{$entity}' as", $sql);
      //NYSS 6713 temp hack to avoid duplicate log records for the same bulk email activity
      if ( $entity == 'log_civicrm_activity_for_target' ) {
        //$sql = str_replace("DAY_MICROSECOND", "DAY_HOUR", $sql);
        $sql = str_replace("EXTRACT(DAY_MICROSECOND FROM entity_log_civireport.log_date), ", "", $sql);
        $sql = str_replace("entity_log_civireport.log_conn_id, ", "", $sql);
      }
      $sql = "INSERT IGNORE INTO civicrm_temp_logcount {$sql}";
      //CRM_Core_Error::debug_var('sql',$sql);
      CRM_Core_DAO::executeQuery($sql);
    }

    $sql = "
      SELECT log_type, log_date, log_conn_id, log_action
      FROM civicrm_temp_logcount
      ORDER BY log_date DESC;
    ";
    $logs = CRM_Core_DAO::executeQuery($sql);

    $logRows = array();
    while ( $logs->fetch() ) {
      $logRows[] = array(
        'log_civicrm_entity_log_type' => $logs->log_type,
        'log_civicrm_entity_log_date' => $logs->log_date,
        'log_civicrm_entity_log_conn_id' => $logs->log_conn_id,
        'log_civicrm_entity_log_action' => $logs->log_action,
      );
    }

    CRM_Logging_ReportSummary::_combineContactRows($logRows, TRUE);
    //CRM_Core_Error::debug_var('$logRows',$logRows);
    //CRM_Core_Error::debug_var('$counts',$counts);

    $totalCount = count($logRows);

    return $totalCount;
  }

  /**
   * Function for find out whether to use logging schema entries for contact
   * summary, instead of normal log entries.
   *
   * @return int report id of Contact Logging Report (Summary) / false
   * @access public
   * @static
   */
  static
  function useLoggingReport() {
    // first check if logging is enabled
    $config = CRM_Core_Config::singleton();
    if (!$config->logging) {
      return FALSE;
    }

    $loggingSchema = new CRM_Logging_Schema();

    if ($loggingSchema->isEnabled()) {
      $params = array('report_id' => 'logging/contact/summary');
      $instance = array();
      CRM_Report_BAO_Instance::retrieve($params, $instance);

      if (!empty($instance) &&
        (!CRM_Utils_Array::value('permission', $instance) ||
          (CRM_Utils_Array::value('permission', $instance) && CRM_Core_Permission::check($instance['permission']))
        )
      ) {
        return $instance['id'];
      }
    }

    return FALSE;
  }
}
