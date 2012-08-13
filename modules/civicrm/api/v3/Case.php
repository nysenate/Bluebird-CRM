<?php
// $Id$

/*
  +--------------------------------------------------------------------+
  | CiviCRM version 4.2                                                |
  +--------------------------------------------------------------------+
  | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * File for the CiviCRM APIv3 Case functions
 * Developed by woolman.org
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Case
 * @copyright CiviCRM LLC (c) 2004-2012
 *
 */

require_once 'CRM/Case/BAO/Case.php';
require_once 'CRM/Case/PseudoConstant.php';

/**
 * Open a new case, add client and manager roles, and add standard timeline
 *
 * @param  array(
    //REQUIRED:
 * 'case_type_id'     => int OR
 * 'case_type' => str (provide one or the other)
 * 'contact_id'       => int // case client
 * 'subject'          => str
 *
 * //OPTIONAL
 * 'medium_id'        => int // see civicrm option values for possibilities
 * 'creator_id'       => int // case manager, default to the logged in user
 * 'status_id'        => int // defaults to 1 "ongoing"
 * 'location'         => str
 * 'start_date'       => str datestamp // defaults to: date('YmdHis')
 * 'duration'         => int // in minutes
 * 'details'          => str // html format
 *
 * @return sucessfully opened case
 *
 * @access public
 * {@getfields case_create}
 */
function civicrm_api3_case_create($params) {

  if (empty($params['contact_id']) && isset($params['client_id'])) {
    $params['contact_id'] = $params['client_id'];
  }

  if (isset($params['id']) || isset($params['case_id'])) {
    return _api_case_update($params);
  }

  // ongoing
  if (!CRM_Utils_Array::value('status_id', $params)) {
    $params['status_id'] = 1;
  }
  if (!array_key_exists('creator_id', $params)) {
    $session = CRM_Core_Session::singleton();
    $params['creator_id'] = $session->get('userID');
  }
  //check parameters
  $errors = _civicrm_api3_case_check_params($params, 'create');

  if ($errors) {
    return $errors;
  }

  _civicrm_api3_case_format_params($params, 'create');
  // If format_params didn't find what it was looking for, return error
  if (empty($params['case_type_id'])) {
    return civicrm_api3_create_error('Invalid case_type. No such case type exists.');
  }
  if (empty($params['case_type'])) {
    return civicrm_api3_create_error('Invalid case_type_id. No such case type exists.');
  }

  // format input with value separators
  $sep = CRM_Core_DAO::VALUE_SEPARATOR;
  $newParams = array('case_type_id' => $sep . $params['case_type_id'] . $sep, 'creator_id' => $params['creator_id'], 'status_id' => $params['status_id'], 'start_date' => $params['start_date'], 'subject' => $params['subject']);

  $caseBAO = CRM_Case_BAO_Case::create($newParams);

  if (!$caseBAO) {
    return civicrm_api3_create_error('Case not created. Please check input params.');
  }

  foreach ((array) $params['contact_id'] as $cid) {
    $contactParams = array('case_id' => $caseBAO->id, 'contact_id' => $cid);
    CRM_Case_BAO_Case::addCaseToContact($contactParams);
  }

  // Initialize XML processor with $params
  require_once 'CRM/Case/XMLProcessor/Process.php';
  $xmlProcessor = new CRM_Case_XMLProcessor_Process();
  $xmlProcessorParams = array('clientID' => $params['contact_id'], 'creatorID' => $params['creator_id'], 'standardTimeline' => 1, 'activityTypeName' => 'Open Case', 'caseID' => $caseBAO->id, 'subject' => $params['subject'], 'location' => CRM_Utils_Array::value('location', $params), 'activity_date_time' => $params['start_date'], 'duration' => CRM_Utils_Array::value('duration', $params), 'medium_id' => CRM_Utils_Array::value('medium_id', $params), 'details' => CRM_Utils_Array::value('details', $params), 'custom' => array());

  // Do it! :-D
  $xmlProcessor->run($params['case_type'], $xmlProcessorParams);

  // return case
  $values = array();
  _civicrm_api3_object_to_array($caseBAO, $values[$caseBAO->id]);

  return civicrm_api3_create_success($values, $params, 'case', 'create', $caseBAO);
}

