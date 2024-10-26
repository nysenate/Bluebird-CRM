<?php

function _civicrm_api3_relationship_type_getcaseroles_spec(&$spec) {
  $spec = CRM_Contact_DAO_RelationshipType::fields();
}

function civicrm_api3_relationship_type_getcaseroles($params) {
  $caseRoleNames = _civicrm_api3_relationship_type_getcaseroles_getCaseRoleNames();

  $params['label_b_a'] = [ 'IN' => $caseRoleNames ];
  $params['is_active'] = 1;

  return civicrm_api3('RelationshipType', 'get', $params);
}

/**
 * Returns a list of relationship type names that have been associated to
 * case types
 *
 * @return array
 */
function _civicrm_api3_relationship_type_getcaseroles_getCaseRoleNames () {
  $rolenamesMap = [];
  $caseTypes = civicrm_api3('CaseType', 'get', [
    'sequential' => 1,
    'is_active' => 1,
    'options' => [ 'limit' => 0 ],
  ]);

  foreach ($caseTypes['values'] as $caseType) {
    foreach ($caseType['definition']['caseRoles'] as $role) {
      $rolenamesMap[$role['name']] = true;
    }
  }

  return array_keys($rolenamesMap);
}
