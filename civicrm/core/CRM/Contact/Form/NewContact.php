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
 * This class build form elements for select exitsing or create new contact widget
 */
class CRM_Contact_Form_NewContact  
{
    function buildQuickForm( &$form ) {
        // call to build contact autocomplete
        $attributes = array( 'width' => '200px' );    
        $form->add('text', "contact", ts('Select Contact'), $attributes );
        $form->addElement('hidden', "contact_select_id" );
        
        if ( CRM_Core_Permission::check( 'edit all contacts' ) ||
             CRM_Core_Permission::check( 'add contacts' ) ) {            
            // build select for new contact
            require_once 'CRM/Core/BAO/UFGroup.php';
            $contactProfiles = CRM_Core_BAO_UFGroup::getReservedProfiles( );
            $form->add( 'select', 'profiles', ts('Create New Contact'),
                        array( '' => ts('- create new contact -') ) + $contactProfiles,
                        false, array( 'onChange' => "if (this.value) newContact( this.value );") );
        }
    }    
}
