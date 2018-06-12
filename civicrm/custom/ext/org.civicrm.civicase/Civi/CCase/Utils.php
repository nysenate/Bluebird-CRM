<?php

namespace Civi\CCase;


class Utils {

  /**
   * Gets a list of manager roles for each case type.
   *
   * @return array
   *   [caseTypeId => relationshipTypeId]
   */
  public static function getCaseManagerRelationshipTypes() {
    $ret = array();
    $caseTypes = civicrm_api3('CaseType', 'get', array(
      'options' => array('limit' => 0),
      'return' => array('name', 'definition'),
    ));
    $relationshipTypes = civicrm_api3('RelationshipType', 'get', array(
      'options' => array('limit' => 0),
      'return' => array('name_b_a'),
    ));
    $relationshipTypes = \CRM_Utils_Array::rekey($relationshipTypes['values'], 'name_b_a');
    foreach ($caseTypes['values'] as $caseType) {
      foreach ($caseType['definition']['caseRoles'] as $role) {
        if (!empty($role['manager'])) {
          $ret[$caseType['id']] = $relationshipTypes[$role['name']]['id'];
        }
      }
    }
    return $ret;
  }

  /**
   * Add a case_manager join
   *
   * @param \CRM_Utils_SQL_Select $sql
   */
  public static function joinOnManager($sql) {
    $caseTypeManagers = self::getCaseManagerRelationshipTypes();
    $managerTypeClause = array();
    foreach ($caseTypeManagers as $caseTypeId => $relationshipTypeId) {
      $managerTypeClause[] = "(a.case_type_id = $caseTypeId AND manager_relationship.relationship_type_id = $relationshipTypeId)";
    }
    $managerTypeClause = implode(' OR ', $managerTypeClause);
    $sql->join('ccc', 'LEFT JOIN (SELECT * FROM civicrm_case_contact WHERE id IN (SELECT MIN(id) FROM civicrm_case_contact GROUP BY case_id)) AS ccc ON ccc.case_id = a.id');
    $sql->join('manager_relationship', "LEFT JOIN civicrm_relationship AS manager_relationship ON ccc.contact_id = manager_relationship.contact_id_a AND manager_relationship.is_active AND ($managerTypeClause) AND manager_relationship.case_id = a.id");
    $sql->join('manager', 'LEFT JOIN civicrm_contact AS manager ON manager_relationship.contact_id_b = manager.id AND manager.is_deleted <> 1');
  }

  /**
   *
   */
  public static function formatCustomSearchField($field) {
    if ($field['html_type'] != 'Autocomplete-Select') {
      $opts = civicrm_api('Case', 'getoptions', array(
        'version' => 3,
        'field' => "custom_{$field['id']}",
      ));
      if (!empty($opts['values'])) {
        $field['options'] = array();
        // Javascript doesn't like php's fast & loose type switching; ensure everything is a string
        foreach ($opts['values'] as $key => $val) {
          $field['options'][] = array(
            'id' => (string) $key,
            'text' => (string) $val,
          );
        }
      }
    }
    // For contact ref fields
    elseif ($field['data_type'] == 'ContactReference') {
      $field['entity'] = 'Contact';
      $field['api_params'] = array();
      if (!empty($field['filter'])) {
        parse_str($field['filter'], $field['api_params']);
        unset($field['api_params']['action']);
        if (!empty($field['api_params']['group'])) {
          $field['api_params']['group'] = explode(',', $field['api_params']['group']);
        }
      }
    }
    else {
      $field['entity'] = 'OptionValue';
      $field['api_params'] = array(
        'option_group_id' => $field['option_group_id'],
        'option_sort' => 'weight',
      );
    }
    unset($field['filter'], $field['option_group_id']);
    $field['name'] = "custom_{$field['id']}";
    $field['is_search_range'] = (bool) \CRM_Utils_Array::value('is_search_range', $field);
    return $field;
  }

}
