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
 * File for the CiviCRM APIv3 tag functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Tag
 * 
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Tag.php 30486 2010-11-02 16:12:09Z shot $
 */

/**
 * Include utility functions
 */
    require_once 'CRM/Core/BAO/Tag.php';

/**
 *  Add a Tag. Tags are used to classify CRM entities (including Contacts, Groups and Actions).
 *
 * Allowed @params array keys are:
 * {@schema Core/Tag.xml}
 * {@example TagCreate.php}
 * @return array of newly created tag property values.
 * @access public
 */
function civicrm_api3_tag_create( $params ) 
{

    civicrm_api3_verify_mandatory ($params,null,array ('name'));
    require_once 'CRM/Core/BAO/Tag.php';
    $ids = array( 'tag' => CRM_Utils_Array::value( 'tag', $params ) );
    if ( CRM_Utils_Array::value( 'tag', $params ) ) {
        $ids['tag'] = $params['tag'];
    }
    if ( CRM_Utils_Array::value( 'id', $params ) ) {
        $ids['tag'] = $params['id'];
    }
    $tagBAO = CRM_Core_BAO_Tag::add($params, $ids);

    if ( is_a( $tagBAO, 'CRM_Core_Error' ) ) {
        return civicrm_api3_create_error( "Tag is not created" );
    } else {
        $values = array( );
        _civicrm_api3_object_to_array($tagBAO, $values[$tagBAO->id]);
        return civicrm_api3_create_success($values,$params,'tag','create',$tagBAO);
    }

}
/*
 * returns defaults for create function
 */
function _civicrm_api3_tag_create_defaults(){
  return array('used_for' =>   "civicrm_contact");
}
/**
 * Deletes an existing Tag
 *
 * @param  array  $params
 * 
 * {@example TagDelete.php 0}
 * @return boolean | error  true if successfull, error otherwise
 * @access public
 */
function civicrm_api3_tag_delete( $params ) 
{

    civicrm_api3_verify_mandatory ($params,null,array ('tag_id'));
    $tagID = CRM_Utils_Array::value( 'tag_id', $params );


    return CRM_Core_BAO_Tag::del( $tagID ) ? civicrm_api3_create_success(1,$params,'tag','delete' ) : civicrm_api3_create_error(  ts( 'Could not delete tag' )  );

}

/**
 * Get a Tag.
 * 
 * This api is used for finding an existing tag.
 * Either id or name of tag are required parameters for this api.
 * 
 * {@example TagGet.php 0}
 * @param  array $params  an associative array of name/value pairs.
 *
 * @return  array details of found tags else error
 * @access public
 */

function civicrm_api3_tag_get($params) 
{   

    civicrm_api3_verify_mandatory($params);
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);

}
