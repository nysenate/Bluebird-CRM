<?php

/**
 * @file
 * Case.getfiles.
 */

/**
 * Case.Getfiles API specification.
 *
 * @param array $spec
 *   description of fields supported by this API call.
 */
function _civicrm_api3_case_getfiles_spec(array &$spec) {
  $spec['case_id'] = [
    'title' => 'Cases',
    'description' => 'Find activities within specified cases.',
    'type' => 1,
    'FKClassName' => 'CRM_Case_DAO_Case',
    'FKApiName' => 'Case',
    'name' => 'case_id',
    'api.required' => 1,
  ];
  $spec['text'] = [
    'name' => 'text',
    'title' => 'Textual filter',
    'html' => [
      'type' => 'Text',
      'maxlength' => 64,
      'size' => 64,
    ],
  ];

  $spec['tag_id'] = [
    'title' => 'Tag Id',
    'description' => 'A single tag Id or array of Tag Ids',
    'type' => CRM_Utils_Type::T_STRING,
  ];

  $fileFields = CRM_Core_BAO_File::fields();
  $spec['mime_type'] = $fileFields['mime_type'];
  $spec['mime_type_cat'] = [
    'name' => 'mime_type_cat',
    'title' => 'General file category',
    'description' => 'A general category. May be a single category ("doc") or multiple (["IN",["doc","sheet"]]).',
  ];
}

/**
 * Case.Getfiles API.
 *
 * Perform a search for files related to a case.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result.
 */
function civicrm_api3_case_getfiles(array $params) {
  $params = _civicrm_api3_case_getfiles_format_params($params);
  $options = _civicrm_api3_get_options_from_params($params);

  $matches = _civicrm_api3_case_getfiles_find($params, $options);
  $result = civicrm_api3_create_success($matches);
  if (!empty($params['options']['xref'])) {
    $result['xref'] = _civicrm_api3_case_getfiles_xref($matches);
  }
  return $result;
}

/**
 * Normalize input parameters.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   Updated $params.
 */
function _civicrm_api3_case_getfiles_format_params(array $params) {
  // Blerg, option value expansions don't seem to work in non-standard actions.
  if (isset($params['activity_type_id'])) {
    $actTypes = CRM_Core_OptionGroup::values('activity_type', FALSE, FALSE, FALSE, NULL, 'name');;

    if (isset($params['activity_type_id'][0]) && $params['activity_type_id'][0] === 'IN') {
      $params['activity_type_id'][1] = array_map(function ($type) use ($actTypes) {
        if (is_numeric($type)) {
          return $type;
        }
        $search = array_search($type, $actTypes);
        return $search === FALSE ? -1 : $search;
      }, $params['activity_type_id'][1]);
    }
    else {
      if (!is_numeric($params['activity_type_id'])) {
        $search = array_search($params['activity_type_id'], $actTypes);
        $params['activity_type_id'] = $search === FALSE ? -1 : $search;
      }
    }
  }

  return $params;
}

/**
 * Find files function.
 *
 * @param array $params
 *   Parameters.
 * @param array $options
 *   Parameter options.
 *
 * @return array
 *   Ex: array(0 => array('case_id' => 123,
 *   'activity_id' => 456, 'file_id' => 789)).
 */
function _civicrm_api3_case_getfiles_find(array $params, array $options) {
  $select = _civicrm_api3_case_getfiles_select($params);

  if (!empty($options['limit'])) {
    $select->limit($options['limit'], isset($options['offset']) ? $options['offset'] : 0);
  }

  $dao = \CRM_Core_DAO::executeQuery($select->toSQL());
  $matches = [];
  while ($dao->fetch()) {
    $matches[$dao->id] = $dao->toArray();
  }
  if (!empty($params['sequential'])) {
    $matches = array_values($matches);
  }
  return $matches;
}

/**
 * Return select query for getting files.
 *
 * @param array $params
 *   Parameters.
 *
 * @return CRM_Utils_SQL_Select
 *   Query select class.
 */
