<?php

use Civi\CCase\Utils as CiviCaseUtils;

/**
 * Case Details API Helper Class.
 */
class CRM_Civicase_APIHelpers_CaseDetails {

  /**
   * Returns the SQL and other parameters needed to fetch the case details.
   *
   * @param array $params
   *   Filters used for fetching the case details.
   *
   * @return array
   *   The SQL, Parameters, and other data needed for fetching case details.
   */
  public static function get(array $params) {
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
    $sql = self::getExtraSort($params);

    // Add clause to search by manager.
    if (!empty($params['case_manager'])) {
      CRM_Civicase_APIHelpers_CasesByManager::filter($sql, $params['case_manager']);
    }

    if (!empty($params['has_role'])) {
      self::handleRoleFilters($sql, $params['has_role']);
    }

    // Add clause to search by non manager role and non client.
    if (!empty($params['contact_involved'])) {
      self::handleContactInvolvedFilters($sql, $params);
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

    return [
      'resultMetadata' => $resultMetadata,
      'params' => $params,
      'toReturn' => $toReturn,
      'sql' => $sql,
    ];
  }

  /**
   * Adds all the conditions needed for filtering by involved contact.
   *
   * @param CRM_Utils_SQL_Select $sql
   *   The SQL Query object to append the filters to.
   * @param array $params
   *   Contains the data used for filtering.
   */
  public static function handleContactInvolvedFilters(CRM_Utils_SQL_Select $sql, array $params) {
    self::prepareParamsForFiltering($params, 'contact_involved');
    $hasActivitiesForInvolvedContact = CRM_Utils_Array::value(
      'has_activities_for_involved_contact', $params, NULL);

    $caseContactFilter = CRM_Core_DAO::createSQLFilter('contact_id', $params['contact_involved']);
    $relContactFilter = CRM_Core_DAO::createSQLFilter('contact_id_b', $params['contact_involved']);
    $query = "
      SELECT case_id FROM civicrm_case_contact WHERE {$caseContactFilter}
      UNION DISTINCT
      SELECT case_id FROM civicrm_relationship WHERE is_active = 1 AND {$relContactFilter} AND case_id IS NOT NULL
    ";

    if ($hasActivitiesForInvolvedContact) {
      $query .= " UNION DISTINCT" . self::getCaseInvolvedInByActivityFilter($params['contact_involved']);
    }

    $sql->join('case_involvement', "
      JOIN ($query) AS case_involvement
      ON case_involvement.case_id = a.id
    ");
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
  private static function getExtraSort(array &$params) {
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
      // Remove extra sort params so the basic_get function doesn't see them.
      $params['options']['sort'] = implode(', ', $sort);
      unset($params['option_sort'], $params['option.sort'], $params['sort']);
    }

    return $sql;
  }

  /**
   * Returns the condition needed for filtering cases involved in by activity.
   *
   * @param string $contactId
   *   The ID of the activity's contact.
   *
   * @return string
   *   The condition that can be used for filtering.
   */
  private static function getCaseInvolvedInByActivityFilter($contactId) {
    $activityContactFilter = CRM_Core_DAO::createSQLFilter('cac.contact_id', $contactId);

    return "
      SELECT DISTINCT case_id
      FROM civicrm_case_activity ca
        INNER JOIN civicrm_activity a ON ca.activity_id = a.id
        INNER JOIN civicrm_activity_contact cac ON a.id = cac.activity_id AND {$activityContactFilter}
      WHERE a.is_deleted = 0
        AND a.is_current_revision = 1
        AND a.is_test = 0";
  }

  /**
   * Returns the Query Object and conditions for filtering by role.
   *
   * @param array $params
   *   The parameters used for filtering by role.
   *
   * @return array
   *   The Query Object and Where string used for filtering by role.
   */
  private static function getRoleQuery(array $params) {
    $where = '';
    $canBeAClient = !isset($params['can_be_client']) || $params['can_be_client'];
    $hasOtherRolesThanClient = isset($params['role_type']);
    $isAllCaseRolesTrue = $params['all_case_roles_selected'];
    $query = new CRM_Utils_SQL_Select('civicrm_case');

    $query->select('civicrm_case.id');
    $query->groupBy('civicrm_case.id');

    if ($canBeAClient) {
      self::joinClient($query, $params);
      $where .= 'case_client.case_id IS NOT NULL';

      if ($hasOtherRolesThanClient || $isAllCaseRolesTrue) {
        self::joinRelationships($query, $params, [
          'joinType' => 'LEFT JOIN',
        ]);

        $where .= ' OR case_relationship.case_id IS NOT NULL';
      }
    }
    else {
      self::joinRelationships($query, $params, [
        'joinType' => 'JOIN',
      ]);
    }

    return [
      'query' => $query,
      'where' => $where,
    ];
  }

  /**
   * Filters cases by contacts related to the case and their relationship types.
   *
   * @param CRM_Utils_SQL_Select $sql
   *   a reference to the SQL object.
   * @param array $params
   *   As provided by the original api action.
   */
  private static function handleRoleFilters(CRM_Utils_SQL_Select $sql, array $params) {
    list(
      'query' => $roleSubQuery,
      'where' => $where
    ) = self::getRoleQuery($params);

    if ($where) {
      $roleSubQuery->where($where);
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
  private static function joinClient(CRM_Utils_SQL_Select $sql, array $params) {
    self::prepareParamsForFiltering($params, 'contact');

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
   * @param array $options
   *   List of options for the join including its type.
   */
  private static function joinRelationships(CRM_Utils_SQL_Select $sql, array $params, array $options = []) {
    self::prepareParamsForFiltering($params, 'contact');

    $contactFilter = CRM_Core_DAO::createSQLFilter('case_relationship.contact_id_b', $params['contact']);
    $joinClause = "
      {$options['joinType']} civicrm_relationship AS case_relationship
      ON case_relationship.case_id = civicrm_case.id
      AND case_relationship.is_active = 1
      AND $contactFilter
    ";

    if (!empty($params['role_type'])) {
      self::prepareParamsForFiltering($params, 'role_type');

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
   * The later is the expected format when using
   * `CRM_Core_DAO::createSQLFilter`.
   *
   * @param array $params
   *   The list of params as provided by the action.
   * @param string $paramName
   *   The name of the specific parameter to fix.
   */
  private static function prepareParamsForFiltering(array &$params, $paramName) {
    if (!is_array($params[$paramName])) {
      $params[$paramName] = [
        '=' => $params[$paramName],
      ];
    }
  }

}
