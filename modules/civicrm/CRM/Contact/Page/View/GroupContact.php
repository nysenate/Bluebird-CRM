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

require_once 'CRM/Core/Page.php';

class CRM_Contact_Page_View_GroupContact extends CRM_Core_Page {
    
    /**
     * This function is called when action is browse
     * 
     * return null
     * @access public
     */
    function browse( ) {
  
        $count   = CRM_Contact_BAO_GroupContact::getContactGroup($this->_contactId, null, null, true);
        
        $in      =& CRM_Contact_BAO_GroupContact::getContactGroup($this->_contactId, 'Added' );
        $pending =& CRM_Contact_BAO_GroupContact::getContactGroup($this->_contactId, 'Pending' );
        $out     =& CRM_Contact_BAO_GroupContact::getContactGroup($this->_contactId, 'Removed' );

        $this->assign       ( 'groupCount'  , $count );
        $this->assign_by_ref( 'groupIn'     , $in );
        $this->assign_by_ref( 'groupPending', $pending );
        $this->assign_by_ref( 'groupOut'    , $out );
    }

    /**
     * This function is called when action is update
     * 
     * @param int    $groupID group id 
     *
     * return null
     * @access public
     */
    function edit( $groupId = null ) {
        $controller = new CRM_Core_Controller_Simple( 'CRM_Contact_Form_GroupContact',
                                                       ts('Contact\'s Groups'),
                                                       $this->_action );
        $controller->setEmbedded( true );

        // set the userContext stack
        $session = CRM_Core_Session::singleton();

        $session->pushUserContext( CRM_Utils_System::url( 'civicrm/contact/view',
                                                          "action=browse&selectedChild=group&cid={$this->_contactId}" ),
                                   false);
        $controller->reset( );

        $controller->set( 'contactId', $this->_contactId );
        $controller->set( 'groupId'  , $groupId );
 
        $controller->process( );
        $controller->run( );

    }

    function preProcess() {
        $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true );
        $this->assign( 'contactId', $this->_contactId );

        // check logged in url permission
        require_once 'CRM/Contact/Page/View.php';
        CRM_Contact_Page_View::checkUserPermission( $this );
        
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, false, 'browse');
        $this->assign( 'action', $this->_action);
    }    

    /**
     * This function is the main function that is called
     * when the page loads, it decides the which action has
     * to be taken for the page.
     * 
     * return null
     * @access public
     */
    function run( ) {
        $this->preProcess( );

        if ( $this->_action == CRM_Core_Action::DELETE ) {
            $groupContactId = CRM_Utils_Request::retrieve( 'gcid', 'Positive',
                                                           CRM_Core_DAO::$_nullObject, true );
            $status         = CRM_Utils_Request::retrieve( 'st', 'String',
                                                           CRM_Core_DAO::$_nullObject, true );
            if ( is_numeric($groupContactId) && $status ) {
                $this->del( $groupContactId, $status, $this->_contactId);
            }
            $session = CRM_Core_Session::singleton();
            CRM_Utils_System::redirect( $session->popUserContext() );
        }

        $this->edit( null, CRM_Core_Action::ADD );
        $this->browse( );
        return parent::run( );
    }

 
    /**
     * function to remove/ rejoin the group
     *
     * @param int $groupContactId id of crm_group_contact
     * @param string $status this is the status that should be updated.
     *
     * $access public
     */
    function del( $groupContactId, $status, $contactID ) {
        $groupId = CRM_Contact_BAO_GroupContact::getGroupId($groupContactId);
       
        switch ($status) {
        case 'i' :
            $groupStatus = 'Added';
            break;
        case 'p' :
            $groupStatus = 'Pending';
           
            break;
        case 'o' :
            $groupStatus = 'Removed';
            break;
        }

        $groupNum = CRM_Contact_BAO_GroupContact::getContactGroup( $this->_contactId, 'Added', 
                                                                   null, true, true );
        if ( defined( 'CIVICRM_MULTISITE' ) && CIVICRM_MULTISITE && 
             $groupNum == 1 && $groupStatus == 'Removed' ) {
            CRM_Core_Session::setStatus( 'make sure at least one contact group association is maintained.' );
            return false;
        }

        $ids = array($contactID);
        $method = 'Admin';

        $session = CRM_Core_Session::singleton();
        $userID  = $session->get( 'userID' );

        if ( $userID == $contactID ) {
            $method = 'Web';
        }

        CRM_Contact_BAO_GroupContact::removeContactsFromGroup($ids, $groupId, $method  ,$groupStatus);
    }
}


