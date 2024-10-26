<?php

/**
 * @file
 * API file.
 */

/**
 * CustomValue.Gettreevalues API specifications.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_custom_value_gettreevalues_spec(array &$spec) {
  CRM_Civicase_APIHelpers_CustomValues::getTreeValuesSpecs($spec);
}

/**
 * CustomValue.Gettreevalues API.
 *
 * This API is a customized version of the Civi Core CustomValue.Gettree API.
 *
 * @param array $params
 *   The Api parameters.
 *
 * @return array
 *   API result descriptor.
 */
function civicrm_api3_custom_value_gettreevalues(array $params) {
  $result = [];
  $isRequestingSingleGroupId = !empty($params['custom_group.name']) &&
    !is_array($params['custom_group.name']);
  $groupID = $isRequestingSingleGroupId
    ? CRM_Civicase_APIHelpers_CustomGroups::getIdForGroupName($params['custom_group.name'])
    : NULL;
  $treeParams = CRM_Civicase_APIHelpers_CustomValues::getTreeParams($params);
  $tree = CRM_Core_BAO_CustomGroup::getTree(
    $treeParams['filters']['entityType'],
    $treeParams['fieldsToReturn'],
    $params['entity_id'],
    $groupID,
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

  return civicrm_api3_create_success($result, $params, 'CustomValue', 'gettree');
}
