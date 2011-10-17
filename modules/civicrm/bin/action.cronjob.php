<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                               |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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

/*
 */

class CRM_Cron_Action {
    
    function __construct() 
    {
        // you can run this program either from an apache command, or from the cli
        if ( php_sapi_name( ) == "cli" ) {
            require_once ("cli.php");
            $cli = new civicrm_cli ( );
            //if it doesn't die, it's authenticated
        } else { 
            //from the webserver
            $this->initialize( );
          
            $config = CRM_Core_Config::singleton();
           
            // this does not return on failure
            CRM_Utils_System::authenticateScript( true );
            
            //log the execution time of script
            CRM_Core_Error::debug_log_message( 'action.cronjob.php' );
            
            // load bootstrap to call hooks
            require_once 'CRM/Utils/System.php';
            CRM_Utils_System::loadBootStrap(  );
        }
    }

    function initialize( ) {
        require_once '../civicrm.config.php';
        require_once 'CRM/Core/Config.php';

        $config = CRM_Core_Config::singleton();
    }

    public function run( $now = null )
    {
        require_once 'CRM/Utils/Time.php';
        $this->_now = $now ? CRM_Utils_Time::setTime( $now ) : CRM_Utils_Time::getTime( );

        require_once 'CRM/Core/BAO/ScheduleReminders.php';
        $mappings = CRM_Core_BAO_ScheduleReminders::getMapping( );

        foreach ( $mappings as $mappingID => $mapping ) {
            $this->buildRecipientContacts( $mappingID );

            $this->sendMailings( $mappingID );
        }
    }

    public function sendMailings( $mappingID ) {
        require_once 'CRM/Activity/BAO/Activity.php';
        require_once 'CRM/Contact/BAO/Contact.php';
        require_once 'CRM/Core/BAO/ActionLog.php';
        require_once 'CRM/Core/BAO/Domain.php';
        $domainValues     = CRM_Core_BAO_Domain::getNameAndEmail( );
        $fromEmailAddress = "$domainValues[0] <$domainValues[1]>";
        
        require_once 'CRM/Core/DAO/ActionMapping.php';
        $mapping = new CRM_Core_DAO_ActionMapping( );
        $mapping->id = $mappingID;
        $mapping->find( true );

        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );
        $actionSchedule->mapping_id = $mappingID;
        $actionSchedule->is_active = 1;
        $actionSchedule->find( false );

        $tokenFields = array( );
        $session = & CRM_Core_Session::singleton();

