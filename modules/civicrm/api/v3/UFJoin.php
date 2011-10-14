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
 * File for the CiviCRM APIv3 user framework join functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_UF
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: UFJoin.php 30171 2010-10-14 09:11:27Z mover $
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Core/BAO/UFJoin.php';

/**
 * takes an associative array and creates a uf join in the database
 *
 * @param array $params assoc array of name/value pairs
 *
 * @return array CRM_Core_DAO_UFJoin Array
 * @access public
 * @example UFJoinCreate.php
 *  {@schema Core/UFJoin.xml}
 *
 */
function civicrm_api3_uf_join_create($params)
{

    civicrm_api3_verify_mandatory($params,'CRM_Core_DAO_UFJoin',array());

    $ufJoin = CRM_Core_BAO_UFJoin::create($params);
    _civicrm_api3_object_to_array( $ufJoin, $ufJoinArray[]);
    return civicrm_api3_create_success($ufJoinArray,$params,'uf_join','create');

}


/**
 * Get CiviCRM UF_Joins (ie joins between CMS user records & CiviCRM user record
 *
 * @param array $params (reference) an assoc array of name/value pairs
 *
 * @return array $result CiviCRM Result Array or null
 * @todo Delete function missing
 * @access public
 *
 */

function civicrm_api3_uf_join_get($params)
{ 

    civicrm_api3_verify_one_mandatory($params);
	  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);


}

