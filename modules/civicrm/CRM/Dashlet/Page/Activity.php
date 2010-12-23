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

/**
 * Main page for activity dashlet
 *
 */
class CRM_Dashlet_Page_Activity extends CRM_Core_Page 
{
    /**
     * List activities as dashlet
     *
     * @return none
     *
     * @access public
     */
    function run( ) {
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get('userID');
        
        // a user can always view their own activity
        // if they have access CiviCRM permission
        $permission = CRM_Core_Permission::VIEW;
        
        // make the permission edit if the user has edit permission on the contact
        require_once 'CRM/Contact/BAO/Contact/Permission.php';
        if ( CRM_Contact_BAO_Contact_Permission::allow( $contactID, CRM_Core_Permission::EDIT ) ) {
            $permission = CRM_Core_Permission::EDIT;
        }
        
        $admin = CRM_Core_Permission::check( 'view all activities' ) ||
                 CRM_Core_Permission::check( 'administer CiviCRM' );
                                 
        require_once 'CRM/Core/Selector/Controller.php';
        $output = CRM_Core_Selector_Controller::SESSION;
        require_once 'CRM/Activity/Selector/Activity.php';
        $selector   = new CRM_Activity_Selector_Activity($contactID, $permission, $admin, 'home' );
        $sortID     = null;
        
        if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) {
            $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ),
                                                   $this->get( CRM_Utils_Sort::SORT_DIRECTION ) );
        }
        
        $controller = new CRM_Core_Selector_Controller( $selector,
                                                        $this->get(CRM_Utils_Pager::PAGE_ID),
                                                        $sortID,
                                                        CRM_Core_Action::VIEW, $this, $output);
        $controller->setEmbedded(true);
        $controller->run();
        $controller->moveFromSessionToTemplate( );

        return parent::run( );
    }
}