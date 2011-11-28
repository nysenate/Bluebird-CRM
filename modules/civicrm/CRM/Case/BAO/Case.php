<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Case/DAO/Case.php';
require_once 'CRM/Case/PseudoConstant.php';

/**
 * This class contains the funtions for Case Management
 *
 */
class CRM_Case_BAO_Case extends CRM_Case_DAO_Case
{
  
    /**
     * static field for all the case information that we can potentially export
     *
     * @var array
     * @static
     */
    static $_exportableFields = null;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * takes an associative array and creates a case object
     *
     * the function extract all the params it needs to initialize the create a
     * case object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     *
     * @return object CRM_Case_BAO_Case object
     * @access public
     * @static
     */
    static function add( &$params ) 
    {
        $caseDAO = new CRM_Case_DAO_Case();
        $caseDAO->copyValues($params);
        return $caseDAO->save();
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params input parameters to find object
     * @param array $values output values of the object
     * @param array $ids    the array that holds all the db ids
     *
     * @return CRM_Case_BAO_Case|null the found object or null
     * @access public
     * @static
     */
    static function &getValues( &$params, &$values, &$ids ) 
    {
        $case = new CRM_Case_BAO_Case( );

        $case->copyValues( $params );
        
        if ( $case->find(true) ) {
            $ids['case']    = $case->id;
            CRM_Core_DAO::storeValues( $case, $values );
            return $case;
        }
        return null;
    }

    /**
     * takes an associative array and creates a case object
     *
     * @param array $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     *
     * @return object CRM_Case_BAO_Case object 
     * @access public
     * @static
     */
    static function &create( &$params ) 
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( ); 
        
        if ( CRM_Utils_Array::value( 'id', $params ) ) {
            CRM_Utils_Hook::pre( 'edit', 'Case', $params['id'], $params );
        } else {
            CRM_Utils_Hook::pre( 'create', 'Case', null, $params );
        }
        
        $case = self::add( $params );

        if ( is_a( $case, 'CRM_Core_Error') ) {
            $transaction->rollback( );
            return $case;
        }

        if ( CRM_Utils_Array::value( 'id', $params ) ) {
            CRM_Utils_Hook::post( 'edit', 'Case', $case->id, $case );
        } else {
            CRM_Utils_Hook::post( 'create', 'Case', $case->id, $case );
        }
        $transaction->commit( );
                
        //we are not creating log for case
        //since case log can be tracked using log for activity.
        return $case;
    }

    /**
     * Create case contact record
     *
     * @param array    case_id, contact_id
     *
     * @return object
     * @access public
     */
    function addCaseToContact( $params ) 
    {
        require_once 'CRM/Case/DAO/CaseContact.php';
        $caseContact = new CRM_Case_DAO_CaseContact();
        $caseContact->case_id = $params['case_id'];
        $caseContact->contact_id = $params['contact_id'];
        $caseContact->find(true);
        $caseContact->save();

        // add to recently viewed    
        require_once 'CRM/Utils/Recent.php';
        require_once 'CRM/Case/PseudoConstant.php';
        require_once 'CRM/Contact/BAO/Contact.php';
        $caseType = CRM_Case_PseudoConstant::caseTypeName( $caseContact->case_id, 'label' );
        $url = CRM_Utils_System::url( 'civicrm/contact/view/case', 
                                      "action=view&reset=1&id={$caseContact->case_id}&cid={$caseContact->contact_id}&context=home" );
        
        $title = CRM_Contact_BAO_Contact::displayName( $caseContact->contact_id ) . ' - ' . $caseType['name'];
        
        $recentOther = array( );
        if ( CRM_Core_Permission::checkActionPermission('CiviCase', CRM_Core_Action::DELETE ) ) {
            $recentOther['deleteUrl'] = CRM_Utils_System::url( 'civicrm/contact/view/case', 
                                                               "action=delete&reset=1&id={$caseContact->case_id}&cid={$caseContact->contact_id}&context=home" ); 
        }

        // add the recently created case
        CRM_Utils_Recent::add( $title,
                               $url,
                               $caseContact->case_id,
                               'Case',
                               $params['contact_id'],
                               null,
                               $recentOther
                               );
        
        return $caseContact;
    }

    /**
     * Delet case contact record
     *
     * @param int    case_id
     *
     * @return Void
     * @access public
     */
    function deleteCaseContact( $caseID ) 
    {
        require_once 'CRM/Case/DAO/CaseContact.php';
        $caseContact = new CRM_Case_DAO_CaseContact();
        $caseContact->case_id = $caseID;
        $caseContact->delete();
        
        // delete the recently created Case
        require_once 'CRM/Utils/Recent.php';
        $caseRecent = array(
                            'id'   => $caseID,
                            'type' => 'Case'
                            );
        CRM_Utils_Recent::del( $caseRecent );
    }

    /**
     * This function is used to convert associative array names to values
     * and vice-versa.
     *
     * This function is used by both the web form layer and the api. Note that
     * the api needs the name => value conversion, also the view layer typically
     * requires value => name conversion
     */
    static function lookupValue(&$defaults, $property, &$lookup, $reverse)
    {
        $id = $property . '_id';

        $src = $reverse ? $property : $id;
        $dst = $reverse ? $id       : $property;

        if (!array_key_exists($src, $defaults)) {
            return false;
        }

        $look = $reverse ? array_flip($lookup) : $lookup;
        
        if(is_array($look)) {
            if (!array_key_exists($defaults[$src], $look)) {
                return false;
            }
        }
        $defaults[$dst] = $look[$defaults[$src]];
        return true;
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. We'll tweak this function to be more
     * full featured over a period of time. This is the inverse function of
     * create.  It also stores all the retrieved values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the name / value pairs
     *                        in a hierarchical manner
     * @param array $ids      (reference) the array that holds all the db ids
     *
     * @return object CRM_Case_BAO_Case object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults, &$ids ) 
    {
        $case = CRM_Case_BAO_Case::getValues( $params, $defaults, $ids );
        return $case;
    }

    /**
     * Function to process case activity add/delete
     * takes an associative array and
     *
     * @param array $params (reference ) an assoc array of name/value pairs
     *
     * @access public
     * @static
     */
    static function processCaseActivity( &$params ) 
    {
        require_once 'CRM/Case/DAO/CaseActivity.php';
        $caseActivityDAO = new CRM_Case_DAO_CaseActivity();
        $caseActivityDAO->activity_id = $params['activity_id'];
        $caseActivityDAO->case_id = $params['case_id'];

        $caseActivityDAO->find( true );
        $caseActivityDAO->save();
    } 

    /**
     * Function to get the case subject for Activity
     *
     * @param int $activityId  activity id
     * @return  case subject or null
     * @access public
     * @static
     */
    static function getCaseSubject ( $activityId )
    {
        require_once 'CRM/Case/DAO/CaseActivity.php';
        $caseActivity =  new CRM_Case_DAO_CaseActivity();
        $caseActivity->activity_id = $activityId;
        if ( $caseActivity->find(true) ) {
            return CRM_Core_DAO::getFieldValue('CRM_Case_BAO_Case', $caseActivity->case_id,'subject' );
        }
        return null;
    }
    
    /**
     * Function to get the case type.
     *
     * @param int $caseId
     *
     * @return  case type
     * @access public
     * @static
     */
    static function getCaseType( $caseId, $colName = 'label' )
    {
        $caseType = null;
        if ( !$caseId ) return $caseType;
        
        $sql = "
    SELECT  ov.{$colName}
      FROM  civicrm_case ca  
INNER JOIN  civicrm_option_group og ON og.name='case_type'
INNER JOIN  civicrm_option_value ov ON ( ca.case_type_id=ov.value AND ov.option_group_id=og.id )
     WHERE  ca.id = %1";

        $params = array( 1 => array( $caseId, 'Integer' ) );
        
        return CRM_Core_DAO::singleValueQuery( $sql, $params );
    }
    
    /**                                                           
     * Delete the record that are associated with this case 
     * record are deleted from case 
     * @param  int  $caseId id of the case to delete
     * 
     * @return void
     * @access public 
     * @static 
     */ 
    static function deleteCase( $caseId , $moveToTrash = false ) 
    {
        CRM_Utils_Hook::pre( 'delete', 'Case', $caseId, CRM_Core_DAO::$_nullArray );
        
        //delete activities
        $activities = self::getCaseActivityDates( $caseId );
        if ( $activities ) {
            require_once"CRM/Activity/BAO/Activity.php";
            foreach( $activities as $value ) {
                CRM_Activity_BAO_Activity::deleteActivity( $value, $moveToTrash );
            }
        }  
        
        if ( ! $moveToTrash ) {
            require_once 'CRM/Core/Transaction.php';
            $transaction = new CRM_Core_Transaction( );
        }
        require_once 'CRM/Case/DAO/Case.php';
        $case     = new CRM_Case_DAO_Case( );
        $case->id = $caseId; 
        if ( ! $moveToTrash ) {  
            $result = $case->delete( );
            $transaction->commit( );
        } else {
            $result = $case->is_deleted = 1;
            $case->save( );
        }
        
        if ( $result ) {
            // CRM-7364, disable relationships
            self::enableDisableCaseRelationships( $caseId, false );            
       	
            CRM_Utils_Hook::post( 'delete', 'Case', $caseId, $case );

            // remove case from recent items.
            $caseRecent = array(
                                'id'   => $caseId,
                                'type' => 'Case'
                        );
            require_once 'CRM/Utils/Recent.php';
            CRM_Utils_Recent::del( $caseRecent );
            return true;
        }
        
        return false;
    }

    /**
     * Function to enable disable case related relationships
     *
     *  @param int      $caseId case id
     *  @param boolean  $enable action 
     *  
     *  @return void
     *  @access public
     *  @static
     */
    static function enableDisableCaseRelationships( $caseId, $enable ) {
        $contactIds = self::retrieveContactIdsByCaseId( $caseId );
        if ( !empty( $contactIds ) ) {
            foreach ( $contactIds as $cid ) {
                $roles = self::getCaseRoles( $cid, $caseId );
                if ( !empty( $roles ) ) {
                    $relationshipIds = implode( ',', array_keys( $roles ) );
                    $enable = (int)$enable;
                    $query = "UPDATE civicrm_relationship SET is_active = {$enable}
                        WHERE id IN ( {$relationshipIds} )";
                    CRM_Core_DAO::executeQuery( $query );
                }
            }
        }
    }  

    /**                                                           
     * Delete the activities related to case
     * @param  int  $activityId id of the activity
     * 
     * @return void
     * @access public 
     * @static 
     */ 
    static function deleteCaseActivity( $activityId ) 
    {
        require_once 'CRM/Case/DAO/CaseActivity.php';
        $case              = new CRM_Case_DAO_CaseActivity( );
        $case->activity_id = $activityId; 
        $case->delete( );
    }

    /** 
     * Retrieve contact_id by case_id
     *
     * @param int    $caseId  ID of the case
     * 
     * @return array
     * @access public
     * 
     */
    function retrieveContactIdsByCaseId( $caseId , $contactID = null ) 
    {
         require_once 'CRM/Case/DAO/CaseContact.php';
         $caseContact = new CRM_Case_DAO_CaseContact( );
         $caseContact->case_id = $caseId;
         $caseContact->find();
         $contactArray = array();
         $count = 1;
         while ( $caseContact->fetch( ) ) {
             if ( $contactID != $caseContact->contact_id ) {
                 $contactArray[$count] = $caseContact->contact_id;
                 $count++;
             }
         }
         
         return $contactArray;
     }
     
    /**
     * Look up a case using an activity ID
     *
     * @param $activity_id
     * @return int, case ID
     */
    static function getCaseIdByActivityId($activityId) {
      $originalId = CRM_Core_DAO::singleValueQuery(
          'SELECT original_id FROM civicrm_activity WHERE id = %1', 
          array('1' => array($activityId, 'Integer'))
      );
      $caseId =  CRM_Core_DAO::singleValueQuery(
          'SELECT case_id FROM civicrm_case_activity WHERE activity_id in (%1,%2)',
          array(
            '1' => array($activityId, 'Integer'),
            '2' => array($originalId ? $originalId : $activityId, 'Integer')
          )
      );
      return $caseId;
    }
    
