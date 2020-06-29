<?php

/**
 * MosaicoTemplate.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_mosaico_template_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * MosaicoTemplate.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_mosaico_template_create($params) {
  return _civicrm_api3_basic_create('CRM_Mosaico_BAO_MosaicoTemplate', $params);
}

/**
 * MosaicoTemplate.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_mosaico_template_delete($params) {
  return _civicrm_api3_basic_delete('CRM_Mosaico_BAO_MosaicoTemplate', $params);
}

/**
 * MosaicoTemplate.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_mosaico_template_get($params) {
  return _civicrm_api3_basic_get('CRM_Mosaico_BAO_MosaicoTemplate', $params);
}

/**
 * Adjust metadata for clone spec action.
 *
 * @param array $spec
 */
function _civicrm_api3_mosaico_template_clone_spec(&$spec) {
  $mailingFields = CRM_Mosaico_DAO_MosaicoTemplate::fields();
  $spec['id'] = $mailingFields['id'];
  $spec['id']['api.required'] = 1;
}

/**
 * Clone mailing.
 *
 * @param array $params
 *
 * @return array
 * @throws \CiviCRM_API3_Exception
 */
function civicrm_api3_mosaico_template_clone($params) {
  $BLACKLIST = ['id'];

  $newParams = CRM_Utils_Array::subset($params, [
    'debug',
    'title',
    'base',
    'html',
    'metadata',
    'content',
  ]);

  $get = civicrm_api3('MosaicoTemplate', 'getsingle', ['id' => $params['id']]);
  foreach ($get as $field => $value) {
    if (!isset($newParams[$field]) && !in_array($field, $BLACKLIST)) {
      $newParams[$field] = $value;
    }
  }

  return civicrm_api3('MosaicoTemplate', 'create', $newParams);
}

/**
 * Adjust metadata for replaceurls spec action.
 *
 * @param array $spec
 */
function _civicrm_api3_mosaico_template_replaceurls_spec(&$spec) {
  $spec['from_url'] = [
    'api.required' => 1,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Base URL of the server where the templates were generated',
  ];

  $spec['to_url'] = [
    'api.required' => 0,
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Base URL of the current server',
  ];
}

/**
 * MosaicoTemplate.replaceurls API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_mosaico_template_replaceurls($params) {
  // If no `to_url` was passed, the current server base URL will be used
  if (empty($params['to_url'])) {
    $params['to_url'] = CRM_Utils_System::url(NULL, NULL, TRUE);
  }

  // Ensure the `from_url` ends with a slash
  if (!preg_match('/' . preg_quote('/', '/') . '$/', $params['from_url'])) {
    $params['from_url'] .= '/';
  }
  
  // Ensure the `to_url` ends with a slash
  if (!preg_match('/' . preg_quote('/', '/') . '$/', $params['to_url'])) {
    $params['to_url'] .= '/';
  }

  CRM_Mosaico_BAO_MosaicoTemplate::replaceUrls($params['from_url'], $params['to_url']);
  return _civicrm_api3_basic_get('CRM_Mosaico_BAO_MosaicoTemplate', ['return' => ['id', 'title']]);
}