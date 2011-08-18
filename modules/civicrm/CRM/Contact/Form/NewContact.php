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
 * This class build form elements for select exitsing or create new contact widget
 */
class CRM_Contact_Form_NewContact  
{
    /**
     * Function used to build form element for new contact or select contact widget
     *
     * @param object   $form form object
     * @param int      $blocNo by default it is one, except for address block where it is
     *                 build for each block
     * @param array    $extrProfiles extra profiles that should be included besides reserved
     *
     * @access public
     * @return void
     */
    function buildQuickForm( &$form, $blockNo = 1, $extraProfiles = null ) {
        // call to build contact autocomplete
        $attributes = array( 'width' => '200px' );    
        $form->add('text', "contact[{$blockNo}]", ts('Select Contact'), $attributes );
        $form->addElement('hidden', "contact_select_id[{$blockNo}]" );
        
        if ( CRM_Core_Permission::check( 'edit all contacts' ) ||
             CRM_Core_Permission::check( 'add contacts' ) ) {            
            // build select for new contact
            require_once 'CRM/Core/BAO/UFGroup.php';
            $contactProfiles = CRM_Core_BAO_UFGroup::getReservedProfiles( 'Contact', $extraProfiles );
            $form->add( 'select', "profiles[{$blockNo}]", ts('Create New Contact'),
                        array( '' => ts('- create new contact -') ) + $contactProfiles,
                        false, array( 'onChange' => "if (this.value) newContact{$blockNo}( this.value, {$blockNo} );") );
        }
        
        $form->assign( 'blockNo', $blockNo );
    }    
}