        while ( $actionSchedule->fetch( ) ) {
            $extraSelect = $extraJoin = $extraWhere = '';

            if ( $actionSchedule->record_activity ) {
                $activityTypeID   = CRM_Core_OptionGroup::getValue( 'activity_type', 
                                                                    'Reminder Sent', 'name' );
                $activityStatusID = CRM_Core_OptionGroup::getValue( 'activity_status', 
                                                                    'Completed', 'name' );
            }

            if ( $mapping->entity == 'civicrm_activity' ) {
                $tokenFields = array( 'activity_id', 'activity_type', 'subject', 'activity_date_time' );
                $extraSelect = ", ov.label as activity_type, e.id as activity_id";
                $extraJoin   = "INNER JOIN civicrm_option_group og ON og.name = 'activity_type'
INNER JOIN civicrm_option_value ov ON e.activity_type_id = ov.value AND ov.option_group_id = og.id";
                $extraWhere = "AND e.is_current_revision = 1 AND e.is_deleted = 0";
            }

            $query = "
SELECT reminder.id as reminderID, reminder.*, e.id as entityID, e.* {$extraSelect} 
FROM  civicrm_action_log reminder
INNER JOIN {$mapping->entity} e ON e.id = reminder.entity_id
{$extraJoin}
WHERE reminder.action_schedule_id = %1 AND reminder.action_date_time IS NULL
{$extraWhere}";
            $dao   = CRM_Core_DAO::executeQuery( $query,
                                                 array( 1 => array( $actionSchedule->id, 'Integer' ) ) );
            
            while ( $dao->fetch() ) {
                $entityTokenParams = array();
                foreach ( $tokenFields as $field ) {
                    $entityTokenParams['activity.' . $field] = $dao->$field;
                }

                $isError = 0; $errorMsg = '';
                $toEmail = CRM_Contact_BAO_Contact::getPrimaryEmail( $dao->contact_id );
                if ( $toEmail ) {
                    $result = CRM_Core_BAO_ScheduleReminders::sendReminder( $dao->contact_id,
                                                                            $toEmail,
                                                                            $actionSchedule->id,
                                                                            $fromEmailAddress,
                                                                            $entityTokenParams );
                    if ( ! $result || is_a( $result, 'PEAR_Error' ) ) {
                        // we could not send an email, for now we ignore, CRM-3406
                        $isError = 1;
                    }
                } else {
                    $isError  = 1;
                    $errorMsg = "Couldn\'t find recipient\'s email address.";
                }

                // update action log record
                $logParams = array( 'id'       => $dao->reminderID,
                                    'is_error' => $isError,
                                    'message'  => $errorMsg ? $errorMsg : "null",
                                    'action_date_time' => $this->_now,
                                    );
                CRM_Core_BAO_ActionLog::create( $logParams );
                
                // insert activity log record if needed
                if ( $actionSchedule->record_activity ) {
                    $activityParams = array( 'subject'            => $actionSchedule->title,
                                             'details'            => $actionSchedule->body_html,
                                             'source_contact_id'  => $session->get('userID') ? 
                                             $session->get('userID') : $dao->contact_id,
                                             'target_contact_id'  => $dao->contact_id,
                                             'activity_date_time' => date('YmdHis'),
                                             'status_id'          => $activityStatusID,
                                             'activity_type_id'   => $activityTypeID,
                                             'source_record_id'   => $dao->entityID
                                             );
                    $activity = CRM_Activity_BAO_Activity::create( $activityParams );
                }
            }
            $dao->free();
        }
    }
    
    public function buildRecipientContacts( $mappingID ) {
        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );
        $actionSchedule->mapping_id = $mappingID;
        $actionSchedule->is_active = 1;
        $actionSchedule->find( );

        while ( $actionSchedule->fetch( ) ) {
            require_once 'CRM/Core/DAO/ActionMapping.php';
            $mapping = new CRM_Core_DAO_ActionMapping( );
            $mapping->id = $mappingID;
            $mapping->find( true );

            $select = $join = $where = array( );

            $value  = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_value  );
            $value  = implode( ',', $value );

            $status = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_status );
            $status = implode( ',', $status );
        
            require_once 'CRM/Core/OptionGroup.php';
            $recipientOptions = CRM_Core_OptionGroup::values( $mapping->entity_recipient );

            $from = "{$mapping->entity} e";

            if ( $mapping->entity == 'civicrm_activity' ) {
                switch ( $recipientOptions[$actionSchedule->recipient] ) {
                case 'Activity Assignees':
                    $contactField = "r.assignee_contact_id";
                    $join[] = "INNER JOIN civicrm_activity_assignment r ON  r.activity_id = e.id";
                    break;
                case 'Activity Source':
                    $contactField = "e.source_contact_id";
                    break;
                case 'Activity Targets':
                    $contactField = "r.target_contact_id";
                    $join[] = "INNER JOIN civicrm_activity_target r ON  r.activity_id = e.id";
                    break;
                default:
                    break;
                }
                $select[] = "{$contactField} as contact_id";
                $select[] = "e.id as entity_id";
                $select[] = "'{$mapping->entity}' as entity_table";
                $select[] = "{$actionSchedule->id} as action_schedule_id";
                $reminderJoinClause   = "civicrm_action_log reminder ON reminder.contact_id = {$contactField} AND 
reminder.entity_id    = e.id AND 
reminder.entity_table = 'civicrm_activity' AND
reminder.action_schedule_id = %1";

                // build where clause
                if ( !empty($value) ) {
                    $where[]  = "e.activity_type_id IN ({$value})";
                }
                if ( !empty($status) ) {
                    $where[]  = "e.status_id IN ({$status})";
                }
                $where[] = " e.is_current_revision = 1 ";
                $where[] = " e.is_deleted = 0 ";
                
                $join[] = "INNER JOIN civicrm_contact c ON c.id = {$contactField}";
                $where[] = "c.is_deleted = 0";

                $startEvent = ( $actionSchedule->start_action_condition == 'before' ? "DATE_SUB" : "DATE_ADD" ) . 
                    "(e.activity_date_time, INTERVAL {$actionSchedule->start_action_offset} {$actionSchedule->start_action_unit})";
            }

            // ( now >= date_built_from_start_time )
            $startEventClause = "reminder.id IS NULL AND '{$this->_now}' >= {$startEvent}";

            // build final query
            $selectClause = "SELECT " . implode( ', ', $select );
            $fromClause   = "FROM $from";
            $joinClause   = !empty( $join ) ? implode( ' ', $join ) : '';
            $whereClause  = "WHERE " . implode( ' AND ', $where );
            
            $query = "
