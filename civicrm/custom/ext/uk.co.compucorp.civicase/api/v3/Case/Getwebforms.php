<?php

/**
 * @file
 * Case.Getrelations API.
 */

/**
 * Case.Getrelations API specification (optional).
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_case_getwebforms_spec(array &$spec) {
}

/**
 * Case.Getwebforms API.
 *
 * Search for webforms that have a atleast 1 case attached to it.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result
 *
 * @throws API_Exception
 */
function civicrm_api3_case_getwebforms(array $params) {
  $webforms = [];
  $sysInfo = civicrm_api3('System', 'get')['values'][0];

  if (!isset($sysInfo['uf']) || $sysInfo['uf'] != 'Drupal') {
    $out = civicrm_api3_create_success([]);
    $out['warning_message'] = 'Only Drupal CMS is supported!';
    return $out;
  }

  if (!module_exists('webform_civicrm')) {
    $out = civicrm_api3_create_success([]);
    $out['warning_message'] = '<p>Webform CiviCRM Drupal module is not installed</p>
      <ul><li>In order to link Drupal Webforms directly from CiviCase you need to install the following Drupal module:
      <a href="https://www.drupal.org/project/webform_civicrm">webform_civicrm</a>.</li></ul>';
    return $out;
  }

  $query = "SELECT a.nid, a.data, n.title
          FROM webform_civicrm_forms a
          INNER JOIN node n ON a.nid = n.nid";

  db_set_active('default');
  $daos = db_query($query);
  db_set_active('civicrm');

  foreach ($daos as $dao) {
    $data = unserialize($dao->data);

    if ($data['case']['number_of_case'] >= 0) {
      $webforms[] = [
        'nid' => $dao->nid,
        'title' => $dao->title,
        'case_type_ids' => _get_case_type_ids_from_webform($data),
        'path' => drupal_get_path_alias('node/' . $dao->nid),
      ];
    }
  }

  $out = civicrm_api3_create_success(array_filter($webforms), $params, 'Case', 'getwebforms');
  $out['count'] = count($webforms);

  return $out;
}

/**
 * Get Case Type Ids from the sent webform.
 *
 * @param array $webform
 *   Parameters.
 *
 * @return array
 *   List of Case Type IDs
 */
function _get_case_type_ids_from_webform(array $webform) {
  $caseTypeIds = [];

  foreach ($webform['case'] as $cases) {
    foreach ($cases['case'] as $case) {
      if (!empty($case['case_type_id'])) {
        array_push($caseTypeIds, $case['case_type_id']);
      }
    }
  }

  return $caseTypeIds;
}
