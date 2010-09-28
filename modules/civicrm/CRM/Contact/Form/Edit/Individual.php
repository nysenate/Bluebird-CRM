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

require_once 'CRM/Core/ShowHideBlocks.php';
require_once 'CRM/Core/PseudoConstant.php';

/**
 * Auxilary class to provide support to the Contact Form class. Does this by implementing
 * a small set of static methods
 *
 */
class CRM_Contact_Form_Edit_Individual {
    /**
     * This function provides the HTML form elements that are specific to the Individual Contact Type
     * 
     * @access public
     * @return None 
     */
    public function buildQuickForm( &$form, $action = null )
    {
        $form->applyFilter('__ALL__','trim');
        
        //prefix
        $prefix = CRM_Core_PseudoConstant::individualPrefix( );
        if ( !empty( $prefix ) ) {
            $form->addElement('select', 'prefix_id', ts('Prefix'), array('' => '') + $prefix );
        }
        
        $attributes = CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact');
        
        // first_name
        $form->addElement('text', 'first_name', ts('First Name'), $attributes['first_name'] );
        
        //middle_name
        $form->addElement('text', 'middle_name', ts('Middle Name'), $attributes['middle_name'] );
        
        // last_name
        $form->addElement('text', 'last_name', ts('Last Name'), $attributes['last_name'] );
        
        // suffix
        $suffix = CRM_Core_PseudoConstant::individualSuffix( );
        if ( $suffix ) {
            $form->addElement('select', 'suffix_id', ts('Suffix'), array('' => '') + $suffix );
        }
        
        // nick_name
        $form->addElement('text', 'nick_name', ts('Nick Name'),
                          CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'nick_name') );
      
        // job title
        // override the size for UI to look better
        $attributes['job_title']['size'] = 30;
        $form->addElement('text', 'job_title', ts('Job title'), $attributes['job_title'], 'size="30"');
            