INSERT INTO civicrm_action_log (contact_id, entity_id, entity_table, action_schedule_id)
{$selectClause} 
{$fromClause} 
{$joinClause}
LEFT JOIN {$reminderJoinClause}
{$whereClause} AND {$startEventClause}";
            CRM_Core_DAO::executeQuery( $query, array( 1 => array( $actionSchedule->id, 'Integer' ) ) );

            // if repeat is turned ON:
            if ( $actionSchedule->is_repeat ) {
                if ( $mapping->entity == 'civicrm_activity' ) {
                    $repeatEvent = ( $actionSchedule->end_action == 'before' ? "DATE_SUB" : "DATE_ADD" ) . 
                        "(e.activity_date_time, INTERVAL {$actionSchedule->end_frequency_interval} {$actionSchedule->end_frequency_unit})";
                }

                if ( $actionSchedule->repetition_frequency_unit == 'day' ) {
                    $hrs = 24 * $actionSchedule->repetition_frequency_interval;
                } else if ( $actionSchedule->repetition_frequency_unit == 'week' ) {
                    $hrs = 24 * $actionSchedule->repetition_frequency_interval * 7;
                } else {
                    $hrs = $actionSchedule->repetition_frequency_interval;
                }
                
                // (now <= repeat_end_time )
                $repeatEventClause = "'{$this->_now}' <= {$repeatEvent}"; 
                // diff(now && logged_date_time) >= repeat_interval
                $havingClause      = "HAVING TIMEDIFF({$this->_now}, latest_log_time) >= TIME('{$hrs}:00:00')";
                $groupByClause     = "GROUP BY reminder.contact_id, reminder.entity_id, reminder.entity_table"; 
                $selectClause     .= ", MAX(reminder.action_date_time) as latest_log_time";

                // Note this query tries to insert MAX(reminder.action_date_time) in place of is_error
                $query = "
INSERT INTO civicrm_action_log (contact_id, entity_id, entity_table, action_schedule_id, is_error)
{$selectClause} 
{$fromClause} 
{$joinClause}
INNER JOIN {$reminderJoinClause}
{$whereClause} AND {$repeatEventClause}
{$groupByClause}
{$havingClause}";
                CRM_Core_DAO::executeQuery( $query, array( 1 => array( $actionSchedule->id, 'Integer' ) ) );

                // just to clean is_error values
                $query = "
UPDATE civicrm_action_log 
SET    is_error = 0 
WHERE  action_date_time IS NULL AND action_schedule_id = %1";
                CRM_Core_DAO::executeQuery( $query, array( 1 => array( $actionSchedule->id, 'Integer' ) ) );
            }
        }
    }
}

$cron = new CRM_Cron_Action( );
$cron->run( );

