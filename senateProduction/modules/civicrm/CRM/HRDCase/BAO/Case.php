<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Case/DAO/Case.php';

/**
 * This class contains the funtions for Case Management
 *
 */
class CRM_Case_BAO_Case extends CRM_Case_DAO_Case
{
    /**  
     * value seletor for multi-select
     **/ 
   
    const VALUE_SEPERATOR = "";
    
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
        
        $case = self::add( $params );

        if ( is_a( $case, 'CRM_Core_Error') ) {
            $transaction->rollback( );
            return $case;
        }
        $session = & CRM_Core_Session::singleton();
        $id = $session->get('userID');
        if ( !$id ) {
            $id = $params['contact_id'];
        } 

        // Log the information on successful add/edit of Case
        require_once 'CRM/Core/BAO/Log.php';
        $logParams = array(
                           'entity_table'  => 'civicrm_case',
                           'entity_id'     => $case->id,
                           'modified_id'   => $id,
                           'modified_date' => date('Ymd')
                           );
        
        CRM_Core_BAO_Log::add( $logParams );
        $transaction->commit( );
        
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
    function addCaseToContact( $params ) {
        require_once 'CRM/Case/DAO/CaseContact.php';
        $caseContact = new CRM_Case_DAO_CaseContact();
        $caseContact->case_id = $params['case_id'];
        $caseContact->contact_id = $params['contact_id'];
        $caseContact->find(true);
        $caseContact->save();

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
    function deleteCaseContact( $caseID ) {
        require_once 'CRM/Case/DAO/CaseContact.php';
        $caseContact = new CRM_Case_DAO_CaseContact();
        $caseContact->case_id = $caseID;
        $caseContact->delete();
    }

    /**
     * Get the values for pseudoconstants for name->value and reverse.
     *
     * @param array   $defaults (reference) the default values, some of which need to be resolved.
     * @param boolean $reverse  true if we want to resolve the values in the reverse direction (value -> name)
     *
     * @return void
     * @access public
     * @static
     */
    static function resolveDefaults(&$defaults, $reverse = false)
    {
    
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
     * Delete the record that are associated with this case 
     * record are deleted from case 
     * @param  int  $caseId id of the case to delete
     * 
     * @return void
     * @access public 
     * @static 
     */ 
    static function deleteCase( $caseId ) 
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        require_once 'CRM/Case/DAO/Case.php';
        $case     = new CRM_Case_DAO_Case( );
        $case->id = $caseId; 
        $case->delete( );

        $transaction->commit( );
    }

   /**                                                           
     * Delete the activities related to case
     *
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
    /* * Retrieve contact_id by case_id
     *
     * @param int    $caseId  ID of the case
     * 
     * @return array
     * 
     * @access public
     * 
     */
    
     function retrieveContactIdsByCaseId( $caseId , $contactID = null ) 
     {
         require_once 'CRM/Case/DAO/CaseContact.php';
         $caseContact =   & new CRM_Case_DAO_CaseContact( );
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
        $queryParam = array();
        $query = "SELECT contact_a.sort_name 
                  FROM civicrm_contact contact_a 
                  LEFT JOIN civicrm_case_contact 
                         ON civicrm_case_contact.contact_id = contact_a.id
                  WHERE civicrm_case_contact.case_id = {$caseId}";
        $dao = CRM_Core_DAO::executeQuery($query,$queryParam);
        $contactNames = array();
        while ( $dao->fetch() ) {
            $contactNames[] =  $dao->sort_name;
        }
        return $contactNames;
    }

    /* * Retrieve case_id by contact_id
     *
     * @param int    $contactId  ID of the contact
     * 
     * @return array
     * 
     * @access public
     * 
     */
    function retrieveCaseIdsByContactId( $contactID ) 
    {
         require_once 'CRM/Case/DAO/CaseContact.php';
         $caseContact =   & new CRM_Case_DAO_CaseContact( );
         $caseContact->contact_id = $contactID;
         $caseContact->find();
         $caseArray = array();
         $count = 1;
         while ( $caseContact->fetch( ) ) {
             $caseArray[$count] = $caseContact->case_id;
             $count++;
         }
         
         return $caseArray;
     }

}


