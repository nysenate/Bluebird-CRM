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

require_once 'CRM/Contact/Form/Task.php';

/**
 * This class provides the functionality to Merge Contacts.
 *
 */
class CRM_Contact_Form_Task_Merge extends CRM_Contact_Form_Task {
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        parent::preProcess( );
        $statusMsg  = null;
        $contactIds = array( );
        if ( is_array( $this->_contactIds ) ) $contactIds = array_unique( $this->_contactIds );
        if ( count( $contactIds ) < 2 ) {
            $statusMsg = ts( 'Minimum two contact records are required to perform merge operation.' );
        }
        
        // do check for same contact type.
        $contactTypes = array( );
        if ( !$statusMsg ) {
            $sql = "SELECT contact_type FROM civicrm_contact WHERE id IN (" . implode( ',', $contactIds ) . ")";
            $contact = CRM_Core_DAO::executeQuery( $sql );
            while ( $contact->fetch( ) ) {
                $contactTypes[$contact->contact_type] = true;
                if ( count( $contactTypes ) > 1 ) break;
            }
            if ( count( $contactTypes ) > 1 ) $statusMsg = ts( 'Please select same contact type records.' ); 
        }
        if ( $statusMsg ) CRM_Core_Error::statusBounce( $statusMsg );
        
        $url = null;
        // redirect to merge form directly.
        if ( count( $contactIds ) == 2 ) {
            $cid = $contactIds[0];
            $oid = $contactIds[1];
            
            //don't allow to delete logged in user.
            $session = CRM_Core_Session::singleton( );
            if ( $oid == $session->get('userID') ) {
                $oid = $cid;
                $cid = $session->get('userID');
            }
            
            $url = CRM_Utils_System::url( 'civicrm/contact/merge', "reset=1&cid={$cid}&oid={$oid}" );
        } else {
            $level = 'Fuzzy';
            $cType = key( $contactTypes );
            
            require_once 'CRM/Dedupe/DAO/RuleGroup.php';
            $rgBao = new CRM_Dedupe_DAO_RuleGroup();
            $rgBao->level        = $level;
            $rgBao->is_default   = 1;
            $rgBao->contact_type = $cType;
            if ( !$rgBao->find (true ) ) {
                CRM_Core_Error::statusBounce("You can not merge contact records because $level rule for $cType does not exist.");
            }
            $ruleGroupID = $rgBao->id;
            $session = CRM_Core_Session::singleton( );
            $session->set( 'selectedSearchContactIds', $contactIds );
            
            // create a hidden group and poceed to merge
            $url = CRM_Utils_System::url( 'civicrm/contact/dedupefind', 
                                          "reset=1&action=update&rgid={$ruleGroupID}&context=search" );
        }
        
        // redirect to merge page.
        if ( $url ) CRM_Utils_System::redirect( $url );
    }
}
