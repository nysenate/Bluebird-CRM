<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('No direct access allowed'); 

// check for php version and ensure its greater than 5.
// do a fatal exit if
if ( (int ) substr( PHP_VERSION, 0, 1 ) < 5 ) {
    echo "CiviCRM requires PHP Version 5.2 or greater. You are running PHP Version " . PHP_VERSION . "<p>";
    exit( );
}

include_once 'civicrm.settings.php';

require_once 'PEAR.php';

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Core/Error.php';
require_once 'CRM/Core/Invoke.php';

civicrm_invoke( );

function civicrm_init( ) {
    $config = CRM_Core_Config::singleton();
}

function plugin_init( ) {
    //invoke plugins.
    JPluginHelper::importPlugin( 'civicrm' );
    $app =& JFactory::getApplication( );
    $app->triggerEvent( 'onCiviLoad' ); 
}

function civicrm_invoke( ) {
    civicrm_init( );

    plugin_init( );

    $user = JFactory::getUser( );

    /* bypass synchronize if running upgrade 
     * to avoid any serious non-recoverable error 
     * which might hinder the upgrade process. 
     */
    require_once 'CRM/Utils/Array.php';
    if ( CRM_Utils_Array::value( 'task', $_REQUEST ) != 'civicrm/upgrade' ) {
        require_once 'CRM/Core/BAO/UFMatch.php';
        CRM_Core_BAO_UFMatch::synchronize( $user, false, 'Joomla', 'Individual', true );
    }

    require_once 'CRM/Utils/System/Joomla.php';
    CRM_Utils_System_Joomla::addHTMLHead( null, true );

    if ( isset( $_GET['task'] ) ) { 
        $args = explode( '/', trim( $_GET['task'] ) );
    } else {
        $_GET['task'] = 'civicrm/dashboard';
        $_GET['reset'] = 1;
        $args = array( 'civicrm', 'dashboard' );
    }
    CRM_Core_Invoke::invoke( $args );
}


