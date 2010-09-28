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
class CRM_Contact_Form_Edit_Organization  
{
    /**
     * This function provides the HTML form elements that are specific to this Contact Type
     *
     * @access public
     * @return None
     */
    public function buildQuickForm( &$form ) {
        $attributes = CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact');

        $form->applyFilter('__ALL__','trim');
        
        // Organization_name
        $form->add('text', 'organization_name', ts('Organization Name'), $attributes['organization_name']);
        
        // legal_name
        $form->addElement('text', 'legal_name', ts('Legal Name'), $attributes['legal_name']);

        // nick_name
        $form->addElement('text', 'nick_name', ts('Nick Name'),
                          CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'nick_name') );

        // sic_code
        $form->addElement('text', 'sic_code', ts('SIC Code'), $attributes['sic_code']);
        
        $form->addElement('text', 'contact_source', ts('Source'));
        $form->add('text', 'external_identifier', ts('External Id'), CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'external_identifier'), false);
        $form->addRule( 'external_identifier',
			ts('External ID already exists in Database.'), 
                        'objectExists', 
			array( 'CRM_Contact_DAO_Contact', $form->_contactId, 'external_identifier' ) );
    }

    static function formRule( $fields, $files, $contactID = null ) {
       
        $errors = array( );
        
        $primaryID = CRM_Contact_Form_Contact::formRule( $fields, $errors, $contactID );
        
        // make sure that organization name is set
        if (! CRM_Utils_Array::value( 'organization_name', $fields ) ) {
            $errors['organization_name'] = 'Organization Name should be set.';
        }
        
        //check for duplicate - dedupe rules
        CRM_Contact_Form_Contact::checkDuplicateContacts( $fields, $errors, $contactID, 'Organization' );
        
        // add code to make sure that the uniqueness criteria is satisfied
        return empty( $errors ) ? true : $errors;
    }
}


    

