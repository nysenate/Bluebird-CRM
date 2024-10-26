<?php

/**
 * @file
 * Returns URL config to fetch case category from.
 */

use CRM_Civicase_Service_CaseCategoryFromUrl as CaseCategoryFromUrl;

return [
  'civicrm/contact/view/case' => [
    'url_type' => CaseCategoryFromUrl::CASE_TYPE_URL,
    'param' => 'id',
  ],
  'civicrm/case/add' => [
    'url_type' => CaseCategoryFromUrl::CASE_CATEGORY_TYPE_URL,
    'param' => 'case_type_category',
  ],
  'civicrm/case/a' => [
    'url_type' => CaseCategoryFromUrl::CASE_CATEGORY_TYPE_URL,
    'param' => 'case_type_category',
  ],
  'civicrm/workflow/a' => [
    'url_type' => CaseCategoryFromUrl::CASE_CATEGORY_TYPE_URL,
    'param' => 'case_type_category',
  ],
  'civicrm/ajax/rest' => [
    'url_type' => CaseCategoryFromUrl::AJAX_TYPE_URL,
    'param' => 'case_type_id.case_type_category',
  ],
  'civicrm/case/activity' => [
    'url_type' => CaseCategoryFromUrl::CASE_TYPE_URL,
    'param' => 'caseid',
  ],
  'civicrm/case/customreport/print' => [
    'url_type' => CaseCategoryFromUrl::CASE_TYPE_URL,
    'param' => 'caseID',
  ],
  'civicrm/case/report/print' => [
    'url_type' => CaseCategoryFromUrl::CASE_TYPE_URL,
    'param' => 'caseID',
  ],
  'civicrm/export/standalone' => [
    'url_type' => CaseCategoryFromUrl::CASE_TYPE_URL,
    'param' => 'id',
  ],
  'civicrm/activity' => [
    'url_type' => CaseCategoryFromUrl::ACTIVITY_TYPE_URL,
    'param' => 'id',
  ],
  'civicrm/activity/add' => [
    'url_type' => CaseCategoryFromUrl::ACTIVITY_TYPE_URL,
    'param' => 'id',
  ],
  'civicrm/activity/pdf/view' => [
    'url_type' => CaseCategoryFromUrl::ACTIVITY_TYPE_URL,
    'param' => 'id',
  ],
  'civicrm/activity/email/view' => [
    'url_type' => CaseCategoryFromUrl::ACTIVITY_TYPE_URL,
    'param' => 'id',
  ],
  'civicrm/case/contact-case-tab' => [
    'url_type' => CaseCategoryFromUrl::CASE_CATEGORY_TYPE_URL,
    'param' => 'case_type_category',
  ],
  'civicrm/case/activity/download-all-files' => [
    'url_type' => CaseCategoryFromUrl::ACTIVITY_TYPE_URL,
    'param' => 'activity_ids',
  ],
];
