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

require_once 'CRM/Core/Page.php';

/**
 * CiviCRM Dashboard
 *
 */
class CRM_Contact_Page_DashBoard extends CRM_Core_Page
{
        
    /**
     * Run dashboard
     *
     * @return none
     * @access public
     */
    function run( )
    {
        $resetCache = CRM_Utils_Request::retrieve( 'resetCache', 'Positive', CRM_Core_DAO::$_nullObject );
        
        if ( $resetCache ) {
            require_once 'CRM/Core/BAO/Dashboard.php';
            CRM_Core_BAO_Dashboard::resetDashletCache( );
        }
        
        CRM_Utils_System::setTitle( ts('CiviCRM Home') );
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get('userID');                
        
        // call hook to get html from other modules
        require_once 'CRM/Utils/Hook.php';
        $contentPlacement = CRM_Utils_Hook::DASHBOARD_BELOW;  // ignored but needed to prevent warnings
        $html = CRM_Utils_Hook::dashboard( $contactID, $contentPlacement );
        if ( is_array( $html ) ) {
            $this->assign_by_ref( 'hookContent', $html );
            $this->assign( 'hookContentPlacement', $contentPlacement );
        }
        
        return parent::run( );
    }
}
