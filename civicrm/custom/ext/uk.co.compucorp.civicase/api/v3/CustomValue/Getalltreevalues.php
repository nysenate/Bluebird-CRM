<?php

/**
 * @file
 * API file.
 */

/**
 * CustomValue.getalltreevalues API specifications.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_custom_value_getalltreevalues_spec(array &$spec) {
  CRM_Civicase_APIHelpers_CustomValues::getTreeValuesSpecs($spec);
}

/**
 * Returns a list of custom values for all active groups.
 *
 * @param array $params
 *   List of filters to use when fetching the tree values.
 *
 * @return array
 *   API results.
 */
function civicrm_api3_custom_value_getalltreevalues(array $params) {
  $result = [];
  $treeParams = CRM_Civicase_APIHelpers_CustomValues::getTreeParams($params);
  $allGroups = CRM_Civicase_APIHelpers_CustomGroups::getAllActiveGroupsForEntity(
    $params['entity_type']
  );

  foreach ($allGroups['values'] as $customGroup) {
    $tree = CRM_Core_BAO_CustomGroup::getTree(
      $treeParams['filters']['entityType'],
      $treeParams['fieldsToReturn'],
      $params['entity_id'],
      $customGroup['id'],
      $treeParams['filters']['subTypes'],
      $treeParams['filters']['subName'],
      TRUE,
      NULL,
      FALSE,
      CRM_Utils_Array::value('check_permissions', $params, TRUE)
    );

    CRM_Civicase_APIHelpers_CustomValues::formatTreeResults(
      $tree,
      $result,
      $treeParams['fieldsToReturn']
    );
  }

  return civicrm_api3_create_success($result, $params, 'CustomValue', 'getalltreevalues');
}
