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

require_once 'CRM/Contact/Form/Search/Custom/Base.php';
require_once 'CRM/Campaign/BAO/Survey.php';

class Engage_Contact_Form_Search_Custom_FindVoters
   extends    CRM_Contact_Form_Search_Custom_Base
   implements CRM_Contact_Form_Search_Interface {

    const
        ACTIVITY_SURVEY_DETAIL_TABLE = 'civicrm_value_survey_activity_details';

    function __construct( &$formValues ) {
        parent::__construct( $formValues );

        $session = CRM_Core_Session::singleton( );
        if ( !($session->get('userID') ) ) {
            CRM_Core_Error::fatal( ts( 'Could not find Interviewer Id.' ) );  
        }
        $this->_interviewerId = $session->get('userID');

        $this->_columns = array(
                                 ts('Contact Name')   => 'display_name',
                                 ts('Street Number')  => 'street_number',
                                 ts('Street Address') => 'street_address',
                                 ts('City')           => 'city',
                                 ts('Postal Code')    => 'postal_code',
                                 ts('State')          => 'state_province',
                                 ts('Country')        => 'country',
                                 ts('Email')          => 'email',
                                 ts('Phone')          => 'phone' );

    }

    function buildForm( &$form ) {
        $form->add( 'text', 'sort_name', ts( 'Contact Name' ), true );
        $form->add( 'text', 'street_number', ts( 'Street Number' ), true );
        $form->add( 'text', 'street_address', ts( 'Street Address' ), true );
        $form->add( 'text', 'city', ts( 'City' ), true );
        
        $surveys = CRM_Campaign_BAO_Survey::getSurveyList( );
        $form->add('select', 'survey_id', ts('Survey'), array('' => ts('- select -') ) + $surveys );
        
        $form->add('checkbox', 'status_id', ts('Is Held'), null, false );
        
        $form->assign( 'elements', array( 'sort_name', 'street_number', 'street_address', 'city', 'status_id', 'survey_id' ) );
        $this->setTitle('Find Voters');
    }

    function all( $offset = 0, $rowcount = 0, $sort = null,
                  $includeContactIDs = false ) {
        
        $selectClause =  "DISTINCT(contact_a.id) as contact_id, contact_a.display_name as display_name, 
                          civicrm_address.id as address_id, civicrm_address.street_address as street_address, civicrm_address.street_number as street_number, civicrm_address.city as city, civicrm_address.postal_code as postal_code, civicrm_state_province.id as state_province_id, civicrm_state_province.abbreviation as state_province, civicrm_state_province.name as state_province_name, civicrm_country.id as country_id, civicrm_country.name as country, civicrm_phone.id as phone_id, civicrm_phone.phone_type_id as phone_type_id, civicrm_phone.phone as phone, civicrm_email.id as email_id, civicrm_email.email as email";

        $query = $this->sql( $selectClause,
                             $offset, $rowcount, $sort,
                             $includeContactIDs, null );

        return $query;
    }
    
    function from( ) {
        return "
               FROM civicrm_contact contact_a LEFT JOIN civicrm_address ON ( contact_a.id = civicrm_address.contact_id AND civicrm_address.is_primary = 1 ) LEFT JOIN civicrm_state_province ON civicrm_address.state_province_id = civicrm_state_province.id  LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id  LEFT JOIN civicrm_email ON (contact_a.id = civicrm_email.contact_id AND civicrm_email.is_primary = 1) LEFT JOIN civicrm_phone ON (contact_a.id = civicrm_phone.contact_id AND civicrm_phone.is_primary = 1) LEFT JOIN civicrm_activity_target activity_target ON ( activity_target.target_contact_id = contact_a.id ) LEFT JOIN ". self::ACTIVITY_SURVEY_DETAIL_TABLE ." survery_details ON ( activity_target.activity_id = survery_details.entity_id )";
       
    }

    function where( $includeContactIDs = false ) {
        $params = $clause = array( );
        $count  = 1;
        $where  = "(contact_a.is_deleted = 0 AND contact_a.contact_type = 'Individual') ";

        $columns = array( 'sort_name', 'street_number', 'street_address', 'city', 'status_id', 'survey_id' );
        foreach( $columns as $column ) {
            if ( $value = CRM_Utils_Array::value( $column , $this->_formValues ) ) {
                if ( $column == 'sort_name' ) {
                    $clause[ ] = "{$column} LIKE %{$count}";
                    $params[$count] = array( '%'.$value.'%', 'String' );
                } else if ( $column == 'status_id' ) { 
                    $clause[ ] = "survery_details.status_id = %{$count}";
                    $params[$count] = array( 'H', 'String' );
                    
                    // show voters contacts held by current interviewer
                    $count++;
                    $clause[ ] = "survery_details.interviewer_id = %{$count}";
                    $params[$count] = array( $this->_interviewerId , 'Integer' );
                    
                } else if ($column == 'survey_id' ) {
                    $clause[ ] = "survery_details.survey_id = %{$count}";
                    $params[$count] = array( $value, 'Integer' );
                } else {
                    $clause[ ] = "{$column} = %{$count}";
                    $params[$count] = array( $value, 'String' );
                }

                $count++;
            }
        }
                   
        if ( !empty($clause) ) {  
            $where .=  ' AND '. implode( ' AND ', $clause );
        }

        return $this->whereClause( $where, $params );
    }

    function templateFile( ) {
        return 'Engage/Contact/Form/Search/Custom/FindVoters.tpl';
    }

    function setTitle( $title ) {
            CRM_Utils_System::setTitle( $title );
    }

}


