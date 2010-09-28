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

require_once 'CRM/Event/Import/Parser.php';

/**
 * class to parse membership csv files
 */
class CRM_Event_Import_Parser_Participant extends CRM_Event_Import_Parser
{
    protected $_mapperKeys;
  
    private $_contactIdIndex;
    
    //private $_totalAmountIndex;
    
    private $_eventIndex;
    private $_participantStatusIndex;
    private $_participantRoleIndex;
    private $_eventTitleIndex;

    /**
     * Array of succesfully imported participants id's
     *
     * @array
     */
    protected $_newParticipants;

    /**
     * class constructor
     */
    function __construct( &$mapperKeys,$mapperLocType = null, $mapperPhoneType = null)
    {
        parent::__construct();
        $this->_mapperKeys =& $mapperKeys;
    }
    
    /**
     * the initializer code, called before the processing
     *
     * @return void
     * @access public
     */
    function init( )
    {
        require_once 'CRM/Event/BAO/Participant.php';
        $fields =& CRM_Event_BAO_Participant::importableFields( $this->_contactType, false );
        $fields['event_id']['title'] = "Event ID";
        require_once 'CRM/Event/BAO/Event.php';
        $eventfields =& CRM_Event_BAO_Event::fields() ;
        $fields['event_title'] = $eventfields['event_title'];
        
        foreach ($fields as $name => $field) {
            $this->addField( $name, $field['title'], $field['type'], $field['headerPattern'], $field['dataPattern']);
        }
        
        $this->_newParticipants = array();
        
        $this->setActiveFields( $this->_mapperKeys );
        
        // FIXME: we should do this in one place together with Form/MapField.php
        $this->_contactIdIndex         = -1;
        $this->_eventIndex             = -1;
        $this->_participantStatusIndex = -1;
        $this->_participantRoleIndex   = -1;
        $this->_eventTitleIndex        = -1;
        
        $index = 0;
        foreach ( $this->_mapperKeys as $key ) {
             
            switch ($key) {
            case 'participant_contact_id':
                $this->_contactIdIndex           = $index;
                break;
            case 'event_id':
                $this->_eventIndex               = $index;
                break;
            case 'participant_status_id':
                $this->_participantStatusIndex   = $index;
                break;
            case 'participant_role_id':
                $this->_participantRoleIndex     = $index;
                break;
            case 'event_title':
                $this->_eventTitleIndex          = $index;
                break;
            }
            $index++;
        }
    }
    
    /**
     * handle the values in mapField mode
     *
     * @param array $values the array of values belonging to this line
     *
     * @return boolean
     * @access public
     */
    function mapField( &$values )
    {
        return CRM_Event_Import_Parser::VALID;
    }
    
    /**
     * handle the values in preview mode
     *
     * @param array $values the array of values belonging to this line
     *
     * @return boolean      the result of this processing
     * @access public
     */
    function preview( &$values )
    {
        return $this->summary($values);
    }
    
    /**
     * handle the values in summary mode
     *
     * @param array $values the array of values belonging to this line
     *
     * @return boolean      the result of this processing
     * @access public
     */
    function summary( &$values )
    {
        $erroneousField = null;
      
        $response = $this->setActiveFieldValues( $values, $erroneousField );
        $errorRequired = false;
        $index = -1;

        if ( $this->_eventIndex > -1 && $this->_eventTitleIndex > -1 ) {
            array_unshift($values, ts('Select either EventID OR Event Title'));
            return CRM_Event_Import_Parser::ERROR;
        } elseif ( $this->_eventTitleIndex > -1 ) {
            $index = $this->_eventTitleIndex;
        } elseif ( $this->_eventIndex > -1 ) {
            $index = $this->_eventIndex;
        }
        $params =& $this->getActiveFieldParams( );

        require_once 'CRM/Import/Parser/Contact.php';
        if (!(($index < 0) || ($this->_participantStatusIndex < 0) )) {
            $errorRequired = ! CRM_Utils_Array::value($this->_participantStatusIndex, $values); 
            if ((!$params['event_id'] && !$params['event_title'])) {
                CRM_Import_Parser_Contact::addToErrorMsg('Event', $missingField);
            } 
            if (!$params['participant_status_id']) {
                CRM_Import_Parser_Contact::addToErrorMsg('Participant Status', $missingField);
            } 
        } else { 
            $errorRequired = true;  
            $missingField  = null;
            if ($index < 0) {
                CRM_Import_Parser_Contact::addToErrorMsg('Event', $missingField);
            } 
            if ($this->_participantStatusIndex < 0) {
                CRM_Import_Parser_Contact::addToErrorMsg('Participant Status', $missingField);
            } 
        }
     
        if ($errorRequired) {
            array_unshift($values, ts('Missing required field(s) :') . $missingField );
            return CRM_Event_Import_Parser::ERROR;
        }
        
        $errorMessage = null;
        
        //for date-Formats
        $session = CRM_Core_Session::singleton( );
        $dateType = $session->get( "dateTypes" );
                
        foreach ( $params as $key => $val ) {
            if( $val && ( $key == 'participant_register_date' ) ) {
                if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                    if (! CRM_Utils_Rule::date($params[$key])) {
                        CRM_Import_Parser_Contact::addToErrorMsg('Register Date', $errorMessage);
                    }
                } else {
                    CRM_Import_Parser_Contact::addToErrorMsg('Register Date', $errorMessage);
                }
            } else if( $val && ( $key == 'participant_role_id' ) ){
                if (!CRM_Import_Parser_Contact::in_value($val,CRM_Event_PseudoConstant::participantRole())) {
                    CRM_Import_Parser_Contact::addToErrorMsg('Participant Role', $errorMessage);
                }   
            } else if( $val && ( $key == 'participant_status_id' ) ){
                if (!CRM_Import_Parser_Contact::in_value($val,CRM_Event_PseudoConstant::participantStatus())) {
                    CRM_Import_Parser_Contact::addToErrorMsg('Participant Status', $errorMessage);
                }   
            }
        }
        //date-Format part ends
        
