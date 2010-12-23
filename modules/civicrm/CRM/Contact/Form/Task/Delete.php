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
require_once 'CRM/Core/Menu.php';
require_once 'CRM/Core/BAO/CustomGroup.php';
require_once 'CRM/Contact/BAO/Contact.php';
/**
 * This class provides the functionality to delete a group of
 * contacts. This class provides functionality for the actual
 * deletion.
 */
class CRM_Contact_Form_Task_Delete extends CRM_Contact_Form_Task {

    /** 
     * Are we operating in "single mode", i.e. sending email to one 
     * specific contact? 
     * 
     * @var boolean 
     */ 
    protected $_single = false;

    /**
     * cache shared address message so we don't query twice
     */ 
    protected $_sharedAddressMessage = null;

    /** 
     * build all the data structures needed to build the form 
     * 
     * @return void 
     * @access public 
     */ 
    function preProcess( ) { 
        
        //check for delete
        if ( !CRM_Core_Permission::check( 'delete contacts' ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );  
        }
        
        $cid = CRM_Utils_Request::retrieve( 'cid', 'Positive',
                                            $this, false ); 

        $this->_searchKey = CRM_Utils_Request::retrieve( 'key', 'String', $this );
                
        // sort out whether it’s a delete-to-trash, delete-into-oblivion or restore (and let the template know)
        $config =& CRM_Core_Config::singleton();
        $values = $this->controller->exportValues();
        require_once 'CRM/Contact/Task.php';
        $this->_skipUndelete = (CRM_Core_Permission::check('access deleted contacts') and (CRM_Utils_Request::retrieve('skip_undelete', 'Boolean', $this) or CRM_Utils_Array::value( 'task', $values ) == CRM_Contact_Task::DELETE_PERMANENTLY));
        $this->_restore      = (CRM_Utils_Request::retrieve('restore',       'Boolean', $this) or CRM_Utils_Array::value( 'task', $values ) == CRM_Contact_Task::RESTORE);
        $this->assign('trash',   $config->contactUndelete and !$this->_skipUndelete);
        $this->assign('restore', $this->_restore);

        if ($this->_restore) CRM_Utils_System::setTitle(ts('Restore Contact'));

        if ( $cid ) { 
            require_once 'CRM/Contact/BAO/Contact/Permission.php';
            if ( !CRM_Contact_BAO_Contact_Permission::allow( $cid, CRM_Core_Permission::EDIT ) ) {
                CRM_Core_Error::fatal( ts( 'You do not have permission to delete this contact. Note: you can delete contacts if you can edit them.' ) );
            }

            $this->_contactIds = array( $cid ); 
            $this->_single     = true; 
            $this->assign( 'totalSelectedContacts', 1 );
            
        } else {
            parent::preProcess( );
        }

        $this->_sharedAddressMessage = $this->get( 'sharedAddressMessage' );
        if ( !$this->_restore && !$this->_sharedAddressMessage ) {
            // we check for each contact for shared contact address
            require_once 'CRM/Core/BAO/Address.php';
            $sharedContactList = array( );
            $sharedAddressCount = 0;
            foreach( $this->_contactIds as $contactId ) {
                // check if a contact that is being deleted has any shared addresses
                $sharedAddressMessage = CRM_Core_BAO_Address::setSharedAddressDeleteStatus( null, $contactId, true );

                if ( $sharedAddressMessage['count'] > 0 ) {
                    $sharedAddressCount += $sharedAddressMessage['count'];
                    $sharedContactList = array_merge( $sharedContactList, 
                                                      $sharedAddressMessage['contactList'] );   
                }
            }
            
            $this->_sharedAddressMessage = array( 'count'       => $sharedAddressCount,
                                                  'contactList' => $sharedContactList ); 

            if ( $sharedAddressCount > 0 ) {
                if ( count( $this->_contactIds ) > 1 ) {
                    //more than one contact is deleted
                    CRM_Core_Session::setStatus(ts('Selected contact(s) has an address record which is shared with %1 other contact(s). Shared addresses will not be removed or altered but will no longer be shared.', array(1 => $sharedAddressCount)));
                } else {
                    // only one contact is been deleted
                    CRM_Core_Session::setStatus(ts('This contact has an address record which is shared with %1 other contact(s). Shared addresses will not be removed or altered but will no longer be shared.', array(1 => $sharedAddressCount)));
                }
            }

            // set in form controller so that queries are not fired again
            $this->set( 'sharedAddressMessage', $this->_sharedAddressMessage );
        }
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
        $label = $this->_restore ? ts('Restore Contact(s)') : ts('Delete Contact(s)');

        if ( $this->_single ) {
            // also fix the user context stack in case the user hits cancel
            $session = CRM_Core_Session::singleton( );
            $session->replaceUserContext( CRM_Utils_System::url('civicrm/contact/view',
                                                                'reset=1&cid=' . $this->_contactIds[0] ) );
            $this->addDefaultButtons( $label, 'done', 'cancel' );
        } else {
            $this->addDefaultButtons( $label, 'done' );
        }
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        $session = CRM_Core_Session::singleton( );
        $currentUserId = $session->get( 'userID' );
        
        $context = CRM_Utils_Request::retrieve( 'context', 'String', $this, false, 'basic' );
        $urlParams = 'force=1';
        if ( CRM_Utils_Rule::qfKey( $this->_searchKey ) ) {
            $urlParams .= "&qfKey=$this->_searchKey";
        } elseif ( $context == 'search' ) {
            $urlParams .= "&qfKey={$this->controller->_key}";
        }
        $urlString = "civicrm/contact/search/$context";
        if ( $context == 'search' ) $urlString = 'civicrm/contact/search';  
        
        $selfDelete = false;
        $deletedContacts = 0;
        foreach ( $this->_contactIds as $contactId ) {
            if ($currentUserId == $contactId) {
                $selfDelete = true;
                continue;
            }
            
            if ( CRM_Contact_BAO_Contact::deleteContact( $contactId, $this->_restore, $this->_skipUndelete ) ) {
                $deletedContacts++;
            }
        }
        if ( ! $this->_single ) {
            $label = $this->_restore ? ts('Restored Contact(s): %1', array(1 => $deletedContacts)) : ts('Deleted Contact(s): %1', array(1 => $deletedContacts));
            $status = array(
                            $label,
                            ts('Total Selected Contact(s): %1', array(1 => count($this->_contactIds))),
                            );
            
            if ( $selfDelete ) {
                $display_name = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                             $currentUserId,
                                                             'display_name' );
                $status[] = ts('The contact record which is linked to the currently logged in user account - \'%1\' - cannot be deleted.', array(1 => $display_name));
            }
        } else {
            if ( $deletedContacts ) {
                
                $session->replaceUserContext( CRM_Utils_System::url( $urlString, $urlParams ) );
                
                if ($this->_restore) {
                    $status = ts('Selected contact was restored sucessfully.');
                    $session->replaceUserContext(CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$this->_contactIds[0]}"));
                } else {
                    $status = ts('Selected contact was deleted sucessfully.');
                }
            } else {
                $status = array(
                                ts('Selected contact cannot be deleted.')
                                ); 
                if ( $selfDelete ) {
                    $display_name = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                                 $currentUserId,
                                                                 'display_name' );
                    $status[] = ts('This contact record is linked to the currently logged in user account - \'%1\' - and cannot be deleted.', array(1 => $display_name));
                } else {
                    $status[] = ts( 'The contact might be the Membership Organization of a Membership Type. You will need to edit the Membership Type and change the Membership Organization before you can delete this contact.' );
                }
            }
        }
        
        if ( isset( $this->_sharedAddressMessage ) && $this->_sharedAddressMessage['count'] > 0 && !$this->_restore ) { 
            if ( count( $this->_contactIds ) > 1 ) {
                $sharedAddressMessage = ts( 'The following contact(s) have address records which were shared with the address you removed from selected contacts. These address records are no longer shared - but they have not been removed or altered.' ) . '<br>' . implode( '<br>', $this->_sharedAddressMessage['contactList'] );
            } else {
                $sharedAddressMessage = ts( 'The following contact(s) have address records which were shared with the address you removed from this contact. These address records are no longer shared - but they have not been removed or altered.' ) . '<br>' . implode( '<br>', $this->_sharedAddressMessage['contactList'] );
 
            }

            if ( is_array( $status ) ) {
                $status[] = $sharedAddressMessage;
            } else {
                $status .= $sharedAddressMessage;
            }

            $this->set( 'sharedAddressMessage', null );
        }            
        
        CRM_Core_Session::setStatus( $status );
        $session->replaceUserContext( CRM_Utils_System::url( $urlString, $urlParams ) );
    }//end of function
}
