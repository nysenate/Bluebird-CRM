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

/**
 * Auxilary class to provide support to the Contact Form class. Does this by implementing
 * a small set of static methods
 *
 */
class CRM_Contact_Form_Edit_Household 
{
    /**
     * This function provides the HTML form elements that are specific to the Individual Contact Type
     *
     * @access public
     * @return None
     */
    public function buildQuickForm( &$form, $action = null ) 
    {
        $attributes = CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact');
        
        $form->applyFilter('__ALL__','trim');  
      
        // household_name
        $form->add('text', 'household_name', ts('Household Name'), $attributes['household_name']);
        
        // nick_name
        $form->addElement('text', 'nick_name', ts('Nick Name'), $attributes['nick_name'] );
        $form->addElement('text', 'contact_source', ts('Source'), CRM_Utils_Array::value( 'source', $attributes ) );
        $form->add('text', 'external_identifier', ts('External Id'), $attributes['external_identifier'], false);
        $form->addRule( 'external_identifier',
                        ts('External ID already exists in Database.'), 
                        'objectExists', 
                        array( 'CRM_Contact_DAO_Contact', $form->_contactId, 'external_identifier' ) );
        
    }
    
    /**
     * add rule for household
     *
     * @params array $fields array of form values
     *
     * @return $error 
     * @static
     * @public
     */
    static function formRule( $fields, $files, $contactID = null ) 
    {
        $errors = array( );
        
        $primaryID = CRM_Contact_Form_Contact::formRule( $fields, $errors, $contactID );
        
        // make sure that household name is set
        if (! CRM_Utils_Array::value( 'household_name', $fields ) ) {
            $errors['household_name'] = 'Household Name should be set.';
        }
        
        //check for duplicate - dedupe rules
        CRM_Contact_Form_Contact::checkDuplicateContacts( $fields, $errors, $contactID, 'Household' );
        
        return empty( $errors ) ? true : $errors;
    }
    
    /**
     * This function synchronizes (updates) the address of individuals,
     * sharing the address of the passed household-contact-ID.
     * @param integer $householdContactID  the household contact id.
     *
     * @return void
     * @access public
     * @static
     */
    static function synchronizeIndividualAddresses( $householdContactID ) 
    {
        require_once 'api/v2/Location.php';
        require_once 'CRM/Core/BAO/Location.php';
        $locValues =& _civicrm_location_get( array( 'version'    => '3.0',
                                                    'contact_id' => $householdContactID ) );
        $query = "
SELECT cc.id as id,ca.id address_id 
FROM civicrm_contact cc LEFT JOIN civicrm_address ca ON cc.id = ca.contact_id 
WHERE mail_to_household_id = $householdContactID;";

        $contact = CRM_Core_DAO::executeQuery( $query );

        $query = "UPDATE civicrm_address ca, civicrm_contact cc
SET is_primary = 0 
WHERE ca.contact_id = cc.id AND mail_to_household_id = $householdContactID;";

        $update = CRM_Core_DAO::singleValueQuery( $query );
        
        if ( CRM_Utils_Array::value( 'address', $locValues ) && count( $locValues['address'] ) ) {
            while ( $contact->fetch( ) ) {
                $locParams = array( 'contact_id' => $contact->id,
                                    'address'    => array( 1 => $locValues['address'][1] ) );

                // removing unwanted ids from the params array
                foreach ( array( 'timezone', 'contact_id' ) as $fld ) {
                    if ( isset( $locParams['address'][1][$fld] ) ) unset( $locParams['address'][1][$fld] );
                }
                
                $locParams['address'][1]['id'] = $contact->address_id;
                CRM_Core_BAO_Location::create( $locParams );
            }
        }
    }
}
    

