<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array(
  0 => array(
    'name' => 'Civicase - Case Activity Pivot Chart',
    'entity' => 'ReportTemplate',
    'params' => array(
      'version' => 3,
      'label' => 'Case with Activity Pivot Chart',
      'description' => 'Pivot Report for Cases + activities. This report will allow you to filter by activity without filtering out cases that
      don\'t have that activity, so, if you want to do stats on a particular activity & include as unknown if it does not exist',
      'class_name' => 'CRM_Civicase_Form_Report_Case_CaseWithActivityPivot',
      'report_url' => 'civicase/activity/pivot',
      'component' => 'CiviCase',
    ),
  ),
);