/**
 * Get details of a particular case, or search for cases, depending on params
 *
 * Please provide one (and only one) of the four get/search parameters:
 *
 * @param array(
    'case_id'    => if set, will get all available info about a case, including contacts and activities
 *
 * // if no case_id provided, this function will use one of the following search parameters:
 * 'client_id'   => finds all cases with a specific client
 * 'activity_id' => returns the case containing a specific activity
 * 'contact_id'  => finds all cases associated with a contact (in any role, not just client)
 *
 * {@getfields case_get}
 *
 * @return (get mode, case_id provided): Array with case details, case roles, case activity ids, (search mode, case_id not provided): Array of cases found
 * @access public
 * @todo Eileen McNaughton 13 Oct 2011 No unit test
 * @todo Erik Hommel 16 dec 2010 check if all DB fields are returned
 */
function civicrm_api3_case_get($params) {

  // Get mode
  if (!($caseId = CRM_Utils_Array::value('id', $params))) {
     $caseId = CRM_Utils_Array::value('case_id', $params);
  }

  if ($caseId) {
    // Validate param
    if (!is_numeric($caseId)) {
      return civicrm_api3_create_error('Invalid parameter: case_id. Must provide a numeric value.');
    }

    $case = _civicrm_api3_case_read($caseId);

    if ($case) {
      //get case contacts
      $contacts         = CRM_Case_BAO_Case::getcontactNames($caseId);
      $relations        = CRM_Case_BAO_Case::getRelatedContacts($caseId);
      $case['contacts'] = array_merge($contacts, $relations);

      //get case activities

      $query = "SELECT activity_id FROM civicrm_case_activity WHERE case_id = $caseId";
      $dao = CRM_Core_DAO::executeQuery($query);

      $case['activities'] = array();

      while ($dao->fetch()) {
        $case['activities'][] = $dao->activity_id;
      }

      $cases = array($caseId => $case);
      return civicrm_api3_create_success($cases);
    }
    else {
      return civicrm_api3_create_success(array());
    }
  }

  //search by client
  if ($client = CRM_Utils_Array::value('client_id', $params)) {

    $ids = array();
    foreach ((array) $client as $cid) {
      if (is_numeric($cid)) {
        $ids = array_merge($ids, CRM_Case_BAO_Case::retrieveCaseIdsByContactId($cid, TRUE));
    }
    }

    if (empty($ids)) {
      return civicrm_api3_create_success(array());
    }

    $cases = array();

    foreach ($ids as $id) {
      $cases[$id] = _civicrm_api3_case_read($id);
    }
    return civicrm_api3_create_success($cases);
  }

  //search by activity
  if ($act = CRM_Utils_Array::value('activity_id', $params)) {

    if (!is_numeric($act)) {
      return civicrm_api3_create_error('Invalid parameter: activity_id. Must provide a numeric value.');
    }

    $caseId = CRM_Case_BAO_Case::getCaseIdByActivityId($act);

    if (!$caseId) {
      return civicrm_api3_create_success(array());
    }

    $case = array($caseId => _civicrm_api3_case_read($caseId));

    return civicrm_api3_create_success($case);
  }

  //search by contacts
  if ($contact = CRM_Utils_Array::value('contact_id', $params)) {
    if (!is_numeric($contact)) {
      return civicrm_api3_create_error('Invalid parameter: contact_id.  Must provide a numeric value.');
    }

    $sql = "
SELECT DISTINCT case_id
  FROM civicrm_relationship
 WHERE (contact_id_a = $contact
    OR contact_id_b = $contact)
   AND case_id IS NOT NULL";
    $dao = &CRM_Core_DAO::executeQuery($sql);

    $cases = array();

    while ($dao->fetch()) {
      $cases[$dao->case_id] = _civicrm_api3_case_read($dao->case_id);
    }

    return civicrm_api3_create_success($cases);
  }

  return civicrm_api3_create_error('Missing required parameter. Must provide case_id, client_id, activity_id, or contact_id.');
}

/**
 * Deprecated. Use activity API instead
 */
function civicrm_api3_case_activity_create($params) {
  require_once 'api/v3/Activity.php';
  return civicrm_api3_activity_create($params);
}

/**
 * Update a specified case.
 *
 * @param  array(
    //REQUIRED:
 * 'case_id'          => int
 *
 * //OPTIONAL
 * 'status_id'        => int
 * 'start_date'       => str datestamp
 * 'contact_id'       => int // case client
 *
 * @return Updated case
 *
 * @access public
 *
 */