function _civicrm_api3_case_getfiles_select(array $params) {
  $select = CRM_Utils_SQL_Select::from('civicrm_case_activity caseact')
    ->strict()
    ->join('ef', 'INNER JOIN civicrm_entity_file ef ON (ef.entity_table = "civicrm_activity" AND ef.entity_id = caseact.activity_id) ')
    ->join('et', 'LEFT JOIN civicrm_entity_tag et ON (et.entity_table = "civicrm_activity" AND et.entity_id = caseact.activity_id) ')
    ->join('f', 'INNER JOIN civicrm_file f ON ef.file_id = f.id')
    ->select('caseact.case_id as case_id, caseact.activity_id as activity_id, f.id as id, act.activity_date_time')
    ->distinct();

  if (isset($params['case_id'])) {
    $select->where('caseact.case_id = #caseIDs', [
      'caseIDs' => $params['case_id'],
    ]);
  }

  if (isset($params['tag_id'])) {
    $select->where(_civicase_get_tag_id_sql($params['tag_id']));
  }

  $select->join('act', 'INNER JOIN civicrm_activity act ON ((caseact.activity_id = act.id OR caseact.activity_id = act.original_id) AND act.is_current_revision=1)');
  if (isset($params['text'])) {
    // The end of the uri contains a hash which we want to ignore.
    // So we match from the start of the file uri as a cheap fix. CRM-20096.
    $select->where('act.subject LIKE @q OR act.details LIKE @q OR f.description LIKE @q OR f.uri LIKE @q OR f.uri LIKE @s', [
      'q' => '%' . $params['text'] . '%',
      's' => $params['text'] . '%',
    ]
    );
  }

  if (isset($params['mime_type_cat'])) {
    if (is_string($params['mime_type_cat'])) {
      $cats = [$params['mime_type_cat']];
    }
    elseif (is_array($params['mime_type_cat'][1]) && $params['mime_type_cat'][0] === 'IN') {
      $cats = $params['mime_type_cat'][1];
    }
    else {
      throw new \API_Exception("Field 'mime_type_cat' only supports string or IN values.");
    }
    $select->where(CRM_Civicase_FileCategory::createSqlFilter('f.mime_type', $cats));
  }

  if (isset($params['mime_type'])) {
    if (is_array($params['mime_type'])) {
      $select->where(CRM_Core_DAO::createSqlFilter('f.mime_type', $params['mime_type'], 'String'));
    }
    else {
      $select->where('f.mime_type LIKE @type', [
        '@type' => $params['mime_type'],
      ]
      );
    }
  }

  if (isset($params['activity_type_id.grouping'])) {
    $groupingFilter = is_array($params['activity_type_id.grouping'])
      ? $params['activity_type_id.grouping']
      : ['=', $params['activity_type_id.grouping']];
    $selectActTypes = CRM_Utils_SQL_Select::from('civicrm_option_value cov')
      ->join('cog', 'INNER JOIN civicrm_option_group cog ON cog.id = cov.option_group_id')
      ->where('cog.name = "activity_type"')
      ->where(CRM_Core_DAO::createSqlFilter('cov.grouping', $groupingFilter, 'String'))
      ->select('cov.value, cov.name');
    $actTypes = $selectActTypes->execute()->fetchMap('value', 'name');
    if ($actTypes) {
      $select->where('act.activity_type_id IN (#type)', [
        '#type' => array_keys($actTypes),
      ]
      );
    }
    else {
      $select->where('0 = 1');
    }
  }

  if (isset($params['activity_type_id'])) {
    if (is_array($params['activity_type_id'])) {
      $select->where(CRM_Core_DAO::createSqlFilter('act.activity_type_id', $params['activity_type_id'], 'String'));
    }
    else {
      $select->where('act.activity_type_id = #type', [
        '#type' => $params['activity_type_id'],
      ]
      );
    }
  }

  $select->orderBy(['act.activity_date_time DESC, act.id DESC, f.id DESC']);
  return $select;
}

/**
 * Returns the sql query to filter by tags.
 *
 * @param mixed $tagIdParam
 *   Parameters.
 *
 * @return null|string|array
 *   Sql Query.
 */
function _civicase_get_tag_id_sql($tagIdParam) {
  if (!is_array($tagIdParam)) {
    $tagIdParam = ['=' => $tagIdParam];
  }

  return CRM_Core_DAO::createSQLFilter('et.tag_id', $tagIdParam);
}

/**
 * Lookup any cross-references in the `getfiles` data.
 *
 * @param array $matches
 *   Ex: array(0 => array('case_id' => 123, 'activity_id' => 456, 'id' => 789))
 *
 * @return array
 *   Ex:
 *     $result['case'][123]['case_type_id'] = 3;
 *     $result['activity'][456]['subject'] = 'the subject';
 *     $result['file'][789]['mime_type'] = 'text/plain';
 */
function _civicrm_api3_case_getfiles_xref(array $matches) {
  $types = [
    // array(string $idField, string $xrefName, string $apiEntity)
    ['case_id', 'case', 'Case'],
    ['activity_id', 'activity', 'Activity'],
    ['id', 'file', 'Attachment'],
  ];

  $result = [];
  foreach ($types as $typeSpec) {
    [$idField, $xrefName, $apiEntity] = $typeSpec;
    $ids = array_unique(CRM_Utils_Array::collect($idField, $matches));
    // WISH: $result[$xrefName] = civicrm_api3(
    // $apiEntity, 'get', array('id'=>array('IN', $ids)))['values'];.
    foreach ($ids as $id) {
      $params = [
        'id' => $id,
      ];

      if ($xrefName == 'activity') {
        $params['return'] = ['subject', 'details', 'activity_type_id', 'status_id', 'source_contact_name',
          'target_contact_name', 'assignee_contact_name', 'activity_date_time', 'is_star',
          'original_id', 'tag_id.name', 'tag_id.description', 'tag_id.color', 'file_id',
          'is_overdue', 'case_id',
        ];
      }

      $result[$xrefName][$id] = civicrm_api3($apiEntity, 'getsingle', $params);
    }
  }

  return $result;
}
