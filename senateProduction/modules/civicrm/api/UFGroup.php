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

require_once 'CRM/Core/BAO/UFGroup.php';

/**
 * Most API functions take in associative arrays ( name => value pairs
 * as parameters. Some of the most commonly used parameters are
 * described below
 *
 * @param array $params           an associative array used in construction
 *                                / retrieval of the object
 * @param array $returnProperties the limited set of object properties that
 *                                need to be returned to the caller
 *
 */



/**                
 * Get all the user framework groups 
 * 
 * @access public                                         
 * @return array - array reference of all groups. 
 * @static 
 */ 
function &crm_uf_get_profile_groups( ) {
    return CRM_Core_PseudoConstant::ufGroup( );
}

/** 
 * Get the form title. 
 * 
 * @param int $id id of uf_form 
 * @return string title 
 * 
 * @access public 
 * @static 
 * 
 */ 
function crm_uf_get_profile_title ( $id ) {
    return CRM_Core_BAO_UFGroup::getTitle( $id );
}

/** 
 * get all the fields that belong to the group with the named title 
 * 
 * @param int $id       the id of the UF group 
 * @param int $register are we interested in registration fields 
 * @param int $action   what action are we doing 
 * @param string $visibility visibility of fields we are interested in 
 * 
 * @return array the fields that belong to this title 
 * @static 
 * @access public 
 */ 
function crm_uf_get_profile_fields ( $id, $register = false, $action = null, $visibility = null ) {
    return CRM_Core_BAO_UFGroup::getFields( $id, $register, $action, null, $visibility );
}

/** 
 * get the html for the form that represents this particular group 
 * 
 * @param int     $userID   the user id that we are actually editing 
 * @param string  $title    the title of the group we are interested in 
 * @param int     $action   the action of the form 
 * @param boolean $register is this the registration form 
 * @param boolean $reset    should we reset the form? 
 * 
 * @return string       the html for the form 
 * @static 
 * @access public 
 */ 
function crm_uf_get_profile_html  ( $userID, $title, $action = null, $register = false, $reset = false ) {
    return CRM_Core_BAO_UFGroup::getEditHTML( $userID, $title, $action, $register, $reset );
}

/** 
 * get the html for the form that represents this particular group 
 * 
 * @param int     $userID    the user id that we are actually editing 
 * @param int     $profileID the id of the group we are interested in 
 * @param int     $action    the action of the form 
 * @param boolean $register  is this the registration form 
 * @param boolean $reset     should we reset the form? 
 * 
 * @return string            the html for the form 
 * @static 
 * @access public 
 */ 
function crm_uf_get_profile_html_by_id  ( $userID,
                                          $profileID,
                                          $action = null,
                                          $register = false,
                                          $reset = false ) {
    return CRM_Core_BAO_UFGroup::getEditHTML( $userID, null, $action, $register, $reset, $profileID );
}

 
/**  
 * get the html for the form for profile creation
 *  
 * @param boolean $reset    should we reset the form?  
 *  
 * @return string       the html for the form  
 * @static  
 * @access public  
 */  
function crm_uf_get_create_html  ( $reset = false ) {
    $session =& CRM_Core_Session::singleton( ); 
    $controller =& new CRM_Core_Controller_Simple( 'CRM_Profile_Form_Edit', '', CRM_Core_Action::ADD ); 
    if ( $reset ) { 
        unset( $_POST['_qf_default'] ); 
        unset( $_REQUEST['_qf_default'] );
    }

    $controller->process( ); 
    $controller->setEmbedded( true ); 
    $controller->run( ); 
 
    $template =& CRM_Core_Smarty::singleton( ); 
    return trim( $template->fetch( 'CRM/Profile/Form/Dynamic.tpl' ) );
} 

/** 
 * get the contact_id given a uf_id 
 * 
 * @param int $ufID
 * 
 * @return int contact_id 
 * @access public    
 * @static 
 */ 
function crm_uf_get_match_id ( $ufID ) {
    require_once 'CRM/Core/BAO/UFMatch.php';
    return CRM_Core_BAO_UFMatch::getContactId( $ufID );
}

/**  
 * get the uf_id given a contact_id  
 *  
 * @param int $contactID
 *  
 * @return int ufID
 * @access public     
 * @static  
 */  
function crm_uf_get_uf_id ( $contactID ) { 
    require_once 'CRM/Core/BAO/UFMatch.php'; 
    return CRM_Core_BAO_UFMatch::getUFId( $contactID ); 
} 

/*******************************************************************/


/**
 * Use this API to create a new group. See the CRM Data Model for uf_group property definitions
 *
 * @param $params  array   Associative array of property name/value pairs to insert in group.
 *
 * @return   Newly create uf_group object
 *
 * @access public 
 */
function crm_create_uf_group( $params ) {
    _crm_initialize( );
    
    if(! is_array($params) || ! isset($params['title']) ) {
        return _crm_error("params is not an array or may be empty array ");
    }
    
    $ids = array();
    require_once 'CRM/Core/BAO/UFGroup.php';
    
    return CRM_Core_BAO_UFGroup::add( $params,$ids );
  
}

/**
 * Use this API to update  group. See the CRM Data Model for uf_group property definitions
 *
 * @param $params  array   Associative array of property name/value pairs to insert in group.
 *  
 * @param $ufGroup Object  A valid UF Group object that to be updated.   
 *  
 * @return  updated  uf_group object
 *
 * @access public 
 */
