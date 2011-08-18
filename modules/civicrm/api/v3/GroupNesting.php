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
 * File for the CiviCRM APIv3 group nesting functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Group
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: GroupNesting.php 21624 2009-08-07 22:02:55Z wmorgan $
 *
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Contact/DAO/GroupNesting.php';
 
/**
 * Provides group nesting record(s) given parent and/or child id.
 *
 * @param array $params  an array containing at least child_group_id or parent_group_id
 *
 * @return  array  list of group nesting records
 */
function civicrm_api3_group_nesting_get( $params )
{

    civicrm_api3_verify_mandatory($params);

    civicrm_api3_verify_mandatory($params);
    return _civicrm_api3_basic_get('CRM_Contact_DAO_GroupNesting', $params);

    return civicrm_api3_create_success($values,$params);

}

/**
 * Creates group nesting record for given parent and child id.
 * Parent and child groups need to exist.
 *
 * @param array $params parameters array - allowed array keys include:
 * {@schema Contact/GroupNesting.xml}
 *
 * @return array TBD
 *
 * @todo Work out the return value.
 */
function civicrm_api3_group_nesting_create( $params )
{
    civicrm_api3_verify_mandatory($params);

  require_once 'CRM/Contact/BAO/GroupNesting.php';

  if ( ! array_key_exists( 'child_group_id', $params ) &&
  ! array_key_exists( 'parent_group_id', $params ) ) {
    return civicrm_api3_create_error(  'You need to define parent_group_id and child_group_id in params.'  );
  }

  CRM_Contact_BAO_GroupNesting::add( $params['parent_group_id'], $params['child_group_id'] );

  // FIXME: CRM_Contact_BAO_GroupNesting requires some work
  $result = array( 'is_error' => 0 );
  return civicrm_api3_create_success($result,$params);

}

/**
 * Removes specific nesting records.
 *
 * @param array $params parameters array - allowed array keys include:
 * {@schema Contact/GroupNesting.xml}
 *
 * @return array TBD
 *
 * @todo Work out the return value.
 */
function civicrm_api3_group_nesting_delete( $params )
{

    civicrm_api3_verify_mandatory($params);
  

  if ( ! array_key_exists( 'child_group_id', $params ) ||
  ! array_key_exists( 'parent_group_id', $params ) ) {
    return civicrm_api3_create_error('You need to define parent_group_id and child_group_id in params.'  );
  }

  require_once 'CRM/Contact/DAO/GroupNesting.php';
  $dao = new CRM_Contact_DAO_GroupNesting();
  $dao->copyValues( $params );

  if( $dao->delete( ) ) {
    $result = array( 'is_error' => 0 );
  }
  return $result;

}