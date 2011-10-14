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
 * File for the CiviCRM APIv3 entity tag functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_EntityTag
 * 
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: EntityTag.php 30879 2010-11-22 15:45:55Z shot $
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';

/**
 *
 * @param array $params
 * @return <type>
 */
function civicrm_api3_entity_tag_get( $params ) {

    civicrm_api3_verify_one_mandatory($params, null,array('entity_id','contact_id'));
    
    $entityID    = null;
    $entityTable = 'civicrm_contact';
   
    if ( !( $entityID = CRM_Utils_Array::value( 'entity_id', $params ) ) ) {
        $entityID = CRM_Utils_Array::value( 'contact_id', $params );
    }
    

    if ( CRM_Utils_Array::value( 'entity_table', $params ) ) {
        $entityTable = $params['entity_table'];
    }

    require_once 'CRM/Core/BAO/EntityTag.php';
    $values =& CRM_Core_BAO_EntityTag::getTag( $entityID, $entityTable );
    $result = array( );
    foreach ( $values as $v ) {
        $result[$v] = array( 'tag_id' => $v );
    }
    return civicrm_api3_create_success($result,$params);


}

/**
 *
 * @param <type> $params
 * @return <type>
 * @todo EM 7 Jan 2011 - believe this should be deleted
 */
function civicrm_api3_entity_tag_display( $params ) {

        civicrm_api3_verify_one_mandatory($params,null,array('entity_id','contact_id'));
    
        $entityID    = null;
        $entityTable = 'civicrm_contact';
   
        if ( !( $entityID = CRM_Utils_Array::value( 'entity_id', $params ) ) ) {
            $entityID = CRM_Utils_Array::value( 'contact_id', $params );
        }
    

        if ( CRM_Utils_Array::value( 'entity_table', $params ) ) {
            $entityTable = $params['entity_table'];
        }

        require_once 'CRM/Core/BAO/EntityTag.php';
        $values =& CRM_Core_BAO_EntityTag::getTag( $entityID, $entityTable );
        $result = array( );
        $tags   = CRM_Core_PseudoConstant::tag( );
        foreach ( $values as $v ) {
            $result[] = $tags[$v];
        }
        return implode( ',', $result );
  

}

/**
 * Returns all entities assigned to a specific Tag.
 * @param  $params      Array   an array valid Tag id                               
 * @return $entities    Array   An array of entity ids.
 * @access public
 */
function civicrm_api3_tag_entities_get( $params )
{

    civicrm_api3_verify_mandatory($params, null,array('tag_id'));
 
    require_once 'CRM/Core/BAO/Tag.php';
    require_once 'CRM/Core/BAO/EntityTag.php';
    $tag      = new CRM_Core_BAO_Tag();
    $tag->id  = CRM_Utils_Array::value( 'tag_id', $params ) ? $params['tag_id'] : null;
    $entities =& CRM_Core_BAO_EntityTag::getEntitiesByTag($tag);    
    return $entities;   

}

/**
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_entity_tag_create( $params ) {


    return _civicrm_api3_entity_tag_common( $params, 'add' );

}

/**
 *
 * @param array $params
 * @return <type>
 */
function civicrm_api3_entity_tag_delete( $params ) {

      return _civicrm_api3_entity_tag_common( $params, 'remove' );

}

/**
 *
 * @param <type> $params
 * @param <type> $op
 * @return <type> 
 */
function _civicrm_api3_entity_tag_common( $params, $op = 'add' ) {
    civicrm_api3_verify_mandatory($params);
    $entityIDs    = array( );
    $tagsIDs      = array( );
    $entityTable  = 'civicrm_contact';
    if (is_array($params)) {
        foreach ( $params as $n => $v ) {
            if ( (substr( $n, 0, 10 ) == 'contact_id') || (substr( $n, 0, 9 ) == 'entity_id') ) {
                $entityIDs[] = $v;
            } else if ( substr( $n, 0, 6 ) == 'tag_id' ) {
                $tagIDs[] = $v;
            } else if ( substr( $n, 0, 12 ) == 'entity_table' ) {
                $entityTable = $v;
            }
        }
    }
    if ( empty( $entityIDs ) ) {
        return civicrm_api3_create_error(  'contact_id is a required field'  );
    }

    if ( empty( $tagIDs ) ) {
        return civicrm_api3_create_error(  'tag_id is a required field'  );
    }
  
    require_once 'CRM/Core/BAO/EntityTag.php';
    $values = array( 'is_error' => 0 );
    if ( $op == 'add' ) {
        $values['total_count'] = $values['added'] = $values['not_added'] = 0;
        foreach ( $tagIDs as $tagID ) {
            list( $te, $a, $na ) = 
                CRM_Core_BAO_EntityTag::addEntitiesToTag( $entityIDs, $tagID, $entityTable );
            $values['total_count'] += $te;
            $values['added']       += $a;
            $values['not_added']   += $na;
        }
    } else {
        $values['total_count'] = $values['removed'] = $values['not_removed'] = 0;
        foreach ( $tagIDs as $tagID ) {
            list( $te, $r, $nr ) = 
                CRM_Core_BAO_EntityTag::removeEntitiesFromTag( $entityIDs, $tagID, $entityTable );
            $values['total_count'] += $te;
            $values['removed']     += $r;
            $values['not_removed'] += $nr;
        }
    }
    return $values;
}
