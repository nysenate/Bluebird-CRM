<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
Civi::resources()
  ->addPermissions(array('administer CiviCase', 'administer CiviCRM',
    'access all cases and activities', 'add cases', 'basic case information'))
  ->addScriptFile('org.civicrm.shoreditch', 'base/js/affix.js', 1000, 'html-header')
  ->addSetting(array(
    'config' => array(
      'enableComponents' => CRM_Core_Config::singleton()->enableComponents,
      'user_contact_id' => (int) CRM_Core_Session::getLoggedInContactID(),
    ),
  ));
// The following changes are only relevant to the full-page app
if (CRM_Utils_System::getUrlPath() == 'civicrm/case/a') {
  // Add shoreditch custom css if not already present
  if (!civicrm_api3('Setting', 'getvalue', array('name' => "customCSSURL"))) {
    Civi::resources()
      ->addStyleFile('org.civicrm.shoreditch', 'css/custom-civicrm.css', 99, 'html-header');
  }
  CRM_Utils_System::resetBreadCrumb();
  $breadcrumb = array(
    array(
      'title' => ts('Home'),
      'url' => CRM_Utils_System::url(),
    ),
    array(
      'title' => ts('CiviCRM'),
      'url' => CRM_Utils_System::url('civicrm', 'reset=1'),
    ),
    array(
      'title' => ts('Case Dashboard'),
      'url' => CRM_Utils_System::url('civicrm/case/a/#/case'),
    ),
  );
  CRM_Utils_System::appendBreadCrumb($breadcrumb);
}
$options = array(
  'activityTypes' => 'activity_type',
  'activityStatuses' => 'activity_status',
  'caseStatuses' => 'case_status',
  'activityCategories' => 'activity_category',
);
foreach ($options as &$option) {
  $result = civicrm_api3('OptionValue', 'get', array(
    'return' => array('value', 'label', 'color', 'icon', 'name', 'grouping'),
    'option_group_id' => $option,
    'is_active' => 1,
    'options' => array('limit' => 0, 'sort' => 'weight'),
  ));
  $option = array();
  foreach ($result['values'] as $item) {
    $key = $item['value'];
    CRM_Utils_Array::remove($item, 'id', 'value');
    $option[$key] = $item;
  }
}
$caseTypes = civicrm_api3('CaseType', 'get', array(
  'return' => array('name', 'title', 'description', 'definition'),
  'options' => array('limit' => 0, 'sort' => 'weight'),
  'is_active' => 1,
));
foreach ($caseTypes['values'] as &$item) {
  CRM_Utils_Array::remove($item, 'id', 'is_forkable', 'is_forked');
}
$options['caseTypes'] = $caseTypes['values'];
$result = civicrm_api3('RelationshipType', 'get', array(
  'is_active' => 1,
  'options' => array('limit' => 0),
));
$options['relationshipTypes'] = $result['values'];
$options['fileCategories'] = CRM_Civicase_FileCategory::getCategories();
$options['activityStatusTypes'] = array(
  'incomplete' => array_keys(\CRM_Activity_BAO_Activity::getStatusesByType(CRM_Activity_BAO_Activity::INCOMPLETE)),
  'completed' => array_keys(\CRM_Activity_BAO_Activity::getStatusesByType(CRM_Activity_BAO_Activity::COMPLETED)),
  'cancelled' => array_keys(\CRM_Activity_BAO_Activity::getStatusesByType(CRM_Activity_BAO_Activity::CANCELLED)),
);
$result = civicrm_api3('CustomGroup', 'get', array(
  'sequential' => 1,
  'return' => array('extends_entity_column_value', 'title', 'extends'),
  'extends' => array('IN' => array('Case', 'Activity')),
  'is_active' => 1,
  'options' => array('sort' => 'weight'),
  'api.CustomField.get' => array(
    'is_active' => 1,
    'is_searchable' => 1,
    'return' => array('label', 'html_type', 'data_type', 'is_search_range', 'filter', 'option_group_id'),
    'options' => array('sort' => 'weight'),
  ),
));
$options['customSearchFields'] = $options['customActivityFields'] = array();
foreach ($result['values'] as $group) {
  if (!empty($group['api.CustomField.get']['values'])) {
    if ($group['extends'] == 'Case') {
      if (!empty($group['extends_entity_column_value'])) {
        $group['caseTypes'] = CRM_Utils_Array::collect('name', array_values(array_intersect_key($caseTypes['values'], array_flip($group['extends_entity_column_value']))));
      }
      foreach ($group['api.CustomField.get']['values'] as $field) {
        $group['fields'][] = Civi\CCase\Utils::formatCustomSearchField($field);
      }
      unset($group['api.CustomField.get']);
      $options['customSearchFields'][] = $group;
    }
    else {
      foreach ($group['api.CustomField.get']['values'] as $field) {
        $options['customActivityFields'][] = Civi\CCase\Utils::formatCustomSearchField($field) + array('group' => $group['title']);
      }
    }
  }
}
// Case tags
$options['tags'] = CRM_Core_BAO_Tag::getColorTags('civicrm_case');
$options['tagsets'] = CRM_Utils_Array::value('values', civicrm_api3('Tag', 'get', array(
  'sequential' => 1,
  'return' => array("id", "name"),
  'used_for' => array('LIKE' => "%civicrm_case%"),
  'is_tagset' => 1,
)));
// Bulk actions for case list - we put this here so it can be modified by other extensions
$options['caseActions'] = array(
  array(
    'title' => ts('Change Case Status'),
    'action' => 'changeStatus(cases)',
    'icon' => 'fa-pencil-square-o',
  ),
  array(
    'title' => ts('Edit Tags'),
    'action' => 'editTags(cases[0])',
    'icon' => 'fa-tags',
    'number' => 1,
  ),
  array(
    'title' => ts('Print Case'),
    'action' => 'print(cases[0])',
    'number' => 1,
    'icon' => 'fa-print',
  ),
  array(
    'title' => ts('Email Case Manager'),
    'action' => 'emailManagers(cases)',
    'icon' => 'fa-envelope-o',
  ),
  array(
    'title' => ts('Print/Merge Document'),
    'action' => 'printMerge(cases)',
    'icon' => 'fa-file-pdf-o',
  ),
  array(
    'title' => ts('Export Cases'),
    'action' => 'exportCases(cases)',
    'icon' => 'fa-file-excel-o',
  ),
  array(
    'title' => ts('Link Cases'),
    'action' => 'linkCases(cases[0])',
    'number' => 1,
    'icon' => 'fa-link',
  ),
  array(
    'title' => ts('Link 2 Cases'),
    'action' => 'linkCases(cases[0], cases[1])',
    'number' => 2,
    'icon' => 'fa-link',
  ),
);
if (CRM_Core_Permission::check('administer CiviCase')) {
  $options['caseActions'][] = array(
    'title' => ts('Merge 2 Cases'),
    'number' => 2,
    'action' => 'mergeCases(cases)',
    'icon' => 'fa-compress',
  );
}
if (CRM_Core_Permission::check('delete in CiviCase')) {
  $options['caseActions'][] = array(
    'title' => ts('Delete Case'),
    'action' => 'deleteCases(cases)',
    'icon' => 'fa-trash',
  );
}
// Contact tasks
$contactTasks = CRM_Contact_Task::permissionedTaskTitles(CRM_Core_Permission::getPermission());
$options['contactTasks'] = array();
foreach (CRM_Contact_Task::$_tasks as $id => $value) {
  if (isset($contactTasks[$id]) && isset($value['url'])) {
    $options['contactTasks'][$id] = $value;
  }
}
// Random setting
$options['allowMultipleCaseClients'] = (bool) Civi::settings()->get('civicaseAllowMultipleClients');
return array(
  'js' => array(
    'assetBuilder://visual-bundle.js', // at the moment, it's safe to include this multiple times -- deduped by resource manager
    'ang/civicase.js',
    'ang/civicase/*.js',
  ),
  'css' => array(
    'assetBuilder://visual-bundle.css', // at the moment, it's safe to include this multiple times -- deduped by resource manager
    'css/*.css',
    'ang/civicase/*.css',
  ),
  'partials' => array(
    'ang/civicase',
  ),
  'settings' => $options,
  'requires' => array('crmAttachment', 'crmUi', 'crmUtil', 'ngRoute', 'angularFileUpload', 'bw.paging', 'crmRouteBinder', 'crmResource', 'ui.bootstrap', 'uibTabsetClass', 'dialogService'),
  'basePages' => array(),
);
