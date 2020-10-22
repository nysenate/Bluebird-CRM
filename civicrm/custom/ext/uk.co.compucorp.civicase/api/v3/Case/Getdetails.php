<?php

/**
 * @file
 * Activity.getDetails file.
 */

require_once 'api/v3/Case.php';
use Civi\CCase\Utils as CiviCaseUtils;

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
  $resultMetadata = [];
  $params += ['return' => []];
  if (is_string($params['return'])) {
    $params['return'] = explode(',', str_replace(' ', '', $params['return']));
  }
  $toReturn = $params['return'];
  $params['options'] = CRM_Utils_Array::value('options', $params, []);
  $extraReturnProperties = [
    'activity_summary', 'last_update', 'activity_count', 'category_count',
    'unread_email_count', 'related_case_ids',
  ];
  $params['return'] = array_diff($params['return'], $extraReturnProperties);

  // Support additional sort params.
  $sql = _civicrm_api3_case_getdetails_extrasort($params);

  // Add clause to search by manager.
  if (!empty($params['case_manager'])) {
    CRM_Civicase_APIHelpers_CasesByManager::filter($sql, $params['case_manager']);
  }

  if (!empty($params['has_role'])) {
    _civicrm_api3_case_getdetails_handle_role_filters($sql, $params);
  }

  // Add clause to search by non manager role and non client.
  if (!empty($params['contact_involved'])) {
    CRM_Civicase_APIHelpers_CasesByContactInvolved::filter($sql, $params['contact_involved']);
  }

  if (!empty($params['exclude_for_client_id'])) {
    $sql->where('a.id NOT IN (SELECT case_id FROM civicrm_case_contact WHERE contact_id = #contact_id)', [
      '#contact_id' => $params['exclude_for_client_id'],
    ]);
  }

  // Filter deleted contacts from results.
  if (isset($params['contact_is_deleted'])) {
    $isDeleted = (int) $params['contact_is_deleted'];
    $sql->where("a.id IN (SELECT case_id FROM civicrm_case_contact ccc, civicrm_contact cc WHERE ccc.contact_id = cc.id AND cc.is_deleted = $isDeleted)");
  }

  // Set page number dynamically based on selected record.
  if (!empty($params['options']['page_of_record'])) {
    $prParams = ['sequential' => 1] + $params;
    $prParams['return'] = ['id'];
    $prParams['options']['limit'] = $prParams['options']['offset'] = 0;
    foreach (CRM_Utils_Array::value('values', civicrm_api3_case_get($prParams), []) as $num => $case) {
      if ($case['id'] == $params['options']['page_of_record']) {
        $resultMetadata['page'] = floor($num / $params['options']['limit']) + 1;
        $params['options']['offset'] = $params['options']['limit'] * ($resultMetadata['page'] - 1);
        break;
      }
    }
  }

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
      $lastActivity = _civicrm_api3_case_get_activities($ids, [
        'check_permissions' => !empty($params['check_permissions']),
        'status_id.filter' => CRM_Activity_BAO_Activity::COMPLETED,
        'sequential' => 1,
        'options' => [
          'limit' => 1,
          'sort' => 'activity_date_time DESC',
        ],
      ]);
      $case['activity_summary']['last'] = $lastActivity['values'];

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
    // Get last update.
    if (in_array('last_update', $toReturn)) {
      // todo.
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
  AND a.activity_type_id IN (SELECT value FROM civicrm_option_value WHERE grouping "
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

/**
 * Support extra sorting in case.getdetails.
 *
 * @param array $params
 *   Parameters.
 *
 * @return \CRM_Utils_SQL_Select
 *   Sql Select query.
 *
 * @throws \API_Exception
 */
function _civicrm_api3_case_getdetails_extrasort(array &$params) {
  $sql = CRM_Utils_SQL_Select::fragment();
  $options = _civicrm_api3_get_options_from_params($params);

  if (!empty($options['sort'])) {
    $sort = explode(', ', $options['sort']);

    // For each one of our special fields we swap it for the placeholder (1)
    // so it will be ignored by the case api.
    foreach ($sort as $index => &$sortString) {
      // Get sort field and direction.
      list($sortField, $dir) = array_pad(explode(' ', $sortString), 2, 'ASC');
      list($sortJoin, $sortField) = array_pad(explode('.', $sortField), 2, 'id');
      // Sort by case manager.
      if ($sortJoin == 'case_manager') {
        // Validate inputs.
        if (!array_key_exists($sortField, CRM_Contact_DAO_Contact::fieldKeys()) || ($dir != 'ASC' && $dir != 'DESC')) {
          throw new API_Exception("Unknown field specified for sort. Cannot order by '$sortString'");
        }
        CiviCaseUtils::joinOnRelationship($sql, 'manager');
        $sql->orderBy("manager.$sortField $dir", NULL, $index);
        $sortString = '(1)';
      }
      // Sort by my role.
      elseif ($sortJoin == 'my_role') {
        $me = CRM_Core_Session::getLoggedInContactID();
        // Validate inputs.
        if (!array_key_exists($sortField, CRM_Contact_DAO_RelationshipType::fieldKeys()) || ($dir != 'ASC' && $dir != 'DESC')) {
          throw new API_Exception("Unknown field specified for sort. Cannot order by '$sortString'");
        }
        $sql->join('ccc', 'LEFT JOIN (SELECT * FROM civicrm_case_contact WHERE id IN (SELECT MIN(id) FROM civicrm_case_contact GROUP BY case_id)) AS ccc ON ccc.case_id = a.id');
        $sql->join('my_relationship', "LEFT JOIN civicrm_relationship AS my_relationship ON ccc.contact_id = my_relationship.contact_id_a AND my_relationship.is_active AND my_relationship.contact_id_b = $me AND my_relationship.case_id = a.id");
        $sql->join('my_relationship_type', 'LEFT JOIN civicrm_relationship_type AS my_relationship_type ON my_relationship_type.id = my_relationship.relationship_type_id');
        $sql->orderBy("my_relationship_type.$sortField $dir", NULL, $index);
        $sortString = '(1)';
      }
      // Sort by upcoming activities.
      elseif (strpos($sortString, 'next_activity') === 0) {
        $sortString = '(1)';
        $category = str_replace('next_activity_category_', '', $sortJoin);
        $actClause = '';
        // If we're limiting to a particiular category.
        if ($category != 'next_activity') {
          $actTypes = civicrm_api3('OptionValue', 'get', [
            'sequential' => 1,
            'option_group_id' => "activity_type",
            'options' => ['limit' => 0],
            'grouping' => ['LIKE' => "%$category%"],
          ]);
          $actTypes = implode(',', CRM_Utils_Array::collect('value', $actTypes['values']));
          if (!$actTypes) {
            continue;
          }
          $actClause = "AND activity_type_id IN ($actTypes)";
        }
        $incomplete = implode(',', array_keys(\CRM_Activity_BAO_Activity::getStatusesByType(\CRM_Activity_BAO_Activity::INCOMPLETE)));
        $sql->join($sortJoin, "LEFT JOIN (
            SELECT MIN(activity_date_time) as activity_date_time, case_id
            FROM civicrm_activity, civicrm_case_activity
            WHERE civicrm_activity.id = civicrm_case_activity.activity_id $actClause AND status_id IN ($incomplete) AND is_current_revision = 1 AND is_test <> 1
            GROUP BY case_id
          ) AS $sortJoin ON $sortJoin.case_id = a.id");
        $sql->orderBy("$sortJoin.activity_date_time $dir", NULL, $index);
      }
    }
    // Remove our extra sort params so the basic_get function doesn't see them.
    $params['options']['sort'] = implode(', ', $sort);
    unset($params['option_sort'], $params['option.sort'], $params['sort']);
  }

  return $sql;
}

/**
 * Filters cases by contacts related to the case and their relationship types.
 *
 * @param CRM_Utils_SQL_Select $sql
 *   a reference to the SQL object.
 * @param array $params
 *   As provided by the original api action.
 */
function _civicrm_api3_case_getdetails_handle_role_filters(CRM_Utils_SQL_Select $sql, array $params) {
  $hasRole = $params['has_role'];
  $canBeAClient = !isset($hasRole['can_be_client']) || $hasRole['can_be_client'];
  $hasOtherRolesThanClient = isset($hasRole['role_type']);
  $isAllCaseRolesTrue = $hasRole['all_case_roles_selected'];
  $roleSubQuery = new CRM_Utils_SQL_Select('civicrm_case');

  $roleSubQuery->select('civicrm_case.id');
  $roleSubQuery->groupBy('civicrm_case.id');

  if ($canBeAClient) {
    _civicrm_api3_case_getdetails_join_client($roleSubQuery, $hasRole);

    if ($hasOtherRolesThanClient || $isAllCaseRolesTrue) {
      _civicrm_api3_case_getdetails_join_relationships($roleSubQuery, $hasRole, [
        'joinType' => 'LEFT JOIN',
      ]);

      $roleSubQuery->where('case_relationship.case_id IS NOT NULL
      OR case_client.case_id IS NOT NULL');
    }
    else {
      $roleSubQuery->where('case_client.case_id IS NOT NULL');
    }
  }
  else {
    _civicrm_api3_case_getdetails_join_relationships($roleSubQuery, $hasRole, [
      'joinType' => 'JOIN',
    ]);
  }

  $roleSubQueryString = $roleSubQuery->toSql();

  $sql->join('case_roles', "
    JOIN ($roleSubQueryString) AS case_roles
    ON case_roles.id = a.id
  ");
}

/**
 * Joins the given SQL object with the case clients table.
 *
 * @param CRM_Utils_SQL_Select $sql
 *   the SQL object reference.
 * @param array $params
 *   List of filters to pass to the client join.
 */
function _civicrm_api3_case_getdetails_join_client(CRM_Utils_SQL_Select $sql, array $params) {
  _civicase_prepare_param_for_filtering($params, 'contact');

  $contactFilter = CRM_Core_DAO::createSQLFilter('case_client.contact_id', $params['contact']);

  $sql->join('case_client', "
    LEFT JOIN civicrm_case_contact AS case_client
    ON case_client.case_id = civicrm_case.id
    AND $contactFilter
  ");
}

/**
 * Joins the given SQL object with the relationships table.
 *
 * @param CRM_Utils_SQL_Select $sql
 *   the SQL object reference.
 * @param array $params
 *   List of filters to pass to the relationship join.
 */
function _civicrm_api3_case_getdetails_join_relationships(CRM_Utils_SQL_Select $sql, array $params, $options = []) {
  _civicase_prepare_param_for_filtering($params, 'contact');

  $contactFilter = CRM_Core_DAO::createSQLFilter('case_relationship.contact_id_b', $params['contact']);
  $joinClause = "
    {$options['joinType']} civicrm_relationship AS case_relationship
    ON case_relationship.case_id = civicrm_case.id
    AND case_relationship.is_active = 1
    AND $contactFilter
  ";

  if (!empty($params['role_type'])) {
    _civicase_prepare_param_for_filtering($params, 'role_type');

    $roleTypeFilter = CRM_Core_DAO::createSQLFilter('case_relationship.relationship_type_id', $params['role_type']);
    $joinClause .= "AND $roleTypeFilter";
  }

  $sql->join('civicrm_relationship', $joinClause);
}

/**
 * Corrects the param structure if not organized using the array notation.
 *
 * From ['paramName' => 'value']
 * To ['paramName' => ['=' => 'value']]
 * The later is the expected format when using `CRM_Core_DAO::createSQLFilter`.
 *
 * @param array $params
 *   The list of params as provided by the action.
 * @param string $paramName
 *   The name of the specific parameter to fix.
 */
function _civicase_prepare_param_for_filtering(array &$params, $paramName) {
  if (!is_array($params[$paramName])) {
    $params[$paramName] = [
      '=' => $params[$paramName],
    ];
  }
}
