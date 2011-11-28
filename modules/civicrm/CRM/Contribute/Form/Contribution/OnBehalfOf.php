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
require_once 'CRM/Contact/BAO/Relationship.php';
require_once 'CRM/Core/BAO/UFGroup.php';
require_once 'CRM/Member/BAO/Membership.php';

class CRM_Contribute_Form_Contribution_OnBehalfOf
{
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    static function preProcess( &$form )
    {
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get( 'userID' );
                        
        require_once 'CRM/Core/BAO/UFJoin.php'; 
        $ufJoinParams     = array( 'module'       => 'onBehalf',
                                   'entity_table' => 'civicrm_contribution_page',   
                                   'entity_id'    => $form->_id );   
        $profileId        = CRM_Core_BAO_UFJoin::getUFGroupIds( $ufJoinParams );
        $form->_profileId = $profileId[0];
         
        if ( !$form->_profileId ||
             !CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $form->_profileId, 'is_active' ) ) {
            CRM_Core_Error::fatal( ts( 'This contribution page has been configured for contribution on behalf of an organization and the selected onbehalf profile is either disabled or not found.' ) );
        }
            
        $requiredProfileFields = array( 'organization_name', 'email' );
        $validProfile          = CRM_Core_BAO_UFGroup::checkValidProfile( $form->_profileId, $requiredProfileFields );
        if ( !$validProfile ) {
            CRM_Core_Error::fatal( ts( 'This contribution page has been configured for contribution on behalf of an organization and the required fields of the selected onbehalf profile are disabled.' ) );
        }
        
        $form->assign( 'profileId', $form->_profileId );
        $form->assign( 'mode', $form->_mode );
               
        if ( $contactID ) {
            $form->_employers = CRM_Contact_BAO_Relationship::getPermissionedEmployer( $contactID );
            if ( !empty( $form->_employers ) ) {
                $form->_relatedOrganizationFound = true;
                
                $locDataURL = CRM_Utils_System::url( 'civicrm/ajax/permlocation', 'cid=', false, null, false );
                $form->assign( 'locDataURL', $locDataURL );
                
                $dataURL = CRM_Utils_System::url( 'civicrm/ajax/employer', 'cid=' . $contactID, false, null, false );
                $form->assign( 'employerDataURL', $dataURL );
            }
            
            if ( $form->_values['is_for_organization'] != 2 ) {
                $form->assign( 'relatedOrganizationFound', $form->_relatedOrganizationFound );
            } else {
                $form->assign( 'onBehalfRequired', $form->_onBehalfRequired );
            }
                                   
            if ( count( $form->_employers ) == 1 ) {
                foreach ( $form->_employers as $id => $value ) {
                    $form->_organizationName = $value['name'];
                    $orgId = $id;
                }
                $form->assign( 'orgId', $orgId );
                $form->assign( 'organizationName', $form->_organizationName );
            }
        }
    }

    /**
     * Function to build form for related contacts / on behalf of organization.
     * 
     * @param $form              object  invoking Object
     * @param $contactType       string  contact type
     * @param $title             string  fieldset title
     *
     * @static
     */
    static function buildQuickForm( &$form ) 
    {
        $form->assign( 'fieldSetTitle', ts('Organization Details') );
        $form->assign( 'buildOnBehalfForm', true );
                
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get( 'userID' );

        if ( $contactID && count( $form->_employers ) >= 1 ) {
            $form->add('text', 'organization_id', ts('Select an existing related Organization OR enter a new one') );
            $form->add('hidden', 'onbehalfof_id', '', array( 'id' => 'onbehalfof_id' ) );
            
            $orgOptions = array( 0 => ts('Select an existing organization'),
                                 1 => ts('Enter a new organization') );
            
            $form->addRadio( 'org_option', ts('options'), $orgOptions );
            $form->setDefaults( array( 'org_option' => 0 ) );
            $form->add( 'checkbox', 'mode', '' );
        }
        
        $profileFields = CRM_Core_BAO_UFGroup::getFields( $form->_profileId, false, CRM_Core_Action::VIEW, null,
                                                          null, false, null, false, null, 
                                                          CRM_Core_Permission::CREATE, null );
        $fieldTypes = array( 'Contact', 'Organization' );
        if ( is_array( $form->_membershipBlock ) && !empty( $form->_membershipBlock ) ) {
            $fieldTypes = array_merge( $fieldTypes, array( 'Membership' ) );
        } else {
            $fieldTypes = array_merge( $fieldTypes, array( 'Contribution' ) );
        }
        
        $stateCountryMap = array( );
        foreach ( $profileFields as $name => $field ) {
            if ( in_array( $field['field_type'], $fieldTypes ) ) {
                list( $prefixName, $index ) = CRM_Utils_System::explode( '-', $name, 2 );
                if ( in_array( $prefixName, array( 'state_province', 'country', 'county' ) ) ) {
                    if ( ! array_key_exists( $index, $stateCountryMap ) ) {
                        $stateCountryMap[$index] = array( );
                    }
                    $stateCountryMap[$index][$prefixName] = 'onbehalf_' . $name;
                } else if ( in_array( $prefixName, array( 'organization_name', 'email' ) ) &&
                            !CRM_Utils_Array::value( 'is_required', $field ) ) {
                    $field['is_required'] = 1;
                }
                
                CRM_Core_BAO_UFGroup::buildProfile( $form, $field, null, null, false, true );
            }
        }
        
        if ( !empty($stateCountryMap) ) {
            require_once 'CRM/Core/BAO/Address.php';
            CRM_Core_BAO_Address::addStateCountryMap( $stateCountryMap );
        }

        $form->assign('onBehalfOfFields', $profileFields);
        $form->addElement( 'hidden', 'hidden_onbehalf_profile', 1 );
    }
}