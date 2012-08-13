<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * This file handles integration of KCFinder with wysiwyg editors
 * supported by CiviCRM
 * Ckeditor and tinyMCE
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

function CheckAuthentication() {

    static $authenticated;
    if ( !isset( $authenticated ) ) {
       $current_cwd   = getcwd();
       $civicrm_root  = dirname(dirname(getcwd()));
       $authenticated = true;
       require_once "{$civicrm_root}/civicrm.config.php";
       require_once 'CRM/Core/Config.php';

       $config = CRM_Core_Config::singleton();
       
       if ( !isset($_SESSION['KCFINDER'] ) ) {
           $_SESSION['KCFINDER'] = array();
       }
       
       $_SESSION['KCFINDER']['disabled'] = false;
       $_SESSION['KCFINDER']['uploadURL'] = $config->imageUploadURL;
       $_SESSION['KCFINDER']['uploadDir'] = $config->imageUploadDir;

       chdir( $current_cwd );
       return true;
    }
}

CheckAuthentication( );

spl_autoload_register('__autoload');

?>
