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

/**
 * Files required for this package
 */
require_once 'api/utils.php';

require_once 'CRM/Core/BAO/Note.php';

/**
 * Creates a new note
 *
 * This api is used to create a note record for an existing contact.
 * 'entity_table', 'entity_id', 'note' and 'contact_id' are the required parameters.
 * 
 * @param array $params  Associative array of name/value property.
 * 
 * @return Array of all Note property values.
 *
 * @access public
 */

function &crm_create_note( &$params ) {
    if ( !is_array($params) ) {
        return _crm_error( 'params is not an array' );
    }
    if ( !isset($params['entity_table']) || 
         !isset($params['entity_id'])    || 
         !isset($params['note'])         || 
         !isset($params['contact_id'] ) ) {
        return _crm_error( 'Required Parameter(s) missing.' );
    }
    $noteBAO =& new CRM_Core_BAO_Note( );
    
    if ( !isset($params['modified_date']) ) {
        $params['modified_date']  = date("Ymd");
    }
    
    $noteBAO->copyValues( $params );
    $noteBAO->save( );
    
    $note = array();
    _crm_object_to_array( $noteBAO, $note);
    return $note;
}

/**
 * Retrieves required note properties, if exists 
 *
 * This api is used to retrieve details of an existing note record.
 * Required Parameters :
 *      1. id OR
 *      2. entity_id and entity_table
 *
 * @param array $params  Associative array of name/value property
 * 
 * @return If successful, array of notes for the contact; otherwise object of CRM_Core_Error
 * @access public
 */

function &crm_get_note( &$params ) {
    if ( ! is_array($params) ) {
        return _crm_error( 'Params is not an array.' );
    }
    
    if ( ! isset($params['id']) && ( ! isset($params['entity_id']) || ! isset($params['entity_table']) ) ) {
        return _crm_error( 'Required parameters missing.' );
    }
    
    $noteBAO =& new CRM_Core_BAO_Note( );
    
    $properties = array('id', 'entity_table', 'entity_id', 'note', 'contact_id', 'modified_date', 'subject');
    
    foreach ($properties as $name) {
        if ( array_key_exists($name, $params) ) {
            $noteBAO->$name = $params[$name];
        }
    }
    
    $noteArray = array();
    
    $noteBAO->find();
    
    while ($noteBAO->fetch()) {
        $note = array();
        _crm_object_to_array( clone($noteBAO), $note);
        $noteArray[$noteBAO->id] = $note;
    }
    
    return $noteArray;
}

/**
 * Deletes a note record. 
 *
 * This api is used to delete an existing note record.
 * 
 * Required Parameters :
 *      1. id OR
 *      2. entity_id and entity_table
 * 
 * @param array $params  Associative array of property name/value pairs, sufficient to delete a note. 
 * 
 * @return number of notes deleted if successfull or CRM_Core_Error otherwise.
 * 
 * @access public
 */
function &crm_delete_note( &$params ) {
    if ( ! is_array( $params )) {
        return _crm_error( 'Params is not an array' );
    }
    
    if ( !isset($params['id']) && ( !isset($params['entity_id']) || !isset($params['entity_table']) ) ) {
        return _crm_error( 'Required parameter(s) missing' );
    }
    
    $noteBAO =& new CRM_Core_BAO_Note( );
    
    $properties = array('id', 'entity_table', 'entity_id', 'note', 'contact_id', 'modified_date', 'subject');
    
    foreach ($properties as $name) {
        if ( array_key_exists($name, $params) ) {
            $noteBAO->$name = $params[$name];
        }
    }
    
    if ( $noteBAO->find() ) {
        $notesDeleted = $noteBAO->delete();
        return $notesDeleted;
    } else {
        return _crm_error( 'Exact match not found.' );
    }
}

/**
 * Updates a note record. 
 *
 * This api is used to update an existing note record.
 * 'id' of the note-record to be updated is the required parameter.
 *
 * @param array $params  Associative array of property name/value pairs with new values to be updated with. 
 * 
 * @return Array of all Note property values (updated).
 *
 * @access public
 */
function &crm_update_note( &$params ) {
    if ( !is_array( $params ) ) {
        return _crm_error( 'Params is not an array' );
    }
    
    if ( !isset($params['id']) ) {
        return _crm_error( 'Required parameter missing' );
    }
    
    $noteBAO =& new CRM_Core_BAO_Note( );
    $noteBAO->id = $params['id'];
    if ($noteBAO->find(true)) {
        $noteBAO->copyValues( $params );
        if ( !$params['modified_date'] && !$noteBAO->modified_date) {
            $noteBAO->modified_date = date("Ymd");
        }
    }
    $noteBAO->save();
    
    $note = array();
    _crm_object_to_array( $noteBAO, $note);
    return $note;
}

