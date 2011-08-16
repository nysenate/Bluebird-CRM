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
 * File for the CiviCRM APIv3 note functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Note
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Note.php 30879 2010-11-22 15:45:55Z shot $
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Core/BAO/Note.php';

/**
 * Create Note
 *
 * This API is used for creating a note.
 * Required parameters : entity_id AND note
 *
 * @param   array  $params  an associative array of name/value property values of civicrm_note
 *
 * @return array note id if note is created otherwise is_error = 1
 * @access public
 * @example NoteCreate.php
 * {@example NoteCreate.php
 */
function civicrm_api3_note_create($params) {

		if (! isset ( $params ['entity_table'] )) {
			$params ['entity_table'] = "civicrm_contact";
		}
		
		civicrm_api3_verify_mandatory ( $params, null, array ('note','entity_id', ) );
		
		$contactID = CRM_Utils_Array::value ( 'contact_id', $params );
		
		if (! isset ( $params ['modified_date'] )) {
			$params ['modified_date'] = date ( "Ymd" );
		}
		
		$ids = array ();
		$ids = array ('id' => CRM_Utils_Array::value ( 'id', $params ) );
		$noteBAO = CRM_Core_BAO_Note::add ( $params, $ids );
		
		if (is_a ( $noteBAO, 'CRM_Core_Error' )) {
			$error = civicrm_api3_create_error ( "Note could not be created" );
			return $error;
		} else {
			$note = array ();
			_civicrm_api3_object_to_array ( $noteBAO, $note [$noteBAO->id] );
		
		}
		$result = civicrm_api3_create_success ( $note, $params );
		return civicrm_api3_create_success ( $note, $params );

}

/**
 * Deletes an existing note
 *
 * This API is used for deleting a note
 *
 * @param  Int  $noteID   Id of the note to be deleted
 *
 * @return null
 * @access public
 */
function civicrm_api3_note_delete($params) {

		civicrm_api3_verify_mandatory ( $params, null, array ('id' ) );
		
		$result = new CRM_Core_BAO_Note ();
		return $result->del ( $params ['id'] ) ? civicrm_api3_create_success () : civicrm_api3_create_error ( 'Error while deleting Note' );

}

/**
 * Retrieve a specific note, given a set of input params
 *
 * @param  array   $params (reference ) input parameters
 *
 * @return array (reference ) array of properties,
 * if error an array with an error id and error message
 *
 * @static void
 * @access public
 */

function civicrm_api3_note_get($params) {

		
		if (empty ( $params ['entity_table'] )) {
			$params ['entity_table'] = "civicrm_contact";
		}
		
		civicrm_api3_verify_mandatory ( $params );
    return _civicrm_api3_basic_get('CRM_Core_BAO_Note', $params);		
	
}

/**
 * Get all descendents of given note
 * @param array $params Associative array; only required 'id' parameter is used
 * @return array Nested associative array beginning with direct children of given note.
 */
function &civicrm_api3_note_tree_get($params) {

		civicrm_api3_verify_mandatory ( $params, null, array ('id' ) );
		
		if (! is_numeric ( $params ['id'] )) {
			return civicrm_api3_create_error ( ts ( "Invalid note ID" ) );
		}
		if (! isset ( $params ['max_depth'] ))
			$params ['max_depth'] = 0;
		if (! isset ( $params ['snippet'] ))
			$params ['snippet'] = FALSE;
		$noteTree = CRM_Core_BAO_Note::getNoteTree ( $params ['id'], $params ['max_depth'], $params ['snippet'] );
		return civicrm_api3_create_success ( $noteTree, $params );

}
