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
    
}