        $params['contact_type'] = 'Participant';
        //checking error in custom data
        CRM_Import_Parser_Contact::isErrorInCustomData($params, $errorMessage);

        if ( $errorMessage ) {
            $tempMsg = "Invalid value for field(s) : $errorMessage";
            array_unshift($values, $tempMsg); 
            $errorMessage = null;
            return CRM_Import_Parser::ERROR;
        }
        return CRM_Event_Import_Parser::VALID;
    }
    
    /**
     * handle the values in import mode
     *
     * @param int $onDuplicate the code for what action to take on duplicates
     * @param array $values the array of values belonging to this line
     *
     * @return boolean      the result of this processing
     * @access public
     */
    function import( $onDuplicate, &$values )
    {
        // first make sure this is a valid line
        $response = $this->summary( $values );
        if ( $response != CRM_Event_Import_Parser::VALID ) {
            return $response;
        }
        $params =& $this->getActiveFieldParams( );
        $session = CRM_Core_Session::singleton();
        $dateType = $session->get( 'dateTypes' );
        $formatted = array();
        $customFields = CRM_Core_BAO_CustomField::getFields( CRM_Utils_Array::value( 'contact_type',$params ) );
        
        // don't add to recent items, CRM-4399
        $formatted['skipRecentView'] = true;
        
        foreach ($params as $key => $val) {
            if( $val ) {
                if ( $key == 'participant_register_date' ) {
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('Register Date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('Register Date', $errorMessage);
                    }
                }
                if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID( $key ) ) {
                    if ( $customFields[$customFieldID]['data_type'] == 'Date' ) {
                        CRM_Import_Parser_Contact::formatCustomDate( $params, $formatted, $dateType, $key );
                        unset( $params[$key] );
                    } else if ( $customFields[$customFieldID]['data_type'] == 'Boolean' ) {
                        $params[$key] = CRM_Utils_String::strtoboolstr( $val );
                    }
                }
            } 
        }

        if ( ! $params['participant_role_id'] ) {
            if ( $params['event_id'] ) {
                $roleId= 
                    CRM_Core_DAO::getFieldValue( "CRM_Event_DAO_Event", $params['event_id'] , 'default_role_id' );
            } else {
                $eventTitle = $params['event_title'];
                $qParams = array();
                $dao = new CRM_Core_DAO();
                $roleId =
                    $dao->singleValueQuery("SELECT default_role_id FROM civicrm_event WHERE title = '$eventTitle' ",
                                           $qParams);
            }
            require_once 'CRM/Event/PseudoConstant.php';
            $params['participant_role_id'] = CRM_Event_PseudoConstant::participantRole( $roleId );
        } 
        //date-Format part ends
        static $indieFields = null;
        if ($indieFields == null) {
            require_once('CRM/Event/BAO/Participant.php');
            $indieFields =& CRM_Event_BAO_Participant::import();
        }
        
        $formatValues = array();
        foreach ($params as $key => $field) {
            if ($field == null || $field === '') {
                continue;
            }
            
            $formatValues[$key] = $field;
        }
        
        $formatError = _civicrm_participant_formatted_param( $formatValues, $formatted, true );
        
        if ( $formatError ) {
            array_unshift($values, $formatError['error_message']);
            return CRM_Event_Import_Parser::ERROR;
        }
        
        if ( !CRM_Utils_Rule::integer($formatted['event_id']) ) {
            array_unshift($values, ts('Invalid value for Event ID') );
            return CRM_Event_Import_Parser::ERROR;
        }
        
        if ( $onDuplicate != CRM_Event_Import_Parser::DUPLICATE_UPDATE ) {
            $formatted['custom'] = CRM_Core_BAO_CustomField::postProcess( $formatted,
                                                                          CRM_Core_DAO::$_nullObject,
                                                                          null,
                                                                          'Participant' );
        } else {
            if ( $formatValues['participant_id'] ) {
                require_once 'CRM/Event/BAO/Participant.php';
                $dao =  new CRM_Event_BAO_Participant();
                $dao->id = $formatValues['participant_id'];
                
                $formatted['custom'] = CRM_Core_BAO_CustomField::postProcess( $formatted,
                                                                              CRM_Core_DAO::$_nullObject,
                                                                              $formatValues['participant_id'],
                                                                              'Participant' );
                if ( $dao->find( true ) ) { 
                    $ids = array(
                                 'participant' => $formatValues['participant_id'],
                                 'userId'      => $session->get('userID')
                                 );
                    
                    $newParticipant =& CRM_Event_BAO_Participant::create( $formatted , $ids );
                    
                    $this->_newParticipant[] = $newParticipant->id;
                    return CRM_Event_Import_Parser::VALID;
                } else {
                    array_unshift($values,"Matching Participant record not found for Participant ID ". $formatValues['participant_id'].". Row was skipped.");
                    return CRM_Event_Import_Parser::ERROR;
                }
            }
        }
        
        require_once "api/v2/Participant.php";
        
        if ( $this->_contactIdIndex < 0 ) {
            
            //retrieve contact id using contact dedupe rule
            $formatValues['contact_type'] = $this->_contactType;
            $error = civicrm_check_contact_dedupe( $formatValues );
            
            if ( civicrm_duplicate( $error ) ) {
                $matchedIDs = explode(',',$error['error_message']['params'][0]);
                if ( count( $matchedIDs) >= 1 ) {
                    foreach($matchedIDs as $contactId) {
                        $formatted['contact_id'] = $contactId;
                        $newParticipant = civicrm_create_participant_formatted( $formatted, $onDuplicate );
                    }
                }  
                
            } else {
                // Using new Dedupe rule.
                $ruleParams = array(
                                    'contact_type' => $this->_contactType,
                                    'level' => 'Strict'
                                    );
                require_once 'CRM/Dedupe/BAO/Rule.php';
                $fieldsArray = CRM_Dedupe_BAO_Rule::dedupeRuleFields($ruleParams);
                
                foreach ( $fieldsArray as $value ) {
                    if(array_key_exists(trim($value),$params)) {
                        $paramValue = $params[trim($value)];
                        if (is_array($paramValue)) {
                            $disp .= $params[trim($value)][0][trim($value)]." ";  
                        } else {
                            $disp .= $params[trim($value)]." ";
                        }
                    }
                }
                
                if ( CRM_Utils_Array::value('external_identifier',$params ) ) {
                    if ( $disp ) {
                        $disp .= "AND {$params['external_identifier']}";
                    } else {
                        $disp = $params['external_identifier']; 
                    }
                }
                
                array_unshift($values,"No matching Contact found for (".$disp.")");
                return CRM_Event_Import_Parser::ERROR;
            }
            
        } else {
            if ( $formatValues['external_identifier'] ) {
                $checkCid = new CRM_Contact_DAO_Contact();
                $checkCid->external_identifier = $formatValues['external_identifier'];
                $checkCid->find(true);
                if ($checkCid->id != $formatted['contact_id']) {
                    array_unshift($values, "Mismatch of External identifier :" . $formatValues['external_identifier'] . " and Contact Id:" . $formatted['contact_id']);
                    return CRM_Event_Import_Parser::ERROR;
                }
            }
            
            $newParticipant = civicrm_create_participant_formatted($formatted, $onDuplicate);
        }
        
        if ( is_array( $newParticipant ) && civicrm_error( $newParticipant ) ) {
            if ( $onDuplicate == CRM_Event_Import_Parser::DUPLICATE_SKIP ) {
                
                $contactID     = CRM_Utils_Array::value( 'contactID', $newParticipant['error_data'] );
                $participantID = CRM_Utils_Array::value( 'participantID', $newParticipant['error_data'] );
                $url           = CRM_Utils_System::url( 'civicrm/contact/view/participant',
                                                        "reset=1&id={$participantID}&cid={$contactID}&action=view", true );
                if ( $participantID = $newParticipant['error_message']['params'][0] ) {
                    array_unshift( $values, $url ); 
                    return CRM_Event_Import_Parser::DUPLICATE;
                }
                return CRM_Event_Import_Parser::ERROR;
            }
        }
        
        if ( ! ( is_array( $newParticipant ) && civicrm_error( $newParticipant ) ) ) {
            $this->_newParticipants[] = $newParticipant['id'];
        }
        
        return CRM_Event_Import_Parser::VALID;
    }
    
    /**
     * Get the array of succesfully imported Participation ids
     *
     * @return array
     * @access public
     */
    function &getImportedParticipations()
    {
        return $this->_newParticipants;
    }
    
    /**
     * the initializer code, called before the processing
     *
     * @return void
     * @access public
     */
    function fini( )
    {
    }
    
}

