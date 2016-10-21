<?php

class CRM_NYSS_BAO_NYSS {

  static function getContactList() {
    // if context is 'customfield'
    if (CRM_Utils_Array::value('context', $_GET) == 'customfield') {
      return self::contactReference();
    }
    $params = array(
      'version' => 3,
      'check_permissions' => TRUE,
      'search_field' => 'sort_name',
    );
    // String params
    // FIXME: param keys don't match input keys, using this array to translate
    $whitelist = array(
      's' => 'name',
      'term' => 'sort_name',
      'fieldName' => 'field_name',
      'tableName' => 'table_name',
      'context' => 'context',
      'rel' => 'rel',
      'contact_sub_type' => 'contact_sub_type'
    );
    foreach ($whitelist as $key => $param) {
      if (!empty($_GET[$key])) {
        $params[$param] = $_GET[$key];
      }
    }
    //CRM-10687: Allow quicksearch by multiple fields
    if (!empty($params['field_name'])) {
      if ($params['field_name'] == 'phone_numeric') {
        $params['name'] = preg_replace('/[^\d]/', '', $params['name']);
      }
      if (!$params['name']) {
        CRM_Utils_System::civiExit();
      }
    }
    // Numeric params
    $whitelist = array(
      'limit',
      'org',
      'employee_id',
      'cid',
      'id',
      'cmsuser',
    );
    foreach ($whitelist as $key) {
      if (!empty($_GET[$key]) && is_numeric($_GET[$key])) {
        $params[$key] = $_GET[$key];
      }
    }
    //CRM_Core_Error::debug_var('$_GET', $_GET);
    //CRM_Core_Error::debug_var('$params', $params);
    $result = civicrm_api('Contact', 'getlist', $params);
    //CRM_Core_Error::debug_var('result', $result);
    if (empty($result['is_error']) && !empty($result['values'])) {
      foreach ($result['values'] as $key => $val) {
        echo "{$val['data']}|{$val['id']}\n";
      }
    }
    CRM_Utils_System::civiExit();
  }
}
