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

require_once 'CRM/Activity/Import/Parser.php';

/**
 * class to parse activity csv files
 */
class CRM_Activity_Import_Parser_Activity extends CRM_Activity_Import_Parser 
{

    protected $_mapperKeys;

    private $_contactIdIndex;
    private $_activityTypeIndex;
    private $_activityNameIndex;
    private $_activityDateIndex;

    /**
     * Array of succesfully imported activity id's
     *
     * @array
     */
    protected $_newActivity;

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
        require_once 'CRM/Activity/BAO/Activity.php';
        require_once 'CRM/Activity/BAO/ActivityTarget.php';
        $fields = array_merge( CRM_Activity_BAO_Activity::importableFields( ), 
                               CRM_Activity_BAO_ActivityTarget::import( ) );
        
        $fields = array_merge( $fields, array( 'activity_name' => array( 'title'         => ts('Activity Type Label' ),
                                                                         'headerPattern' => '/(activity.)?type label?/i') ) );
        
        foreach ($fields as $name => $field) {
            $this->addField( $name, $field['title'], $field['type'], $field['headerPattern'], $field['dataPattern']);
        }

        $this->_newActivity = array();
        
        $this->setActiveFields( $this->_mapperKeys );
        
        // FIXME: we should do this in one place together with Form/MapField.php
        $this->_contactIdIndex        = -1;
        $this->_activityTypeIndex     = -1;
        $this->_activityNameIndex     = -1;
        $this->_activityDateIndex     = -1;
        
