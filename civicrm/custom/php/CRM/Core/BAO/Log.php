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
        
        $log = new CRM_Core_DAO_Log( );
        
        $log->entity_table = $table;
        $log->entity_id    = $id;
        $log->orderBy( 'modified_date desc' );
        $log->limit( 1 );
        $result = CRM_Core_DAO::$_nullObject;
    if ($log->find(TRUE)) {
            list( $displayName, $contactImage ) = CRM_Contact_BAO_Contact::getDisplayAndImage( $log->modified_id );
      $result = array(
        'id' => $log->modified_id,
                             'name'  => $displayName,
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

        $log = new CRM_Core_DAO_Log( );
        $log->copyValues($params);
        $log->save();
    }

  static
  function register($contactID,
                              $tableName,
                              $tableID  ,
    $userID = NULL
  ) {
        if ( ! self::$_processed ) {
            self::$_processed = array( );
        }

        if ( ! $userID ) {
            $session = CRM_Core_Session::singleton( );
            $userID  =  $session->get( 'userID' );
        }

        if ( ! $userID ) {
            $userID  =  $contactID;
        }
        
        if ( ! $userID ) {
            return;
        }

        $log = new CRM_Core_DAO_Log( );
    $log->id = NULL;

        if ( isset( self::$_processed[$contactID] ) ) {
            if ( isset( self::$_processed[$contactID][$userID] ) ) {
                $log->id = self::$_processed[$contactID][$userID];
            }
            self::$_processed[$contactID][$userID] = 1;
    }
    else {
            self::$_processed[$contactID] = array( $userID => 1 );
        }

        $logData = "$tableName,$tableID";

        //NYSS - LCD #2365
        $session = & CRM_Core_Session::singleton();
        $jobID = $session->get('jobID');
        if ( $jobID ) {
            $logData .= ', [Job: '.$jobID.']';
        }
        //NYSS end

        if ( ! $log->id ) {
            $log->entity_table  = 'civicrm_contact';
            $log->entity_id     = $contactID;
            $log->modified_id   = $userID;
            $log->modified_date = date( "YmdHis" );
            $log->data          = $logData;
            $log->save( );
    }
    else {
            $query = "
UPDATE civicrm_log
   SET data = concat( data, ':$logData' )
 WHERE id = {$log->id}
";
            CRM_Core_DAO::executeQuery( $query );
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

    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $loggingDB = $dsn['database'];

    $bbconfig = get_bluebird_instance_config();
    $civiDB   = $bbconfig['db.civicrm.prefix'].$bbconfig['db.basename'];

    $counts = array();
    $tblKey = array(
      'civicrm_contact' =>
      array(
        'id'     => 'id',
        'group'  => 'log_conn_id, log_user_id, EXTRACT(DAY_MINUTE FROM log_date)'
      ),
      'civicrm_phone' =>
      array(
        'id' => 'contact_id',
      ),
      'civicrm_email' =>
      array(
        'id' => 'contact_id',
      ),
      'civicrm_im' =>
      array(
        'id' => 'contact_id',
      ),
      /*'civicrm_website' =>
      array(
        'id' => 'contact_id',
      ),*/
      'civicrm_address' =>
      array(
        'id' => 'contact_id',
      ),
      'civicrm_entity_tag' =>
      array(
        'id'     => 'entity_id',
        'where'  => 'entity_table = "civicrm_contact"'
      ),
      'civicrm_note' =>
      array(
        'id'     => 'entity_id',
        'where'  => 'entity_table = "civicrm_contact"'
      ),
      'civicrm_comments' =>
      array(
        'table'  => 'civicrm_note',
        'id'     => 'n.entity_id',
        'join'   => "JOIN $loggingDB.log_civicrm_note n
                     ON civicrm_comments.entity_id = n.id
                     AND n.log_action = 'Insert'",
        'where'  => 'civicrm_comments.entity_table = "civicrm_note"'
      ),
      'civicrm_group_contact' =>
      array(
        'id'     => 'contact_id',
        'noinit' => TRUE,
        'join'   => "JOIN (
                       SELECT id, name, title, is_hidden
                       FROM $loggingDB.log_civicrm_group
                       GROUP BY id ) cg
                     ON civicrm_group_contact.group_id = cg.id",
        'where'  => "cg.is_hidden != 1
                     AND log_action != 'Initialization'",
      ),
      'civicrm_relationship_a' =>
      array(
        'id'     => 'contact_id_a',
        'table'  => 'civicrm_relationship',
      ),
      'civicrm_relationship_b' =>
      array(
        'id'     => 'contact_id_b',
        'table'  => 'civicrm_relationship',
      ),
    );

    $logRows = array();
    foreach ( $tblKey as $tbl => $details ) {
      $alias = $tbl;
      if ( isset($details['table']) && $details['table'] ) {
        $tbl = $details['table'];
      }

      $sql = "SELECT {$alias}.id, 'log_{$tbl}' as log_type, {$alias}.log_conn_id, {$alias}.log_date
              FROM $loggingDB.log_{$tbl} $alias
              {$details['join']}
              WHERE {$details['id']} = $contactID";
      if ( !isset($details['noinit']) || !$details['noinit'] ) {
        $sql .= " AND ($alias.log_action != 'Initialization') ";
      }
      if ( isset($details['where']) && $details['where'] ) {
        $sql .= " AND {$details['where']} ";
      }
      if ( isset($details['group']) && $details['group'] ) {
        $sql .= " GROUP BY {$details['group']} ";

        //now wrap in a subquery to get total count
        //$sql = "SELECT count(*) FROM ( $sql ) tmp";
      }
      //CRM_Core_Error::debug_var('sql',$sql);
      $logs = CRM_Core_DAO::executeQuery($sql);

      while ( $logs->fetch() ) {
        $logRows[] = array(
          'log_civicrm_entity_log_type' => $logs->log_type,
          'log_civicrm_entity_log_date' => $logs->log_date,
          'log_civicrm_entity_log_conn_id' => $logs->log_conn_id,
        );
      }

      $sqlCount = "SELECT count(*) FROM ( $sql ) tmp";
      $counts[$alias] = CRM_Core_DAO::singleValueQuery($sqlCount);
    }

    CRM_Logging_ReportSummary::_combineContactRows($logRows);
    //CRM_Core_Error::debug_var('$logRows',$logRows);
    //CRM_Core_Error::debug_var('$counts',$counts);

    /*$totalCount = 0;
    foreach ( $counts as $count ) {
      if ( $count ) {
        $totalCount += $count;
      }
    }*/

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
         if ( ! $config->logging ) {
      return FALSE;
         }

         $loggingSchema = new CRM_Logging_Schema( );
         
         if ( $loggingSchema->isEnabled() ) {
             $params   = array( 'report_id' => 'logging/contact/summary' );
             $instance = array( );
             CRM_Report_BAO_Instance::retrieve($params, $instance);
             
             if ( !empty($instance) &&
                  ( !CRM_Utils_Array::value('permission', $instance) ||
          (CRM_Utils_Array::value('permission', $instance) && CRM_Core_Permission::check($instance['permission']))
        )
      ) {
                 return $instance['id'];
             }
    }
             
    return FALSE;
         }
     }  