function crm_update_uf_group( $params ,$ufGroup) {
    
    _crm_initialize( );
    
    $groupId = $ufGroup->id;
    
    if(! is_array( $params ) ) {
        return _crm_error("params is not an array ");
    }
    
    if(! isset( $groupId ) ) {
        return _crm_error("parameter $groupId  is not set ");
    }
    $ids = array();
    $ids['ufgroup'] = $groupId;
    require_once 'CRM/Core/BAO/UFGroup.php';
    
    return CRM_Core_BAO_UFGroup::add( $params ,$ids );
    
    
}
/**
 * Defines 'uf field' within a group.
 *
 * @param $UFGroup object Valid uf_group object
 *
 * @param $params       array  Associative array of property name/value pairs to create new uf field.
 *
 * @return Newly created custom_field object
 *
 * @access public 
 *
 */
function crm_create_uf_field( $UFGroup , $params ) {
    
    _crm_initialize( );
    
    if(! isset($UFGroup->id) ) {
        return _crm_error("id is not set in uf_group object");
    }
    
    $field_type       = $params['field_type'];
    $field_name       = $params['field_name'];
    $location_type_id = $params['location_type_id'];
    $phone_type       = $params['phone_type'];
    
    $params['field_name'] =  array( $field_type, $field_name, $location_type_id, $phone_type);
   
    if(! is_array( $params ) || $params['field_name'][1] == null || $params['weight'] == null ) {
        return _crm_error("missing required fields ");
    }
   
    if ( !( CRM_Utils_Array::value('group_id', $params) ) ) {
        $params['group_id'] =  $UFGroup->id;
    }
    
    $ids = array();
    $ids['uf_group'] = $UFGroup->id;
    
    require_once 'CRM/Core/BAO/UFField.php';
    if (CRM_Core_BAO_UFField::duplicateField($params, $ids) ) {
        return _crm_error("The field was not added. It already exists in this profile.");
    }
    return CRM_Core_BAO_UFField::add( $params , $ids );
} 

/**
 * Use this API to update uf field . See the CRM Data Model for uf_field property definitions
 *
 * @param $params  array   Associative array of property name/value pairs to update in field.
 *  
 * @param $ufField Object  A valid uf field object that to be updated.
 *  
 * @return  updated  uf_field object
 *
 * @access public 
 */
function crm_update_uf_field( $params , $ufField) {
    
    _crm_initialize( );
    
    $fieldId = $ufField->id;
    
    if(! isset( $fieldId ) ) {
        return _crm_error("parameter fieldId is not set");
    }
    
    if(! is_array( $params ) ) {
        return _crm_error("params is not an array ");
    }   
    
    $field_type       = $params['field_type'];
    $field_name       = $params['field_name'];
    $location_type_id = $params['location_type_id'];
    $phone_type       = $params['phone_type'];
    
    $params['field_name'] =  array( $field_type, $field_name, $location_type_id, $phone_type);
    
    require_once 'CRM/Core/BAO/UFField.php';
    $UFField = &new CRM_core_BAO_UFField();
    $UFField->id = $fieldId;
    
    if ( !( CRM_Utils_Array::value('group_id', $params) ) && $UFField->find(true) ) {
        $params['group_id'] =  $UFField->uf_group_id;
    }

    $ids = array();

    if ( $UFField->find(true) ) { 
        $ids['uf_group'] =  $UFField->uf_group_id;
    } else {
        return _crm_error("there is no field for this fieldId");
    }
    $ids['uf_field'] = $fieldId;
    
    if (CRM_Core_BAO_UFField::duplicateField($params, $ids) ) {
        return _crm_error("The field was not added. It already exists in this profile.");
    }
    
    return CRM_Core_BAO_UFField::add( $params , $ids );
    
}


/**
 * Delete uf group
 *  
 * @param $ufGroup Object  Valid uf_group object that to be deleted
 *
 * @return true on successful delete or return error
 *
 * @access public
 *
 */
function crm_delete_uf_group( $ufGroup ) {
    _crm_initialize( );
    
    $groupId = $ufGroup->id;
    
    if(! isset( $groupId ) ) {
        return _crm_error("parameter $groupId  is not set ");
    }
    
    require_once 'CRM/Core/BAO/UFGroup.php';
    return CRM_Core_BAO_UFGroup::del($groupId);

}

/**
 * Delete uf field
 *  
 * @param $ufField Object  Valid uf_field object that to be deleted
 *
 * @return true on successful delete or return error
 *
 * @access public
 *
 */
function crm_delete_uf_field( $ufField ) {
    _crm_initialize( );
    
    $fieldId = $ufField->id;
    
    if(! isset( $fieldId ) ) {
        return _crm_error("parameter $fieldId  is not set ");
    }
    
    require_once 'CRM/Core/BAO/UFField.php';
    return CRM_Core_BAO_UFField::del($fieldId);
    
}

/**
 * check the data validity
 *
 * @param int    $userID    the user id 
 * @param string $title     the title of the group we are interested in
 * @param  boolean $register is this the registrtion form
 * @param int    $action  the action of the form
 *
 * @return error   if data not valid
 * 
 * @access public
 */
function crm_validate_profile_html($userID, $title, $action = null, $register = false) {
    return CRM_Core_BAO_UFGroup::isValid( $userID, $title, $register, $action );
}


