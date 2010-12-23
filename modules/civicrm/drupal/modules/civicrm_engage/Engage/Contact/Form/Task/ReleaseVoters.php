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

require_once 'CRM/Contact/Form/Task.php';
require_once 'CRM/Campaign/BAO/Survey.php';

/**
 * This class provides the functionality to add contacts for
 * voter reservation.
 */
class Engage_Contact_Form_Task_ReleaseVoters extends CRM_Contact_Form_Task {

    /**
     * survet id
     *
     * @var int
     */
    protected $_surveyId;
    
    /**
     * number of voters
     *
     * @var int
     */
    protected $_interviewerId;

    /**
     * survey details
     *
     * @var object
     */
    protected $_surveyDetails;

    /**
     * custom data table
     *
     */
    CONST
        ACTIVITY_SURVEY_DETAIL_TABLE = 'civicrm_value_survey_activity_details';
    
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        parent::preProcess( );
        
        require_once 'CRM/Utils/Rule.php';
        $qfKey     = CRM_Utils_Request::retrieve( 'qfKey', 'String', $this );
        $urlParams = 'force=1';
        if ( CRM_Utils_Rule::qfKey( $qfKey ) ) $urlParams .= '&qfKey='.$qfKey;

        $session = CRM_Core_Session::singleton( );
        $url     = CRM_Utils_System::url('civicrm/contact/search/custom', $urlParams ); 
        $session->replaceUserContext( $url );

        //get the survey id from user submitted values.
        $this->_surveyId = CRM_Utils_Array::value( 'survey_id', $this->get( 'formValues' ) );
        $isHeld          = CRM_Utils_Array::value( 'status_id', $this->get( 'formValues' ) );
        if ( !$this->_surveyId || !$isHeld ) {
            CRM_Core_Error::statusBounce( ts( "Please search with 'Is Held' and 'Survey Id' filters to apply this action.") );
        }
        
        $session = CRM_Core_Session::singleton( );
        if ( empty($this->_contactIds) || !($session->get('userID')) ) {
            CRM_Core_Error::statusBounce( ts( "Could not find contacts for release voters resevation Or Missing Interviewer contact.") );
        }
        $this->_interviewerId = $session->get('userID');

        $surveyDetails = array( );
        $params        = array( 'id' => $this->_surveyId );
        $this->_surveyDetails = CRM_Campaign_BAO_Survey::retrieve($params, $surveyDetails);

        $numVoters = CRM_Core_DAO::singleValueQuery( "SELECT COUNT(*) FROM ". self::ACTIVITY_SURVEY_DETAIL_TABLE ." WHERE status_id = 'H' AND survey_id = %1 AND interviewer_id = %2", array( 1 => array( $this->_surveyId, 'Integer' ), 2 => array( $this->_interviewerId, 'Integer' )  ) );

        if ( !isset($numVoters) || ($numVoters < 1) ) {
            CRM_Core_Error::statusBounce( ts( "All voters held by you are already released for this survey.") );
        }

        $this->assign( 'surveyTitle', $surveyDetails['title'] );
        
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
       
        $this->addDefaultButtons( ts('Release Voters') );
    }

    function addRules( )
    {
        $this->addFormRule( array( 'Engage_Contact_Form_Task_ReleaseVoters', 'formRule'), $this );
    }
    
    static function formRule( $params, $rules, &$form ) {
        $errors = array();
        return $errors;
    }

    function postProcess( ) {
        //get the submitted values in an array
        $params    = $this->controller->exportValues( $this->_name );
        
        $heldContacts = array( );
        
        // interviewer can release only those contacts which are held by himself
        $query = "SELECT target.target_contact_id as contact_id, survey.entity_id as entity_id FROM ". self::ACTIVITY_SURVEY_DETAIL_TABLE ." survey INNER JOIN civicrm_activity_target target ON ( target.activity_id = survey.entity_id ) WHERE survey.status_id = 'H' AND survey.survey_id = %1  AND survey.interviewer_id = %2 AND target.target_contact_id IN (". implode(',', $this->_contactIds) .") ";
        $findHeld = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $this->_surveyId, 'Integer'), 2 => array( $this->_interviewerId, 'Integer') ) );
        
        while( $findHeld->fetch() ) {
            $heldContacts[$findHeld->contact_id] = $findHeld->entity_id; 
        }

        if ( !empty($heldContacts) ) {
            $query = "UPDATE ". self::ACTIVITY_SURVEY_DETAIL_TABLE ." SET status_id = 'R' WHERE survey_id=%1 AND interviewer_id = %2 AND entity_id IN (". implode(',', $heldContacts ) .")";
            CRM_Core_DAO::executeQuery( $query, array( 1 => array( $this->_surveyId, 'Integer'), 2 => array( $this->_interviewerId, 'Integer') ) );
        }
        
        $status = array( );
        if ( count($heldContacts) > 0 ) {
            $status[ ] = ts("%1 voters has been released.", array( 1 => count($heldContacts) ) );
        }
        if ( count($this->_contactIds) > count($heldContacts) ) {
            $status[ ] = ts("%1 voters did not release.", array( 1 => (count($this->_contactIds) > count($heldContacts)) ) );  
        }
        
        if ( !empty($status) ) {
            CRM_Core_Session::setStatus( implode('&nbsp;', $status) );
        } 
    }

}