<?php

/*
 * NYSS functions related to logging
 */
class CRM_NYSS_BAO_Log {
  /**
   * NYSS pull changelog count via ajax
   * Function to get the count of the change log.
   */
  static function getTabCount( ) {
    $contactId = CRM_Utils_Array::value( 'contactId', $_POST, NULL );
    //CRM_Core_Error::debug_var('getTabCount contactId', $contactId, TRUE, TRUE, 'logCount');

    if ( $contactId && CRM_Core_BAO_Log::useLoggingReport() ) {
      //NYSS 6719 call count function directly
      //CRM_Core_Error::debug_var('getTabCount $_POST', $_POST, TRUE, TRUE, 'logCount');
      echo self::getEnhancedContactLogCount( $contactId );
    }
    CRM_Utils_System::civiExit( );
  }

  /*
   * NYSS 5173 calculate log records using enhanced logging
   */
  static function getEnhancedContactLogCount( $contactID ) {
    $rptSummary = new CRM_Report_Form_Contact_LoggingSummary();
    //CRM_Core_Error::debug_var('rptSummary', $rptSummary, TRUE, TRUE, 'logCount');
    //CRM_Core_Error::debug_var('contactID', $contactID, TRUE, TRUE, 'logCount');

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
    //CRM_Core_Error::debug_var('sql', $sql, TRUE, TRUE, 'logCount');
    CRM_Core_DAO::executeQuery($sql);

    $logTables = $rptSummary->getVar('_logTables');

    foreach ( $logTables as $entity => $detail ) {
      //CRM_Core_Error::debug_var('entity', $entity, TRUE, TRUE, 'logCount');
      //CRM_Core_Error::debug_var('detail',$detail);

      $rptSummary->from($entity);
      $from = $rptSummary->_from;
      //CRM_Core_Error::debug_var('from', $rptSummary->_from, TRUE, TRUE, 'logCount');

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
}
