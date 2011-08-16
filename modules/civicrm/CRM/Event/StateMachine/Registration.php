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

require_once 'CRM/Core/StateMachine.php';
require_once 'CRM/Utils/Rule.php';

/**
 * State machine for managing different states of the EventWizard process.
 *
 */
class CRM_Event_StateMachine_Registration extends CRM_Core_StateMachine 
{

    /**
     * class constructor
     *
     * @param object  CRM_Event_Controller
     * @param int     $action
     *
     * @return object CRM_Event_StateMachine
     */
    function __construct( $controller, $action = CRM_Core_Action::NONE ) 
    {
        parent::__construct( $controller, $action );
        $id          = CRM_Utils_Request::retrieve( 'id', 'Positive', $controller, true );
        $is_monetary = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $id, 'is_monetary' );
        
        $pages = array( 'CRM_Event_Form_Registration_Register' => null );
        
        //handle additional participant scenario, where we need to insert participant pages on runtime
        $additionalParticipant = null;

        // check that the controller has some data, hence we dont send the form name                                         
        // which results in an invalid argument error                                                                        
        $values = $controller->exportValues( );
        //first check POST value then QF
        if ( isset( $_POST['additional_participants'] ) && CRM_Utils_Rule::positiveInteger( $_POST['additional_participants'] ) ) {
            // we need to use $_POST since the QF framework has not yet been called
            // and the additional participants page is the next one, so need to set this up
            // now
            $additionalParticipant = $_POST['additional_participants'];
        } else if ( isset( $values['additional_participants'] ) && CRM_Utils_Rule::positiveInteger( $values['additional_participants'] ) ) {
            $additionalParticipant = $values['additional_participants'];
        }
        
        if ( $additionalParticipant ) {
            $additionalParticipant = CRM_Utils_Type::escape( $additionalParticipant, 'Integer' );
            $controller->set( 'addParticipant', $additionalParticipant );
        }

        //to add instances of Additional Participant page, only if user has entered any additional participants
        if ( $additionalParticipant ) {
            require_once "CRM/Event/Form/Registration/AdditionalParticipant.php";
            $extraPages =& CRM_Event_Form_Registration_AdditionalParticipant::getPages( $additionalParticipant );
            $pages = array_merge( $pages, $extraPages );
        }

        $additionalPages = array( 'CRM_Event_Form_Registration_Confirm'   => null,
                                  'CRM_Event_Form_Registration_ThankYou'  => null
                                  );
        
        $pages = array_merge( $pages, $additionalPages );
        
        if ( !$is_monetary ) {
            unset( $pages['CRM_Event_Form_Registration_Confirm'] );
        }

        $this->addSequentialPages( $pages, $action );
    }
    
}


