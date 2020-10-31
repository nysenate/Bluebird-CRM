<?php

/**
 * @file
 * Case.getrelations file.
 */

require_once 'api/v3/Contact.php';

/**
 * Case.Getrelations API specification.
 *
 * @param array $spec
 *   description of fields supported by this API call.
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_case_getrelations_spec(array &$spec) {
  _civicrm_api3_contact_get_spec($spec);
  $spec['case_id'] = array(
    'title' => 'Case ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => TRUE,
  );
}

/**
 * Case.Getrelations API.
 *
 * Perform a search for contacts related to clients of a case.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result.
 *
 * @throws API_Exception
 */
function civicrm_api3_case_getrelations(array $params) {
  $relations = array();
  $params += array('options' => array());
  $caseContacts = civicrm_api3('CaseContact', 'get', array(
    'case_id' => $params['case_id'],
    'contact_id.is_deleted' => 0,
    'return' => 'contact_id',
    'options' => array('limit' => 0),
  ));
  $clientIds = CRM_Utils_Array::collect('contact_id', $caseContacts['values']);
  $relationshipParams = array(
    'is_active' => 1,
    'relationship_type_id.is_active' => 1,
    'case_id' => array('IS NULL' => 1),
    "contact_id_a" => array('IN' => $clientIds),
    "contact_id_b" => array('IN' => $clientIds),
    'options' => array('or' => array(array("contact_id_a", "contact_id_b"))) + $params['options'],
    'return' => array(
      'relationship_type_id',
      'contact_id_a',
      'contact_id_b',
      'description',
    ),
  );
  $result = civicrm_api3('Relationship', 'get', $relationshipParams);

  foreach ($result['values'] as $relation) {
    $a = in_array($relation['contact_id_a'], $clientIds) ? 'b' : 'a';
    $b = in_array($relation['contact_id_a'], $clientIds) ? 'a' : 'b';
    $contactIds[$relation["contact_id_$a"]] = $relation["contact_id_$a"];
    $relations[] = array(
      'id' => $relation["contact_id_$a"],
      'client_contact_id' => $relation["contact_id_$b"],
      'relationship_id' => $relation['id'],
      'relationship_type_id' => $relation['relationship_type_id'],
      'relationship_description' => $relation['description'],
      'relationship_direction' => "{$a}_{$b}",
    );
  }

  if (!$relations) {
    return $result;
  }

  unset($params['case_id'], $params['options']);
  $contacts = civicrm_api3('Contact', 'get', array('sequential' => 0, 'id' => array('IN' => $contactIds)) + $params);

  foreach ($relations as &$relation) {
    if (isset($contacts['values'][$relation['id']])) {
      $relation += $contacts['values'][$relation['id']];
    }
    else {
      $relation = NULL;
      --$result['count'];
    }
  }

  $out = civicrm_api3_create_success(array_filter($relations), $params, 'Case', 'getrelations');
  $out['count'] = $result['count'];

  return $out;
}