        if ( $action & CRM_Core_Action::UPDATE ) {
            $mailToHouseholdID  = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', 
                                                               $form->_contactId, 
                                                               'mail_to_household_id', 
                                                               'id' );
            $form->assign('mailToHouseholdID',$mailToHouseholdID );  
        }
       
        //Shared Address Element
        require_once 'CRM/Contact/BAO/ContactType.php';
        if( CRM_Contact_BAO_ContactType::isActive( 'Household' ) ) {
            $label = CRM_Contact_BAO_ContactType::getLabel( 'Household' );
            $form->addElement( 'checkbox', 'use_household_address', null, 
                               ts('Use %1 Address',array( 1=> $label ) ) );
        }
        $housholdDataURL = CRM_Utils_System::url( 'civicrm/ajax/search', "hh=1", false, null, false );
        $form->assign('housholdDataURL',$housholdDataURL );
        $form->add( 'text', 'shared_household', ts( 'Select Household' ) );
        $form->add( 'hidden', 'shared_household_id', '', array( 'id' => 'shared_household_id' ));
                
        //Current Employer Element
        $employerDataURL =  CRM_Utils_System::url( 'civicrm/ajax/rest', 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=contact&org=1', false, null, false );
        $form->assign('employerDataURL',$employerDataURL );
        
        $form->addElement('text', 'current_employer', ts('Current Employer'), '' );
        $form->addElement('hidden', 'current_employer_id', '', array( 'id' => 'current_employer_id') );
        $form->addElement('text', 'contact_source', ts('Source'));

        $checkSimilar = defined( 'CIVICRM_CONTACT_AJAX_CHECK_SIMILAR' ) ? CIVICRM_CONTACT_AJAX_CHECK_SIMILAR : true;
        $form->assign('checkSimilar',$checkSimilar );
 

        //External Identifier Element
        $form->add('text', 'external_identifier', ts('External Id'), 
                   CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'external_identifier'), false);

        $form->addRule( 'external_identifier',
                        ts('External ID already exists in Database.'), 
                        'objectExists', 
                        array( 'CRM_Contact_DAO_Contact', $form->_contactId, 'external_identifier' ) );
        $config = CRM_Core_Config::singleton();
        CRM_Core_ShowHideBlocks::links($form, 'demographics', '' , '');
    }

    /**
     * global form rule
     *
     * @param array $fields  the input form values
     * @param array $files   the uploaded files if any
     * @param array $options additional user data
     *
     * @return true if no errors, else array of errors
     * @access public
     * @static
     */
    static function formRule( $fields, $files, $contactID = null ) 
    {
        $errors = array( );
        //FIXME 
        if ( CRM_Utils_Array::value( 'state_province_id', $fields['address'][1] )  == 'undefined' ) {
            $fields['address'][1]['state_province_id'] ='';
        }
        $primaryID = CRM_Contact_Form_Contact::formRule( $fields, $errors, $contactID );
        
        // check for state/country mapping
        require_once 'CRM/Contact/Form/Edit/Address.php';
        CRM_Contact_Form_Edit_Address::formRule( $fields, $errors );
        
        // make sure that firstName and lastName or a primary OpenID is set
        if ( !$primaryID && ( !CRM_Utils_Array::value( 'first_name', $fields ) ||  
                              !CRM_Utils_Array::value( 'last_name' , $fields ) ) ) {
            $errors['_qf_default'] = ts('First Name and Last Name OR an email OR an OpenID in the Primary Location should be set.'); 
        }
        
        //check for duplicate - dedupe rules
        CRM_Contact_Form_Contact::checkDuplicateContacts( $fields, $errors, $contactID, 'Individual' );
        
        // if use_household_address option is checked, make sure 'valid household_name' is also present.
        if ( CRM_Utils_Array::value('use_household_address',$fields ) && 
             !CRM_Utils_Array::value( 'shared_household_id', $fields ) ) {
            $errors["shared_household"] = ts("Please select a household from the 'Select Household' list");
        }
        
        return empty($errors) ? true : $errors; 
    }
    
    /**
     * Function to Copy household address, if use_household_address option is checked.
     *
     * @param array $params  the input form values
     *
     * @return void
     * @access public
     * @static
     */
    static function copyHouseholdAddress( &$params ) 
    { 
        if ( $params['shared_household'] ) {
            $params['mail_to_household_id'] = $params['shared_household'];
        }
        
        if ( !$params['mail_to_household_id'] ) {
            CRM_Core_Error::statusBounce( ts("Shared Household-ID not found.") );
        }
        
        $locParams      = array( 'version'    => '3.0', 
                                 'contact_id' => $params['mail_to_household_id'] );
        $location_types = array( );

        require_once 'api/v2/Location.php';
        $values =& _civicrm_location_get( $locParams, $location_types );
 
        $addressFields = CRM_Core_DAO_Address::fields();
        foreach($addressFields as  $key =>$val ){
		   if( !CRM_Utils_Array::value( $key, $values['address'][1] ) ){
                $values['address'][1][$key]="";
            }
        }
		
        if( $is_billing = $params['address'][1]['is_billing'] ){
            $values['address'][1]['is_billing']=$is_billing;
        }
        if( $values['address'][1]['country_id']=="null"){
            $values['address'][1]['country_id']=0;
        }
        if( $values['address'][1]['state_province_id']=="null"){
            $values['address'][1]['state_province_id']=0;
        }
      
        foreach ( $values['address'][1] as $key => $val ) {
            if ( ! in_array( $key, array( 'location_type_id', 'is_billing', 'is_primary' ) ) ) {
                $params['address'][1][$key] = $values['address'][1][$key];
            }
        }

        // unset all the ids and unwanted fields
        $unsetFields = array( 'id', 'location_id', 'timezone', 'note' );
        foreach ( $unsetFields as $fld ) {
            unset( $params['address'][1][$fld] );
        } 
    }
    
    /**
     * Function to create a new shared household (used if create-new-household options is checked).
     *
     * @param array $params  the input form values
     *
     * @return void
     * @access public
     * @static
     */
    static function createSharedHousehold( &$params ) 
    {
        $houseHoldId = null;
        
        // if household id is passed.
        if ( is_numeric( $params['shared_household'] ) ) {
            $houseHoldId = $params['shared_household'];
        } else {
            $householdParams = array();

            $householdParams['address']['1'] = $params['address']['1'];
          
            $householdParams['household_name'] = $params['shared_household'];
            require_once 'CRM/Dedupe/Finder.php';
            $dedupeParams = CRM_Dedupe_Finder::formatParams($householdParams, 'Household');
                    
            $dupeIDs = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Household', 'Fuzzy');
           
            if ( empty($dupeIDs) ) {
                //create new Household
                $newHousehold = array ( 'contact_type'   => 'Household',
                                        'household_name' => $params['shared_household'], 
                                        'address'        => $householdParams['address'] );
                $houseHold   = CRM_Contact_BAO_Contact::create( $newHousehold );
                $houseHoldId = $houseHold->id;
            } else {
                $houseHoldId = $dupeIDs[0];
            } 
        }
        if ( $houseHoldId ) {
            $params['mail_to_household_id'] = $houseHoldId;
            return true;
        }
        return false;
    }
    
    /**
     * Function to Add/Edit/Delete the relation of individual with shared-household.
     *
     * @param integer $contactID  the input form values
     * @param array   $params     the input form values
     *
     * @return void
     * @access public
     * @static
     */
    static function handleSharedRelation( $contactID, &$params ) 
    {
        if ( CRM_Utils_Array::value( 'old_mail_to_household_id', $params ) != $params['mail_to_household_id'] ) {
            require_once 'CRM/Contact/BAO/Relationship.php';
            $relID  = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_RelationshipType', 'Household Member of', 'id', 'name_a_b' );
            
            if ( CRM_Utils_Array::value( 'old_mail_to_household_id', $params ) ) {
                $relationship = new CRM_Contact_DAO_Relationship( );
                $relationship->contact_id_b         = $params['old_mail_to_household_id'];
                $relationship->contact_id_a         = $contactID;
                $relationship->relationship_type_id = $relID;
                if ( $relationship->find(true) ) {
                    $relationship->delete( );
                }
            }
            
            if ( $params['mail_to_household_id'] ) {
                $ids = array('contact' => $params['mail_to_household_id'] );
                              
                $relationshipParams = array();
                $relationshipParams['relationship_type_id'] = $relID.'_b_a';
                $relationshipParams['is_active']            = 1;
                
                $relationship = new CRM_Contact_DAO_Relationship( );
                $relationship->contact_id_b         = $params['mail_to_household_id'];
                $relationship->contact_id_a         = $contactID;
                $relationship->relationship_type_id = $relID;
                // if relationship already not present, add a new one
                if ( !$relationship->find(true) ) { 
                    CRM_Contact_BAO_Relationship::add( $relationshipParams, $ids, $contactID );
                }
            }
        }
        
        return ;
    }

}
   

