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
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
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

class CRM_Upgrade_Incremental_php_ThreeTwo {
    
    function verifyPreDBstate ( &$errors ) {
        return true;
    }
    
    function upgrade_3_2_alpha1( $rev ) 
    {
        //CRM-5666 -if user already have 'access CiviCase'
        //give all new permissions and drop access CiviCase.
        $config = CRM_Core_Config::singleton( );
        if ( $config->userFramework == 'Drupal' ) {
            db_query( "UPDATE {permission} SET perm = REPLACE( perm, 'access CiviCase', 'access my cases and activities, access all cases and activities, administer CiviCase' )" );
            //insert core acls.
            $casePermissions = array( 'delete in CiviCase',
                                      'administer CiviCase', 
                                      'access my cases and activities', 
                                      'access all cases and activities', );
            require_once 'CRM/ACL/DAO/ACL.php';
            $aclParams = array( 'name'         => 'Core ACL',
                                'deny'         => 0,
                                'acl_id'       => NULL,
                                'object_id'    => NULL,
                                'acl_table'    => NULL,
                                'entity_id'    => 1,
                                'operation'    => 'All',
                                'is_active'    => 1,
                                'entity_table' => 'civicrm_acl_role' );
            foreach ( $casePermissions as $per ) {
                $aclParams['object_table'] = $per;
                $acl = new CRM_ACL_DAO_ACL( );
                $acl->object_table = $per;
                if ( !$acl->find( true ) ) {
                    $acl->copyValues( $aclParams );
                    $acl->save( );
                }
            }
            //drop 'access CiviCase' acl
            CRM_Core_DAO::executeQuery( "DELETE FROM civicrm_acl WHERE object_table = 'access CiviCase'" );
        }
        
        $upgrade =& new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }
    
  }
