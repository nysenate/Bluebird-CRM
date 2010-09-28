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
 * Definition of the User Profile Group of the CRM API. 
 * More detailed documentation can be found 
 * {@link http://objectledge.org/confluence/display/CRM/CRM+v1.0+Public+APIs
 * here}
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/**
 * Files required for this package
 */
require_once 'api/utils.php'; 

require_once 'CRM/Core/BAO/UFJoin.php';

/**
 * takes an associative array and creates a uf join object
 *
 * @param array $params assoc array of name/value pairs
 *
 * @return object CRM_Core_DAO_UFJoin object 
 * @access public
 * 
 */
function crm_add_uf_join($params) 
{
    if ( ! is_array( $params ) ) {
        return _crm_error("params is not an array");
    }
    
    if ( empty( $params ) ) {
        return _crm_error("params is an empty array");
    }
    
    if ( ! isset( $params['uf_group_id'] ) ) {
        return _crm_error("uf_group_id is required field");
    }
    
    return CRM_Core_BAO_UFJoin::create($params);
}

/**
 * takes an associative array and updates a uf join object
 *
 * @param array $params assoc array of name/value pairs
 *
 * @return object  updated CRM_Core_DAO_UFJoin object 
 * @access public
 * 
 */
function crm_edit_uf_join(&$ufJoin, &$params) 
{
    if ( ! is_array( $params ) ) {
        return _crm_error("params is not an array");
    }
    
    if ( empty( $params ) ) {
        return _crm_error("params is an empty array");
    }
    
    if ( ! is_a($ufJoin, 'CRM_Core_DAO_UFJoin') ) {
        return _crm_error('$ufJoin is not a valid object');
    }
    
    $error = _crm_update_object($ufJoin, $params);
    
    if( is_a( $error, 'CRM_Core_Error' ) ) {
        return $error;
    }
    
    return $ufJoin;
}

/**
 * Given an assoc list of params, finds if there is a record
 * for this set of params
 *
 * @param array $params (reference) an assoc array of name/value pairs 
 * 
 * @return int or null
 * @access public
 * 
 */

function crm_find_uf_join_id(&$params) 
{
    if ( ! is_array($params) || empty($params)) {
        return _crm_error("$params is not valid array");
    }
    
    if ( ! isset( $params['id'] ) && 
         ( ! isset( $params['entity_table'] ) && 
           ! isset( $params['entity_id']    ) && 
           ! isset( $params['weight']       ) 
           ) ) {
        return _crm_error("$param should have atleast entity_table or entiy_id or weight");
    }
    
    return CRM_Core_BAO_UFJoin::findJoinEntryId($params);
}

/**
 * Given an assoc list of params, find if there is a record
 * for this set of params and return the group id
 *
 * @param array $params (reference) an assoc array of name/value pairs 
 * 
 * @return int or null
 * @access public
 * 
 */
function crm_find_uf_join_UFGroupId(&$params) 
{
    if ( ! is_array($params) || empty($params)) {
        return _crm_error("$params is not valid array");
    }
    
    if (! isset( $params['entity_table'] ) && 
        ! isset( $params['entity_id']    ) && 
        ! isset( $params['weight']       ) 
        ) {
        return _crm_error("$param should have atleast entity_table or entiy_id or weight");
    }
    
    return CRM_Core_BAO_UFJoin::findUFGroupId($params);
}

