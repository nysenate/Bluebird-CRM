<?php

/**
 * Obtains list of columns that can be viewed on case lists.
 *
 * @param array $params
 *   Parameters for the call.
 *
 * @return array
 *   List of headers.
 */
function civicrm_api3_case_getcaselistheaders($params) {
  $caseList = new CRM_Civicase_APIHelpers_CaseList();
  return $caseList->getAllowedHeaders();
}