function _api_case_update($params) {

  if (empty($params['case_id'])) {
    $params['case_id'] = CRM_Utils_Array::value('id', $params);
  }

  civicrm_api3_verify_mandatory($params);
  $errors = array();
  //check for various error and required conditions
  $errors = _civicrm_api3_case_check_params($params, 'update');

  if (!empty($errors)) {
    return $errors;
  }

  // return error if modifing creator id
  if (array_key_exists('creator_id', $params)) {
    return civicrm_api3_create_error(ts('You cannot update creator id'));
  }

  $mCaseId = array();
  $origContactIds = array();

  // get original contact id and creator id of case
  if ($params['contact_id']) {
    $origContactIds = CRM_Case_BAO_Case::retrieveContactIdsByCaseId($params['case_id']);
    $origContactId = $origContactIds[1];
  }

  if (count($origContactIds) > 1) {
    // check valid orig contact id
    if ($params['orig_contact_id'] && !in_array($params['orig_contact_id'], $origContactIds)) {
      return civicrm_api3_create_error('Invalid case contact id (orig_contact_id)');
    }
    elseif (!$params['orig_contact_id']) {
      return civicrm_api3_create_error('Case is linked with more than one contact id. Provide the required params orig_contact_id to be replaced');
    }
    $origContactId = $params['orig_contact_id'];
  }

  // check for same contact id for edit Client
  if ($params['contact_id'] && !in_array($params['contact_id'], $origContactIds)) {
    $mCaseId = CRM_Case_BAO_Case::mergeCases($params['contact_id'], $params['case_id'], $origContactId, NULL, TRUE);
  }

  if (CRM_Utils_Array::value('0', $mCaseId)) {
    $params['case_id'] = $mCaseId[0];
  }

  $dao = new CRM_Case_BAO_Case();
  $dao->id = $params['case_id'];

  $dao->copyValues($params);
  $dao->save();

  $case = array();

  _civicrm_api3_object_to_array($dao, $case);

  return civicrm_api3_create_success($case);
}

/**
 * Delete a specified case.
 *
 * @param  array(
    //REQUIRED:
 * 'case_id'           => int
 *
 * //OPTIONAL
 * 'move_to_trash'     => bool (defaults to false)
 *
 * @return boolean: true if success, else false
 * {@getfields case_delete}
 * @access public
 * @todo Eileen McNaughton 13 Oct 2011 No unit test
 * @todo Erik Hommel 16 dec 2010 use utils function civicrm_verify_mandatory to check for required params
 */
function civicrm_api3_case_delete($params) {

  //check parameters
  $errors = _civicrm_api3_case_check_params($params, 'delete');

  if ($errors) {
    return $errors;
  }

  if (CRM_Case_BAO_Case::deleteCase($params['case_id'], $params['move_to_trash'])) {
    return civicrm_api3_create_success('Case Deleted');
  }
  else {
    return civicrm_api3_create_error('Could not delete case.');
  }
}

/***********************************/
/*                                 */


/*     INTERNAL FUNCTIONS          */


/*                                 */

/***********************************/

/**
 * Internal function to retrieve a case.
 *
 * @param int $caseId
 *
 * @return array (reference) case object
 *
 */
function _civicrm_api3_case_read($caseId) {

  $dao = new CRM_Case_BAO_Case();
  $dao->id = $caseId;
  if ($dao->find(TRUE)) {
    $case = array();
    _civicrm_api3_object_to_array($dao, $case);
    $case['client_id'] = $dao->retrieveContactIdsByCaseId($caseId);

    //handle multi-value case type
    $sep = CRM_Core_DAO::VALUE_SEPARATOR;
    $case['case_type_id'] = trim(str_replace($sep, ',', $case['case_type_id']), ',');

    return $case;
  }
  else {
    return FALSE;
  }
}

/**
 * Internal function to format params for processing
 */
