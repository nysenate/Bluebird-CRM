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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';

require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';

require_once 'CRM/Activity/BAO/Activity.php';

/**
 * This class is used to retrieve and display activities for a contact
 *
 */
class CRM_Activity_Selector_Activity extends CRM_Core_Selector_Base implements CRM_Core_Selector_API 
{
    /**
     * This defines two actions - Details and Delete.
     *
     * @var array
     * @static
     */
    static $_actionLinks;

    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     * @static
     */
    static $_columnHeaders;

    /**
     * contactId - contact id of contact whose activies are displayed
     *
     * @var int
     * @access protected
     */
    protected $_contactId;

    protected $_admin;

    protected $_context;
    
    protected $_viewOptions;

    /**
     * Class constructor
     *
     * @param int $contactId - contact whose activities we want to display
     * @param int $permission - the permission we have for this contact 
     *
     * @return CRM_Contact_Selector_Activity
     * @access public
     */
    function __construct($contactId, $permission, $admin = false, $context = 'activity' ) 
    {
        $this->_contactId  = $contactId;
        $this->_permission = $permission;
        $this->_admin      = $admin;
        $this->_context    = $context;

        // get all enabled view componentc (check if case is enabled)
        require_once 'CRM/Core/BAO/Preferences.php';
        $this->_viewOptions = CRM_Core_BAO_Preferences::valueOptions( 'contact_view_options', true, null, true );
    }