    /**
     * Retrieve contact names by caseId
     *
     * @param int    $caseId  ID of the case
     * 
     * @return array
     * 
     * @access public
     * 
     */
    static function getContactNames( $caseId ) 
    {
        $contactNames = array( );
        if ( !$caseId ) {
            return $contactNames;
        }
        
        $query ="
    SELECT  contact_a.sort_name name, 
            contact_a.display_name as display_name, 
            contact_a.id cid, 
            contact_a.birth_date as birth_date,
            ce.email as email,
            cp.phone as phone
      FROM  civicrm_contact contact_a 
 LEFT JOIN  civicrm_case_contact ON civicrm_case_contact.contact_id = contact_a.id
 LEFT JOIN  civicrm_email ce ON ( ce.contact_id = contact_a.id AND ce.is_primary = 1)
 LEFT JOIN  civicrm_phone cp ON ( cp.contact_id = contact_a.id AND cp.is_primary = 1)
     WHERE  civicrm_case_contact.case_id = %1";
        
        $dao = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $caseId, 'Integer' ) ) );
        while ( $dao->fetch() ) {
            $contactNames[$dao->cid]['contact_id']   =  $dao->cid;
            $contactNames[$dao->cid]['sort_name']    =  $dao->name;
            $contactNames[$dao->cid]['display_name'] =  $dao->display_name;
            $contactNames[$dao->cid]['email']        =  $dao->email;
            $contactNames[$dao->cid]['phone']        =  $dao->phone;
            $contactNames[$dao->cid]['birth_date']   =  $dao->birth_date;
            $contactNames[$dao->cid]['role']         =  ts('Client');
        }
        
        return $contactNames;
    }
     
    /** 
     * Retrieve case_id by contact_id
     *
     * @param int     $contactId      ID of the contact
     * @param boolean $includeDeleted include the deleted cases in result
     * @return array
     * 
     * @access public
     * 
     */
     function retrieveCaseIdsByContactId( $contactID, $includeDeleted = false ) 
     {
         $query = "
SELECT ca.id as id
FROM civicrm_case_contact cc
INNER JOIN civicrm_case ca ON cc.case_id = ca.id
WHERE cc.contact_id = %1
";
         if (!$includeDeleted) {
             $query .= " AND ca.is_deleted = 0";
         }
         
         $params = array( 1 => array( $contactID, 'Integer' ) );
         $dao = CRM_Core_DAO::executeQuery( $query, $params ); 

         $caseArray = array( );
         while ( $dao->fetch( ) ) {
             $caseArray[] = $dao->id;
         }
         
         $dao->free( );
         return $caseArray;
     }

    function getCaseActivityQuery( $type = 'upcoming', $userID = null, $condition = null, $isDeleted = 0 ) 
    {
        if ( !$userID ) {
            $session = CRM_Core_Session::singleton( );
            $userID = $session->get( 'userID' );
        }
        
        $actStatus         = array_flip( CRM_Core_PseudoConstant::activityStatus('name') );
        $scheduledStatusId = $actStatus['Scheduled'];
        
        $query = "SELECT
                  civicrm_case.id as case_id,
                  civicrm_case.subject as case_subject,
                  civicrm_contact.id as contact_id,
                  civicrm_contact.sort_name as sort_name,
                  civicrm_phone.phone as phone,
                  civicrm_contact.contact_type as contact_type,
                  civicrm_contact.contact_sub_type as contact_sub_type,
                  civicrm_activity.activity_type_id,
                  cov_type.label as case_type,
                  cov_type.name as case_type_name,
                  cov_status.label as case_status,
                  cov_status.label as case_status_name,
                  civicrm_activity.status_id,
                  civicrm_case.start_date as case_start_date,
                  case_relation_type.label_b_a as case_role, ";
        if ( $type == 'upcoming' ) {
            $query .=  " civicrm_activity.activity_date_time as case_scheduled_activity_date,
                         civicrm_activity.id as case_scheduled_activity_id,
                         aov.name as case_scheduled_activity_type_name,
                         aov.label as case_scheduled_activity_type ";       
        } else if ( $type == 'recent' ) {
            $query .=  " civicrm_activity.activity_date_time as case_recent_activity_date,
                         civicrm_activity.id as case_recent_activity_id,
                         aov.name as case_recent_activity_type_name,
                         aov.label as case_recent_activity_type ";
        } 
        
        $query .= 
            " FROM civicrm_case
                  INNER JOIN civicrm_case_activity
                        ON civicrm_case_activity.case_id = civicrm_case.id  
            
                  LEFT JOIN civicrm_case_contact ON civicrm_case.id = civicrm_case_contact.case_id
                  LEFT JOIN civicrm_contact ON civicrm_case_contact.contact_id = civicrm_contact.id
                  LEFT JOIN civicrm_phone ON (civicrm_phone.contact_id = civicrm_contact.id AND civicrm_phone.is_primary=1) ";

        if ( $type == 'upcoming' ) {
            $query .= " LEFT JOIN civicrm_activity
                             ON ( civicrm_case_activity.activity_id = civicrm_activity.id
                                  AND civicrm_activity.is_current_revision = 1
                                  AND civicrm_activity.status_id = $scheduledStatusId
                                  AND civicrm_activity.activity_date_time <= DATE_ADD( NOW(), INTERVAL 14 DAY ) ) ";
        } else if ( $type == 'recent' ) {
            $query .= " LEFT JOIN civicrm_activity
                             ON ( civicrm_case_activity.activity_id = civicrm_activity.id
                                  AND civicrm_activity.is_current_revision = 1
                                  AND civicrm_activity.status_id != $scheduledStatusId
                                  AND civicrm_activity.activity_date_time <= NOW() 
                                  AND civicrm_activity.activity_date_time >= DATE_SUB( NOW(), INTERVAL 14 DAY ) ) ";
        }
               
        $query .= "
                  LEFT JOIN civicrm_option_group aog  ON aog.name = 'activity_type'
                  LEFT JOIN civicrm_option_value aov
                        ON ( civicrm_activity.activity_type_id = aov.value
                             AND aog.id = aov.option_group_id )         

                  LEFT  JOIN  civicrm_relationship case_relationship 
                        ON ( case_relationship.contact_id_a = civicrm_case_contact.contact_id 
                             AND case_relationship.contact_id_b = {$userID}  
                             AND case_relationship.case_id = civicrm_case.id )
     
                  LEFT  JOIN civicrm_relationship_type case_relation_type 
                        ON ( case_relation_type.id = case_relationship.relationship_type_id 
                             AND case_relation_type.id = case_relationship.relationship_type_id )

                  LEFT JOIN civicrm_option_group cog_type ON cog_type.name = 'case_type'
                  LEFT JOIN civicrm_option_value cov_type
                        ON ( civicrm_case.case_type_id = cov_type.value
                             AND cog_type.id = cov_type.option_group_id )

                  LEFT JOIN civicrm_option_group cog_status ON cog_status.name = 'case_status'
                  LEFT JOIN civicrm_option_value cov_status 
                       ON ( civicrm_case.status_id = cov_status.value
                            AND cog_status.id = cov_status.option_group_id ) ";

        $query .= "
                  LEFT JOIN civicrm_activity ca2
                             ON ( ca2.id IN ( SELECT cca.activity_id FROM civicrm_case_activity cca 
                                              WHERE cca.case_id = civicrm_case.id )
                                  AND ca2.is_current_revision = 1 
                                  AND ca2.is_deleted = $isDeleted ";
        
        if ( $type == 'upcoming' ) {
            $query .= "AND ca2.status_id = $scheduledStatusId
                       AND ca2.activity_date_time <= DATE_ADD( NOW(), INTERVAL 14 DAY ) 
                       AND civicrm_activity.activity_date_time > ca2.activity_date_time )";
        } else if ( $type == 'recent' ) {
            $query .= "AND ca2.status_id != $scheduledStatusId
                       AND ca2.activity_date_time <= NOW() 
                       AND ca2.activity_date_time >= DATE_SUB( NOW(), INTERVAL 14 DAY )
                       AND civicrm_activity.activity_date_time < ca2.activity_date_time )";
        }
        
        $query .= " WHERE ca2.id IS NULL";

        if ( $condition ) {
            $query .= $condition;
        }

        if ( $type == 'upcoming' ) {
            $query .=" ORDER BY case_scheduled_activity_date ASC ";
        } else if ( $type == 'recent' ) {
            $query .= " ORDER BY case_recent_activity_date ASC ";
        }

        return $query;
    }

    /**
     * Retrieve cases related to particular contact or whole contact
     * used in Dashboad and Tab
     *
     * @param boolean    $allCases  
     * 
     * @param int        $userID 
     *
     * @param String     $type /upcoming,recent,all/ 
     *
     * @return array     Array of Cases
     * 
     * @access public
     * 
     */
    function getCases( $allCases = true, $userID = null, $type = 'upcoming', $context = 'dashboard' )
    {
        $condition = null;
        $casesList = array( );
        
        //validate access for own cases.
        if ( !self::accessCiviCase( ) ) {
            return $casesList;
        }
        
        if ( !$userID ) {
            $session = CRM_Core_Session::singleton( );
            $userID = $session->get( 'userID' );
        }
        
        //validate access for all cases.
        if ( $allCases && !CRM_Core_Permission::check( 'access all cases and activities' ) ) {
            $allCases = false;
        }
        
        if ( !$allCases ) {
            $condition = " AND case_relationship.contact_id_b = {$userID}";
        }

        $condition .= " 
AND civicrm_activity.is_deleted = 0
AND civicrm_case.is_deleted     = 0";
        
        if ( $type == 'upcoming' ) {
            require_once 'CRM/Core/OptionGroup.php';
            $closedId    = CRM_Core_OptionGroup::getValue( 'case_status', 'Closed', 'name' );
            $condition .= "
AND civicrm_case.status_id != $closedId";
        }
        
        $query = self::getCaseActivityQuery( $type, $userID, $condition );
 
        $queryParams = array();
        $result = CRM_Core_DAO::executeQuery( $query, $queryParams );

        require_once 'CRM/Core/OptionGroup.php';
        $caseStatus = CRM_Core_OptionGroup::values( 'case_status', false, false, false, " AND v.name = 'Urgent' " );

        $resultFields = array( 'contact_id',
                               'contact_type',
                               'sort_name',
                               'phone',
                               'case_id',
                               'case_subject',
                               'case_type',
                               'case_type_name',
                               'status_id',
                               'case_status',
                               'case_status_name',
                               'activity_type_id',
                               'case_start_date',
                               'case_role', 
                               );

        if ( $type == 'upcoming' ) {
            $resultFields[] = 'case_scheduled_activity_date';
            $resultFields[] = 'case_scheduled_activity_type_name';
            $resultFields[] = 'case_scheduled_activity_type';
            $resultFields[] = 'case_scheduled_activity_id';
        } else if ( $type == 'recent' ) {
            $resultFields[] = 'case_recent_activity_date';
            $resultFields[] = 'case_recent_activity_type_name';
            $resultFields[] = 'case_recent_activity_type';
            $resultFields[] = 'case_recent_activity_id';
        }

        // we're going to use the usual actions, so doesn't make sense to duplicate definitions
        require_once( 'CRM/Case/Selector/Search.php');
        $actions = CRM_Case_Selector_Search::links();

        require_once "CRM/Contact/BAO/Contact/Utils.php";
        
        // check is the user has view/edit signer permission
        $permissions = array( CRM_Core_Permission::VIEW );
        if ( CRM_Core_Permission::check( 'access all cases and activities' ) ||
             (!$allCases && CRM_Core_Permission::check( 'access my cases and activities' )) ) {
            $permissions[] = CRM_Core_Permission::EDIT;
        }
        if ( CRM_Core_Permission::check( 'delete in CiviCase' ) ) {
            $permissions[] = CRM_Core_Permission::DELETE;
        }
        $mask = CRM_Core_Action::mask( $permissions );

        while ( $result->fetch() ) {
            foreach( $resultFields as $donCare => $field ) {
                $casesList[$result->case_id][$field] = $result->$field;
                if( $field == 'contact_type' ) {
                    $casesList[$result->case_id]['contact_type_icon'] 
                        = CRM_Contact_BAO_Contact_Utils::getImage( $result->contact_sub_type ? 
                                                                   $result->contact_sub_type : $result->contact_type );
                    $casesList[$result->case_id]['action'] 
                        = CRM_Core_Action::formLink( $actions['primaryActions'], $mask,
                                                     array( 'id'  => $result->case_id,
                                                            'cid' => $result->contact_id,
                                                            'cxt' => $context
                                                           ) );
                    $casesList[$result->case_id]['moreActions'] 
                        = CRM_Core_Action::formLink( $actions['moreActions'], 
                                                     $mask, 
                                                     array( 'id'  => $result->case_id,
                                                            'cid' => $result->contact_id,
                                                            'cxt' => $context   
                                                          ),
                                                     ts( 'more' ),
                                                     true 
                                                     );

                } elseif ( $field == 'case_status' ) {  
                    if ( in_array($result->$field, $caseStatus) ) {
                        $casesList[$result->case_id]['class'] = "status-urgent";
                    }else {
                        $casesList[$result->case_id]['class'] = "status-normal";
                    }
                }
            }
            //CRM-4510.
            $caseManagerContact = self::getCaseManagerContact( $result->case_type_name, $result->case_id );
            if ( !empty($caseManagerContact) ) {
                $casesList[$result->case_id]['casemanager_id'] = CRM_Utils_Array::value('casemanager_id', $caseManagerContact );
                $casesList[$result->case_id]['casemanager'   ] = CRM_Utils_Array::value('casemanager'   , $caseManagerContact );              
            } 
            
            //do check user permissions for edit/view activity.
            if ( ($actId = CRM_Utils_Array::value('case_scheduled_activity_id', $casesList[$result->case_id])) ||
                 ($actId = CRM_Utils_Array::value('case_recent_activity_id', $casesList[$result->case_id])) ) {
                $casesList[$result->case_id]["case_{$type}_activity_editable"] = 
                    self::checkPermission( $actId, 
                                           'edit',   
                                           $casesList[$result->case_id]['activity_type_id'], $userID );
                $casesList[$result->case_id]["case_{$type}_activity_viewable"] = 
                    self::checkPermission( $actId,
                                           'view',   
                                           $casesList[$result->case_id]['activity_type_id'], $userID );
            }
        }
        
        return $casesList;        
    }

    /**
     * Function to get the summary of cases counts by type and status.
     */
    function getCasesSummary( $allCases = true, $userID )
    {
        $caseSummary = array( );
        
        //validate access for civicase.
        if ( !self::accessCiviCase( ) ) {
            return $caseSummary;
        }
        
        //validate access for all cases.
        if ( $allCases && !CRM_Core_Permission::check( 'access all cases and activities' ) ) {
            $allCases = false;
        }
        
        require_once 'CRM/Case/PseudoConstant.php';
        $caseTypes    = CRM_Case_PseudoConstant::caseType( );
        $caseStatuses = CRM_Case_PseudoConstant::caseStatus( );
        $caseTypes    = array_flip( $caseTypes );  
        
        // get statuses as headers for the table
        $url =  CRM_Utils_System::url( 'civicrm/case/search',"reset=1&force=1&all=1&status=" ) ;
        foreach( $caseStatuses as $key => $name ) {
            $caseSummary['headers'][$key]['status'] = $name; 
            $caseSummary['headers'][$key]['url']    = $url.$key; 
        }
        
        // build rows with actual data
        $rows = array();
        $myGroupByClause = $mySelectClause = $myCaseFromClause = $myCaseWhereClause = '';
        
        if( $allCases ) {
            $userID = 'null';
            $all = 1;
        } else {
            $all = 0;
            $myCaseWhereClause = " AND case_relationship.contact_id_b = {$userID}";
            $myGroupByClause   = " GROUP BY CONCAT(case_relationship.case_id,'-',case_relationship.contact_id_b)";
        }
        
        $seperator = CRM_Core_DAO::VALUE_SEPARATOR;
   
        $query = "
SELECT case_status.label AS case_status, status_id, case_type.label AS case_type, 
REPLACE(case_type_id,'{$seperator}','') AS case_type_id, case_relationship.contact_id_b
FROM civicrm_case
LEFT JOIN civicrm_option_group option_group_case_type ON ( option_group_case_type.name = 'case_type' )
LEFT JOIN civicrm_option_value case_type ON ( civicrm_case.case_type_id = case_type.value
AND option_group_case_type.id = case_type.option_group_id )
LEFT JOIN civicrm_option_group option_group_case_status ON ( option_group_case_status.name = 'case_status' )
LEFT JOIN civicrm_option_value case_status ON ( civicrm_case.status_id = case_status.value
AND option_group_case_status.id = case_status.option_group_id )
LEFT JOIN civicrm_relationship case_relationship ON ( case_relationship.case_id  = civicrm_case.id 
AND case_relationship.contact_id_b = {$userID})
WHERE is_deleted =0 
{$myCaseWhereClause} {$myGroupByClause}";
        
        $res = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        while( $res->fetch() ) {
            if ( CRM_Utils_Array::value($res->case_type, $rows) &&  CRM_Utils_Array::value($res->case_status, $rows[$res->case_type]) ) {
                $rows[$res->case_type][$res->case_status]['count'] = $rows[$res->case_type][$res->case_status]['count'] + 1;
            } else {
                $rows[$res->case_type][$res->case_status] = array( 'count' => 1,
                                                                   'url'   => CRM_Utils_System::url( 'civicrm/case/search',
                                                                                                     "reset=1&force=1&status={$res->status_id}&type={$res->case_type_id}&all={$all}" ) 
                                                                   );
            }
        }
        $caseSummary['rows'] = array_merge( $caseTypes, $rows );
        
        return $caseSummary;
    }

    /**
     * Function to get Case roles
     *
     * @param int $contactID contact id
     * @param int $caseID case id
     *
     * @return returns case role / relationships
     *
     * @static
     */
    static function getCaseRoles( $contactID, $caseID, $relationshipID = null )
    {
        $query = '
    SELECT  civicrm_relationship.id as civicrm_relationship_id, 
            civicrm_contact.sort_name as sort_name, 
            civicrm_email.email as email, 
            civicrm_phone.phone as phone, 
            civicrm_relationship.contact_id_b as civicrm_contact_id, 
            civicrm_relationship_type.label_a_b as relation, 
            civicrm_relationship_type.id as relation_type 
      FROM  civicrm_relationship 
INNER JOIN  civicrm_relationship_type ON civicrm_relationship.relationship_type_id = civicrm_relationship_type.id
INNER JOIN  civicrm_contact ON civicrm_relationship.contact_id_b = civicrm_contact.id
 LEFT JOIN  civicrm_phone ON (civicrm_phone.contact_id = civicrm_contact.id AND civicrm_phone.is_primary = 1) 
 LEFT JOIN  civicrm_email ON (civicrm_email.contact_id = civicrm_contact.id ) 
     WHERE  civicrm_relationship.contact_id_a = %1 AND civicrm_relationship.case_id = %2';
        
        $params = array( 1 => array( $contactID, 'Positive' ),
                         2 => array( $caseID,    'Positive' ) );
        
		if ( $relationshipID ) {
			$query .= ' AND civicrm_relationship.id = %3 ';
			$params[3] = array( $relationshipID, 'Integer' );
		}
        
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        
        $values = array( );
        while ( $dao->fetch( ) ) {
            $rid = $dao->civicrm_relationship_id;
            $values[$rid]['cid']           = $dao->civicrm_contact_id;
            $values[$rid]['relation']      = $dao->relation;
            $values[$rid]['name']          = $dao->sort_name;
            $values[$rid]['email']         = $dao->email;
            $values[$rid]['phone']         = $dao->phone;
            $values[$rid]['relation_type'] = $dao->relation_type;
        }
        
        $dao->free( );
        return $values;
    }

    /**
     * Function to get Case Activities
     *
     * @param int    $caseID case id
     * @param array  $params posted params 
     * @param int    $contactID contact id
     *
     * @return returns case activities
     *
     * @static
     */
    static function getCaseActivity( $caseID, &$params, $contactID, $context = null, $userID = null, $type = null )
    {
        $values = array( );
                        
        // CRM-5081 - formatting the dates to omit seconds.
        // Note the 00 in the date format string is needed otherwise later on it thinks scheduled ones are overdue.
        $select = "SELECT count(ca.id) as ismultiple, ca.id as id, 
                          ca.activity_type_id as type,
                          ca.activity_type_id as activity_type_id,  
                          cc.sort_name as reporter,
                          cc.id as reporter_id,
                          acc.sort_name AS assignee,
                          acc.id AS assignee_id,
                          DATE_FORMAT(IF(ca.activity_date_time < NOW() AND ca.status_id=ov.value,
                            ca.activity_date_time,
                            DATE_ADD(NOW(), INTERVAL 1 YEAR)
                          ), '%Y%m%d%H%i00') as overdue_date,
                          DATE_FORMAT(ca.activity_date_time, '%Y%m%d%H%i00') as display_date,
                          ca.status_id as status, 
                          ca.subject as subject, 
                          ca.is_deleted as deleted,
                          ca.priority_id as priority ";

        $from  = 'FROM civicrm_case_activity cca 
                  INNER JOIN civicrm_activity ca ON ca.id = cca.activity_id
                  INNER JOIN civicrm_contact cc ON cc.id = ca.source_contact_id
                  INNER JOIN civicrm_option_group cog ON cog.name = "activity_type"
                  INNER JOIN civicrm_option_value cov ON cov.option_group_id = cog.id 
                         AND cov.value = ca.activity_type_id AND cov.is_active = 1
                  LEFT OUTER JOIN civicrm_option_group og ON og.name="activity_status"
                  LEFT OUTER JOIN civicrm_option_value ov ON ov.option_group_id=og.id AND ov.name="Scheduled"
                  LEFT JOIN civicrm_activity_assignment caa 
                                ON caa.activity_id = ca.id 
                               LEFT JOIN civicrm_contact acc ON acc.id = caa.assignee_contact_id  '; 

        $where = 'WHERE cca.case_id= %1 
                    AND ca.is_current_revision = 1';

		if ( CRM_Utils_Array::value( 'reporter_id', $params ) ) {
            $where .= " AND ca.source_contact_id = ".CRM_Utils_Type::escape( $params['reporter_id'], 'Integer' );
        }

		if ( CRM_Utils_Array::value( 'status_id', $params ) ) {
            $where .= " AND ca.status_id = ".CRM_Utils_Type::escape( $params['status_id'], 'Integer' );
        }

		if ( CRM_Utils_Array::value( 'activity_deleted', $params ) ) {
            $where .= " AND ca.is_deleted = 1";
        } else {
            $where .= " AND ca.is_deleted = 0";
        }


		if ( CRM_Utils_Array::value( 'activity_type_id', $params ) ) {
            $where .= " AND ca.activity_type_id = ".CRM_Utils_Type::escape( $params['activity_type_id'], 'Integer' );
        }

		if ( CRM_Utils_Array::value( 'activity_date_low', $params ) ) {
            $fromActivityDate = CRM_Utils_Type::escape( CRM_Utils_Date::processDate( $params['activity_date_low'] ), 'Date' );
        }
		if ( CRM_Utils_Array::value( 'activity_date_high', $params ) ) {
            $toActivityDate   = CRM_Utils_Type::escape( CRM_Utils_Date::processDate( $params['activity_date_high'] ), 'Date' );
            $toActivityDate   = $toActivityDate ? $toActivityDate + 235959 : null;
        }
        
        if ( !empty( $fromActivityDate ) ) {
            $where .= " AND ca.activity_date_time >= '{$fromActivityDate}'";
        }
            
        if ( !empty( $toActivityDate ) ) {
            $where .= " AND ca.activity_date_time <= '{$toActivityDate}'";
        }
            
        // hack to handle to allow initial sorting to be done by query
        if ( CRM_Utils_Array::value( 'sortname', $params ) == 'undefined' ) {
            $params['sortname'] = null;
        }

        if ( CRM_Utils_Array::value( 'sortorder', $params ) == 'undefined' ) {
            $params['sortorder'] = null;
        }

        $sortname  = CRM_Utils_Array::value( 'sortname', $params );
        $sortorder = CRM_Utils_Array::value( 'sortorder', $params );
        
        $groupBy = " GROUP BY ca.id ";
        
        if ( !$sortname AND !$sortorder ) {
            // CRM-5081 - added id to act like creation date
            $orderBy = " ORDER BY overdue_date ASC, display_date DESC, ca.id DESC";
        } else {
            $orderBy = " ORDER BY {$sortname} {$sortorder}";
            if ( $sortname != 'display_date' ) {
                $orderBy .= ', display_date DESC';
            }
        }
        
        $page = CRM_Utils_Array::value( 'page', $params );
        $rp   = CRM_Utils_Array::value( 'rp', $params );
        
        if (!$page) $page = 1;
        if (!$rp) $rp = 10;

        $start = (($page-1) * $rp);
        
        $query  = $select . $from . $where . $groupBy . $orderBy;
				    
        $params = array( 1 => array( $caseID, 'Integer' ) );
        $dao    =& CRM_Core_DAO::executeQuery( $query, $params );
        $params['total'] = $dao->N;

        //FIXME: need to optimize/cache these queries
        $limit  = " LIMIT $start, $rp";
        $query .= $limit;
        $dao    =& CRM_Core_DAO::executeQuery( $query, $params );
        
        require_once "CRM/Utils/Date.php";
        require_once "CRM/Core/PseudoConstant.php";
        require_once 'CRM/Case/PseudoConstant.php';
        
        $activityTypes    = CRM_Case_PseudoConstant::activityType( false, true );
        $activityStatus   = CRM_Core_PseudoConstant::activityStatus( );
        $activityPriority = CRM_Core_PseudoConstant::priority( );
        
        $url = CRM_Utils_System::url( "civicrm/case/activity",
                                      "reset=1&cid={$contactID}&caseid={$caseID}", false, null, false ); 
        
        $contextUrl = '';
        if ($context == 'fulltext') {
            $contextUrl = "&context={$context}";
        } 
        $editUrl    = "{$url}&action=update{$contextUrl}";
        $deleteUrl  = "{$url}&action=delete{$contextUrl}";
        $restoreUrl = "{$url}&action=renew{$contextUrl}";
        $viewTitle  = ts('View this activity.');

        require_once 'CRM/Core/OptionGroup.php';
        $emailActivityTypeIDs = array('Email' => CRM_Core_OptionGroup::getValue( 'activity_type', 
                                                               'Email', 
                                                               'name' ),
                                      'Inbound Email' => CRM_Core_OptionGroup::getValue( 'activity_type', 
                                                               'Inbound Email', 
                                                               'name' ),
                                     );
       
        require_once 'CRM/Core/OptionGroup.php'; 
        $emailActivityTypeIDs = array('Email' => CRM_Core_OptionGroup::getValue( 'activity_type', 
                                                               'Email', 
                                                               'name' ),
                                      'Inbound Email' => CRM_Core_OptionGroup::getValue( 'activity_type', 
                                                               'Inbound Email', 
                                                               'name' ),
                                     );
                                     
        require_once 'CRM/Case/BAO/Case.php';
        $caseDeleted = CRM_Core_DAO::getFieldValue( 'CRM_Case_DAO_Case', $caseID, 'is_deleted' );
        
        // define statuses which are handled like Completed status (others are assumed to be handled like Scheduled status)
        $compStatusValues = array();
        $compStatusNames = array('Completed', 'Left Message', 'Cancelled', 'Unreachable', 'Not Required');
        foreach($compStatusNames as $name) {
            $compStatusValues[] = CRM_Core_OptionGroup::getValue( 'activity_status', $name, 'name' );
        }
        $contactViewUrl = CRM_Utils_System::url( "civicrm/contact/view",
                                                 "reset=1&cid=", false, null, false );
        require_once 'CRM/Activity/BAO/ActivityTarget.php';
        $hasViewContact = CRM_Core_Permission::giveMeAllACLs( );
        $clientIds = self::retrieveContactIdsByCaseId( $caseID );
        
        if ( !$userID ) {
            $session = CRM_Core_Session::singleton( );
            $userID  = $session->get( 'userID' );
        }
        
        while ( $dao->fetch( ) ) {
            
            $allowView   = self::checkPermission( $dao->id, 'view',   $dao->activity_type_id, $userID );
            $allowEdit   = self::checkPermission( $dao->id, 'edit',   $dao->activity_type_id, $userID );
            $allowDelete = self::checkPermission( $dao->id, 'delete', $dao->activity_type_id, $userID );
            
            //do not have sufficient permission 
            //to access given case activity record.  
            if ( !$allowView && !$allowEdit && !$allowDelete ) {
                continue;
            }
            
            $values[$dao->id]['id']           = $dao->id;
            $values[$dao->id]['type']         = $activityTypes[$dao->type]['label'];
            
            $reporterName = $dao->reporter;
            if ( $hasViewContact ) {
                $reporterName = '<a href="'. $contactViewUrl . $dao->reporter_id . '">'.$dao->reporter.'</a>';
            }
            $values[$dao->id]['reporter'] = $reporterName;
            
            $targetNames = CRM_Activity_BAO_ActivityTarget::getTargetNames( $dao->id );
            $targetContactUrls = $withContacts = array( );
            foreach ( $targetNames as $targetId => $targetName ) {
                if ( !in_array( $targetId, $clientIds ) ) {
                    $withContacts[$targetId] = $targetName;
                }
            }
            foreach ( $withContacts as $cid => $name ) {
                if ( $hasViewContact ) $name =  '<a href="' . $contactViewUrl . $cid .'">'. $name. '</a>';
                $targetContactUrls[] = $name;
            }
            $values[$dao->id]['with_contacts'] = implode( '; ', $targetContactUrls );
            
            $values[$dao->id]['display_date'] = CRM_Utils_Date::customFormat( $dao->display_date );
            $values[$dao->id]['status']       = $activityStatus[$dao->status];
            
            //check for view activity.
            $subject = (empty($dao->subject)) ? '(' . ts('no subject') . ')'  : $dao->subject;
            if ( $allowView ) {
                $subject = '<a href="javascript:'. $type .'viewActivity('. $dao->id .','. $contactID . ',' .'\''. $type . '\' );" title=' .$viewTitle .'>' .$subject .'</a>'; 
            }
            $values[$dao->id]['subject'] = $subject;
            
            // add activity assignee to activity selector. CRM-4485.
            if ( isset($dao->assignee) ) {
                if( $dao->ismultiple == 1 ) {
                    if ( $dao->reporter_id != $dao->assignee_id ) {
                        $values[$dao->id]['reporter'] .= ($hasViewContact)? ' / '."<a href='{$contactViewUrl}{$dao->assignee_id}'>$dao->assignee</a>": ' / '.$dao->assignee;
                    }
                    $values[$dao->id]['assignee']  = $dao->assignee;
                } else {
                    $values[$dao->id]['reporter'] .= ' / ' .ts('(multiple)');
                } 
            }
            $url = "";
            $additionalUrl = "&id={$dao->id}";
            if ( !$dao->deleted ) {
                //hide edit link of activity type email.CRM-4530.
                if ( ! in_array($dao->type, $emailActivityTypeIDs) ) {
                    //hide Edit link if activity type is NOT editable (special case activities).CRM-5871
                    if ( $allowEdit ) {
                        $url = '<a href="' .$editUrl.$additionalUrl.'">'. ts('Edit') . '</a> ';
                    }
                }
                if ( $allowDelete ) {
                    if ( !empty($url) ) {
                        $url .= " | ";   
                    }
                    $url .= '<a href="' .$deleteUrl.$additionalUrl.'">'. ts('Delete') . '</a>';
                }
            } else if ( !$caseDeleted ) {
                $url = '<a href="' .$restoreUrl.$additionalUrl.'">'. ts('Restore') . '</a>';
                $values[$dao->id]['status']  = $values[$dao->id]['status'].'<br /> (deleted)'; 
            } 
            
            //check for operations.
            if ( self::checkPermission( $dao->id, 'Move To Case', $dao->activity_type_id ) ) {
                $url .= " | ". '<a href="#" onClick="Javascript:fileOnCase( \'move\','. $dao->id .', '. $caseID . ' ); return false;">'. ts('Move To Case') . '</a> ';
            }
            if ( self::checkPermission( $dao->id, 'Copy To Case', $dao->activity_type_id ) ) {
                $url .= " | ". '<a href="#" onClick="Javascript:fileOnCase( \'copy\','. $dao->id .','. $caseID .' ); return false;">' . ts('Copy To Case') . '</a> ';
            }
            
            $values[$dao->id]['links'] = $url;
            $values[$dao->id]['class'] = "";

            if ( !empty($dao->priority) ) {
                if ( $dao->priority == CRM_Core_OptionGroup::getValue( 'priority', 'Urgent', 'name' ) ) {
                    $values[$dao->id]['class'] = $values[$dao->id]['class']."priority-urgent ";
                } elseif ( $dao->priority == CRM_Core_OptionGroup::getValue( 'priority', 'Low', 'name' ) ) {
                    $values[$dao->id]['class'] = $values[$dao->id]['class']."priority-low ";
                }
            }
            
            if ( CRM_Utils_Array::crmInArray( $dao->status, $compStatusValues ) ) {
                $values[$dao->id]['class'] = $values[$dao->id]['class']." status-completed";
            } else {
                if ( CRM_Utils_Date::overdue( $dao->display_date ) ) {
                    $values[$dao->id]['class'] = $values[$dao->id]['class']." status-overdue";  
                } else {
                    $values[$dao->id]['class'] = $values[$dao->id]['class']." status-scheduled";    
                } 
            }
        }
        $dao->free( );
        
        return $values;
    }
    
    /**
     * Function to get Case Related Contacts
     *
     * @param int     $caseID case id
     * @param boolean $skipDetails if true include details of contacts  
     *
     * @return returns $searchRows array of returnproperties
     *
     * @static
     */
    static function getRelatedContacts( $caseID, $skipDetails = false )
    {
        $values = array( );
        $query = 'SELECT cc.display_name as name, cc.sort_name as sort_name, cc.id, crt.label_b_a as role, ce.email 
FROM civicrm_relationship cr 
LEFT JOIN civicrm_relationship_type crt ON crt.id = cr.relationship_type_id 
LEFT JOIN civicrm_contact cc ON cc.id = cr.contact_id_b 
LEFT JOIN civicrm_email   ce ON ce.contact_id = cc.id
WHERE cr.case_id =  %1 AND ce.is_primary= 1
GROUP BY cc.id';
        
        $params = array( 1 => array( $caseID, 'Integer' ) );
        $dao    =& CRM_Core_DAO::executeQuery( $query, $params );

        while ( $dao->fetch( ) ) {
            if ( $skipDetails ) {
                $values[$dao->id] = 1;
                
            } else {
                $values[] = array( 'contact_id'   => $dao->id,
                                   'display_name' => $dao->name,
                                   'sort_name'    => $dao->sort_name,
                                   'role'         => $dao->role,
                                   'email'        => $dao->email
                                   );
            }
        }
        $dao->free( );

        return $values;
    }

    /**
     * Function that sends e-mail copy of activity
     * 
     * @param int     $activityId activity Id
     * @param array   $contacts array of related contact
     *
     * @return void
     * @access public
     */
    static function sendActivityCopy( $clientId, $activityId, $contacts, $attachments = null, $caseId )
    {   
        if ( !$activityId ) {
            return;
        }

        require_once 'CRM/Utils/Mail.php';
        require_once 'CRM/Contact/BAO/Contact/Location.php';        
        $tplParams = $activityInfo = array( );
        //if its a case activity
        if ( $caseId ) {
            $activityTypeId       = CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity', $activityId, 'activity_type_id');
            $nonCaseActivityTypes = CRM_Core_PseudoConstant::activityType( );
            if ( CRM_Utils_Array::value( $activityTypeId, $nonCaseActivityTypes ) ) {
                $anyActivity = true;
            } else {
                $anyActivity = false; 
            }
            $tplParams['isCaseActivity'] = 1;
            $tplParams['client_id'] = $clientId;
        } else {
            $anyActivity = true;
        }
        
        require_once 'CRM/Case/XMLProcessor/Process.php';
        $xmlProcessorProcess = new CRM_Case_XMLProcessor_Process( );
        $isRedact = $xmlProcessorProcess->getRedactActivityEmail( );

        require_once 'CRM/Case/XMLProcessor/Report.php';
        $xmlProcessorReport = new CRM_Case_XMLProcessor_Report( );

        $activityInfo = $xmlProcessorReport->getActivityInfo($clientId, $activityId, $anyActivity, $isRedact );
        if ( $caseId ) { 
            $activityInfo['fields'][] = array( 'label' => 'Case ID', 'type' => 'String', 'value' => $caseId ); 
        }
        $tplParams['activity'] = $activityInfo;
        foreach ($tplParams['activity']['fields'] as $k => $val) {
            if ( CRM_Utils_Array::value('label', $val) == ts('Subject') ) {
                $activitySubject = $val['value'];
                break;
            }
        }
        $session = CRM_Core_Session::singleton( );
        // CRM-8926 If user is not logged in, use the activity creator as userID
        if ( !( $userID = $session->get( 'userID' ) ) ) {
            $userID = CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity', $activityId, 'source_contact_id');
        }
        
        //also create activities simultaneously of this copy.
        require_once "CRM/Activity/BAO/Activity.php";
        $activityParams = array( );
        
        $activityParams['source_record_id']   = $activityId; 
        $activityParams['source_contact_id']  = $userID; 
        $activityParams['activity_type_id']   = CRM_Core_OptionGroup::getValue( 'activity_type', 'Email', 'name' );
        $activityParams['activity_date_time'] = date('YmdHis');
        $activityParams['status_id']          = CRM_Core_OptionGroup::getValue( 'activity_status', 'Completed', 'name' );
        $activityParams['medium_id']          = CRM_Core_OptionGroup::getValue( 'encounter_medium', 'email', 'name' );
        $activityParams['case_id']            = $caseId;
        $activityParams['is_auto']            = 0;
        
        $tplParams['activitySubject'] = $activitySubject;

        // if it’s a case activity, add hashed id to the template (CRM-5916)
        if ($caseId) {
            $tplParams['idHash']  = substr(sha1(CIVICRM_SITE_KEY . $caseId), 0, 7);
        } 
        
        $result = array();
        list ($name, $address) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $userID );
        
        $receiptFrom = "$name <$address>";   
        
        $recordedActivityParams = array();
        
        foreach ( $contacts as $mail => $info ) {
            $tplParams['contact'] = $info;
            self::buildPermissionLinks( $tplParams, $activityParams );

            $displayName = $info['display_name'];

            require_once 'CRM/Core/BAO/MessageTemplates.php';
            list ($result[$info['contact_id']], $subject, $message, $html) = CRM_Core_BAO_MessageTemplates::sendTemplate(
                array(
                    'groupName'   => 'msg_tpl_workflow_case',
                    'valueName'   => 'case_activity',
                    'contactId'   => $info['contact_id'],
                    'tplParams'   => $tplParams,
                    'from'        => $receiptFrom,
                    'toName'      => $displayName,
                    'toEmail'     => $mail,
                    'attachments' => $attachments,
                )
            );

            $activityParams['subject']           = $activitySubject.' - copy sent to '.$displayName;
            $activityParams['details']           = $message;
            
            if ($result[$info['contact_id']]) {
            	/*
            	 * Really only need to record one activity with all the targets combined.
            	 * Originally the template was going to possibly have different content, e.g. depending on permissions,
            	 * but it's always the same content at the moment.
            	 */
                if ( empty( $recordedActivityParams ) ) {
                    $recordedActivityParams = $activityParams;
                } else {
                	$recordedActivityParams['subject'] .= "; $displayName";
                }
                $recordedActivityParams['target_contact_id'][] = $info['contact_id'];
            } else {
                unset($result[$info['contact_id']]);  
            }
        }

        if ( ! empty( $recordedActivityParams ) ) {
	        $activity = CRM_Activity_BAO_Activity::create( $recordedActivityParams );
	                
	        //create case_activity record if its case activity.
	        if ( $caseId ) {
	            $caseParams = array( 'activity_id' => $activity->id,
	                                 'case_id'     => $caseId );
	            self::processCaseActivity( $caseParams );
	        }
        }
        
        return $result;
    }
    
    /**
     * Retrieve count of activities having a particular type, and
     * associated with a particular case.
     *
     * @param int    $caseId          ID of the case
     * @param int    $activityTypeId  ID of the activity type
     * 
     * @return array
     * 
     * @access public
     * 
     */
    static function getCaseActivityCount( $caseId, $activityTypeId ) 
    {
        $queryParam = array( 1 => array( $caseId, 'Integer' ),
                             2 => array( $activityTypeId, 'Integer' ) );
        $query = "SELECT count(ca.id) as countact 
FROM       civicrm_activity ca
INNER JOIN civicrm_case_activity cca ON ca.id = cca.activity_id 
WHERE      ca.activity_type_id = %2 
AND       cca.case_id = %1
AND        ca.is_deleted = 0"            
;
        
        $dao = CRM_Core_DAO::executeQuery($query, $queryParam);
        if ( $dao->fetch() ) {
            return $dao->countact;
        }
        
        return false;
    }
    
    /**
     * Create an activity for a case via email
     * 
     * @param int    $file   email sent       
     *       
     * @return $activity object of newly creted activity via email
     * 
     * @access public
     * 
     */
    static function recordActivityViaEmail( $file ) 
    {
        if ( ! file_exists( $file ) ||
             ! is_readable( $file ) ) {
            return CRM_Core_Error::fatal( ts( 'File %1 does not exist or is not readable',
                                              array( 1 => $file ) ) );
        }
        
        require_once 'CRM/Utils/Mail/Incoming.php';
        $result = CRM_Utils_Mail_Incoming::parse( $file );
        if ( $result['is_error'] ) {
            return $result;
        }

        foreach( $result['to'] as $to ) {
            $caseId = null;

            $emailPattern = '/^([A-Z0-9._%+-]+)\+([\d]+)@[A-Z0-9.-]+\.[A-Z]{2,4}$/i';
            $replacement  = preg_replace ($emailPattern, '$2', $to['email']); 

            if ( $replacement !== $to['email'] ) {
                $caseId = $replacement;
                //if caseId is invalid, return as error file
                if( !CRM_Core_DAO::getFieldValue('CRM_Case_DAO_Case', $caseId, 'id') ) {
                    return CRM_Core_Error::createAPIError( ts( 'Invalid case ID ( %1 ) in TO: field.',
                                                               array( 1 => $caseId ) ) );  
                }
            } else {
                continue;
            }

// TODO: May want to replace this with a call to getRelatedAndGlobalContacts() when this feature is revisited.
// (Or for efficiency call the global one outside the loop and then union with this each time.)            
            $contactDetails = self::getRelatedContacts( $caseId, true );

            if ( CRM_Utils_Array::value( $result['from']['id'], $contactDetails ) ) {
                $params = array( );
                $params['subject']            = $result['subject'];
                $params['activity_date_time'] = $result['date'];
                $params['details']            = $result['body'];
                $params['source_contact_id']  = $result['from']['id'];
                $params['status_id']          = CRM_Core_OptionGroup::getValue('activity_status',
                                                                               'Completed',
                                                                               'name' );
            
                $details = CRM_Case_PseudoConstant::activityType( );
                $matches = array( );
                preg_match( '/^\W+([a-zA-Z0-9_ ]+)(\W+)?\n/i',
                            $result['body'], $matches );

                if ( !empty($matches) && isset($matches[1]) ) {
                    $activityType = trim($matches[1]);
                    if ( isset($details[$activityType]) ) {
                        $params['activity_type_id'] = $details[$activityType]['id'];
                    }
                }
                if ( ! isset($params['activity_type_id']) ) {
                    $params['activity_type_id'] = 
                        CRM_Core_OptionGroup::getValue( 'activity_type', 'Inbound Email', 'name' );
                }

                // create activity
                require_once "CRM/Activity/BAO/Activity.php";
                $activity = CRM_Activity_BAO_Activity::create( $params );

                $caseParams = array( 'activity_id' => $activity->id,
                                     'case_id'     => $caseId   );
                self::processCaseActivity( $caseParams );
            } else {
                return CRM_Core_Error::createAPIError( ts( 'FROM email contact %1 doesn\'t have a relationship to the referenced case.',
                                                           array( 1 => $result['from']['email'] ) ) );   
            }
        } 
    }

    /**
     * Function to retrive the scheduled activity type and date
     * 
     * @param  array  $cases  Array of contact and case id        
     *       
     * @return array  $activityInfo Array of scheduled activity type and date
     * 
     * @access public
     *
     * @static
     */
    static function getNextScheduledActivity( $cases, $type = 'upcoming' ) 
    {
        $session   = CRM_Core_Session::singleton( );
        $userID    = $session->get( 'userID' );

        $caseID    = implode ( ',', $cases['case_id']);
        $contactID = implode ( ',', $cases['contact_id'] );

        $condition = "
AND civicrm_case_contact.contact_id IN( {$contactID} ) 
AND civicrm_case.id IN( {$caseID})
AND civicrm_activity.is_deleted = {$cases['case_deleted']}
AND civicrm_case.is_deleted     = {$cases['case_deleted']}";

        $query = self::getCaseActivityQuery( $type, $userID, $condition, $cases['case_deleted'] );

        $res   = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );

        $activityInfo = array();
        while( $res->fetch() ) {
            if ( $type == 'upcoming' ) {
                $activityInfo[$res->case_id]['date'] = $res->case_scheduled_activity_date;
                $activityInfo[$res->case_id]['type'] = $res->case_scheduled_activity_type;
            } else {
                $activityInfo[$res->case_id]['date'] = $res->case_recent_activity_date;
                $activityInfo[$res->case_id]['type'] = $res->case_recent_activity_type;
            }
        } 

        return $activityInfo;
    }

    /**
     * combine all the exportable fields from the lower levels object
     *     
     * @return array array of exportable Fields
     * @access public
     */
    function &exportableFields( ) 
    {
        if ( ! self::$_exportableFields ) {
            if ( ! self::$_exportableFields ) {
                self::$_exportableFields = array();
            }
            require_once 'CRM/Case/DAO/Case.php';
            
            $fields = CRM_Case_DAO_Case::export( );
            $fields['case_role']   = array( 'title' => ts('Role in Case') );
            $fields['case_type']   = array( 'title' => ts( 'Case Type' ),
                                            'name'  => 'case_type' );
            $fields['case_status'] = array( 'title' => ts( 'Case Status' ),
                                            'name'  => 'case_status' );

            self::$_exportableFields = $fields;
        }
        return self::$_exportableFields;
    }
    
    /**                                                           
     * Restore the record that are associated with this case 
     * 
     * @param  int  $caseId id of the case to restore
     * 
     * @return true if success.
     * @access public 
     * @static 
     */ 
    static function restoreCase( $caseId ) 
    {
        //restore activities
        $activities = self::getCaseActivityDates( $caseId );
        if ( $activities ) {
            require_once"CRM/Activity/BAO/Activity.php";
            foreach( $activities as $value ) {
                CRM_Activity_BAO_Activity::restoreActivity( $value );
            }
        }  
        //restore case
        require_once 'CRM/Case/DAO/Case.php';
        $case     = new CRM_Case_DAO_Case( );
        $case->id = $caseId; 
        $case->is_deleted = 0;
        $case->save( );
        
        //CRM-7364, enable relationships
        self::enableDisableCaseRelationships( $caseId, true );            
        return true;
    }
    
    static function getGlobalContacts(&$groupInfo)
    {
        $globalContacts = array();

        require_once 'CRM/Case/XMLProcessor/Settings.php';
        require_once 'CRM/Contact/BAO/Group.php';
        $settingsProcessor = new CRM_Case_XMLProcessor_Settings();
        $settings = $settingsProcessor->run();
        if (!empty($settings)) {
            $groupInfo['name'] = $settings['groupname'];
            if ($groupInfo['name']) {
                $searchParams = array('name' => $groupInfo['name']);
                $results = array();
                CRM_Contact_BAO_Group::retrieve($searchParams, $results);
                if ($results) {
                    $groupInfo['id']    = $results['id'];
                    $groupInfo['title'] = $results['title'];
                    require_once 'CRM/Contact/BAO/Query.php';
                    $params = array(array('group', 'IN', array($groupInfo['id'] => 1), 0, 0));
                    $return = array('sort_name', 'display_name', 'email', 'phone');
                    list($globalContacts, $_) = CRM_Contact_BAO_Query::apiQuery($params, $return);
                }
            }
        }
        return $globalContacts;
    }

	/* 
	 * Convenience function to get both case contacts and global in one array
	 */
	static function getRelatedAndGlobalContacts( $caseId )
	{
		$relatedContacts = self::getRelatedContacts( $caseId );
            
		$groupInfo = array();
		$globalContacts = self::getGlobalContacts( $groupInfo );
             
        //unset values which are not required.
        foreach( $globalContacts as $k => &$v ) {
             unset($v['email_id']);
             unset($v['group_contact_id']); 
             unset($v['status']);
             unset($v['phone']);
             $v['role'] = $groupInfo['title'];
        }
        //include multiple listings for the same contact/different roles.
        $relatedGlobalContacts = array_merge( $relatedContacts, $globalContacts );
        return $relatedGlobalContacts;
	}   

    /**
     * Function to get Case ActivitiesDueDates with given criteria. 
     *
     * @param int      $caseID case id
     * @param array    $criteriaParams given criteria
     * @param boolean  $latestDate if set newest or oldest date is selceted.
     *
     * @return returns case activities due dates
     *
     * @static
     */
    static function getCaseActivityDates( $caseID, $criteriaParams = array( ), $latestDate = false )
    {
        $values     = array( );
        $selectDate = " ca.activity_date_time";
        $where      = $groupBy = ' ';
        
        if ( !$caseID ) {
            return;
        }
        
        if ( $latestDate ) {
            if ( CRM_Utils_Array::value( 'activity_type_id', $criteriaParams ) ) {
                $where   .= " AND ca.activity_type_id    = ".CRM_Utils_Type::escape( $criteriaParams['activity_type_id'], 'Integer' );
                $where   .= " AND ca.is_current_revision = 1";
                $groupBy .= " GROUP BY ca.activity_type_id";
            }
            
            if ( CRM_Utils_Array::value( 'newest', $criteriaParams ) ) {
                $selectDate = " max(ca.activity_date_time) "; 
            } else {
                $selectDate = " min(ca.activity_date_time) "; 
            }
        }
        
        $query = "SELECT ca.id, {$selectDate} as activity_date
                  FROM civicrm_activity ca 
                  LEFT JOIN civicrm_case_activity cca ON cca.activity_id = ca.id LEFT JOIN civicrm_case cc ON cc.id = cca.case_id 
                  WHERE cc.id = %1 {$where} {$groupBy}";
        
        $params = array( 1 => array( $caseID, 'Integer' ) );
        $dao    =& CRM_Core_DAO::executeQuery( $query, $params );
        
        while ( $dao->fetch( ) ) {
            $values[$dao->id]['id']            = $dao->id;
            $values[$dao->id]['activity_date'] = $dao->activity_date;
        }
        $dao->free( );
        return $values;
    }
    
    /**
     * Function to create activities when Case or Other roles assigned/modified/deleted. 
     *
     * @param int      $caseID case id
     * @param int      $relationshipId relationship id
     * @param int      $relContactId case role assigne contactId.
     *
     * @return void on success creates activity and case activity 
     *
     * @static
     */
    static function createCaseRoleActivity( $caseId, $relationshipId, $relContactId = null, $contactId = null )
    {
        if ( !$caseId || !$relationshipId || empty($relationshipId) ) {
            return;    
        }
        
        $queryParam = array( );
        if ( is_array($relationshipId) ) {
            $relationshipId     = implode( ',', $relationshipId );
            $relationshipClause = " civicrm_relationship.id IN ($relationshipId)";
        } else {
            $relationshipClause = " civicrm_relationship.id = %1";
            $queryParam[1] = array( $relationshipId, 'Positive' );
        }
        
        $query = "
   SELECT  cc.display_name as clientName, 
           cca.display_name as  assigneeContactName,  
           civicrm_relationship.case_id as caseId,
           civicrm_relationship_type.label_a_b as relation_a_b,
           civicrm_relationship_type.label_b_a as relation_b_a,
           civicrm_relationship.contact_id_b as rel_contact_id,            
           civicrm_relationship.contact_id_a as assign_contact_id 
     FROM  civicrm_relationship_type,  civicrm_relationship 
LEFT JOIN  civicrm_contact cc  ON cc.id  = civicrm_relationship.contact_id_b  
LEFT JOIN  civicrm_contact cca ON cca.id = civicrm_relationship.contact_id_a
    WHERE  civicrm_relationship.relationship_type_id = civicrm_relationship_type.id AND {$relationshipClause}";
        
        $dao = CRM_Core_DAO::executeQuery( $query,$queryParam );
        
        while ( $dao->fetch() ) {
            //to get valid assignee contact(s).
            if ( isset($dao->caseId) || $dao->rel_contact_id != $contactId ) { 
                $caseRelationship = $dao->relation_a_b;
                $assigneContactName = $dao->clientName;
                $assigneContactIds[$dao->rel_contact_id] = $dao->rel_contact_id;
            } else {
                $caseRelationship = $dao->relation_b_a;
                $assigneContactName = $dao->assigneeContactName;
                $assigneContactIds[$dao->assign_contact_id] = $dao->assign_contact_id; 
            }
        }
        
        require_once 'CRM/Core/OptionGroup.php';
        $session = & CRM_Core_Session::singleton();
        $activityParams = array('source_contact_id'    => $session->get( 'userID' ),
                                'subject'              => $caseRelationship.' : '. $assigneContactName,
                                'activity_date_time'   => date('YmdHis'),
                                'status_id'            => CRM_Core_OptionGroup::getValue( 'activity_status', 'Completed', 'name' )
                                );

        //if $relContactId is passed, role is added or modified.
        if ( !empty($relContactId) ) {
            $activityParams['assignee_contact_id'] = $assigneContactIds;

            $activityTypeID = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                              'Assign Case Role',
                                                              'name' );
        } else {
            $activityTypeID = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                              'Remove Case Role',
                                                              'name' );
        }
        
        $activityParams['activity_type_id']    = $activityTypeID;
        
        require_once "CRM/Activity/BAO/Activity.php";
        $activity = CRM_Activity_BAO_Activity::create( $activityParams );
        
        //create case_activity record.
        $caseParams = array( 'activity_id' => $activity->id,
                             'case_id'     => $caseId );
        
        require_once "CRM/Activity/BAO/Activity.php";
        CRM_Case_BAO_Case::processCaseActivity( $caseParams );
    }

    /**
     * Function to get case manger 
     * contact which is assigned a case role of case manager. 
     *
     * @param int    $caseType case type
     * @param int    $caseId   case id
     *
     * @return array $caseManagerContact array of contact on success otherwise empty 
     *
     * @static
     */
    static function getCaseManagerContact( $caseType, $caseId )
    {
        if ( !$caseType || !$caseId ) {
            return;
        }
        
        $caseManagerContact = array( );
        require_once 'CRM/Case/XMLProcessor/Process.php';
        $xmlProcessor  = new CRM_Case_XMLProcessor_Process( );
        
        $managerRoleId = $xmlProcessor->getCaseManagerRoleId( $caseType );
        
        if ( !empty($managerRoleId) ) {
            $managerRoleQuery = "
SELECT civicrm_contact.id as casemanager_id, 
       civicrm_contact.sort_name as casemanager
FROM civicrm_contact 
LEFT JOIN civicrm_relationship ON (civicrm_relationship.contact_id_b = civicrm_contact.id AND civicrm_relationship.relationship_type_id = %1)
LEFT JOIN civicrm_case ON civicrm_case.id = civicrm_relationship.case_id
WHERE civicrm_case.id = %2";
            
            $managerRoleParams = array( 1 => array( $managerRoleId  , 'Integer' ),
                                        2 => array( $caseId         , 'Integer' ) );

            $dao = CRM_Core_DAO::executeQuery( $managerRoleQuery, $managerRoleParams );
            if ( $dao->fetch() ) {
                    $caseManagerContact['casemanager_id'] = $dao->casemanager_id;
                    $caseManagerContact['casemanager'   ] = $dao->casemanager;
            }
        }
        
        return $caseManagerContact; 
    }
    
    /**
     * Get all cases with no end dates
     * 
     * @return array of case and related data keyed on case id
     */
    static function getUnclosedCases( $params = array( ), $excludeCaseIds =array( ), $excludeDeleted = true  )
    {
    	//params from ajax call.
        $where = array( '( ca.end_date is null )' );
        if ( $caseType = CRM_Utils_Array::value( 'case_type',$params ) ) {
            $where[] = "( ov.label LIKE '%$caseType%' )"; 
        }
        if ( $sortName = CRM_Utils_Array::value( 'sort_name',$params )  ) {
            $config  = CRM_Core_Config::singleton( );
            $search  = ( $config->includeWildCardInName ) ? "%$sortName%" : "$sortName%";
            $where[] = "( sort_name LIKE '$search' )";
        }
        if ( is_array( $excludeCaseIds ) && 
             !CRM_Utils_System::isNull( $excludeCaseIds ) ) {
            $where[] = ' ( ca.id NOT IN ( ' . implode( ',', $excludeCaseIds ) . ' ) ) ';
        }
        if ( $excludeDeleted ) {
            $where[] =  ' ( ca.is_deleted = 0 OR ca.is_deleted IS NULL ) '; 
        }

        //filter for permissioned cases.
        $filterCases = array( );
        $doFilterCases = false;
        if ( !CRM_Core_Permission::check( 'access all cases and activities' ) ) {
            $doFilterCases = true;
            $session  = CRM_Core_Session::singleton( );
            $filterCases = CRM_Case_BAO_Case::getCases( false, $session->get( 'userID' ) );
        }
        $whereClause = implode( ' AND ', $where );
        
        $limitClause = '';
        if ( $limit = CRM_Utils_Array::value( 'limit', $params ) ) {
            $limitClause = "LIMIT 0, $limit"; 
        }
        
        $query = "
    SELECT  c.id as contact_id, 
            c.sort_name,
            ca.id, 
            ca.subject as case_subject,
            ov.label as case_type,
            ca.start_date as start_date
      FROM  civicrm_case ca INNER JOIN civicrm_case_contact cc ON ca.id=cc.case_id
INNER JOIN  civicrm_contact c ON cc.contact_id=c.id
INNER JOIN  civicrm_option_group og ON og.name='case_type'
INNER JOIN  civicrm_option_value ov ON (ca.case_type_id=ov.value AND ov.option_group_id=og.id)
     WHERE  {$whereClause} 
  ORDER BY  c.sort_name
            {$limitClause}
";        
        $dao = CRM_Core_DAO::executeQuery( $query );
        $unclosedCases = array();
        while ( $dao->fetch() ) {
            if ( $doFilterCases && 
                 !array_key_exists( $dao->id, $filterCases ) ) {
                continue;
            }
            $unclosedCases[$dao->id] = array( 'sort_name'    => $dao->sort_name,
                                              'case_type'    => $dao->case_type,
                                              'contact_id'   => $dao->contact_id,
                                              'start_date'   => $dao->start_date,
                                              'case_subject' => $dao->case_subject
                                              );
        }
        $dao->free( );
        
        return $unclosedCases;
    }
    
    function caseCount( $contactId = null, $excludeDeleted = true )
    {
        $whereConditions = array( );
        if ( $excludeDeleted ) {
            $whereConditions[] = "( civicrm_case.is_deleted = 0 OR civicrm_case.is_deleted IS NULL )";
        }
        if ( $contactId ) {
            $whereConditions[] = "civicrm_case_contact.contact_id = {$contactId}";
        }
        if ( !CRM_Core_Permission::check( 'access all cases and activities' ) ) {
            static $accessibleCaseIds;
            if ( !is_array( $accessibleCaseIds ) ) {
                $session  = CRM_Core_Session::singleton( );
                $accessibleCaseIds = array_keys( self::getCases( false, $session->get( 'userID' ) ) );
            }
            //no need of further processing.
            if ( empty( $accessibleCaseIds ) ) {
                return 0;
            }
            $whereConditions[] = "( civicrm_case.id in (". implode( ',', $accessibleCaseIds ). ") )";
        }

        $whereClause = '';
        if ( !empty( $whereConditions ) ) {
            $whereClause = "WHERE " . implode( ' AND ', $whereConditions );
        }
        
        $query = "       
   SELECT  count( civicrm_case.id )
     FROM  civicrm_case
LEFT JOIN  civicrm_case_contact ON ( civicrm_case.id = civicrm_case_contact.case_id )
           {$whereClause}";
        
        return CRM_Core_DAO::singleValueQuery( $query ); 
    }
    
    /**
     * Retrieve cases related to particular contact.
     *
     * @param int     $contactId contact id
     * @param boolean $excludeDeleted do not include deleted cases.
     *
     * @return an array of cases.
     * 
     * @access public
     */
    function getContactCases( $contactId, $excludeDeleted = true )
    {
        $cases = array( );
        if ( !$contactId ) {
            return $cases;
        }
        
        $whereClause = "civicrm_case_contact.contact_id = %1";
        if ( $excludeDeleted ) {
            $whereClause .= " AND ( civicrm_case.is_deleted = 0 OR civicrm_case.is_deleted IS NULL )";
        }
        
        $query = "
    SELECT  civicrm_case.id, case_type_ov.label as case_type, civicrm_case.start_date
      FROM  civicrm_case
INNER JOIN  civicrm_case_contact ON ( civicrm_case.id = civicrm_case_contact.case_id )
 LEFT JOIN  civicrm_option_group case_type_og ON ( case_type_og.name = 'case_type' )
 LEFT JOIN  civicrm_option_value case_type_ov ON ( civicrm_case.case_type_id = case_type_ov.value
                                                   AND case_type_og.id = case_type_ov.option_group_id )
     WHERE  {$whereClause}";
        
        $dao = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $contactId,  'Integer' ) ) );
        while ( $dao->fetch( ) ) {
            $cases[$dao->id] = array( 'case_id'         => $dao->id,
                                      'case_type'       => $dao->case_type,
                                      'case_start_date' => $dao->start_date );
        }
        $dao->free( );
        
        return $cases;
    }
    
    /**
     * Retrieve related cases for give case.
     *
     * @param int     $mainCaseId     id of main case
     * @param int     $contactId      id of contact
     * @param boolean $excludeDeleted do not include deleted cases.
     *
     * @return an array of related cases.
     * 
     * @access public
     */
    function getRelatedCases( $mainCaseId, $contactId, $excludeDeleted = true )
    {
        //FIXME : do check for permissions.
        
        $relatedCases = array( );
        if ( !$mainCaseId || !$contactId ) {
            return $relatedCases;
        }
        
        require_once 'CRM/Core/PseudoConstant.php';
        $linkActType = array_search( 'Link Cases', 
                                     CRM_Core_PseudoConstant::activityType( true, true, false, 'name' ) );
        if ( !$linkActType ) {
            return $relatedCases;
        }
        
        $whereClause = "mainCase.id = %2";
        if ( $excludeDeleted ) {
            $whereClause .= " AND ( relAct.is_deleted = 0 OR relAct.is_deleted IS NULL )";
        }
        
        //1. first fetch related case ids.
        $query = "
    SELECT  relCaseAct.case_id
      FROM  civicrm_case mainCase
INNER JOIN  civicrm_case_activity mainCaseAct ON (mainCaseAct.case_id = mainCase.id)
INNER JOIN  civicrm_activity mainAct          ON (mainCaseAct.activity_id = mainAct.id AND mainAct.activity_type_id = %1)
INNER JOIN  civicrm_case_activity relCaseAct  ON (relCaseAct.activity_id = mainAct.id AND mainCaseAct.id !=  relCaseAct.id) 
INNER JOIN  civicrm_activity relAct           ON (relCaseAct.activity_id = relAct.id  AND relAct.activity_type_id = %1)
     WHERE  $whereClause";
        
        $dao = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $linkActType, 'Integer' ),
                                                          2 => array( $mainCaseId,  'Integer' ) ) );
        $relatedCaseIds = array( );
        while ( $dao->fetch( ) ) {
            $relatedCaseIds[$dao->case_id] = $dao->case_id;
        }
        $dao->free( );
        
        // there are no related cases.
        if ( empty( $relatedCaseIds ) ) {
            return $relatedCases;
        }
        
        $whereClause = 'relCase.id IN ( ' . implode( ',', $relatedCaseIds ) . ' )';
        if ( $excludeDeleted ) {
            $whereClause .= " AND ( relCase.is_deleted = 0 OR relCase.is_deleted IS NULL )";
        }
        

        //filter for permissioned cases.
        $filterCases = array( );
        $doFilterCases = false;
        if ( !CRM_Core_Permission::check( 'access all cases and activities' ) ) {
            $doFilterCases = true;
            $session  = CRM_Core_Session::singleton( );
            $filterCases = CRM_Case_BAO_Case::getCases( false, $session->get( 'userID' ) );
        }
        
        //2. fetch the details of related cases.
        $query = "
    SELECT  relCase.id as id, 
            case_type_ov.label as case_type, 
            client.display_name as client_name,
            client.id as client_id
      FROM  civicrm_case relCase 