function _civicrm_api3_case_format_params(&$params, $mode) {
  switch ($mode) {
    case 'create':
      if (empty($params['start_date'])) {
        $params['start_date'] = date('YmdHis');
      }
      if (empty($params['contact_id']) && isset($params['client_id'])) {
        $params['contact_id'] = $params['client_id'];
      }
      // figure out case type id, if not supplied
      if (!CRM_Utils_Array::value('case_type_id', $params)) {
        $sql = "
SELECT  ov.value
  FROM  civicrm_option_value ov
  JOIN  civicrm_option_group og ON og.id = ov.option_group_id
 WHERE  ov.label = %1 AND og.name = 'case_type'";

        $values = array(1 => array($params['case_type'], 'String'));
        $params['case_type_id'] = CRM_Core_DAO::singleValueQuery($sql, $values);
      }
      elseif (!CRM_Utils_Array::value('case_type', $params)) {
        // figure out case type, if not supplied
        $sql = "
SELECT  ov.name
  FROM  civicrm_option_value ov
  JOIN  civicrm_option_group og ON og.id = ov.option_group_id
 WHERE  ov.value = %1 AND og.name = 'case_type'";

        $values = array(1 => array($params['case_type_id'], 'Integer'));
        $params['case_type'] = CRM_Core_DAO::singleValueQuery($sql, $values);
      }
      break;

    case 'activity':
      //set defaults
      if (!$params['activity_date_time']) {
        $params['activity_date_time'] = date('YmdHis');
      }
      break;
  }
}

/**
 * Internal function to check for valid parameters
 */
function _civicrm_api3_case_check_params($params, $mode = NULL) {

  civicrm_api3_verify_mandatory($params);
  switch ($mode) {
    case 'create':

      if (!$params['case_type_id'] && !$params['case_type']) {

        return civicrm_api3_create_error('Missing input parameters. Must provide case_type or case_type_id.');
      }

    $required = array('contact_id' => '', 'subject' => 'str');

      if (!CRM_Utils_Array::value('case_type', $params)) {

        $required['case_type_id'] = 'num';
      }
      if (!CRM_Utils_Array::value('case_type_id', $params)) {
        $required['case_type'] = 'str';
      }
      break;

    case 'update':
    case 'delete':
      $required = array('case_id' => 'num');
      break;

    default:
      return NULL;
  }

  foreach ($required as $req => $type) {

    if (!$params[$req]) {

      return civicrm_api3_create_error('Missing required parameter: %1.', array(1 => $req));
    }

    if ($type == 'num' && !is_numeric($params[$req])) {

      return civicrm_api3_create_error('Invalid parameter: %1. Must provide a numeric value.', array(1 => $req));
    }

    if ($type == 'str' && !is_string($params[$req])) {

      return civicrm_api3_create_error('Invalid parameter: %1. Must provide a string.', array(1 => $req));
    }
  }

  $caseTypes = CRM_Case_PseudoConstant::caseType();
  if (CRM_Utils_Array::value('case_type', $params) && !in_array($params['case_type'], $caseTypes)) {
    return civicrm_api3_create_error('Invalid Case Type');
  }

  if (CRM_Utils_Array::value('case_type_id', $params)) {
    if (!array_key_exists($params['case_type_id'], $caseTypes)) {
      return civicrm_api3_create_error('Invalid Case Type Id');
    }

    // check case type miss match error
    if (CRM_Utils_Array::value('case_type', $params) && $params['case_type_id'] != array_search($params['case_type'], $caseTypes)) {
      return civicrm_api3_create_error('Case type and case type id mismatch');
    }

    $sep = CRM_Core_DAO::VALUE_SEPARATOR;
    $params['case_type'] = $caseTypes[$params['case_type_id']];
    $params['case_type_id'] = $sep . $params['case_type_id'] . $sep;
  }

  // check for valid status id
  $caseStatusIds = CRM_Case_PseudoConstant::caseStatus();
  if (CRM_Utils_Array::value('status_id', $params) && !array_key_exists($params['status_id'], $caseStatusIds)) {
    return civicrm_api3_create_error('Invalid Case Status Id');
  }

  // check for valid medium id
  $encounterMedium = CRM_Core_OptionGroup::values('encounter_medium');
  if (CRM_Utils_Array::value('medium_id', $params) && !array_key_exists($params['medium_id'], $encounterMedium)) {
    return civicrm_api3_create_error('Invalid Case Medium Id');
  }

  $contactIds = array('creator' => CRM_Utils_Array::value('creator_id', $params), 'contact' => CRM_Utils_Array::value('contact_id', $params));
  foreach ($contactIds as $key => $value) {
    if ($value && !CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $value, 'id')) {
      return civicrm_api3_create_error('Invalid %1 Id', array(1 => ucfirst($key)));
    }
  }
}