    /**
     * This method returns the action links that are given for each search row.
     * currently the action links added for each row are 
     * 
     * - View
     *
     * @param string $activityType type of activity
     *
     * @return array
     * @access public
     *
     */
    function actionLinks( $activityTypeId, 
                          $sourceRecordId = null, 
                          $accessMailingReport = false, 
                          $activityId = null, 
                          $key = null ) 
    {
        $activityTypes   = CRM_Core_PseudoConstant::activityType( false );
        $activityTypeIds = array_flip( CRM_Core_PseudoConstant::activityType( true, false, false, 'name' ) );
        
        $extraParams = ( $key ) ? "&key={$key}" : null;
        
        //show  edit link only for meeting/phone and other activities
        $showUpdate = false;
        $showDelete = false;
        if ( $activityTypeId == $activityTypeIds['Event Registration'] )  { // event registration
            $url      = 'civicrm/contact/view/participant';
            $qsView   = "action=view&reset=1&id={$sourceRecordId}&cid=%%cid%%&context=%%cxt%%{$extraParams}";
        } elseif ( $activityTypeId == $activityTypeIds['Contribution'] ) { //contribution
            $url      = 'civicrm/contact/view/contribution';
            $qsView   = "action=view&reset=1&id={$sourceRecordId}&cid=%%cid%%&context=%%cxt%%{$extraParams}";
        } elseif ( in_array($activityTypeId, 
                            array( $activityTypeIds['Membership Signup'], $activityTypeIds['Membership Renewal'] ) 
                            ) ) {  // membership
            $url      = 'civicrm/contact/view/membership';
            $qsView   = "action=view&reset=1&id={$sourceRecordId}&cid=%%cid%%&context=%%cxt%%{$extraParams}";
        } elseif ( $activityTypeId == CRM_Utils_Array::value( 'Pledge Acknowledgment', $activityTypeIds ) || 
                   $activityTypeId == CRM_Utils_Array::value( 'Pledge Reminder', $activityTypeIds ) ) { //pledge acknowledgment
            $url      = 'civicrm/contact/view/activity';
            $qsView   = "atype={$activityTypeId}&action=view&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%{$extraParams}";
        } elseif ( $activityTypeId == $activityTypeIds['Email'] ||  $activityTypeId == $activityTypeIds['Bulk Email'] ) {
            $url      = 'civicrm/activity/view';
            $delUrl   = 'civicrm/activity';
            $qsView   = "atype={$activityTypeId}&action=view&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%{$extraParams}";
            // allow delete of regular outbound emails (CRM-)
            if ( $activityTypeId == $activityTypeIds['Email'] ) {
                $showDelete = true;
            }
        } elseif ( $activityTypeId == $activityTypeIds['Inbound Email'] ) {
            $url      = 'civicrm/contact/view/activity';
            $qsView   = "atype={$activityTypeId}&action=view&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%{$extraParams}";
        } else {
            $showUpdate = true;
            $showDelete = true;
            $url      = 'civicrm/contact/view/activity';
            $qsView   = "atype={$activityTypeId}&action=view&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%{$extraParams}";
            $qsUpdate = "atype={$activityTypeId}&action=update&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%{$extraParams}";
        }

        $qsDelete  = "atype={$activityTypeId}&action=delete&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%{$extraParams}";
        
        if ( $this->_context == 'case' ) {
            $qsView   .= "&caseid=%%caseid%%";
            $qsDelete .= "&caseid=%%caseid%%";
            if ( $showUpdate ) {
                $qsUpdate .= "&caseid=%%caseid%%";
            }
        }
        
        self::$_actionLinks = array(
                                    CRM_Core_Action::VIEW => 
                                    array(
                                          'name'     => ts('View'),
                                          'url'      => $url,
                                          'qs'       => $qsView,
                                          'title'    => ts('View Activity'),
                                          )
                                    );
        
        if ( $showUpdate ) {
            self::$_actionLinks = self::$_actionLinks +  array ( CRM_Core_Action::UPDATE => 
                                                                 array(
                                                                       'name'     => ts('Edit'),
                                                                       'url'      => $url,
                                                                       'qs'       => $qsUpdate,
                                                                       'title'    => ts('Update Activity') ) );
        }

        require_once 'CRM/Case/BAO/Case.php';
        if ( CRM_Case_BAO_Case::checkPermission( $activityId, 'File On Case', $activityTypeId ) ) {
            self::$_actionLinks = self::$_actionLinks +  array ( CRM_Core_Action::ADD =>
                                                                 array( 
                                                                       'name'     => ts('File On Case'),
                                                                       'url'      => CRM_Utils_System::currentPath( ),
                                                                       'extra'    => 'onClick="Javascript:fileOnCase( \'file\', \'%%id%%\' ); return false;"',
                                                                       'title'    => ts('File On Case') ) );
        }

        if ( $showDelete ) {
            if ( ! isset($delUrl) || ! $delUrl ) {
                $delUrl = $url;
            }
            
            self::$_actionLinks = self::$_actionLinks +  array ( CRM_Core_Action::DELETE => 
                                                                 array(
                                                                       'name'     => ts('Delete'),
                                                                       'url'      => $delUrl,
                                                                       'qs'       => $qsDelete,
                                                                       'title'    => ts('Delete Activity') ) );
        }
        
        if ( $this->_context == 'case' ) {
            $qsDetach = "atype={$activityTypeId}&action=detach&reset=1&id=%%id%%&cid=%%cid%%&context=%%cxt%%&caseid=%%caseid%%{$extraParams}";

            self::$_actionLinks = self::$_actionLinks +  array ( CRM_Core_Action::DETACH => 
                                                                 array(
                                                                       'name'     => ts('Detach'),
                                                                       'url'      => $url,
                                                                       'qs'       => $qsDetach,
                                                                       'title'    => ts('Detach Activity') ) );
        }
        
        if ( $accessMailingReport ) {
            self::$_actionLinks = self::$_actionLinks +  array ( CRM_Core_Action::BROWSE => 
                                                                 array(
                                                                       'name'     => ts('Mailing Report'),
                                                                       'url'      => 'civicrm/mailing/report',
                                                                       'qs'       => "mid={$sourceRecordId}&reset=1&cid=%%cid%%&context=activitySelector",
                                                                       'title'    => ts('View Mailing Report'),
                                                                       ));    
        }
        
        return self::$_actionLinks;
    }

    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams($action, &$params) 
    {
        $params['status']       = ts('Activities %%StatusMessage%%');
        $params['csvString']    = null;
        $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;

        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
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
    function &getColumnHeaders($action = null, $output = null) 
    {
        if ($output==CRM_Core_Selector_Controller::EXPORT || $output==CRM_Core_Selector_Controller::SCREEN) {
            $csvHeaders = array( ts('Activity Type'), ts('Description'), ts('Activity Date'));
            foreach (self::_getColumnHeaders() as $column ) {
                if (array_key_exists( 'name', $column ) ) {
                    $csvHeaders[] = $column['name'];
                }
            }
            return $csvHeaders;
        } else {
            return self::_getColumnHeaders();
        }
        
    }


    /**
     * Returns total number of rows for the query.
     *
     * @param string $action - action being performed
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action, $case = null ) { 
        require_once 'CRM/Activity/BAO/Activity.php';
        return CRM_Activity_BAO_Activity::getActivitiesCount( $this->_contactId, $this->_admin, $case, $this->_context );
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
     * @return int   the total number of rows for this action
     */
    function &getRows($action, $offset, $rowCount, $sort, $output = null, $case = null) 
    {
        $params['contact_id'] = $this->_contactId;
        $config = CRM_Core_Config::singleton();
        $rows =& CRM_Activity_BAO_Activity::getActivities( $params, $offset, $rowCount, $sort, 
                                                           $this->_admin, $case, $this->_context );
        
        if ( empty( $rows ) ) {
            return $rows;
        }

        $activityStatus = CRM_Core_PseudoConstant::activityStatus( );
        
        //CRM-4418
        $permissions = array( $this->_permission );
        if ( CRM_Core_Permission::check( 'delete activities' ) ) {
            $permissions[] = CRM_Core_Permission::DELETE;
        }
        $mask = CRM_Core_Action::mask( $permissions );
        
        foreach ($rows as $k => $row) {
            $row =& $rows[$k];
            
            // DRAFTING: provide a facility for db-stored strings
            // localize the built-in activity names for display
            // (these are not enums, so we can't use any automagic here)
            switch ($row['activity_type']) {
                case 'Meeting':    $row['activity_type'] = ts('Meeting');    break;
                case 'Phone Call': $row['activity_type'] = ts('Phone Call'); break;
                case 'Email':      $row['activity_type'] = ts('Email');      break;
                case 'SMS':        $row['activity_type'] = ts('SMS');        break;
                case 'Event':      $row['activity_type'] = ts('Event');      break;
            }

            // add class to this row if overdue
            if ( CRM_Utils_Date::overdue( CRM_Utils_Array::value( 'activity_date_time', $row ) ) 
                 && CRM_Utils_Array::value( 'status_id', $row) == 1 ) {
                $row['overdue'] = 1;
                $row['class']   = 'status-overdue';
            } else {
                $row['overdue'] = 0;
                $row['class']   = 'status-ontime';
            }
                  
            $row['status'] = $row['status_id']?$activityStatus[$row['status_id']]:null;
            
            //CRM-3553
            $accessMailingReport = false;
            if ( CRM_Utils_Array::value( 'mailingId', $row ) ) {
                $accessMailingReport = true; 
            }
            
            $actionLinks = $this->actionLinks( CRM_Utils_Array::value( 'activity_type_id', $row ),
                                               CRM_Utils_Array::value( 'source_record_id', $row ),
                                               $accessMailingReport,
                                               CRM_Utils_Array::value( 'activity_id', $row ),
                                               $this->_key );
            
            $actionMask  = array_sum(array_keys($actionLinks)) & $mask;
            
            if ( $output != CRM_Core_Selector_Controller::EXPORT && $output != CRM_Core_Selector_Controller::SCREEN ) {
                $row['action'] = CRM_Core_Action::formLink( $actionLinks,
                                                            $actionMask,
                                                            array('id'     => $row['activity_id'],
                                                                  'cid'    => $this->_contactId,
                                                                  'cxt'    => $this->_context,
                                                                  'caseid' => CRM_Utils_Array::value( 'case_id', $row ) 
                                                                  ));
            }
            
            if($config->civiHRD){
                require_once 'CRM/Core/OptionGroup.php';
                $caseActivityType = CRM_Core_OptionGroup::values('case_activity_type');
                $row['activitytag1'] =  $caseActivityType[CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity',$row['id'],'activity_tag1_id' )];
            }
            unset($row);
        }
         
        return $rows;
       
    }
    
    /**
     * name of export file.
     *
     * @param string $output type of output
     * @return string name of the file
     */
    function getExportFileName($output = 'csv')
    {
        return ts('CiviCRM Activity');
    }

    /**
     * get colunmn headers for search selector
     *
     *
     * @return array $_columnHeaders
     * @access private
     */
    private static function &_getColumnHeaders() 
    {
        if (!isset(self::$_columnHeaders)) {
            self::$_columnHeaders = array(
                                          array('name'      => ts('Type'),
                                                'sort'      => 'activity_type',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('Subject'),
                                                'sort'      => 'subject',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('Added By'),
                                                'sort'      => 'source_contact_name',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('With') ),
                                          array('name'      => ts('Assigned') ),
                                          array(
                                                'name'      => ts('Date'),
                                                'sort'      => 'activity_date_time',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Status'),
                                                'sort'      => 'status_id',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('desc' => ts('Actions')),
                                          );
        }

        return self::$_columnHeaders;
    }
}

