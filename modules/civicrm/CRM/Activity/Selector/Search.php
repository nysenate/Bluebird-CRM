<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
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

require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';
require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';
require_once 'CRM/Contact/BAO/Query.php';

/**
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Activity_Selector_Search extends CRM_Core_Selector_Base implements CRM_Core_Selector_API 
{
    /**
     * This defines two actions- View and Edit.
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     * @static
     */
    static $_columnHeaders;

    /**
     * Properties of contact we're interested in displaying
     * @var array
     * @static
     */
    
    static $_properties = array(  
                                'contact_id',
                                'contact_type', 
                                'contact_sub_type', 
                                'sort_name', 
                                'display_name',
                                'activity_id',
                                'activity_date_time',
                                'activity_status_id',
                                'activity_status',
                                'activity_subject',
                                'source_contact_id',
                                'source_record_id',
                                'source_contact_name',
                                'activity_type_id',
                                'activity_type',
                                'activity_is_test'
                                );
    
    /** 
     * are we restricting ourselves to a single contact 
     * 
     * @access protected   
     * @var boolean   
     */   
    protected $_single = false;
    
    /**  
     * are we restricting ourselves to a single contact  
     *  
     * @access protected    
     * @var boolean    
     */    
    protected $_limit = null;
    
    /**
     * what context are we being invoked from
     *   
     * @access protected     
     * @var string
     */     
    protected $_context = null;
    
    /**
     * what component context are we being invoked from
     *   
     * @access protected     
     * @var string
     */     
    protected $_compContext = null;

    /**
     * queryParams is the array returned by exportValues called on
     * the HTML_QuickForm_Controller for that page.
     *
     * @var array
     * @access protected
     */
    public $_queryParams;
    
    /**
     * represent the type of selector
     *
     * @var int
     * @access protected
     */
    protected $_action;

    /** 
     * The additional clause that we restrict the search with 
     * 
     * @var string 
     */ 
    protected $_activityClause = null;
    
    /** 
     * The query object
     * 
     * @var string 
     */ 
    protected $_query;
    
    /**
     * Class constructor
     *
     * @param array   $queryParams array of parameters for query
     * @param int     $action - action of search basic or advanced.
     * @param string  $activityClause if the caller wants to further restrict the search (used in activities)
     * @param boolean $single are we dealing only with one contact?
     * @param int     $limit  how many activities do we want returned
     *
     * @return CRM_Contact_Selector
     * @access public
     */
    function __construct(&$queryParams,
                         $action = CRM_Core_Action::NONE,
                         $activityClause = null,
                         $single = false,
                         $limit = null,
                         $context = 'search',
                         $compContext = null ) 
    {
        // submitted form values
        $this->_queryParams =& $queryParams;
        
        $this->_single  = $single;
        $this->_limit   = $limit;
        $this->_context = $context;
        $this->_compContext = $compContext;

        $this->_activityClause = $activityClause;
        
        // type of selector
        $this->_action = $action;
        $this->_query = new CRM_Contact_BAO_Query( $this->_queryParams, null, null, false, false,
                                                   CRM_Contact_BAO_Query::MODE_ACTIVITY );
        $this->_query->_distinctComponentClause = 'DISTINCT ( civicrm_activity.id )';
    
    	//CRM_Core_Error::debug( $this->_query ); exit();
    }//end of constructor
    
    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams( $action, &$params ) 
    {
        $params['status']       = ts('Activities %%StatusMessage%%');
        $params['csvString']    = null;
        $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    } //end of function
    
    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action)
    {
        return $this->_query->searchQuery( 0, 0, null,
                                           true, false, 
                                           false, false, 
                                           false, 
                                           $this->_activityClause );
    }
    
    /**
     * returns all the rows in the given offset and rowCount
     *
     * @param enum   $action   the action being performed
     * @param int    $offset   the row number to start from
     * @param int    $rowCount the number of rows to return
     * @param string $sort     the sql string that describes the sort order
     * @param enum   $output   what should the result set include (web/email/csv)
     *
     * @return array  rows in the given offset and rowCount
     */
     function &getRows( $action, $offset, $rowCount, $sort, $output = null ) {
                  
         $result = $this->_query->searchQuery( $offset, $rowCount, $sort,
                                               false, false, 
                                               false, false, 
                                               false, 
                                               $this->_activityClause );
         $rows = array( );
         require_once 'CRM/Mailing/BAO/Mailing.php';
         $mailingIDs =& CRM_Mailing_BAO_Mailing::mailingACLIDs( );
         $accessCiviMail = CRM_Core_Permission::check( 'access CiviMail' );
         
         while ( $result->fetch( ) ) {
             $row = array( );

             // ignore rows where we dont have an activity id
             if ( empty( $result->activity_id ) ) {
                 continue;
             }

            // the columns we are interested in
            foreach ( self::$_properties as $property) {
                if ( isset( $result->$property ) ) {
                    $row[$property] = $result->$property;
                }
            }
            
            $contactId = CRM_Utils_Array::value( 'contact_id', $row );
            if ( !$contactId ) {
                $contactId = CRM_Utils_Array::value( 'source_contact_id', $row );
            }

            $row['target_contact_name'] = CRM_Activity_BAO_ActivityTarget::getTargetNames( $row['activity_id'] );
            $row['assignee_contact_name'] = CRM_Activity_BAO_ActivityAssignment::getAssigneeNames( $row['activity_id'] );
                     
            if ( CRM_Utils_Array::value( 'source_contact_id', $row ) ) {
                $row['source_contact_name'] = CRM_Contact_BAO_Contact::displayName( $row['source_contact_id'] );
            }
            if ( $this->_context == 'search' ) {
                $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->activity_id;
            }
            require_once( 'CRM/Contact/BAO/Contact/Utils.php' );
            $row['contact_type'] =
                CRM_Contact_BAO_Contact_Utils::getImage( $result->contact_sub_type ?
                                                         $result->contact_sub_type : $result->contact_type ,false,$result->contact_id);
            $accessMailingReport = false;
            $activityType = CRM_Core_PseudoConstant::activityType( true, true );
            $activityTypeId = CRM_Utils_Array::key( $row['activity_type'], $activityType );
            if ( $row['activity_is_test'] ) {
                $row['activity_type'] = $row['activity_type'] . " (test)";
            }
            $bulkActivityTypeID = CRM_Utils_Array::key( 'Bulk Email', $activityType );
            if ( $accessCiviMail && in_array( $result->source_record_id, $mailingIDs ) && ( $bulkActivityTypeID == $activityTypeId ) ) {
                $row['mailingId'] = 
                    CRM_Utils_System::url( 'civicrm/mailing/report', 
                                           "mid={$result->source_record_id}&reset=1&cid={$result->source_contact_id}&context=activitySelector" ); 
                $row['recipients'] = ts('(recipients)');
                $row['target_contact_name'] = '';
                $row['assignee_contact_name'] = '';
                $accessMailingReport = true;
            }
            require_once 'CRM/Activity/Selector/Activity.php';
            $activityActions = new CRM_Activity_Selector_Activity( $result->contact_id, null );
            $actionLinks = $activityActions->actionLinks( $activityTypeId,
                                                          CRM_Utils_Array::value( 'source_record_id', $row ),
                                                          $accessMailingReport,
                                                          CRM_Utils_Array::value( 'activity_id', $row ),
                                                          $this->_key,
                                                          $this->_compContext );
            $row['action'] = CRM_Core_Action::formLink( $actionLinks, null,
                                                        array( 'id'  => $result->activity_id,
                                                               'cid' => $contactId,
                                                               'cxt' => $this->_context ) );
            $rows[] = $row;
         }
         
         return $rows;
     }
     
     /**
      * @return array  $qill  which contains an array of strings
      * @access public
      */
     
     public function getQILL( )
     {
         return $this->_query->qill( );
     }
     
     /** 
      * returns the column headers as an array of tuples: 
      * (name, sortName (key to the sort array)) 
      * 
      * @param string $action the action being performed 
      * @param enum   $output what should the result set include (web/email/csv) 
      * 
      * @return array the column headers that need to be displayed 
      * @access public 
      */ 
     public function &getColumnHeaders( $action = null, $output = null ) 
     {
         if ( ! isset( self::$_columnHeaders ) ) {
             self::$_columnHeaders = array(
                                           array(
                                                 'name'      => ts('Type'),
                                                 'sort'      => 'activity_type_id',
                                                 'direction' => CRM_Utils_Sort::DONTCARE,
                                                 ),
                                           array(
                                                 'name'      => ts('Subject'),
                                                 'sort'      => 'subject',
                                                 'direction' => CRM_Utils_Sort::DONTCARE,
                                                 ),
                                           array(
                                                 'name'      => ts('Added By'),
                                                 'sort'      => 'sort_name',
                                                 'direction' => CRM_Utils_Sort::DONTCARE,
                                                 ),
                                           array( 'name'      => ts('With') ),
                                           array( 'name'      => ts('Assigned') ),
                                           array(
                                                 'name'      => ts('Date'),
                                                 'sort'      => 'activity_date_time',
                                                 'direction' => CRM_Utils_Sort::DESCENDING,
                                                 ),
                                           array(
                                                 'name'      => ts('Status'),
                                                 'sort'      => 'status_id',
                                                 'direction' => CRM_Utils_Sort::DONTCARE,
                                                 ),
                                           array(
                                                 'desc' => ts('Actions')),
                                           );
             
         }
         return self::$_columnHeaders;
     }
     
     function alphabetQuery( ) {
         return $this->_query->searchQuery( null, null, null, false, false, true );
     }

     function &getQuery( ) {
         return $this->_query;
     }
     
     /** 
      * name of export file. 
      * 
      * @param string $output type of output 
      * @return string name of the file 
      */ 
     function getExportFileName( $output = 'csv')
     { 
         return ts('CiviCRM Activity Search'); 
     } 
     
}//end of class