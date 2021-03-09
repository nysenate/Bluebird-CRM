<?php

/**
 * @file
 * Activity.getDetails file.
 */

require_once 'api/v3/Case.php';
use CRM_Civicase_APIHelpers_CaseDetails as CaseDetailsQuery;

/**
 * Case.getdetails API specification.
 *
 * @param array $spec
 *   description of fields supported by this API call.
 */
function _civicrm_api3_case_getdetails_spec(array &$spec) {
  $result = civicrm_api3('Case', 'getfields', ['api_action' => 'get']);
  $spec = $result['values'];

  $spec['case_manager'] = [
    'title' => 'Case Manager',
    'description' => 'Contact id of the case manager',
    'type' => CRM_Utils_Type::T_INT,
  ];

  $spec['has_activities_for_involved_contact'] = [
    'title' => 'Has Activities For Involved Contact',
    'description' => "Has activities created by, assigned to, or targeting the involved contact",
    'type' => CRM_Utils_Type::T_INT,
  ];

  $spec['exclude_for_client_id'] = [
    'title' => 'Exclude For Client ID',
    'description' => "Contact id of the Client to be excluded",
    'type' => CRM_Utils_Type::T_INT,
  ];

  $spec['contact_involved'] = [
    'title' => 'Contact Involved',
    'description' => 'Id of the contact involved as case roles',
    'type' => CRM_Utils_Type::T_INT,
  ];

  $spec['has_role'] = [
    'title' => 'Case has role',
    'description' => '{ contact, role_type, can_be_client }',
    'type' => CRM_Utils_Type::T_STRING,
  ];

  $spec['contact_is_deleted'] = [
    'title' => 'Contact Is Deleted',
    'description' => 'Set FALSE to filter out cases for deleted contacts, TRUE to return only cases of deleted contacts',
    'type' => CRM_Utils_Type::T_BOOLEAN,
  ];
}

/**
 * Case.getdetails API.
 *
 * Provided by the CiviCase extension.
 * It gives more robust output than the regular get action.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result.
 *
 * @throws API_Exception
 */
