<?php

/**
 * @file
 * Case.getCaselist file.
 */

/**
 * Specification of the API action.
 *
 * @param array $spec
 *   Specifications.
 */
function _civicrm_api3_case_getcaselist_spec(array &$spec) {
  $spec = civicrm_api3('Case', 'getfields', array('api_action' => 'get'))['values'];

  $spec['case_manager'] = array(
    'title' => 'Case Manager',
    'description' => 'Contact id of the case manager',
    'type' => CRM_Utils_Type::T_INT,
  );

  $spec['contact_involved'] = array(
    'title' => 'Contact Involved',
    'description' => 'Id of the contact involved as case roles',
    'type' => CRM_Utils_Type::T_INT,
  );

  $spec['has_role'] = [
    'title' => 'Case has role',
    'description' => '{ contact, role_type, can_be_client }',
    'type' => CRM_Utils_Type::T_STRING,
  ];

  $spec['contact_is_deleted'] = array(
    'title' => 'Contact Is Deleted',
    'description' => 'Set FALSE to filter out cases for deleted contacts, TRUE to return only cases of deleted contacts',
    'type' => CRM_Utils_Type::T_BOOLEAN,
  );
}

/**
 * Returns list of cases according to given parameters.
 *
 * @param array $params
 *   Parameters to be passed to API call to obtain case list.
 *
 * @return array
 *   API result with the list of cases.
 */
function civicrm_api3_case_getcaselist(array $params) {
  $caseList = new CRM_Civicase_Api_Wrapper_CaseList();

  return $caseList->getCaseList($params);
}
