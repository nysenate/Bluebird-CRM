<?php

/**
 * @file
 * Case Category Instance API file.
 */

/**
 * CaseCategoryInstance.create API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_case_category_instance_create(array $params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * CaseCategoryInstance.delete API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_case_category_instance_delete(array $params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * CaseCategoryInstance.get API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_case_category_instance_get(array $params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
