<?php

/**
 * Tutorial.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_tutorial_create($params) {
  // Workaround for the api3 html input encoder - html IS allowed in these fields
  if (!empty($params['steps'])) {
    foreach ($params['steps'] as &$step) {
      $step['title'] = str_replace(['&lt;', '&gt;'], ['<', '>'], $step['title']);
      $step['content'] = str_replace(['&lt;', '&gt;'], ['<', '>'], $step['content']);
    }
  }
  $tutorial = CRM_Tutorial_BAO_Tutorial::create($params);
  return civicrm_api3_create_success([$tutorial['id'] => $tutorial], $params, 'Tutorial', 'create');
}

/**
 * @param array $fields
 */
function _civicrm_api3_tutorial_create_spec(&$fields) {
  $fields = array_column(CRM_Tutorial_BAO_Tutorial::fields(), NULL, 'name');
}

/**
 * Tutorial.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_tutorial_delete($params) {
  CRM_Tutorial_BAO_Tutorial::delete($params);
  return civicrm_api3_create_success();
}

/**
 * Adjust metadata for delete action.
 *
 * @param $spec
 */
function _civicrm_api3_tutorial_delete_spec(&$spec) {
  $spec['id']['type'] = CRM_Utils_TYPE::T_STRING;
}

/**
 * Tutorial.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_tutorial_get($params) {
  $files = CRM_Tutorial_BAO_Tutorial::get();
  return _civicrm_api3_basic_array_get('Tutorial', $params, $files, 'id', ['id', 'url', 'groups']);
}

/**
 * @param array $fields
 */
function _civicrm_api3_tutorial_get_spec(&$fields) {
  $fields = array_column(CRM_Tutorial_BAO_Tutorial::fields(), NULL, 'name');
}

/**
 * Tutorial.mark API - mark a tutorial as viewed
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_tutorial_mark($params) {
  if (empty($params['id'])) {
    throw new API_Exception("Mandatory key(s) missing from params array: id", "mandatory_missing", array("fields" => ['id']));
  }
  CRM_Tutorial_BAO_Tutorial::mark($params);
  return civicrm_api3_create_success();
}