INNER JOIN  civicrm_case_contact relCaseContact ON ( relCase.id = relCaseContact.case_id )
INNER JOIN  civicrm_contact      client         ON ( client.id = relCaseContact.contact_id ) 
 LEFT JOIN  civicrm_option_group case_type_og   ON ( case_type_og.name = 'case_type' )
 LEFT JOIN  civicrm_option_value case_type_ov   ON ( relCase.case_type_id = case_type_ov.value
                                                     AND case_type_og.id = case_type_ov.option_group_id )
     WHERE  {$whereClause}";
        
        $dao = CRM_Core_DAO::executeQuery( $query );
        $contactViewUrl = CRM_Utils_System::url( "civicrm/contact/view", "reset=1&cid=" );
        $hasViewContact = CRM_Core_Permission::giveMeAllACLs( );
        
        while ( $dao->fetch( ) ) {
            $caseView = null;
            if ( !$doFilterCases || array_key_exists( $dao->id, $filterCases ) ) {
                $caseViewStr = "reset=1&id={$dao->id}&cid={$dao->client_id}&action=view&context=case&selectedChild=case";
                $caseViewUrl = CRM_Utils_System::url( "civicrm/contact/view/case", $caseViewStr );
                $caseView    = "<a href='{$caseViewUrl}'>". ts( 'View Case' ). "</a>";
            }
            $clientView = $dao->client_name;
            if ( $hasViewContact ) {
                $clientView  = "<a href='{$contactViewUrl}{$dao->client_id}'>$dao->client_name</a>";
            }
            
            $relatedCases[$dao->id] = array( 'case_id'      => $dao->id,
                                             'case_type'    => $dao->case_type,
                                             'client_name'  => $clientView,
                                             'links'        => $caseView );
        }
        $dao->free( );
        
        return $relatedCases;
    }
    
    /**
     * Function perform two task.
     * 1. Merge two duplicate contacts cases - follow CRM-5758 rules.
     * 2. Merge two cases of same contact - follow CRM-5598 rules.
     *
     * @param int $mainContactId    contact id of main contact record.
     * @param int $mainCaseId       case id of main case record.
     * @param int $otherContactId   contact id of record which is going to merge.
     * @param int $otherCaseId      case id of record which is going to merge.
     *
     * @return void.
     * @static
     */  
    function mergeCases( $mainContactId, $mainCaseId = null, 
                         $otherContactId = null, $otherCaseId = null, $changeClient = false ) 
    {
        $moveToTrash = true;
        
        $duplicateContacts = false;
        if ( $mainContactId && $otherContactId &&
             $mainContactId != $otherContactId ) {
            $duplicateContacts = true;
        }
        
        $duplicateCases = false;
        if ( $mainCaseId && $otherCaseId && 
             $mainCaseId != $otherCaseId ) {
            $duplicateCases = true;
        }
        
        $mainCaseIds = array( );
        if ( !$duplicateContacts && !$duplicateCases ) {
            return $mainCaseIds;
        }
        
        require_once 'CRM/Core/PseudoConstant.php';
        $activityTypes    = CRM_Core_PseudoConstant::activityType( true, true, false, 'name' );
        $activityStatuses = CRM_Core_PseudoConstant::activityStatus( 'name' );
        
        $processCaseIds = array( $otherCaseId );
        if ( $duplicateContacts && !$duplicateCases ) {
            if ( $changeClient ) {
                $processCaseIds = array( $mainCaseId );
            } else {
                //get all case ids for other contact.
                $processCaseIds = self::retrieveCaseIdsByContactId( $otherContactId, true );
            }
            if ( !is_array( $processCaseIds ) ) return;
        }
        
        require_once 'CRM/Case/DAO/CaseContact.php';
        require_once 'CRM/Activity/DAO/Activity.php';
        require_once 'CRM/Case/DAO/CaseActivity.php';
        require_once 'CRM/Contact/DAO/Relationship.php';
        require_once 'CRM/Activity/DAO/ActivityTarget.php';
        require_once 'CRM/Activity/DAO/ActivityAssignment.php';
        require_once 'CRM/Activity/BAO/Activity.php';
        
        $session = CRM_Core_Session::singleton( );
        $currentUserId = $session->get( 'userID' );
        
        // copy all cases and connect to main contact id.
        foreach ( $processCaseIds as $otherCaseId ) {
            if ( $duplicateContacts ) {            
                $mainCase = CRM_Core_DAO::copyGeneric( 'CRM_Case_DAO_Case', array( 'id' => $otherCaseId ) );
                $mainCaseId = $mainCase->id;
                if ( !$mainCaseId ) continue;
                $mainCase->free( );
                $mainCaseIds[] = $mainCaseId;
                //insert record for case contact.
                $otherCaseContact = new CRM_Case_DAO_CaseContact( );
                $otherCaseContact->case_id = $otherCaseId;
                $otherCaseContact->find( );
                while ( $otherCaseContact->fetch( ) ) {
                    $mainCaseContact = new CRM_Case_DAO_CaseContact( );
                    $mainCaseContact->case_id    = $mainCaseId;
                    $mainCaseContact->contact_id = $otherCaseContact->contact_id;
                    if ( $mainCaseContact->contact_id == $otherContactId ) {
                        $mainCaseContact->contact_id = $mainContactId;
                    }
                    //avoid duplicate object.
                    if ( !$mainCaseContact->find(true) ) {
                        $mainCaseContact->save( );
                    }
                    $mainCaseContact->free( );
                }
                $otherCaseContact->free( );
            } else if ( !$otherContactId ) {
                $otherContactId = $mainContactId;
            }
            
            if ( !$mainCaseId || !$otherCaseId ||
                 !$mainContactId || !$otherContactId ) {
                continue;
            }
            
            // get all activities for other case.
            $otherCaseActivities = array( );
            CRM_Core_DAO::commonRetrieveAll( 'CRM_Case_DAO_CaseActivity', 'case_id',  $otherCaseId, $otherCaseActivities );
            
            //for duplicate cases do not process singleton activities.
            $otherActivityIds = $singletonActivityIds = array( );
            foreach ( $otherCaseActivities as $caseActivityId => $otherIds ) {
                $otherActId = CRM_Utils_Array::value( 'activity_id', $otherIds );
                if ( !$otherActId || in_array( $otherActId, $otherActivityIds ) ) {
                    continue;
                }
                $otherActivityIds[] = $otherActId;
            }
            if ( $duplicateCases ) {
                if ( $openCaseType = array_search( 'Open Case', $activityTypes ) ) { 
                    $sql = "
SELECT  id
  FROM  civicrm_activity 
 WHERE  activity_type_id = $openCaseType 
   AND  id IN ( ". implode( ',', array_values( $otherActivityIds ) ) . ');';
                    $dao = CRM_Core_DAO::executeQuery( $sql );
                    while ( $dao->fetch( ) ) {
                        $singletonActivityIds[] = $dao->id;
                    }
                    $dao->free( );
                }
            }
            
            // migrate all activities and connect to main contact.
            $copiedActivityIds = $activityMappingIds = array( );
            sort( $otherActivityIds );
            foreach ( $otherActivityIds as $otherActivityId ) {
                         
                //for duplicate cases - 
                //do not migrate singleton activities.
                if ( !$otherActivityId || in_array( $otherActivityId, $singletonActivityIds ) ) {
                    continue; 
                }
                
                //migrate activity record.
                $otherActivity = new CRM_Activity_DAO_Activity( );
                $otherActivity->id = $otherActivityId;
                if ( !$otherActivity->find( true ) ) continue;
                
                $mainActVals  = array( );
                $mainActivity = new CRM_Activity_DAO_Activity( );
                CRM_Core_DAO::storeValues( $otherActivity, $mainActVals );
                $mainActivity->copyValues( $mainActVals );
                $mainActivity->id = null;
                $mainActivity->activity_date_time = CRM_Utils_Date::isoToMysql($otherActivity->activity_date_time);
                //do check for merging contact,
                if ( $mainActivity->source_contact_id == $otherContactId ) {
                    $mainActivity->source_contact_id = $mainContactId;
                }
                $mainActivity->source_record_id = CRM_Utils_Array::value( $mainActivity->source_record_id, 
                                                                          $activityMappingIds );
                
                $mainActivity->original_id = CRM_Utils_Array::value( $mainActivity->original_id, 
                                                                     $activityMappingIds );
                
                $mainActivity->parent_id   = CRM_Utils_Array::value( $mainActivity->parent_id, 
                                                                     $activityMappingIds );
                $mainActivity->save( );
                $mainActivityId = $mainActivity->id;
                if ( !$mainActivityId ) continue;
                
                $activityMappingIds[$otherActivityId] = $mainActivityId ;
                // insert log of all activites
                CRM_Activity_BAO_Activity::logActivityAction( $mainActivity );
                                
                $otherActivity->free( );
                $mainActivity->free( );
                $copiedActivityIds[] = $otherActivityId;
                
                //create case activity record.
                $mainCaseActivity = new CRM_Case_DAO_CaseActivity( );
                $mainCaseActivity->case_id     = $mainCaseId;
                $mainCaseActivity->activity_id = $mainActivityId;
                $mainCaseActivity->save( );
                $mainCaseActivity->free( );
                
                //migrate target activities.
                $otherTargetActivity = new CRM_Activity_DAO_ActivityTarget( );
                $otherTargetActivity->activity_id = $otherActivityId;
                $otherTargetActivity->find( );
                while ( $otherTargetActivity->fetch( ) ) {
                    $mainActivityTarget = new CRM_Activity_DAO_ActivityTarget( );
                    $mainActivityTarget->activity_id       = $mainActivityId;
                    $mainActivityTarget->target_contact_id = $otherTargetActivity->target_contact_id; 
                    if ( $mainActivityTarget->target_contact_id == $otherContactId ) {
                        $mainActivityTarget->target_contact_id = $mainContactId;
                    }
                    //avoid duplicate object.
                    if ( !$mainActivityTarget->find( true ) ) {
                        $mainActivityTarget->save( );
                    }
                    $mainActivityTarget->free( );
                }
                $otherTargetActivity->free( );
                
                //migrate assignee activities.
                $otherAssigneeActivity = new CRM_Activity_DAO_ActivityAssignment( );
                $otherAssigneeActivity->activity_id = $otherActivityId;
                $otherAssigneeActivity->find( );
                while ( $otherAssigneeActivity->fetch( ) ) {
                    $mainAssigneeActivity = new CRM_Activity_DAO_ActivityAssignment( );
                    $mainAssigneeActivity->activity_id         = $mainActivityId;
                    $mainAssigneeActivity->assignee_contact_id = $otherAssigneeActivity->assignee_contact_id;
                    if ( $mainAssigneeActivity->assignee_contact_id == $otherContactId ) {
                        $mainAssigneeActivity->assignee_contact_id = $mainContactId;
                    }
                    //avoid duplicate object.
                    if ( !$mainAssigneeActivity->find( true ) ) {
                        $mainAssigneeActivity->save( );
                    }
                    $mainAssigneeActivity->free( );
                }
                $otherAssigneeActivity->free( );
            }
            
            //copy case relationship.
            if ( $duplicateContacts ) {
                //migrate relationship records.
                $otherRelationship = new CRM_Contact_DAO_Relationship( );
                $otherRelationship->case_id = $otherCaseId;
                $otherRelationship->find( );
                $otherRelationshipIds = array( );
                while ( $otherRelationship->fetch( ) ) {
                    $otherRelVals = array( );
                    $updateOtherRel = false;
                    CRM_Core_DAO::storeValues( $otherRelationship, $otherRelVals );
                    
                    $mainRelationship = new CRM_Contact_DAO_Relationship( );
                    $mainRelationship->copyValues( $otherRelVals );
                    $mainRelationship->id = null;
                    $mainRelationship->case_id = $mainCaseId;
                    if ( $mainRelationship->contact_id_a == $otherContactId ) {
                        $updateOtherRel = true;
                        $mainRelationship->contact_id_a = $mainContactId;
                    }
                    
                    //case creator change only when we merge user contact.
                    if ( $mainRelationship->contact_id_b == $otherContactId ) {
                        //do not change creator for change client. 
                        if ( !$changeClient ) {
                            $updateOtherRel = true;
                            $mainRelationship->contact_id_b = ( $currentUserId ) ? $currentUserId : $mainContactId;
                        }
                    }
                    $mainRelationship->end_date   = CRM_Utils_Date::isoToMysql( $otherRelationship->end_date );
                    $mainRelationship->start_date = CRM_Utils_Date::isoToMysql( $otherRelationship->start_date );
                    
                    //avoid duplicate object.
                    if ( !$mainRelationship->find( true ) ) {
                        $mainRelationship->save( );
                    }
                    $mainRelationship->free( );
                    
                    //get the other relationship ids to update end date.
                    if ( $updateOtherRel ) $otherRelationshipIds[$otherRelationship->id] = $otherRelationship->id;
                }
                $otherRelationship->free( );
                
                //update other relationships end dates
                if ( !empty( $otherRelationshipIds ) ) {
                    $sql = 'UPDATE  civicrm_relationship 
                               SET  end_date = CURDATE() 
                             WHERE  id IN ( '. implode( ',', $otherRelationshipIds ) . ')';
                    CRM_Core_DAO::executeQuery( $sql );
                }
            }
            
            //move other case to trash.
            $mergeCase = self::deleteCase( $otherCaseId, $moveToTrash );
            if ( !$mergeCase ) continue;

            $mergeActSubject = $mergeActSubjectDetails = $mergeActType = '';
            if ( $changeClient ) {
                require_once 'CRM/Contact/BAO/Contact.php';
                $mainContactDisplayName  = CRM_Contact_BAO_Contact::displayName( $mainContactId  );
                $otherContactDisplayName = CRM_Contact_BAO_Contact::displayName( $otherContactId );
                
                $mergeActType = array_search( 'Reassigned Case', $activityTypes );
                $mergeActSubject = ts( "Case %1 reassigned client from %2 to %3. New Case ID is %4.", 
                                       array( 1 => $otherCaseId,            2 => $otherContactDisplayName, 
                                              3 => $mainContactDisplayName, 4 => $mainCaseId ) ); 
            } else if ( $duplicateContacts ) {
                $mergeActType = array_search( 'Merge Case', $activityTypes );
                $mergeActSubject = ts( "Case %1 copied from contact id %2 to contact id %3 via merge. New Case ID is %4.", 
                                       array( 1 => $otherCaseId,   2 => $otherContactId, 
                                              3 => $mainContactId, 4 => $mainCaseId ) ); 
            } else {
                $mergeActType = array_search( 'Merge Case', $activityTypes );
                $mergeActSubject = ts( "Case %1 merged into case %2", array( 1 => $otherCaseId, 2 => $mainCaseId ) );
                if ( !empty( $copiedActivityIds ) ) {
                    $sql = '
SELECT id, subject, activity_date_time, activity_type_id
FROM civicrm_activity
WHERE id IN ('. implode( ',', $copiedActivityIds ) . ')';
                    $dao = CRM_Core_DAO::executeQuery( $sql );
                    while ( $dao->fetch( ) ) {
                        $mergeActSubjectDetails .= "{$dao->activity_date_time} :: {$activityTypes[$dao->activity_type_id]}";
                        if ( $dao->subject ) {
                           $mergeActSubjectDetails .= " :: {$dao->subject}";
                        }
                        $mergeActSubjectDetails .= "<br />";
                    }
                }
            }
            
            //create merge activity record.
            $activityParams = array( 'subject'            => $mergeActSubject,
                                     'details'            => $mergeActSubjectDetails,
                                     'status_id'          => array_search( 'Completed',  $activityStatuses ),
                                     'activity_type_id'   => $mergeActType,
                                     'source_contact_id'  => $mainContactId,
                                     'activity_date_time' => date('YmdHis') );
            
            $mergeActivity = CRM_Activity_BAO_Activity::create( $activityParams );
            $mergeActivityId = $mergeActivity->id;
            if ( !$mergeActivityId ) continue; 
            $mergeActivity->free( );
            
            //connect merge activity to case. 
            $mergeCaseAct = array( 'case_id'     => $mainCaseId,
                                   'activity_id' => $mergeActivityId );
            
            self::processCaseActivity( $mergeCaseAct );
        }
        return $mainCaseIds;
    }
    
    /**
     * Validate contact permission for 
     * edit/view on activity record and build links.
     *
     * @param array   $tplParams       params to be sent to template for sending email.
     * @param array   $activityParams  info of the activity.
     *
     * @return void
     * @static
     */
    function buildPermissionLinks( &$tplParams, $activityParams ) 
    {
        $activityTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Activity_DAO_Activity', $activityParams['source_record_id'], 
                                                       'activity_type_id', 'id' );
        
        if ( CRM_Utils_Array::value( 'isCaseActivity', $tplParams ) ) {
            $tplParams['editActURL'] = CRM_Utils_System::url( 'civicrm/case/activity', 
                                                              "reset=1&cid={$activityParams['source_contact_id']}&caseid={$activityParams['case_id']}&action=update&id={$activityParams['source_record_id']}", true );
            
            $tplParams['viewActURL'] = CRM_Utils_System::url( 'civicrm/case/activity/view', 
                                                              "reset=1&aid={$activityParams['source_record_id']}&cid={$tplParams['contact']['contact_id']}&caseID={$activityParams['case_id']}", true );

            $tplParams['manageCaseURL'] = CRM_Utils_System::url( 'civicrm/contact/view/case', 
                                                              "reset=1&id={$activityParams['case_id']}&cid={$tplParams['contact']['contact_id']}&action=view&context=home", true );
        } else {   
            $tplParams['editActURL'] = CRM_Utils_System::url( 'civicrm/contact/view/activity', 
                                                              "atype=$activityTypeId&action=update&reset=1&id={$activityParams['source_record_id']}&cid={$tplParams['contact']['contact_id']}&context=activity", true );
            
            $tplParams['viewActURL'] = CRM_Utils_System::url( 'civicrm/contact/view/activity', 
                                                              "atype=$activityTypeId&action=view&reset=1&id={$activityParams['source_record_id']}&cid={$tplParams['contact']['contact_id']}&context=activity", true );
        }
    }
    /**
     * Validate contact permission for 
     * given operation on activity record.
     *
     * @param int     $activityId      activity record id.
     * @param string  $operation       user operation.
     * @param int     $actTypeId       activity type id.
     * @param int     $contactId       contact id/if not pass consider logged in
     * @param boolean $checkComponent  do we need to check component enabled.
     *
     * @return boolean $allow  true/false
     * @static
     */
    function checkPermission( $activityId, $operation, $actTypeId = null, $contactId = null, $checkComponent = true )
    {
        $allow = false;
        if ( !$actTypeId && $activityId ) {
            $actTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Activity_DAO_Activity', $activityId, 'activity_type_id' );  
        }
        
        if ( !$activityId || !$operation || !$actTypeId ) {
            return $allow;
        }
        
        //do check for civicase component enabled.
        if ( $checkComponent ) {
            static $componentEnabled;
            if ( !isset( $componentEnabled ) ) {
                $config = CRM_Core_Config::singleton( );
                $componentEnabled = false;
                if ( in_array( 'CiviCase', $config->enableComponents ) ) {
                    $componentEnabled = true;
                }
            }
            if ( !$componentEnabled ) {
                return $allow;
            }
        }
        
        //do check for cases.
        $caseActOperations = array( 'File On Case', 'Link Cases', 'Move To Case', 'Copy To Case' );
        if ( in_array( $operation, $caseActOperations ) ) {
            static $unclosedCases;
            if ( !is_array( $unclosedCases ) ) {
                $unclosedCases = self::getUnclosedCases( );
            }
            if ( $operation == 'File On Case' ) {
                $allow = (empty( $unclosedCases )) ? false : true; 
            } else {
                $allow = (count( $unclosedCases ) > 1) ? true : false;
            }
        }
                
        $actionOperations = array( 'view', 'edit', 'delete' );
        if ( in_array( $operation, $actionOperations ) ) {
            
            //do cache when user has non/supper permission.
            static $allowOperations;
            
            if ( !is_array( $allowOperations ) || 
                 !array_key_exists( $operation, $allowOperations ) ) {
                
                if ( !$contactId ) {
                    $session   = CRM_Core_Session::singleton( );
                    $contactId = $session->get('userID'); 
                }
                
                //check for permissions.
                $permissions = array( 'view'   => array( 'access my cases and activities', 
                                                         'access all cases and activities' ),
                                      'edit'   => array( 'access my cases and activities',
                                                         'access all cases and activities' ),
                                      'delete' => array( 'delete activities' ) );
                
                //check for core permission.
                require_once 'CRM/Core/Permission.php';
                $hasPermissions   = array( );
                $checkPermissions = CRM_Utils_Array::value( $operation, $permissions );
                if ( is_array( $checkPermissions ) ) {
                    foreach ( $checkPermissions as $per ) {
                        if ( CRM_Core_Permission::check( $per ) ) {
                            $hasPermissions[$operation][] = $per;
                        }
                    }
                }
                
                //has permissions.
                if ( !empty( $hasPermissions ) ) {
                    //need to check activity object specific.
                    if ( in_array( $operation, array( 'view', 'edit' ) ) ) {
                        //do we have supper permission.
                        if ( in_array( 'access all cases and activities', $hasPermissions[$operation] ) ) {
                            $allowOperations[$operation] = $allow = true;
                        } else {
                            //user has only access to my cases and activity.
                            //here object specific permmions come in picture.
                            
                            //edit - contact must be source or assignee
                            //view - contact must be source/assignee/target
                            $isTarget = $isAssignee = $isSource = false;
                            
                            require_once 'CRM/Activity/DAO/Activity.php';
                            require_once 'CRM/Activity/DAO/ActivityTarget.php';
                            require_once 'CRM/Activity/DAO/ActivityAssignment.php';
                            
                            $target = new CRM_Activity_DAO_ActivityTarget( );
                            $target->activity_id       = $activityId;
                            $target->target_contact_id = $contactId;
                            if ( $target->find( true ) ) $isTarget = true;
                            
                            $assignee = new CRM_Activity_DAO_ActivityAssignment( );
                            $assignee->activity_id         = $activityId;
                            $assignee->assignee_contact_id = $contactId;
                            if ( $assignee->find( true ) ) $isAssignee = true;
                            
                            $activity = new CRM_Activity_DAO_Activity( );
                            $activity->id                = $activityId;
                            $activity->source_contact_id = $contactId;
                            if ( $activity->find( true ) ) $isSource = true;
                            
                            if ( $operation == 'edit' ) {
                                if ( $isAssignee || $isSource ) $allow = true;
                            }
                            if ( $operation == 'view' ) {
                                if ( $isTarget ||$isAssignee || $isSource ) $allow = true;
                            }
                        }
                    } else if ( is_array( $hasPermissions[$operation] ) ) {
                        $allowOperations[$operation] = $allow = true;
                    }
                } else {
                    //contact do not have permission.
                    $allowOperations[$operation] = false;
                }
            } else {
                //use cache.
                //here contact might have supper/non permission.
                $allow = $allowOperations[$operation];
            }
        }
        
        //do further only when operation is granted. 
        if ( $allow ) {
            require_once 'CRM/Core/PseudoConstant.php';
            $activityTypes = CRM_Core_PseudoConstant::activityType( true, true, false, 'name' );
            
            //get the activity type name.
            $actTypeName = CRM_Utils_Array::value( $actTypeId, $activityTypes );
            
            //do not allow multiple copy / edit action.
            $singletonNames = array( 'Open Case', 'Reassigned Case', 'Merge Case', 'Link Cases', 'Assign Case Role', 'Email', 'Inbound Email' );
            
            //do not allow to delete these activities, CRM-4543
            $doNotDeleteNames = array( 'Open Case', 'Change Case Type', 'Change Case Status', 'Change Case Start Date' );
            
            //allow edit operation.
            $allowEditNames = array( 'Open Case' );
            
            if ( in_array( $actTypeName, $singletonNames ) ) {
                $allow = false;
                if ( in_array( $operation, $actionOperations ) ) {
                    $allow = true;
                    if ( $operation == 'edit' ) {
                        $allow = (in_array($actTypeName, $allowEditNames)) ? true : false;
                    } else if ( $operation == 'delete' ) {
                        $allow = (in_array($actTypeName, $doNotDeleteNames)) ? false : true;
                    }
                }
            }
            if ( $allow && ($operation == 'delete') && 
                 in_array( $actTypeName, $doNotDeleteNames ) ) {
                $allow = false;
            }
            
            //check settings file for masking actions
            //on the basis the activity types
            //hide Edit link if activity type is NOT editable 
            //(special case activities).CRM-5871
            if ( $allow && in_array( $operation, $actionOperations ) ) {
                static $actionFilter = array( );
                if ( !array_key_exists( $operation, $actionFilter ) ) {
                    require_once 'CRM/Case/XMLProcessor/Process.php';
                    $xmlProcessor = new CRM_Case_XMLProcessor_Process( );
                    $actionFilter[$operation] = $xmlProcessor->get( 'Settings', 'ActivityTypes', false, $operation );
                }
                if ( array_key_exists( $operation, $actionFilter[$operation] ) &&
                     in_array( $actTypeId, $actionFilter[$operation][$operation] ) ) {
                    $allow = false;
                }
            }
        }
        
        return $allow;
    }
    
    /**
     * since we drop 'access CiviCase', allow access
     * if user has 'access my cases and activities' 
     * or 'access all cases and activities'
     */
    function accessCiviCase( ) 
    {
        static $componentEnabled;
        if ( !isset( $componentEnabled ) ) {
            $componentEnabled = false;
            $config = CRM_Core_Config::singleton( );
            if (  in_array( 'CiviCase', $config->enableComponents ) ) {
                $componentEnabled = true; 
            }
        }
        if ( !$componentEnabled ) return false;
        
        if ( CRM_Core_Permission::check( 'access my cases and activities' ) ||
             CRM_Core_Permission::check( 'access all cases and activities' ) ) {
            return true;  
        }
        
        return false;
    }
    
    /**
     * Function to check whether activity is a case Activity
     *
     * @param  int      $activityID   activity id
     *
     * @return boolean  $isCaseActivity true/false
     */
    static function isCaseActivity( $activityID )
    {
        $isCaseActivity = false;
        if ( $activityID ) {
            $params = array( 1 => array( $activityID, 'Integer' ) ); 
            $query = "SELECT id FROM civicrm_case_activity WHERE activity_id = %1";
            if ( CRM_Core_DAO::singleValueQuery( $query, $params ) ) {
                $isCaseActivity = true;
            }
        }
        
        return $isCaseActivity;
    }

    /**
     * Function to get all the case type ids currently in use 
     *
     * 
     * @return array $caseTypeIds 
     */
    static function getUsedCaseType( ) 
    {
        static $caseTypeIds;
        
        if ( !is_array( $caseTypeIds ) ) {
            $query = "SELECT DISTINCT( civicrm_case.case_type_id ) FROM civicrm_case";
            
            $dao = CRM_Core_DAO::executeQuery( $query );
            $caseTypeIds = array( );
            while ( $dao->fetch( ) ) {
                $typeId        = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                          $dao->case_type_id );
                $caseTypeIds[] = $typeId[1];
            }  
        }

        return $caseTypeIds;
    }

    /**
     * Function to get all the case status ids currently in use 
     *
     * 
     * @return array $caseStatusIds 
     */
    static function getUsedCaseStatuses( ) 
    {
        static $caseStatusIds;

        if ( !is_array( $caseStatusIds ) ) {
            $query = "SELECT DISTINCT( civicrm_case.status_id ) FROM civicrm_case";
            
            $dao = CRM_Core_DAO::executeQuery( $query );
            $caseStatusIds = array( );
            while ( $dao->fetch( ) ) {
                $caseStatusIds[] = $dao->status_id;
            }
        }
        
        return $caseStatusIds;
    }

    /**
     * Function to get all the encounter medium ids currently in use 
     *
     * 
     * @return array  
     */
    static function getUsedEncounterMediums( ) 
    {
        static $mediumIds;
        
        if ( !is_array( $mediumIds ) ) {
            $query = "SELECT DISTINCT( civicrm_activity.medium_id )  FROM civicrm_activity";
            
            $dao = CRM_Core_DAO::executeQuery( $query );
            $mediumIds = array( );
            while ( $dao->fetch( ) ) {
                $mediumIds[] = $dao->medium_id;
            }
        }
        
        return  $mediumIds;
    }
    
    /**
     * Function to check case configuration.
     *
     * @return an array $configured  
     */
    function isCaseConfigured( $contactId = null ) 
    {
        $configured = array_fill_keys( array( 'configured', 'allowToAddNewCase', 'redirectToCaseAdmin' ), false ); 
        
        //lets check for case configured.
        require_once 'CRM/Case/BAO/Case.php';
        $allCasesCount = CRM_Case_BAO_Case::caseCount( null, false );
        $configured['configured'] = ( $allCasesCount ) ? true : false;
        if ( !$configured['configured'] ) {
            //do check for case type and case status.
            require_once 'CRM/Case/PseudoConstant.php';
            $caseTypes = CRM_Case_PseudoConstant::caseType( 'label', false );
            if ( !empty( $caseTypes ) ) {
                $configured['configured'] = true;
                if ( !$configured['configured'] ) {
                    $caseStatuses = CRM_Case_PseudoConstant::caseStatus( 'label', false );
                    if ( !empty( $caseStatuses ) ) $configured['configured'] = true;
                }
            }
        }
        if ( $configured['configured'] ) {
            //do check for active case type and case status.
            require_once 'CRM/Case/PseudoConstant.php';
            $caseTypes = CRM_Case_PseudoConstant::caseType( );
            if ( !empty( $caseTypes ) ) {
                $caseStatuses = CRM_Case_PseudoConstant::caseStatus( );
                if ( !empty( $caseStatuses ) ) $configured['allowToAddNewCase'] = true;
            }
            
            //do we need to redirect user to case admin.
            if ( !$configured['allowToAddNewCase'] && $contactId ) {
                //check for current contact case count.
                $currentContatCasesCount = CRM_Case_BAO_Case::caseCount( $contactId );
                //redirect user to case admin page.
                if ( !$currentContatCasesCount ) $configured['redirectToCaseAdmin'] = true; 
            }
        }
        
        return $configured;
    }
    
}

