<?php

/**
 * Tutorial.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_tutorial_create($params) {
  $whitelist = ['url', 'steps', 'groups', 'title', 'auto_start', 'id'];
  $tutorial = array_intersect_key($params, array_flip($whitelist));
  $dir = $filePath = Civi::paths()->getPath('[civicrm.files]/crm-tutorials');
  if (!is_dir($dir)) {
    mkdir($dir);
  }
  $allFiles = _civitutorial_get_files();
  // Create fileName for id
  if (empty($tutorial['id'])) {
    $fileNames = array_column($allFiles, 'id');
    $tutorial['id'] = CRM_Utils_String::munge($tutorial['url'], '-') . '-' . CRM_Utils_String::munge($tutorial['title']);
    // Add suffix if filename is not already unique
    $suffix = '';
    while (in_array($tutorial['id'] . $suffix, $fileNames)) {
      $suffix = $suffix ? $suffix + 1 : 1;
    }
    $tutorial['id'] .= $suffix;
  }
  $id = $tutorial['id'];
  $filePath = Civi::paths()->getPath('[civicrm.files]/crm-tutorials/' . $id . '.js');
  // Update file if it exists
  if (!empty($params['id'])) {
    foreach ($allFiles as $path => $file) {
      if ($id === $file['id'] && $path == $filePath) {
        $tutorial += $file;
      }
    }
  }
  // Workaround for the api3 html input encoder - html IS allowed in these fields
  foreach ($tutorial['steps'] as &$step) {
    $step['title'] = str_replace(['&lt;', '&gt;'], ['<', '>'], $step['title']);
    $step['content'] = str_replace(['&lt;', '&gt;'], ['<', '>'], $step['content']);
  }
  // Id is redundant with filename
  unset($tutorial['id']);
  file_put_contents($filePath, _civitutorial_encode($tutorial) . "\n");
  Civi::cache('community_messages')->delete('tutorials');
  return civicrm_api3_create_success([$id => ['id' => $id] + $tutorial], $params, 'Tutorial', 'create');
}

/**
 * Tutorial.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_tutorial_delete($params) {
  $filePath = Civi::paths()->getPath('[civicrm.files]/crm-tutorials/' . $params['id'] . '.js');
  unlink($filePath);
  Civi::cache('community_messages')->delete('tutorials');
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
  $files = _civitutorial_get_files();
  return _civicrm_api3_basic_array_get('Tutorial', $params, $files, 'id', ['id', 'url', 'groups']);
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
  $cid = CRM_Core_Session::getLoggedInContactID();
  if ($cid) {
    /** @var Civi\Core\SettingsBag $settings */
    $settings = Civi::service('settings_manager')->getBagByContact(NULL, $cid);
    $views = (array) $settings->get('tutorials');
    $views[$params['id']] = date('Y-m-d H:i:s');
    $settings->set('tutorials', $views);
  }
  return civicrm_api3_create_success();
}