        $index = 0;
        foreach ( $this->_mapperKeys as $key ) {
            switch ($key) {
            case 'target_contact_id':
            case 'external_identifier':
                $this->_contactIdIndex        = $index;
                break;
            case 'activity_name' :
                $this->_activityNameIndex     = $index;
                break;
            case 'activity_type_id' :
                $this->_activityTypeIndex     = $index;
                break;
            case 'activity_date_time':
                $this->_activityDateIndex     = $index;
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
        return CRM_Activity_Import_Parser::VALID;
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
        $index = -1;
        $errorRequired = false;
        
        if ( $this->_activityTypeIndex > -1 && $this->_activityNameIndex > -1 ) {
            array_unshift($values, ts('Please select either Activity Type ID OR Activity Type Label.'));
            return CRM_Activity_Import_Parser::ERROR;
        } elseif ( $this->_activityNameIndex > -1 ) {
            $index = $this->_activityNameIndex;
        } elseif ( $this->_activityTypeIndex > -1 ) {
            $index = $this->_activityTypeIndex;
        }
        
        if ( $index < 0 or $this->_activityDateIndex < 0 ) {
            $errorRequired = true;
        } else {
            $errorRequired = ! CRM_Utils_Array::value($index, $values) ||
                ! CRM_Utils_Array::value($this->_activityDateIndex, $values);
        }

        if ($errorRequired) {
            array_unshift($values, ts('Missing required fields'));
            return CRM_Activity_Import_Parser::ERROR;
        }

        $params =& $this->getActiveFieldParams( );
        
        
        require_once 'CRM/Import/Parser/Contact.php';
        $errorMessage = null;
        
        //for date-Formats
        $session = CRM_Core_Session::singleton();
        $dateType = $session->get("dateTypes");
        if(!isset($params['source_contact_id'])) $params['source_contact_id'] = $session->get( 'userID' );
        foreach ($params as $key => $val) {
            if ( $key == 'activity_date_time' ) {
                if ( $val ) {
                    $dateValue = self::formatDate( $val, $dateType );
                    if ( $dateValue ) {
                        $params[$key] = $dateValue;
                    } else {
                        CRM_Import_Parser_Contact::addToErrorMsg( 'Activity date', $errorMessage );  
                    }
                }
            } else if ( $key == 'activity_engagement_level' && $val &&
                        !CRM_Utils_Rule::positiveInteger( $val ) ) {
                CRM_Import_Parser_Contact::addToErrorMsg( 'Activity Engagement Index', $errorMessage ); 
            }
        }
        //date-Format part ends

        //checking error in custom data
        $params['contact_type'] =  $this->_contactType;
        CRM_Import_Parser_Contact::isErrorInCustomData($params, $errorMessage);

        if ( $errorMessage ) {
            $tempMsg = "Invalid value for field(s) : $errorMessage";
            array_unshift($values, $tempMsg);
            $errorMessage = null;
            return CRM_Import_Parser::ERROR;
        }
        
        return CRM_Activity_Import_Parser::VALID;
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
        
        if ( $response != CRM_Activity_Import_Parser::VALID ) {
            return $response;
        }
        $params =& $this->getActiveFieldParams( );
        $activityName = array_search( 'activity_name',$this->_mapperKeys);
        if ( $activityName ) {
            $params = array_merge( $params, array( 'activity_name' => $values[$activityName]) );
        }
        //for date-Formats
        $session = CRM_Core_Session::singleton();
        $dateType = $session->get("dateTypes");
        if(!isset($params['source_contact_id'])) $params['source_contact_id'] = $session->get( 'userID' );
        $formatted = array();
        $customFields = CRM_Core_BAO_CustomField::getFields( CRM_Utils_Array::value( 'contact_type',$params ) );
        
        foreach ($params as $key => $val) {
            if ( $key ==  'activity_date_time' && $val ) {
                $params[$key] = self::formatDate( $val, $dateType );
            } else if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID( $key ) ) {
                if ( $customFields[$customFieldID]['data_type'] == 'Date' ) {
                    CRM_Import_Parser_Contact::formatCustomDate( $params, $params, $dateType, $key );
                } else if ( $customFields[$customFieldID]['data_type'] == 'Boolean' ) {
                    $params[$key] = CRM_Utils_String::strtoboolstr( $val );
                }
            } else if ( $key == 'activity_subject' ) {
                $params['subject'] = $val;
            }
        }
        //date-Format part ends
        require_once 'api/v2/utils.v2.php';
        $formatError = _civicrm_activity_formatted_param( $params, $params, true );
        
        if ( $formatError ) {
            array_unshift( $values, $formatError['error_message'] );
            return CRM_Activity_Import_Parser::ERROR;
        }
        
        $params['custom'] = CRM_Core_BAO_CustomField::postProcess( $params,
                                                                   CRM_Core_DAO::$_nullObject,
                                                                   null,
                                                                   'Activity' );
        
        if ( $this->_contactIdIndex < 0 ) {
            
            //retrieve contact id using contact dedupe rule.
            //since we are support only individual's activity import.
            $params['contact_type'] = 'Individual';
            $error = civicrm_check_contact_dedupe( $params );
            
            if ( civicrm_duplicate( $error ) ) {
                $matchedIDs = explode(',',$error['error_message']['params'][0]);
                if (count( $matchedIDs) > 1) {
                    array_unshift($values,"Multiple matching contact records detected for this row. The activity was not imported");
                    return CRM_Activity_Import_Parser::ERROR;
                } else {
                    $cid = $matchedIDs[0];
                    $params['target_contact_id'] = $cid;
                    $params['version'] = 3;
                    $newActivity = civicrm_api('activity', 'create', $params); 
                    if ( CRM_Utils_Array::value( 'is_error', $newActivity ) ) {
                        array_unshift($values, $newActivity['error_message']);
                        return CRM_Activity_Import_Parser::ERROR;
                    }
                    
                    $this->_newActivity[] = $newActivity['id'];
                    return CRM_Activity_Import_Parser::VALID;
                }
                
            } else {
                // Using new Dedupe rule.
                $ruleParams = array(
                                    'contact_type' => 'Individual',
                                    'level' => 'Strict'
                                    );
                require_once 'CRM/Dedupe/BAO/Rule.php';
                $fieldsArray = CRM_Dedupe_BAO_Rule::dedupeRuleFields($ruleParams);
                
                foreach ( $fieldsArray as $value) {
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
                return CRM_Activity_Import_Parser::ERROR;
            }
          
        } else {
            if ( CRM_Utils_Array::value('external_identifier', $params ) ) {
                $targetContactId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', 
                                                               $params['external_identifier'], 'id', 'external_identifier' ); 
                
                if ( CRM_Utils_Array::value( 'target_contact_id', $params ) && 
                     $params['target_contact_id'] != $targetContactId ) {
                    array_unshift($values, "Mismatch of External identifier :" . $params['external_identifier'] . " and Contact Id:" . $params['target_contact_id']);
                    return CRM_Activity_Import_Parser::ERROR;
                } else if ( $targetContactId )  {
                    $params['target_contact_id'] = $targetContactId;
                } else {
                    array_unshift($values, "No Matching Contact for External identifier :" . $params['external_identifier'] );
                    return CRM_Activity_Import_Parser::ERROR;
                }
            }
            
            $params['version'] = 3;
            $newActivity = civicrm_api('activity', 'create', $params );
            if ( CRM_Utils_Array::value( 'is_error', $newActivity ) ) {
                array_unshift($values, $newActivity['error_message']);
                return CRM_Activity_Import_Parser::ERROR;
            }
            
            $this->_newActivity[] = $newActivity['id'];
            return CRM_Activity_Import_Parser::VALID;
        }
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

    static function formatDate( $date, $dateType ) 
    {
        $formattedDate = null;
        if ( empty( $date ) ) {
            return $formattedDate;  
        }
        
        //1. first convert date to default format.
        //2. append time to default formatted date (might be removed during format)  
        //3. validate date / date time.
        //4. If date and time then convert to default date time format.
        
        $dateKey       = 'date';
        $dateParams    = array( $dateKey => $date ); 
        
        require_once 'CRM/Utils/Date.php';
        if ( CRM_Utils_Date::convertToDefaultDate( $dateParams, $dateType, $dateKey ) ) { 
            $dateVal   = $dateParams[$dateKey];
            $ruleName  = 'date';
            if ( $dateType == 1 ) {
                $matches = array( );
                if ( preg_match("/(\s(([01]\d)|[2][0-3]):([0-5]\d))$/", $date, $matches ) ) {
                    $ruleName = 'dateTime';
                    if ( strpos( $date, '-' ) !== false ) {
                        $dateVal .= array_shift( $matches );
                    }
                }
            }
            
            // validate date.
            require_once 'CRM/Utils/Rule.php';
            eval( '$valid = CRM_Utils_Rule::' . $ruleName . '( $dateVal );' );
            
            if ( $valid ) {
                //format date and time to default.
                if ( $ruleName == 'dateTime' ) {
                    $dateVal  = CRM_Utils_Date::customFormat( preg_replace("/(:|\s)?/", "", $dateVal ), '%Y%m%d%H%i' );
                    //hack to add seconds
                    $dateVal .= '00';
                }
                $formattedDate = $dateVal;
            }
        }
        
        return $formattedDate;
    }
    
}


