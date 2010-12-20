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
 * $Id$
 *
 */

require_once "CRM/Core/Form.php";

/**
 * This class generates form components for OpenCase Activity
 * 
 */
class CRM_Case_Form_Activity_ChangeCaseStartDate
{

    static function preProcess( &$form ) 
    {
        if ( !isset($form->_caseId) ) {
            CRM_Core_Error::fatal(ts('Case Id not found.'));
        }
    }

    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( &$form ) 
    {
        $defaults = array(); 
        list( $defaults['start_date'] ) = CRM_Utils_Date::setDateDefaults( );
        return $defaults;
    }

    static function buildQuickForm( &$form ) 
    { 
        $currentStartDate = CRM_Core_DAO::getFieldValue( 'CRM_Case_DAO_Case', $form->_caseId, 'start_date' );
        $form->assign('current_start_date',  $currentStartDate );
        $form->addDate( 'start_date', ts('New Start Date'), false, array( 'formatType' => 'birth' ) );   
    }

    /**
     * global validation rules for the form
     *
     * @param array $values posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $values, $files, $form ) 
    {
        return true;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function beginPostProcess( &$form, &$params ) 
    {
        if ( $form->_context == 'case' ) {
            $params['id'] = $form->_id;
        }
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function endPostProcess( &$form, &$params, $activity ) 
    {
        if ( CRM_Utils_Array::value('start_date', $params ) ) {
            $params['start_date'] = CRM_Utils_Date::processDate( $params['start_date'] );
        }
       
        $caseType = $form->_caseType;

        if ( !$caseType && $form->_caseId ) {

            $query = "
SELECT  cov_type.label as case_type FROM civicrm_case 
LEFT JOIN  civicrm_option_group cog_type ON cog_type.name = 'case_type'
LEFT JOIN civicrm_option_value cov_type ON 
( civicrm_case.case_type_id = cov_type.value AND cog_type.id = cov_type.option_group_id ) 
WHERE civicrm_case.id=  %1";
            
            $queryParams = array(1 => array($form->_caseId, 'Integer'));
            $caseType = CRM_Core_DAO::singleValueQuery( $query, $queryParams );  
        }
        
        if ( ! $form->_currentlyViewedContactId  ||
             ! $form->_currentUserId             ||
             ! $form->_caseId                    || 
             ! $caseType
             ) {
            CRM_Core_Error::fatal('Required parameter missing for ChangeCaseType - end post processing');
        }
        
        $config =& CRM_Core_Config::singleton();
        
        // 1. save activity subject with new start date
        $currentStartDate = CRM_Utils_Date::customFormat( CRM_Core_DAO::getFieldValue( 'CRM_Case_DAO_Case',
                                                                                       $form->_caseId, 'start_date' ), $config->dateformatFull );
        $newStartDate = CRM_Utils_Date::customFormat(CRM_Utils_Date::mysqlToIso($params['start_date']), $config->dateformatFull );
        $subject = 'Change Case Start Date from ' . $currentStartDate . ' to ' . $newStartDate;
        $activity->subject = $subject;
        $activity->save();
        // 2. initiate xml processor
        $xmlProcessor = new CRM_Case_XMLProcessor_Process( );
        $xmlProcessorParams = array( 
                                    'clientID'           => $form->_currentlyViewedContactId,
                                    'creatorID'          => $form->_currentUserId,
                                    'standardTimeline'   => 0,
                                    'activity_date_time' => $params['start_date'],
                                    'caseID'             => $form->_caseId,
                                    'caseType'           => $caseType,
                                    'activityTypeName'   => 'Change Case Start Date',
                                    'activitySetName'    => 'standard_timeline',
                                    'resetTimeline'      => 1,           
                                     );
        
        $xmlProcessor->run( $caseType, $xmlProcessorParams );
        
        // 3.status msg
        $params['statusMsg'] = ts('Case Start Date changed successfully.');
    }
}
