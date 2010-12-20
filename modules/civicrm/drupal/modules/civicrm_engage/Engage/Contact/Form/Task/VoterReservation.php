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
class Engage_Contact_Form_Task_VoterReservation extends CRM_Contact_Form_Task {

    /**
     * survet id
     *
     * @var int
     */
    protected $_surveyId;
    
    /**
     * interviewer id
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
     * number of voters
     *
     * @var int
     */
    protected $_numVoters;

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
        
        if ( empty($this->_contactIds) || !($session->get('userID')) ) {
            CRM_Core_Error::statusBounce( ts( "Could not find contacts for voter reservation Or Missing Interviewer contact.") );
        }
        $this->_interviewerId = $session->get('userID');

    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
       
        $surveys = CRM_Campaign_BAO_Survey::getSurveyList( );
        $this->add('select', 'survey_id', ts('Survey'), array('' => ts('- select -') ) + $surveys, true );
        $this->addDefaultButtons( ts('Add Voter Reservation') );
    }

    function addRules( )
    {
        $this->addFormRule( array( 'Engage_Contact_Form_Task_VoterReservation', 'formRule'), $this );
    }
    
    static function formRule( $params, $rules, &$form ) {
        $errors = array();
        $surveyDetails = array( );

        if ( CRM_Utils_Array::value('survey_id', $params) )  {
            $form->_surveyId = $params['survey_id'];
            
            $params        = array( 'id' => $form->_surveyId );
            $form->_surveyDetails = CRM_Campaign_BAO_Survey::retrieve($params, $surveyDetails);
            
            $numVoters = CRM_Core_DAO::singleValueQuery( "SELECT COUNT(*) FROM ". self::ACTIVITY_SURVEY_DETAIL_TABLE ." WHERE status_id = 'H' AND survey_id = %1 ", array( 1 => array( $form->_surveyId, 'Integer') ) );
            $form->_numVoters = isset($numVoters)? $numVoters : 0;
            
            if ( CRM_Utils_Array::value('max_number_of_contacts', $surveyDetails) &&
                 $form->_numVoters &&
                 ( $surveyDetails['max_number_of_contacts'] <= $form->_numVoters ) ) {
                $errors['survey_id'] = ts( "Voter Reservation is full for this survey." );
            } else if ( CRM_Utils_Array::value('default_number_of_contacts',$surveyDetails) ) {
                if ( count($form->_contactIds) > $surveyDetails['default_number_of_contacts'] ) {
                    $errors['survey_id'] = ts( "You can select maximum %1 contact(s) at a time for voter reservation of this survey.", array( 1 => $surveyDetails['default_number_of_contacts']) );
                }
            }
        }
        return $errors;
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess( ) {
        //get the submitted values in an array
        $params  = $this->controller->exportValues( $this->_name );

        require_once 'CRM/Activity/BAO/Activity.php';
        require_once 'CRM/Contact/BAO/Contact.php';
        require_once 'CRM/Core/BAO/CustomField.php';
        require_once 'CRM/Core/BAO/CustomGroup.php';
        require_once 'CRM/Core/BAO/CustomValueTable.php';
     
        $this->_groupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', 
                                                       'civicrm_value_survey_activity_details' , 'id', 'table_name' );

        $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Activity',
                                                        $this,
                                                        null,
                                                        $this->_groupId );

        $this->_surveyId = CRM_Utils_Array::value( 'survey_id', $params);
        
        $activityGroupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, $this );

        $fieldMapper = array( );
        foreach( $activityGroupTree[$this->_groupId]['fields'] as $fieldId => $field ) {
            $fieldMapper[$field['column_name']] = $field['element_name'];
        }

        $duplicateContacts = array( );

        $query = "SELECT DISTINCT(target.target_contact_id) as contact_id FROM ". self::ACTIVITY_SURVEY_DETAIL_TABLE ." survey INNER JOIN civicrm_activity_target target ON ( target.activity_id = survey.entity_id ) WHERE survey.status_id = 'H' AND survey.survey_id = %1  AND target.target_contact_id IN (". implode(',', $this->_contactIds) .") ";
        $findDuplicate = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $this->_surveyId, 'Integer') ) );
        
        while( $findDuplicate->fetch() ) {
            $duplicateContacts[$findDuplicate->contact_id] = $findDuplicate->contact_id; 
        }

        $customFields  = CRM_Core_BAO_CustomField::getFields( 'Activity' );
        $surveyDetails = $this->_surveyDetails;
        $maxVoters     = $surveyDetails->max_number_of_contacts;

        list( $cName, $cEmail, $doNotEmail, $onHold, $isDeceased ) = CRM_Contact_BAO_Contact::getContactDetails( $this->_interviewerId );

        $fieldParams[$fieldMapper['survey_id']]                = $this->_surveyId;
        $fieldParams[$fieldMapper['status_id']]                = 'H';
        $fieldParams[$fieldMapper['interviewer_id']]           = $this->_interviewerId;
        $fieldParams[$fieldMapper['interviewer_display_name']] = CRM_Utils_Type::escape($cName, 'String');
        $fieldParams[$fieldMapper['interviewer_email']]        = CRM_Utils_Type::escape($cEmail, 'String');
        $fieldParams[$fieldMapper['interviewer_ip']]           = CRM_Utils_Type::escape($_SERVER['REMOTE_ADDR'], 'String');

        $countVoters = 0;
        foreach ( $this->_contactIds as $cid ) {
            if ($maxVoters && ($maxVoters <= ($this->_numVoters + $countVoters) ) ) {
                break;
            }
            if ( in_array($cid ,$duplicateContacts) ) {
                continue;
            }

            $countVoters++;
            $activityParams = array( );  

            $activityParams['source_contact_id']   = $this->_interviewerId;
            $activityParams['assignee_contact_id'] = array( $this->_interviewerId );
            $activityParams['target_contact_id']   = array( $cid );
            $activityParams['activity_type_id' ]   = $surveyDetails->survey_type_id;
            $activityParams['subject']             = ts('Voter Reservation');
            $activityParams['status_id']           = 1;        
            $activityParams['campaign_id']         = $surveyDetails->campaign_id;
            $result = CRM_Activity_BAO_Activity::create( $activityParams );

            $fieldParams[$fieldMapper['subject_display_name']] = CRM_Contact_BAO_Contact::displayName( $cid );

            if ( $result ) {
                CRM_Core_BAO_CustomValueTable::postProcess( $fieldParams,
                                                            $customFields,
                                                            'civicrm_activity',
                                                            $result->id,
                                                            'Activity' );
            }
        }
 
        $status = array( );
        if ( $countVoters > 0 ) {
            $status[] = ts('Voter Reservation has been added for %1 Contact(s).', array( 1 => $countVoters ));
        }
        if ( count($this->_contactIds) > $countVoters ) {
            $status[] = ts('Voter Reservation did not add for %1 Contact(s).', array( 1 => ( count($this->_contactIds) - $countVoters) ) );
        }
        if ( !empty($status) ) {
            CRM_Core_Session::setStatus( implode('&nbsp;', $status) );
        }
    }
}


