<?php

use CRM_ReportError_ExtensionUtil as E;

class CRM_ReportError_Handler_SmartGroupRefresh {

  /**
   * Try to handle a failing smartgroup refresh.
   * This will automatically disable a broken smartgroup.
   */
  static public function handler($vars, $options_overrides) {
    if (!isset($vars['exception']) && !isset($vars['pearError'])) {
      return FALSE;
    }

    if (!Civi::settings()->get('reporterror_smartgroups_autodisable')) {
      return FALSE;
    }

    $sql = NULL;
    $error_message = NULL;

    if (empty($sql) && isset($vars['exception']) && method_exists($vars['exception'], 'getExtraParams')) {
      $extra_params = $vars['exception']->getExtraParams();

      if (isset($extra_params['sql'])) {
        $sql = $extra_params['sql'];
      }

      $error_message = $vars['exception']->getMessage();
    }

    if (empty($sql) && isset($vars['pearError'])) {
      if (!empty($vars['pearError']->userinfo)) {
        $sql = $vars['pearError']->userinfo;
      }

      $error_message = $vars['pearError']->message;
    }

    if (preg_match('/^CREATE TEMPORARY TABLE civicrm_temp_group_contact_cache\d+ \(SELECT (\d+) as group_id/', $sql, $matches)) {
      $broken_group_id = $matches[1];

      $output = [
        'data' => [],
      ];

      $result = civicrm_api3('Group', 'getsingle', [
        'group_id' => $broken_group_id,
      ]);

      $description = ($result['description'] ? $result['description'] . ' -- ' : '') . 'Disabled automatically by reporterror: ' . $error_message;

      civicrm_api3('Group', 'create', [
        'group_id' => $broken_group_id,
        'description' => $description,
        'is_active' => 0,
      ]);

      if (CRM_Utils_Array::value('update_smart_groups', $_REQUEST) == 1) {
        CRM_Core_Session::setStatus(E::ts('ERROR: Group ID %1 could not be loaded and has been disabled. This may be the result of a deleted custom field or a bug in a custom search.', [1 => $broken_group_id]), '', 'error');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/group', 'reset=1'));
        return TRUE;
      }
      else {
        // Assumes this is while the main database was being loaded on /civicrm/group?reset=1
        $output['data'][] = [
          'id' => 99999,
          'count' => 1,
          'title' => E::ts('ERROR: Group ID %1 could not be loaded and has been disabled. This may be the result of a deleted custom field or a bug in a custom search.', [1 => $broken_group_id]),
          'description' => '',
          'group_type' => '',
          'visibility' => '',
          'links' => '',
          'created_by' => '',
          'DT_RowId' => 'row_99999',
          'DT_RowClass' => 'crm-group-parent',
          'DT_RowAttr' => [
            'data-id' => 99999,
            'data-entity' => 'group',
          ],
        ];

        echo json_encode($output);
      }

      $vars['reporterror_subject'] = "SmartGroupRefresh";
      CRM_ReportError_Utils::sendReport($vars, $options_overrides);

      return TRUE;
    }

    return FALSE;
  }

}
