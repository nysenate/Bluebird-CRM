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


require_once 'CRM/Member/Import/Parser.php';
require_once 'api/v2/Membership.php';

/**
 * class to parse membership csv files
 */
class CRM_Member_Import_Parser_Membership extends CRM_Member_Import_Parser 
{

    protected $_mapperKeys;

    private $_contactIdIndex;
    private $_totalAmountIndex;
    private $_membershipTypeIndex;
    private $_membershipStatusIndex;

    /**
     * Array of succesfully imported membership id's
     *
     * @array
     */
    protected $_newMemberships;

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
        require_once 'CRM/Member/BAO/Membership.php';
        $fields =& CRM_Member_BAO_Membership::importableFields( $this->_contactType, false );

        foreach ($fields as $name => $field) {
            $this->addField( $name, $field['title'], $field['type'], $field['headerPattern'], $field['dataPattern']);
        }

        $this->_newMemberships = array();

        $this->setActiveFields( $this->_mapperKeys );

        // FIXME: we should do this in one place together with Form/MapField.php
        $this->_contactIdIndex        = -1;
        $this->_membershipTypeIndex   = -1;
        $this->_membershipStatusIndex = -1;
        
        $index = 0;
        foreach ( $this->_mapperKeys as $key ) {
            switch ($key) {
            case 'membership_contact_id':
                $this->_contactIdIndex        = $index;
                break;
            case 'membership_type_id':
                $this->_membershipTypeIndex   = $index;
                break;
            case 'status_id':
                $this->_membershipStatusIndex   = $index;
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
        return CRM_Member_Import_Parser::VALID;
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
        
        if ( $this->_membershipTypeIndex < 0 ) {
            $errorRequired = true;
        } else {           
            $errorRequired = ! CRM_Utils_Array::value($this->_membershipTypeIndex, $values) ;
        }
       
        if ($errorRequired) {
            array_unshift($values, ts('Missing required fields'));
            return CRM_Member_Import_Parser::ERROR;
        }
        
        $params =& $this->getActiveFieldParams( );
       
        require_once 'CRM/Import/Parser/Contact.php';
        $errorMessage = null;

        
        //To check whether start date or join date is provided
        if( !$params['membership_start_date'] && !$params['join_date']) {
            $errorMessage = "Membership Start Date is required to create a memberships.";
            CRM_Import_Parser_Contact::addToErrorMsg('Start Date', $errorMessage);
        } 
        //end                                 
       
        //for date-Formats
        $session = CRM_Core_Session::singleton();
        $dateType = $session->get("dateTypes");
        foreach ($params as $key => $val) {

            if( $val ) {
                switch( $key ) {
                case  'join_date': 
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('Join Date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('Join Date', $errorMessage);
                    } 
                    break;
                case  'membership_start_date': 
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('Start Date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('Start Date', $errorMessage);
                    }
                    break;
                case  'membership_end_date': 
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('End date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('End date', $errorMessage);
                    }
                    break;
                case 'membership_type_id':    
                    if (!CRM_Utils_Array::crmInArray( $val, CRM_Member_PseudoConstant::membershipType() )) {    
                        CRM_Import_Parser_Contact::addToErrorMsg('Membership Type', $errorMessage);
                    }
                    break;                    
                case 'status_id':                      
                    if (!CRM_Utils_Array::crmInArray( $val, CRM_Member_PseudoConstant::membershipStatus() )) {
                        CRM_Import_Parser_Contact::addToErrorMsg('Membership Status', $errorMessage);
                    }
                    break;
                case 'email':
                    if (! CRM_Utils_Rule::email($val)) {
                        CRM_Import_Parser_Contact::addToErrorMsg('Email Address', $errorMessage);
                    }
                }
            }
        }
        //date-Format part ends
        
        $params['contact_type'] = 'Membership';
        
        //checking error in custom data
        CRM_Import_Parser_Contact::isErrorInCustomData($params, $errorMessage);
        
        if ( $errorMessage ) {
            $tempMsg = "Invalid value for field(s) : $errorMessage";
            array_unshift($values, $tempMsg);
            $errorMessage = null;
            return CRM_Import_Parser::ERROR;
        }
        
        return CRM_Member_Import_Parser::VALID;
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
    function import( $onDuplicate, &$values) 
    {
        // first make sure this is a valid line
        $response = $this->summary( $values );
        if ( $response != CRM_Member_Import_Parser::VALID ) {
            return $response;
        }
        
        $params =& $this->getActiveFieldParams( );
        
        //assign join date equal to start date if join date is not provided
        if( !$params['join_date'] && $params['membership_start_date']) {
            $params['join_date'] = $params['membership_start_date'];
        }
               
        $session = CRM_Core_Session::singleton();
        $dateType = $session->get("dateTypes");
        $formatted = array();
        $customFields = CRM_Core_BAO_CustomField::getFields( CRM_Utils_Array::value( 'contact_type',$params ) );
        
        // don't add to recent items, CRM-4399
        $formatted['skipRecentView'] = true;
        
        foreach ($params as $key => $val) {
            if( $val ) {
                switch( $key ) {
                case  'join_date': 
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('Join Date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('Join Date', $errorMessage);
                    } 
                    break;
                case  'membership_start_date': 
                    if (CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('Start Date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('Start Date', $errorMessage);
                    } 
                    break;
                case  'membership_end_date': 
                    if( CRM_Utils_Date::convertToDefaultDate( $params, $dateType, $key )) {
                        if (! CRM_Utils_Rule::date($params[$key])) {
                            CRM_Import_Parser_Contact::addToErrorMsg('End Date', $errorMessage);
                        }
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg('End Date', $errorMessage);
                    } 
                    break;
                case  'is_override':
                    $params[$key] = CRM_Utils_String::strtobool( $val );
                    break;
                }
                if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID( $key ) ) {
                    if ( $customFields[$customFieldID][2] == 'Date' ) {
                        CRM_Import_Parser_Contact::formatCustomDate( $params, $formatted, $dateType, $key );
                        unset( $params[$key] );
                    } else if ( $customFields[$customFieldID][2] == 'Boolean' ) {
                        $params[$key] = CRM_Utils_String::strtoboolstr( $val );
                    }
                    
                }
            }
        }
        //date-Format part ends
        
        static $indieFields = null;
        if ($indieFields == null) {
            require_once('CRM/Member/DAO/Membership.php');
            $tempIndieFields =& CRM_Member_DAO_Membership::import();
            $indieFields = $tempIndieFields;
        }

        $formatValues = array( );
        foreach ($params as $key => $field) {
            if ($field == null || $field === '') {
                continue;
            }
            
            $formatValues[$key] = $field;
        }
        
        $formatError = _civicrm_membership_formatted_param( $formatValues, $formatted, true);
        
        if ( $formatError ) {
            array_unshift($values, $formatError['error_message']);
            return CRM_Member_Import_Parser::ERROR;
        }
        
        if ( $onDuplicate != CRM_Member_Import_Parser::DUPLICATE_UPDATE ) {
            $formatted['custom'] = CRM_Core_BAO_CustomField::postProcess( $formatted,
                                                                          CRM_Core_DAO::$_nullObject,
                                                                          null,
                                                                          'Membership' );
        } else {
            //fix for CRM-2219 Update Membership
            // onDuplicate == CRM_Member_Import_Parser::DUPLICATE_UPDATE
            if ( CRM_Utils_Array::value('is_override', $formatted ) && 
                 !CRM_Utils_Array::value( 'status_id', $formatted ) ) {
                array_unshift($values, "Required parameter missing: Status");
                return CRM_Member_Import_Parser::ERROR;
            }
            
            if ( $formatValues['membership_id'] ) {
                require_once 'CRM/Member/BAO/Membership.php';
                $dao =  new CRM_Member_BAO_Membership();
                $dao->id = $formatValues['membership_id'];
                $dates = array('join_date','start_date','end_date');
                foreach ( $dates as $v ) {
                    if ( !$formatted[$v] ) {
                        $formatted[$v] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership', $formatValues['membership_id'], $v );
                    }
                }
                
                $formatted['custom'] = CRM_Core_BAO_CustomField::postProcess( $formatted,
                                                                              CRM_Core_DAO::$_nullObject,
                                                                              $formatValues['membership_id'],
                                                                              'Membership' );
                if ( $dao->find( true ) ) { 
                    $ids = array( 'membership' => $formatValues['membership_id'],
                                  'userId'     => $session->get('userID') );
                    
                    $newMembership =& CRM_Member_BAO_Membership::create( $formatted , $ids, true );
                    if ( civicrm_error( $newMembership ) ) {
                        array_unshift($values, $newMembership['is_error']." for Membership ID ". $formatValues['membership_id'].". Row was skipped.");
                        return CRM_Member_Import_Parser::ERROR;
                    } else {
                        $this->_newMemberships[] = $newMembership->id;
                        return CRM_Member_Import_Parser::VALID;
                    }
                } else {
                    array_unshift($values,"Matching Membership record not found for Membership ID ". $formatValues['membership_id'].". Row was skipped.");
                    return CRM_Member_Import_Parser::ERROR;
                }
            }
        }
        
        //Format dates
        $startDate  = CRM_Utils_Date::customFormat($formatted['start_date'],'%Y-%m-%d');
        $endDate    = CRM_Utils_Date::customFormat($formatted['end_date'],'%Y-%m-%d');
        $joinDate   = CRM_Utils_Date::customFormat($formatted['join_date'],'%Y-%m-%d');          
        
        if ( $this->_contactIdIndex < 0 ) {
            
            //retrieve contact id using contact dedupe rule
            $formatValues['contact_type'] = $this->_contactType;
            $error = civicrm_check_contact_dedupe( $formatValues );
            
            if ( civicrm_duplicate( $error ) ) { 
                $matchedIDs = explode(',',$error['error_message']['params'][0]);
                if (count( $matchedIDs) >1) {                   
                    array_unshift($values,"Multiple matching contact records detected for this row. The membership was not imported");
                    return CRM_Member_Import_Parser::ERROR;                   
                } else {
                    $cid = $matchedIDs[0];
                    $formatted['contact_id'] = $cid;
                    
                    //fix for CRM-1924
                    require_once 'CRM/Member/BAO/MembershipStatus.php';
                    require_once 'CRM/Member/BAO/MembershipType.php';
                    require_once 'CRM/Member/PseudoConstant.php';
                    $calcDates = CRM_Member_BAO_MembershipType::getDatesForMembershipType( $formatted['membership_type_id'], 
                                                                                           $joinDate, 
                                                                                           $startDate, 
                                                                                           $endDate);
                    self::formattedDates( $calcDates, $formatted );
                    
                    //fix for CRM-3570, exclude the statuses those having is_admin = 1
                    //now user can import is_admin if is override is true.
                    $excludeIsAdmin = false;
                    if ( !CRM_Utils_Array::value('is_override', $formatted ) ) {
                        $formatted['exclude_is_admin'] = $excludeIsAdmin = true;
                    }
                    $calcStatus = CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate( $startDate,
                                                                                              $endDate,
                                                                                              $joinDate,
                                                                                              'today',
                                                                                              $excludeIsAdmin );
                    
                    if ( ! $formatted['status_id']) {                        
                        $formatted['status_id'] = $calcStatus['id'];
                    } elseif ( !CRM_Utils_Array::value('is_override', $formatted ) ) { 
                        if ( empty( $calcStatus ) ) {
                            array_unshift($values,"Status in import row (" . $formatValues['status_id'].") does not match calculated status based on your configured Membership Status Rules. Record was not imported.");
                            return CRM_Member_Import_Parser::ERROR;
                        } else if ( $formatted['status_id'] != $calcStatus['id'] ) { 
                            //Status Hold" is either NOT mapped or is FALSE  
                            array_unshift($values,"Status in import row (" . $formatValues['status_id'].") does not match calculated status based on your configured Membership Status Rules (".$calcStatus['name']."). Record was not imported.");
                            return CRM_Member_Import_Parser::ERROR;
                        }
                    }
                    
                    $newMembership = civicrm_contact_membership_create( $formatted );
                    if ( civicrm_error( $newMembership ) ) {
                        array_unshift($values, $newMembership['error_message']);
                        return CRM_Member_Import_Parser::ERROR;
                    }
                    
                    $this->_newMemberships[] = $newMembership['id'];
                    return CRM_Member_Import_Parser::VALID;
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
                return CRM_Member_Import_Parser::ERROR;
            }
            
        } else {
            if ( $formatValues['external_identifier'] ) {
                $checkCid = new CRM_Contact_DAO_Contact();
                $checkCid->external_identifier = $formatValues['external_identifier'];
                $checkCid->find(true);
                if ($checkCid->id != $formatted['contact_id']) {
                    array_unshift($values, "Mismatch of External identifier :" . $formatValues['external_identifier'] . " and Contact Id:" . $formatted['contact_id']);
                    return CRM_Member_Import_Parser::ERROR;
                }
            }
            
            //to calculate dates
            require_once 'CRM/Member/BAO/MembershipType.php';
            $calcDates = CRM_Member_BAO_MembershipType::getDatesForMembershipType($formatted['membership_type_id'], 
                                                                                  $joinDate,
                                                                                  $startDate,
                                                                                  $endDate );
            self::formattedDates( $calcDates, $formatted );
            //end of date calculation part
            
            //fix for CRM-3570, exclude the statuses those having is_admin = 1
            //now user can import is_admin if is override is true.
            $excludeIsAdmin = false;
            if ( !CRM_Utils_Array::value('is_override', $formatted ) ) {
                $formatted['exclude_is_admin'] = $excludeIsAdmin = true;
            }
            require_once 'CRM/Member/BAO/MembershipStatus.php';
            $calcStatus = CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate( $startDate,
                                                                                      $endDate,
                                                                                      $joinDate,
                                                                                      'today',
                                                                                      $excludeIsAdmin );
            if ( ! $formatted['status_id']) {
                $formatted['status_id'] = $calcStatus['id'];
            } else if ( !CRM_Utils_Array::value('is_override', $formatted ) ) {
                if ( empty( $calcStatus ) ) {
                    array_unshift($values,"Status in import row (" . $formatValues['status_id'].") does not match calculated status based on your configured Membership Status Rules. Record was not imported.");
                    return CRM_Member_Import_Parser::ERROR;
                } else if ( $formatted['status_id'] != $calcStatus['id'] ) { 
                    //Status Hold" is either NOT mapped or is FALSE  
                    array_unshift($values,"Status in import row (" . $formatValues['status_id'].") does not match calculated status based on your configured Membership Status Rules (".$calcStatus['name']."). Record was not imported.");
                    return CRM_Member_Import_Parser::ERROR;
                }
            }
            
            $newMembership = civicrm_contact_membership_create( $formatted );
            if ( civicrm_error( $newMembership ) ) {
                array_unshift($values, $newMembership['error_message']);
                return CRM_Member_Import_Parser::ERROR;
            }
            
            $this->_newMemberships[] = $newMembership['id'];
            return CRM_Member_Import_Parser::VALID;
        }
    }
    
    /**
     * Get the array of succesfully imported membership id's
     *
     * @return array
     * @access public
     */
    function &getImportedMemberships() 
    {
        return $this->_newMemberships;
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

    /**
     *  to calculate join, start and end dates 
     *  
     *  @param Array $calcDates array of dates returned by getDatesForMembershipType()
     *  
     *  @return Array formatted containing date values 
     *  
     *  @access public 
     */
    function formattedDates( $calcDates, &$formatted ) {
        $dates = array( 'join_date',
                        'start_date',
                        'end_date',
                        );
        
        foreach ( $dates as $d ) {
            if ( isset( $formatted[$d] ) &&
                 ! CRM_Utils_System::isNull( $formatted[$d] ) ) {
                $formatted[$d] = CRM_Utils_Date::isoToMysql($formatted[$d]);                       
            } else if ( isset( $calcDates[$d] ) ) {
                $formatted[$d] = CRM_Utils_Date::isoToMysql($calcDates[$d]);
            }
        }
    }

}


