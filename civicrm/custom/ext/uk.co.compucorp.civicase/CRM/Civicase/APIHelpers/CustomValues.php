<?php

/**
 * Custom Values API Helper Class.
 */
class CRM_Civicase_APIHelpers_CustomValues {

  /**
   * Appends the specs needed by tree value endpoints.
   *
   * @param array $spec
   *   Specs as passed by CiviCRM to the spec hook function.
   */
  public static function getTreeValuesSpecs(array &$spec) {
    $spec['entity_id'] = [
      'title' => 'Entity Id',
      'description' => 'Id of entity',
      'type' => CRM_Utils_Type::T_INT,
      'api.required' => 1,
    ];

    $entities = civicrm_api3('Entity', 'get');
    $entities = array_diff($entities['values'], $entities['deprecated']);
    $spec['entity_type'] = [
      'title' => 'Entity Type',
      'description' => 'API name of entity type, e.g. "Contact"',
      'type' => CRM_Utils_Type::T_STRING,
      'api.required' => 1,
      'options' => array_combine($entities, $entities),
    ];
    // Return params for custom group, field & value.
    foreach (CRM_Core_DAO_CustomGroup::fields() as $field) {
      $name = 'custom_group.' . $field['name'];
      $spec[$name] = ['name' => $name] + $field;
    }
    foreach (CRM_Core_DAO_CustomField::fields() as $field) {
      $name = 'custom_field.' . $field['name'];
      $spec[$name] = ['name' => $name] + $field;
    }
    $spec['custom_value.id'] = [
      'title' => 'Custom Value Id',
      'description' => 'Id of record in custom value table',
      'type' => CRM_Utils_Type::T_INT,
    ];
    $spec['custom_value.data'] = [
      'title' => 'Custom Value (Raw)',
      'description' => 'Raw value as stored in the database',
      'type' => CRM_Utils_Type::T_STRING,
    ];
    $spec['custom_value.display'] = [
      'title' => 'Custom Value (Formatted)',
      'description' => 'Custom value formatted for display',
      'type' => CRM_Utils_Type::T_STRING,
    ];
  }

  /**
   * Returns the filters needed to get the custom values as a tree.
   *
   * @param array $params
   *   A list of parameters to filter the custom values, including the entity
   *   type, entity id, options, etc.
   *
   * @return array
   *   The filters and fields to return when getting the custom value tree.
   */
  public static function getTreeParams(array $params) {
    $ret = [];
    $options = _civicrm_api3_get_options_from_params($params);
    $toReturn = [
      'custom_group' => [],
      'custom_field' => [],
      'custom_value' => [],
    ];
    foreach (array_keys($options['return']) as $r) {
      list($type, $field) = explode('.', $r);
      if (isset($toReturn[$type])) {
        $toReturn[$type][] = $field;
      }
    }
    // We must have a name if not indexing sequentially.
    if (empty($params['sequential']) && $toReturn['custom_field']) {
      $toReturn['custom_field'][] = 'name';
    }
    switch ($params['entity_type']) {
      case 'Contact':
        $ret = ['entityType' => 'contact_type', 'subTypes' => 'contact_sub_type'];
        break;

      case 'Activity':
      case 'Campaign':
      case 'Case':
      case 'Contribution':
      case 'Event':
      case 'Grant':
      case 'Membership':
      case 'Relationship':
        $ret = ['subTypes' => strtolower($params['entity_type']) . '_type_id'];
        break;

      case 'CaseType':
        $ret = ['subTypes' => 'id'];
        break;

      case 'Participant':
        // Todo.
    }
    $treeParams = [
      'entityType' => $params['entity_type'],
      'subTypes' => [],
      'subName' => NULL,
    ];

    // Fetch entity data for custom group type/sub-type
    // Also verify access permissions
    // (api3 will throw an exception if permission denied)
    if ($ret || !empty($params['check_permissions'])) {
      $entityData = civicrm_api3($params['entity_type'], 'getsingle', [
        'id' => $params['entity_id'],
        'check_permissions' => !empty($params['check_permissions']),
        'return' => array_merge(['id'], array_values($ret)),
      ]);
      foreach ($ret as $param => $key) {
        if (isset($entityData[$key])) {
          $treeParams[$param] = $entityData[$key];
        }
      }
    }

    return [
      'filters' => $treeParams,
      'fieldsToReturn' => $toReturn,
    ];
  }

  /**
   * Formats the given tree results and appends them to the result array.
   *
   * @param array $tree
   *   Custom Value tree data as returned by the
   *   `CRM_Core_BAO_CustomGroup::getTree` function.
   * @param array $result
   *   A list where the formated tree results will be stored.
   * @param array $toReturn
   *   A list of fields to return. Used when formatting the results.
   */
  public static function formatTreeResults(array $tree, array &$result, array $toReturn) {
    unset($tree['info']);
    foreach ($tree as $group) {
      $result[$group['name']] = [];
      $groupToReturn = $toReturn['custom_group'] ? $toReturn['custom_group'] : array_keys($group);
      foreach ($groupToReturn as $item) {
        $result[$group['name']][$item] = CRM_Utils_Array::value($item, $group);
      }
      $result[$group['name']]['fields'] = [];
      foreach ($group['fields'] as $fieldInfo) {
        $field = ['value' => NULL];
        $fieldToReturn = $toReturn['custom_field'] ? $toReturn['custom_field'] : array_keys($fieldInfo);
        foreach ($fieldToReturn as $item) {
          $field[$item] = CRM_Utils_Array::value($item, $fieldInfo);
        }
        unset($field['customValue']);
        if (!empty($fieldInfo['customValue'])) {
          $field['value'] = CRM_Utils_Array::first($fieldInfo['customValue']);
          if (!$toReturn['custom_value'] || in_array('display', $toReturn['custom_value'])) {
            $field['value']['display'] = CRM_Core_BAO_CustomField::displayValue($field['value']['data'], $fieldInfo);
          }
          foreach (array_keys($field['value']) as $key) {
            if ($toReturn['custom_value'] && !in_array($key, $toReturn['custom_value'])) {
              unset($field['value'][$key]);
            }
          }
        }
        if (empty($params['sequential'])) {
          $result[$group['name']]['fields'][$fieldInfo['name']] = $field;
        }
        else {
          $result[$group['name']]['fields'][] = $field;
        }
      }
    }
  }

}