function civicrm_api3_case_getdetails(array $params) {
  list(
    'resultMetadata' => $resultMetadata,
    'params' => $params,
    'toReturn' => $toReturn,
    'sql' => $sql
  ) = CaseDetailsQuery::get($params);

  // Call the case api.
  $result = civicrm_api3_case_get(['sequential' => 0] + $params, $sql);
  if (!empty($result['values'])) {
    $ids = array_keys($result['values']);

    // Remove legacy cruft.
    foreach ($result['values'] as &$case) {
      unset($case['client_id']);
    }

    $activityCategories = civicrm_api3('OptionValue', 'get', [
      'return' => ['name'],
      'option_group_id' => "activity_category",
    ]);
    $activityCategories = CRM_Utils_Array::collect('name', $activityCategories['values']);

    // Get activity summary.
    if (in_array('activity_summary', $toReturn)) {
      $catetoryLimits = CRM_Utils_Array::value('categories', $params['options'], array_fill_keys($activityCategories, 1));
      $categories = array_fill_keys(array_keys($catetoryLimits), []);
      foreach ($result['values'] as &$case) {
        $case['activity_summary'] = $categories;
      }
      $allTypes = [];
      foreach (array_keys($categories) as $grouping) {
        $option = civicrm_api3('OptionValue', 'get', [
          'return' => ['value'],
          'option_group_id' => 'activity_type',
          'grouping' => ['LIKE' => "%{$grouping}%"],
          'options' => ['limit' => 0],
        ]);
        foreach ($option['values'] as $val) {
          $categories[$grouping][] = $allTypes[] = $val['value'];
        }
      }

      // Get last activity.
      $lastActivities = _civicrm_api3_case_get_activities($ids, [
        'check_permissions' => !empty($params['check_permissions']),
        'status_id.filter' => CRM_Activity_BAO_Activity::COMPLETED,
        'options' => [
          'sort' => 'activity_date_time DESC',
        ],
      ]);

      foreach ($lastActivities['values'] as $act) {
        foreach ((array) $act['case_id'] as $actCaseId) {
          if (isset($result['values'][$actCaseId])) {
            $case =& $result['values'][$actCaseId];
            if (!isset($case['activity_summary']['last'])) {
              $case['activity_summary']['last'][] = $act;
            }
          }
        }
      }

      // Get next activities.
      $activities = _civicrm_api3_case_get_activities($ids, [
        'check_permissions' => !empty($params['check_permissions']),
        'status_id.filter' => CRM_Activity_BAO_Activity::INCOMPLETE,
      ]);

      foreach ($activities['values'] as $act) {
        foreach ((array) $act['case_id'] as $actCaseId) {
          if (isset($result['values'][$actCaseId])) {
            $case =& $result['values'][$actCaseId];
            if (!isset($case['activity_summary']['next'])) {
              $case['activity_summary']['next'][] = $act;
            }
            foreach ($categories as $category => $grouping) {
              if (in_array($act['activity_type_id'], $grouping) && (empty($catetoryLimits[$category]) || count($case['activity_summary'][$category]) < $catetoryLimits[$category])) {
                $case['activity_summary'][$category][] = $act;
              }
            }
          }
        }
      }
    }
    // Get activity count.
    if (in_array('activity_count', $toReturn)) {
      foreach ($result['values'] as $id => &$case) {
        $query = "SELECT COUNT(a.id) as count, a.activity_type_id
          FROM civicrm_activity a
          INNER JOIN civicrm_case_activity ca ON ca.activity_id = a.id
          WHERE a.is_current_revision = 1 AND a.is_test = 0 AND ca.case_id = $id
          GROUP BY a.activity_type_id";
        $dao = CRM_Core_DAO::executeQuery($query);
        while ($dao->fetch()) {
          $case['activity_count'][$dao->activity_type_id] = $dao->count;
        }
      }
    }
    // Get count of activities by category.
    if (in_array('category_count', $toReturn)) {
      $statusTypes = [
        'incomplete' => implode(',', array_keys(\CRM_Activity_BAO_Activity::getStatusesByType(\CRM_Activity_BAO_Activity::INCOMPLETE))),
        'completed' => implode(',', array_keys(\CRM_Activity_BAO_Activity::getStatusesByType(\CRM_Activity_BAO_Activity::COMPLETED))),
      ];
      // Creates category_count object with empty values.
      foreach ($result['values'] as &$case) {
        $case['category_count'] = array_fill_keys(array_values($activityCategories), []);
      }

      // Fills each category with respective counts.
      foreach ($activityCategories as $category) {
        // Calculates complete and incomplete activities.
        foreach ($statusTypes as $statusType => $statusTypeIds) {
          calculate_activities_for_category($category, $ids, $statusTypeIds, $statusType, FALSE, $result);
        }
        // Calculates overdue activities.
        calculate_activities_for_category($category, $ids, $statusTypes['incomplete'], $statusType, TRUE, $result);
      }

      // Calculates activities which does not have any activity category.
      foreach ($statusTypes as $statusType => $statusTypeIds) {
        calculate_activities_for_category(NULL, $ids, $statusTypeIds, $statusType, FALSE, $result);
      }

      // Calculates overdue activities not having any activity category.
      calculate_activities_for_category(NULL, $ids, $statusTypes['incomplete'], $statusType, TRUE, $result);
    }
    // Unread email activity count.
    if (in_array('unread_email_count', $toReturn)) {
      $query = "SELECT COUNT(a.id) as count, ca.case_id
        FROM civicrm_activity a, civicrm_case_activity ca
        WHERE ca.activity_id = a.id AND a.is_current_revision = 1 AND a.is_test = 0 AND ca.case_id IN (" . implode(',', $ids) . ")
        AND a.activity_type_id = (SELECT value FROM civicrm_option_value WHERE name = 'Inbound Email' AND option_group_id = (SELECT id FROM civicrm_option_group WHERE name = 'activity_type'))
        AND a.status_id = (SELECT value FROM civicrm_option_value WHERE name = 'Unread' AND option_group_id = (SELECT id FROM civicrm_option_group WHERE name = 'activity_status'))
        GROUP BY ca.case_id";
      $dao = CRM_Core_DAO::executeQuery($query);
      while ($dao->fetch()) {
        $result['values'][$dao->case_id]['unread_email_count'] = (int) $dao->count;
      }
    }
    // Get related_case_ids.
    if (in_array('related_case_ids', $toReturn)) {
      foreach ($result['values'] as &$case) {
        $case['related_case_ids'] = CRM_Case_BAO_Case::getRelatedCaseIds($case['id']);
      }
    }
    if (!empty($params['sequential'])) {
      $result['values'] = array_values($result['values']);
    }
  }
  return $resultMetadata + $result;
}

