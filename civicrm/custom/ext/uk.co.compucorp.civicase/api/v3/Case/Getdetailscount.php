<?php

/**
 * @file
 * Case.getdetailscount file.
 */

/**
 * Case getdetailscount API function.
 *
 * Provides a count of cases but properly respects filters unlike `getcount`.
 *
 * @param array $params
 *   List of parameters to use for filtering.
 *
 * @return array
 *   API result.
 *
 * @throws API_Exception
 */
function civicrm_api3_case_getdetailscount(array $params) {
  $params['options'] = CRM_Utils_Array::value('options', $params, []);
  $params['options']['is_count'] = 1;

  // Remove unnecesary parameters:
  unset($params['return'], $params['sequential']);

  $casesList = civicrm_api3('Case', 'getdetails', $params);

  return $casesList['values'];
}
