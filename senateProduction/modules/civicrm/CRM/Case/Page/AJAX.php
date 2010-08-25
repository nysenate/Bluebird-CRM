<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

require_once 'CRM/Utils/Type.php';

/**
 * This class contains all case related functions that are called using AJAX (jQuery)
 */
class CRM_Case_Page_AJAX
{
    /**
     * Retrieve unclosed cases.
     */
    static function unclosedCases( ) 
    {
        $criteria =  explode( '-', CRM_Utils_Type::escape( CRM_Utils_Array::value( 's', $_GET ), 'String' ) );
        
        $limit = null;
        if ( $limit = CRM_Utils_Array::value( 'limit', $_GET ) ) {
            $limit =  CRM_Utils_Type::escape( $limit, 'Integer' );
        }
        
        $params   =  array( 'limit'     => $limit, 
                            'case_type' => trim( CRM_Utils_Array::value( 1, $criteria ) ),
                            'sort_name' => trim( CRM_Utils_Array::value( 0, $criteria ) ) );
        
        $excludeCaseIds = array( );
        if ( $caseIdStr = CRM_Utils_Array::value( 'excludeCaseIds', $_GET ) ) {
            $excludeIdStr   = CRM_Utils_Type::escape( $caseIdStr, 'String' );
            $excludeCaseIds = explode( ',', $excludeIdStr );
        }
        require_once 'CRM/Case/BAO/Case.php';
        $unclosedCases = CRM_Case_BAO_Case::getUnclosedCases( $params, $excludeCaseIds );
        
        foreach ( $unclosedCases as $caseId => $details ) {
            echo $details['sort_name'].' - '.$details['case_type']."|$caseId|".$details['contact_id'].'|'.$details['case_type'].'|'.$details['sort_name']."\n";
        }
        
        CRM_Utils_System::civiExit( );
    }

    function processCaseTags( ) {
        require_once 'CRM/Core/BAO/EntityTag.php';
        
        $caseId    = CRM_Utils_Type::escape($_POST['case_id'], 'Integer');
        $tags      = CRM_Utils_Type::escape($_POST['tag'], 'String');

        if ( empty($caseId) ) {
            echo 'false';
            CRM_Utils_System::civiExit( );
        }
        
        $tagIds = array( );
        if ( $tags ) {   
            $tagIds = explode( ',', $tags );
        }

        $params = array( 'entity_id'    => $caseId,
                         'entity_table' => 'civicrm_case' );
        
        CRM_Core_BAO_EntityTag::del( $params );
        
        foreach( $tagIds as $tagid ) {
            $params['tag_id'] = $tagid;
            CRM_Core_BAO_EntityTag::add( $params );
        }
        
        $session =& CRM_Core_Session::singleton( );

        require_once "CRM/Activity/BAO/Activity.php";
        require_once "CRM/Core/OptionGroup.php";
        $activityParams = array( );
        
        $activityParams['source_contact_id']  = $session->get( 'userID' ); 
        $activityParams['activity_type_id']   = CRM_Core_OptionGroup::getValue( 'activity_type', 'Change Case Tags', 'name' );
        $activityParams['activity_date_time'] = date('YmdHis');
        $activityParams['status_id']          = CRM_Core_OptionGroup::getValue( 'activity_status', 'Completed', 'name' );
        $activityParams['case_id']            = $caseId;
        $activityParams['is_auto']            = 0;
        $activityParams['subject']            = 'Change Case Tags';
 
        $activity = CRM_Activity_BAO_Activity::create( $activityParams );
        
        require_once "CRM/Case/BAO/Case.php";
        $caseParams = array( 'activity_id' => $activity->id,
                             'case_id'     => $caseId );
        
        CRM_Case_BAO_Case::processCaseActivity( $caseParams );

        echo 'true';
        CRM_Utils_System::civiExit( );
    }
}