/**
 * Returns activities related to the cases.
 *
 * @param array $case_ids
 *   Cases ids.
 * @param array $params
 *   (Optional) Additional api request parameters.
 *
 * @return array
 *   Civicrm api request result with activities.
 *
 * @throws \CiviCRM_API3_Exception
 *   Civicrm exception.
 */
function _civicrm_api3_case_get_activities(array $case_ids, array $params = []) {
  $default_params = [
    'return' => [
      'activity_type_id', 'subject', 'activity_date_time', 'status_id',
      'case_id', 'target_contact_name', 'assignee_contact_name',
      'is_overdue', 'is_star', 'file_id', 'tag_id.name',
      'tag_id.description', 'tag_id.color',
    ],
    'case_id' => ['IN' => $case_ids],
    'is_current_revision' => 1,
    'is_test' => 0,
    'activity_type_id' => ['!=' => 'Bulk Email'],
    'options' => [
      'limit' => 0,
      'sort' => 'activity_date_time',
    ],
  ];

  $params = array_merge($default_params, $params);

  return civicrm_api3('Activity', 'get', $params);
}

/**
 * Calculates the number of activities for the given category.
 *
 * @param string $category
 *   Category.
 * @param array $ids
 *   IDs.
 * @param string $statusTypeIds
 *   Status Type IDs.
 * @param string $statusType
 *   Status Type.
 * @param bool $isOverdue
 *   Is Overdue.
 * @param array $result
 *   Result.
 */
function calculate_activities_for_category($category, array $ids, $statusTypeIds, $statusType, $isOverdue, array &$result) {
  $isOverdueCondition = $isOverdue ? "AND a.activity_date_time < NOW()" : "";
  $categoryCondition = empty($category) ? "IS NULL" : "LIKE '%$category%'";

  $query = "SELECT COUNT(a.id) as count, ca.case_id
  FROM civicrm_activity a, civicrm_case_activity ca
  WHERE ca.activity_id = a.id AND a.is_current_revision = 1 AND a.is_test = 0 AND ca.case_id IN (" . implode(',', $ids) . ")
  AND a.activity_type_id IN (SELECT value FROM civicrm_option_value WHERE `grouping` "
  . $categoryCondition . " AND option_group_id = (SELECT id FROM civicrm_option_group WHERE name = 'activity_type'))
  " . $isOverdueCondition . "
  AND is_current_revision = 1
  AND is_deleted = 0
  AND a.status_id IN ($statusTypeIds)
  GROUP BY ca.case_id";

  $dao = CRM_Core_DAO::executeQuery($query);

  while ($dao->fetch()) {
    $categoryName = empty($category) ? 'none' : $category;
    $statusTypeName = $isOverdue ? "overdue" : $statusType;
    $result['values'][$dao->case_id]['category_count'][$categoryName][$statusTypeName] = (int) $dao->count;
  }
}
