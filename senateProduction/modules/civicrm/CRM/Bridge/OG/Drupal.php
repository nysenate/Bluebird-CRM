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

class CRM_Bridge_OG_Drupal {

    static function nodeapi( &$params, $op ) {
        require_once 'CRM/Bridge/OG/Utils.php';

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        // first create or update the CiviCRM group
        $groupParams               = $params;
        $groupParams['source']     = CRM_Bridge_OG_Utils::ogSyncName( $params['og_id'] );
        $groupParams['group_type'] = array( '2' => 1 );
        self::updateCiviGroup( $groupParams, $op, $groupType );

        if ( CRM_Bridge_OG_Utils::aclEnabled( ) ) {
            // next create or update the CiviCRM ACL group
            $aclParams               = $params;
            $aclParams['name']       = $aclParams['title'] = "{$aclParams['name']}: Administrator";
            $aclParams['source']     = CRM_Bridge_OG_Utils::ogSyncACLName( $params['og_id'] );
            $aclParams['group_type'] = array('1');
            self::updateCiviGroup    ( $aclParams, $op );
            
            $aclParams['acl_group_id']     = $aclParams['group_id'];
            $aclParams['civicrm_group_id'] = $groupParams['group_id'];
            
            self::updateCiviACLTables    ( $aclParams, $op );
        }

        $transaction->commit( );
    }

    static function updateCiviGroup( &$params, $op, $groupType = null ) {
        $abort        = ( $op == 'delete' ) ? true : false;
        $params['id'] = CRM_Bridge_OG_Utils::groupID( $params['source'], $params['title'], $abort );

        if ( $op == 'add' ) {
            require_once 'api/v2/Group.php';
            if ( $groupType ) {
                $params['group_type'] = $groupType;
            }
            
            $group = civicrm_group_add( $params );
            if ( ! civicrm_error( $group ) ) {
                $params['group_id'] = $group['result'];
            }
        } else {
            // do this only if we have a valid id
            if ( $params['id'] ) {
                require_once 'CRM/Contact/BAO/Group.php';
                CRM_Contact_BAO_Group::discard( $params['id'] );
                $params['group_id'] = $params['id'];
            }
        }
        unset( $params['id'] );
    }
        
    static function updateCiviACLTables( $aclParams, $op ) {
        if ( $op == 'delete' ) {
            self::updateCiviACL          ( $aclParams, $op );
            self::updateCiviACLEntityRole( $aclParams, $op );
            self::updateCiviACLRole      ( $aclParams, $op );
        } else {
            self::updateCiviACLRole      ( $aclParams, $op );
            self::updateCiviACLEntityRole( $aclParams, $op );
            self::updateCiviACL          ( $aclParams, $op );
        }
    }

    static function updateCiviACLRole( &$params, $op ) {
        require_once 'CRM/Core/DAO/OptionValue.php';

        $optionGroupID = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup',
                                                      'acl_role',
                                                      'id',
                                                      'name' );
    
        $dao = new CRM_Core_DAO_OptionValue( );
        $dao->option_group_id = $optionGroupID;
        $dao->description     = $params['source'];
        
        if ( $op == 'delete' ) {
            $dao->delete( );
            return;
        }

        $dao->label           = $params['title' ];
        $dao->is_active       = 1;

        $weightParams = array( 'option_group_id' => $optionGroupID );
        $dao->weight          = CRM_Utils_Weight::getDefaultWeight( 'CRM_Core_DAO_OptionValue', 
                                                                    $weightParams );
        $dao->value           = CRM_Utils_Weight::getDefaultWeight( 'CRM_Core_DAO_OptionValue', 
                                                                    $weightParams,
                                                                    'value' );
    
        $query = "
SELECT v.id
  FROM civicrm_option_value v
 WHERE v.option_group_id = %1
   AND v.description     = %2
";
        $queryParams  = array( 1 => array( $optionGroupID   , 'Integer' ),
                               2 => array( $params['source'], 'String'  ) );
        $dao->id = CRM_Core_DAO::singleValueQuery( $query, $queryParams );
        $dao->save( );
        $params['acl_role_id'] = $dao->value;
    }

    static function updateCiviACLEntityRole( &$params, $op ) {
        require_once 'CRM/ACL/DAO/EntityRole.php';
        $dao = new CRM_ACL_DAO_EntityRole( );

        $dao->entity_table = 'civicrm_group';
        $dao->entity_id    = $params['acl_group_id'];
        if ( $op == 'delete' ) {
            $dao->delete( );
            return;
        }

        $dao->acl_role_id  = $params['acl_role_id'];

        $dao->find( true );
        $dao->is_active    = true;
        $dao->save( );
        $params['acl_entity_role_id'] = $dao->id;
    }

    static function updateCiviACL( &$params, $op ) {
        require_once 'CRM/ACL/DAO/ACL.php';
        $dao = new CRM_ACL_DAO_ACL( );

        $dao->object_table = 'civicrm_saved_search';
        $dao->object_id    = $params['civicrm_group_id'];

        if ( $op == 'delete' ) {
            $dao->delete( );
            return;
        }

        $dao->find( true );

        $dao->entity_table = 'civicrm_acl_role';
        $dao->entity_id    = $params['acl_role_id'];
        $dao->operation    = 'Edit';

        $dao->is_active = true;
        $dao->save( );
        $params['acl_id'] = $dao->id;
    }

    static function og( &$params, $op ) {
        require_once 'CRM/Bridge/OG/Utils.php';

        $contactID = CRM_Bridge_OG_Utils::contactID( $params['uf_id'] );
        if ( ! $contactID ) {
            CRM_Core_Error::fatal( );
        }

        // get the group id of this OG
        $groupID   = CRM_Bridge_OG_Utils::groupID( CRM_Bridge_OG_Utils::ogSyncName( $params['og_id'] ),
                                                   null, true );
        
        $groupParams = array( 'contact_id' => $contactID,
                              'group_id'   => $groupID  );

        require_once 'api/v2/GroupContact.php';
        if ( $op == 'add' ) {
            $groupParams['status'] = $params['is_active'] ? 'Added' : 'Pending';
            civicrm_group_contact_add( $groupParams );
        } else {
            $groupParams['status'] = 'Removed';
            civicrm_group_contact_remove( $groupParams );
        }

        if ( CRM_Bridge_OG_Utils::aclEnabled( ) &&
             $params['is_admin'] !== null ) {
            // get the group ID of the acl group
            $groupID   = CRM_Bridge_OG_Utils::groupID( CRM_Bridge_OG_Utils::ogSyncACLName( $params['og_id'] ),
                                                       null, true );
            
            $groupParams = array( 'contact_id' => $contactID,
                                  'group_id'   => $groupID  ,
                                  'status'     => $params['is_admin'] ? 'Added' : 'Removed' );
            
            if ( $params['is_admin'] ) {
                civicrm_group_contact_add( $groupParams );
            } else {
                civicrm_group_contact_remove( $groupParams );
            }
        }
    }

}

