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

require_once 'CRM/Core/Form.php';
require_once "CRM/Activity/BAO/Activity.php";

/**
 * This class handle activity view mode
 * 
 */
class CRM_Activity_Form_ActivityView extends CRM_Core_Form
{
    /**  
     * Function to set variables up before form is built  
     *                                                            
     * @return void  
     * @access public  
     */
    public function preProcess( ) 
    {
        //get the activity values
        $activityId = CRM_Utils_Request::retrieve('id', 'Positive', $this );
        $context    = CRM_Utils_Request::retrieve( 'context', 'String', $this );
        $cid        = CRM_Utils_Request::retrieve('cid','Positive', $this);
        
        //check for required permissions, CRM-6264 
        if ( $activityId &&
             !CRM_Activity_BAO_Activity::checkPermission( $activityId, CRM_Core_Action::VIEW ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page.' ) );
        }
        
        $session = CRM_Core_Session::singleton();
        if ( $context != 'home' ) {
            $url = CRM_Utils_System::url( 'civicrm/contact/view', "reset=1&cid={$cid}&selectedChild=activity");
        } else {
            $url = CRM_Utils_System::url('civicrm/dashboard', 'reset=1');
        }

        $session->pushUserContext( $url );

        $params = array( 'id' => $activityId );
        CRM_Activity_BAO_Activity::retrieve( $params, $defaults );

        //set activity type name and description to template
        require_once 'CRM/Core/BAO/OptionValue.php';
        list( $activityTypeName, $activityTypeDescription ) = CRM_Core_BAO_OptionValue::getActivityTypeDetails( $defaults['activity_type_id'] );
        
        $this->assign( 'activityTypeName', $activityTypeName );
        $this->assign( 'activityTypeDescription', $activityTypeDescription );
        
        if (  CRM_Utils_Array::value('mailingId', $defaults) ) {
            $this->_mailing_id = CRM_Utils_Array::value( 'source_record_id', $defaults );
            require_once 'CRM/Mailing/BAO/Mailing.php';
            $mailingReport =& CRM_Mailing_BAO_Mailing::report( $this->_mailing_id, true );
            CRM_Mailing_BAO_Mailing::getMailingContent( $mailingReport, $this ); 
            $this->assign( 'mailingReport', $mailingReport );
        }

        foreach ( $defaults as $key => $value ) {
            if ( substr( $key, -3)  != '_id' ) {
                $values[$key] = $value;
            }
        }  
        
        require_once 'CRM/Core/BAO/File.php';
        $values['attachment'] = CRM_Core_BAO_File::attachmentInfo( 'civicrm_activity',
                                                                   $activityId );
        $this->assign( 'values', $values ); 
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addButtons(array(  
                                array ( 'type'      => 'next',  
                                        'name'      => ts('Done'),  
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                        'isDefault' => true   )
                                )
                          );
    }

}


