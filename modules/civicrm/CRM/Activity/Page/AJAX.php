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
 *
 */

/**
 * This class contains all the function that are called using AJAX (jQuery)
 */
class CRM_Activity_Page_AJAX
{
    static function getCaseActivity( ) 
    {
        $caseID    = CRM_Utils_Type::escape( $_GET['caseID'], 'Integer' );
        $contactID = CRM_Utils_Type::escape( $_GET['cid'], 'Integer' );
        $userID    = CRM_Utils_Type::escape( $_GET['userID'], 'Integer' );
        $context   = CRM_Utils_Type::escape( CRM_Utils_Array::value( 'context', $_GET ), 'String' );
        
        $sortMapper  = array( 0 => 'display_date', 1 => 'ca.subject', 2 => 'ca.activity_type_id', 
                              3 => 'acc.sort_name', 4 => 'cc.sort_name', 5 => 'ca.status_id'  );

        $sEcho       = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
        $offset      = isset($_REQUEST['iDisplayStart'])? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer'):0;
        $rowCount    = isset($_REQUEST['iDisplayLength'])? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer'):25; 
        $sort        = isset($_REQUEST['iSortCol_0'] )? CRM_Utils_Array::value( CRM_Utils_Type::escape($_REQUEST['iSortCol_0'],'Integer'), $sortMapper ): null;
        $sortOrder   = isset($_REQUEST['sSortDir_0'] )? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String'):'asc';

        $params    = $_POST;
        if ( $sort && $sortOrder ) {
            $params['sortname']  = $sort;
            $params['sortorder'] = $sortOrder;
        }
        $params['page'] = ($offset/$rowCount) + 1;
        $params['rp']   = $rowCount;

        // get the activities related to given case
        require_once "CRM/Case/BAO/Case.php";
        $activities = CRM_Case_BAO_Case::getCaseActivity( $caseID, $params, $contactID, $context, $userID );

        require_once "CRM/Utils/JSON.php";
        $iFilteredTotal = $iTotal =  $params['total'];
        $selectorElements = array( 'display_date', 'subject', 'type', 'with_contacts', 'reporter', 'status', 'links', 'class' );

        echo CRM_Utils_JSON::encodeDataTableSelector( $activities, $sEcho, $iTotal, $iFilteredTotal, $selectorElements );
        CRM_Utils_System::civiExit( );

    }
    
    static function convertToCaseActivity()
    {
        $params = array( 'caseID', 'activityID', 'contactID', 'newSubject', 'targetContactIds', 'mode' );
        foreach ( $params as $param ) {
            $$param = CRM_Utils_Array::value( $param, $_POST );
        }
        
        if ( !$activityID || !$caseID ) {
            echo json_encode( array('error_msg' => 'required params missing.' ) );
            CRM_Utils_System::civiExit( );
        }
        
        require_once "CRM/Activity/DAO/Activity.php";
        $otherActivity = new CRM_Activity_DAO_Activity();
        $otherActivity->id = $activityID;
        if ( !$otherActivity->find( true ) ) {
            echo json_encode( array('error_msg' => 'activity record is missing.' ) );
            CRM_Utils_System::civiExit( );  
        }
        $actDateTime = CRM_Utils_Date::isoToMysql( $otherActivity->activity_date_time );
        
        //create new activity record.
        $mainActivity = new CRM_Activity_DAO_Activity( );
        $mainActVals  = array( );
        CRM_Core_DAO::storeValues( $otherActivity, $mainActVals );
        
        //get new activity subject.
        if ( !empty( $newSubject ) ) $mainActVals['subject'] = $newSubject;
        
        $mainActivity->copyValues( $mainActVals );
        $mainActivity->id = null;
        $mainActivity->activity_date_time = $actDateTime;
        //make sure this is current revision.
        $mainActivity->is_current_revision = true;
        //drop all relations.
        $mainActivity->parent_id = $mainActivity->original_id = null;
        
        $mainActivity->save( );
        $mainActivityId = $mainActivity->id;
        require_once 'CRM/Activity/BAO/Activity.php';
        CRM_Activity_BAO_Activity::logActivityAction( $mainActivity );
        $mainActivity->free( );
        
        //mark previous activity as deleted.
        if ( in_array( $mode, array( 'move', 'file' ) ) ) {
            $otherActivity->activity_date_time = $actDateTime;
            $otherActivity->is_deleted = 1;
            $otherActivity->save( );
        }
        $otherActivity->free( ); 
        
        require_once "CRM/Activity/BAO/Activity.php";
        $targetContacts = array( );
        if ( !empty( $targetContactIds ) ) {
            $targetContacts = array_unique( explode( ',', $targetContactIds ) );
        }
        foreach ( $targetContacts as $key => $value ) { 
            $params = array( 'activity_id' => $mainActivityId, 
                             'target_contact_id' => $value );
            CRM_Activity_BAO_Activity::createActivityTarget( $params );
        }
        
        //attach newly created activity to case.
        require_once "CRM/Case/DAO/CaseActivity.php";
        $caseActivity = new CRM_Case_DAO_CaseActivity( );
        $caseActivity->case_id     = $caseID;
        $caseActivity->activity_id = $mainActivityId;
        $caseActivity->save( );
        $error_msg = $caseActivity->_lastError;
		$caseActivity->free( ); 
        
        echo json_encode(array('error_msg' => $error_msg));
        CRM_Utils_System::civiExit( );
    }
}