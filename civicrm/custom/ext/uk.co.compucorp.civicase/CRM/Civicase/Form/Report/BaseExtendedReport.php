<?php

/**
 * Base class for case reports.
 */
abstract class CRM_Civicase_Form_Report_BaseExtendedReport extends CRM_Civicase_Form_Report_ExtendedReport {

  /**
   * Aggregate date fields.
   *
   * @var array
   */
  protected $aggregateDateFields;

  /**
   * The filter pane currently clicked.
   *
   * @var string
   */
  protected $filterPane;

  /**
   * Date SQL grouping options.
   *
   * @var array
   */
  protected $dateSqlGrouping = [
    'month' => "%Y-%m",
    'year' => "%Y",
  ];

  /**
   * Data functions array.
   *
   * @var array
   */
  protected $dataFunctions = [
    'COUNT' => 'COUNT',
    'COUNT UNIQUE' => 'COUNT UNIQUE',
    'SUM' => 'SUM',
  ];

  /**
   * Date grouping array.
   *
   * @var array
   */
  protected $dateGroupingOptions = ['month' => 'Month', 'year' => 'Year'];


  /**
   * Aggregate column html types.
   *
   * @var array
   */
  private $aggregateColumnHtmlTypes =
    ['Select', 'Radio', 'Autocomplete-Select', 'CheckBox'];

  /**
   * Used options list.
   *
   * @var array
   */
  protected $usedOptions = [];

  /**
   * CRM_Civicase_Form_Report_BaseExtendedReport constructor.
   */
  public function __construct() {
    parent::__construct();
    $this->addResultsTab();
  }

  /**
   * Add the results tab to the tabs list.
   */
  protected function addResultsTab() {
    $this->tabs['Results'] = [
      'title' => ts('Results'),
      'tpl' => 'Results',
      'div_label' => 'set-results',
    ];
  }

  /**
   * Function that allows additional filter fields.
   *
   * These fields are provided by extending class to be added to the
   * where clause for the report.
   */
  abstract protected function addAdditionalFiltersToWhereClause();

  /**
   * Returns additional filter fields provided by extending report class.
   *
   * @return array
   *   Additional filters.
   */
  abstract protected function getAdditionalFilterFields();

  /**
   * This function provides the template name to use for the filter fields.
   *
   * Overriding this will allow extending class to provide its own default
   * filter template in case it needs to provide additional filter fields.
   *
   * @return string
   *   Template name.
   */
  protected function getFiltersTemplateName() {
    return 'Filters';
  }

  /**
   * Returns Custom fields meta data.
   *
   * @param array $customFields
   *   Custom fields.
   *
   * @return array
   *   Custom fields meta data.
   */
  private function getCustomFieldsMeta(array $customFields) {
    $optionGroupIds = [];
    $sortedLists = [];
    foreach ($customFields as $customField) {
      if (!empty($customField['option_group_id'])) {
        $optionGroupIds[] = $customField['option_group_id'];
      }
    }
    $extendsString = implode("','", $optionGroupIds);
    $ogDAO = CRM_Core_DAO::executeQuery("
      SELECT ov.value, ov.label, ov.option_group_id
      FROM civicrm_option_value ov
      WHERE ov.option_group_id IN ('" . $extendsString . "')
      ORDER BY ov.weight;
    ");

    while ($ogDAO->fetch()) {
      $sortedLists[$ogDAO->option_group_id][$ogDAO->value] = $ogDAO->label;
    }

    return $sortedLists;
  }

  /**
   * Add the fields to select the aggregate fields to the report.
   *
   * This function is overridden because of a bug that does not allow
   * the custom fields to appear in the Filters tab in the base class.
   */
  protected function addAggregateSelectorsToForm() {
    if (!$this->isPivot) {
      return;
    }
    $aggregateColumnHeaderFields = $this->getAggregateColumnFields();
    $aggregateRowHeaderFields = $this->getAggregateRowFields();

    foreach ($this->_customGroupExtended as $key => $groupSpec) {
      $extendsKey = implode(',', $groupSpec['extends']);
      if (isset($this->customDataDAOs[$extendsKey])) {
        continue;
      }
      $customDAOs = $this->getCustomDataDaos($groupSpec['extends']);
      $customFieldMeta = $this->getCustomFieldsMeta($customDAOs);
      foreach ($customDAOs as $customField) {
        $tableKey = $customField['prefix'] . $customField['table_name'];
        $prefix = $customField['prefix'];
        $fieldName = 'custom_' . ($prefix ? $prefix . '_' : '') . $customField['id'];
        $this->addCustomTableToColumns($customField, $customField['table_name'], $prefix, $customField['prefix_label'], $tableKey);
        $this->_columns[$tableKey]['metadata'][$fieldName] = $this->getCustomFieldMetadata($customField, $customField['prefix_label'], '', $customFieldMeta);
        if (!empty($groupSpec['filters'])) {
          $this->_columns[$tableKey]['metadata'][$fieldName]['is_filters'] = TRUE;
          $this->_columns[$tableKey]['metadata'][$fieldName]['extends_table'] = $this->_columns[$tableKey]['extends_table'];
          $this->_columns[$tableKey]['filters'][$fieldName] = $this->_columns[$tableKey]['metadata'][$fieldName];
        }
        $this->metaData['metadata'][$fieldName] = $this->_columns[$tableKey]['metadata'][$fieldName];
        $this->metaData['metadata'][$fieldName]['is_aggregate_columns'] = TRUE;
        $this->metaData['metadata'][$fieldName]['table_alias'] = $this->_columns[$tableKey]['alias'];
        $this->metaData['aggregate_columns'][$fieldName] = $this->metaData['metadata'][$fieldName];
        $this->metaData['filters'][$fieldName] = $this->metaData['metadata'][$fieldName];
        $customFieldTitle = $customField['prefix_label'] . $customField['title'] . ' - ' . $customField['label'];
        $aggregateRowHeaderFields[$fieldName] = $customFieldTitle;
        if (in_array(
          $customField['html_type'],
          $this->aggregateColumnHtmlTypes
        )) {
          $aggregateColumnHeaderFields[$fieldName] = $customFieldTitle;
        }
      }

    }

    $this->addSelect(
      'aggregate_column_headers',
      [
        'entity' => '',
        'option_url' => NULL,
        'label' => ts('Aggregate Report Column Headers'),
        'options' => $aggregateColumnHeaderFields,
        'id' => 'aggregate_column_headers',
        'placeholder' => ts('- select -'),
        'class' => 'huge',
      ],
      FALSE
    );
    $this->addSelect(
      'aggregate_row_headers',
      [
        'entity' => '',
        'option_url' => NULL,
        'label' => ts('Row Fields'),
        'options' => $aggregateRowHeaderFields,
        'id' => 'aggregate_row_headers',
        'placeholder' => ts('- select -'),
        'class' => 'huge',
      ],
      FALSE
    );

    $this->addSelect(
      'aggregate_column_date_grouping',
      [
        'entity' => '',
        'option_url' => NULL,
        'label' => ts('Date Grouping'),
        'options' => $this->dateGroupingOptions,
        'id' => 'aggregate_column_date_grouping',
        'placeholder' => ts('- select -'),
      ],
      TRUE
    );

    $this->addSelect(
      'aggregate_row_date_grouping',
      [
        'entity' => '',
        'option_url' => NULL,
        'label' => ts('Date Grouping'),
        'options' => $this->dateGroupingOptions,
        'id' => 'aggregate_row_date_grouping',
        'placeholder' => ts('- select -'),
      ],
      FALSE
    );

    $this->addSelect(
      'data_function_field',
      [
        'entity' => '',
        'option_url' => NULL,
        'label' => ts('Data Function Fields'),
        'options' => $aggregateRowHeaderFields,
        'id' => 'data_function_fields',
        'placeholder' => ts('- select -'),
        'class' => 'huge',
      ],
      TRUE
    );

    $this->addSelect(
      'data_function',
      [
        'entity' => '',
        'option_url' => NULL,
        'label' => ts('Data Functions'),
        'options' => $this->dataFunctions,
        'id' => 'data_functions',
        'placeholder' => ts('- select -'),
        'class' => 'huge',
      ],
      TRUE
    );
    $this->add('hidden', 'charts');
    $this->_columns[$this->_baseTable]['fields']['include_null'] = [
      'title' => 'Show column for unknown',
      'pseudofield' => TRUE,
      'default' => TRUE,
    ];
    $this->tabs['Aggregate'] = [
      'title' => ts('Pivot table'),
      'tpl' => 'Aggregates',
      'div_label' => 'set-aggregates',
    ];

    $this->assign('aggregateDateFields', json_encode(array_flip($this->aggregateDateFields)));
    $this->assignTabs();
  }

  /**
   * Overrides function in base class.
   *
   * This function is overridden because of a bug that selects wrong data
   * for custom fields extending an entity when there are multiple instances
   * of the Entity in columns.
   *
   * For example, there are more than one Contact Entity columns, for
   * Case client contact, and also Case roles contacts, the custom field
   * value for the other Contact custom fields is selected wrongly because
   * the db alias of the first Contact entity is used in all case.
   * This is fixed by using the table key to form the alias rather than the
   * original table name which is same for all Contact entity data.
   *
   * @param string $field
   *   Custom field.
   * @param string $prefixLabel
   *   Prefix label.
   * @param string $prefix
   *   Prefix.
   * @param array $customFieldMeta
   *   Custom field meta data.
   *
   * @return mixed
   *   Custom field meta data.
   */
  protected function getCustomFieldMetadata($field, $prefixLabel, $prefix = '', array $customFieldMeta = []) {
    $field = array_merge($field, [
      'name' => $field['column_name'],
      'title' => $prefixLabel . $field['label'],
      'dataType' => $field['data_type'],
      'htmlType' => $field['html_type'],
      'operatorType' => $this->getOperatorType($this->getFieldType($field), [], []),
      'is_fields' => TRUE,
      'is_filters' => TRUE,
      'is_group_bys' => FALSE,
      'is_order_bys' => FALSE,
      'is_join_filters' => FALSE,
      'type' => $this->getFieldType($field),
      'dbAlias' => $prefix . $field['table_key'] . '.' . $field['column_name'],
      'alias' => $prefix . $field['table_name'] . '_' . 'custom_' . $field['id'],
      'filter' => $field['filter'],
    ]);
    $field['is_aggregate_columns'] =
      in_array($field['html_type'], $this->aggregateColumnHtmlTypes);

    if (!empty($field['option_group_id'])) {
      if (in_array($field['html_type'], [
        'Multi-Select',
        'AdvMulti-Select',
        'CheckBox',
      ])) {
        $field['operatorType'] = CRM_Report_Form::OP_MULTISELECT_SEPARATOR;
      }
      else {
        $field['operatorType'] = CRM_Report_Form::OP_MULTISELECT;
      }

      $field['options'] = !empty($customFieldMeta[$field['option_group_id']]) ? $customFieldMeta[$field['option_group_id']] : NULL;
    }

    if ($field['type'] === CRM_Utils_Type::T_BOOLEAN) {
      $field['options'] = [
        '' => ts('- select -'),
        1 => ts('Yes'),
        0 => ts('No'),
      ];
    }
    return $field;
  }

  /**
   * Overrides function in base class.
   *
   * This function is overridden because there is an issue with the
   * naming for the custom group panel labels on the filter section
   * in the UI.
   * The group title for the custom groups can not be passed in when defining
   * the fields hence the need to override this function.
   *
   * @param array $field
   *   Field data.
   * @param string $currentTable
   *   Current table.
   * @param string $prefix
   *   Prefix.
   * @param string $prefixLabel
   *   Prefix label.
   * @param string $tableKey
   *   Table key.
   */
  protected function addCustomTableToColumns(array $field, $currentTable, $prefix, $prefixLabel, $tableKey) {
    $entity = $field['extends'];
    if (in_array($entity, ['Individual', 'Organization', 'Household'])) {
      $entity = 'Contact';
    }
    if (!isset($this->_columns[$tableKey])) {
      $this->_columns[$tableKey]['extends'] = $field['extends'];
      $this->_columns[$tableKey]['grouping'] = $prefix . $field['table_name'];
      $this->_columns[$tableKey]['group_title'] = $field['table_label'];
      $this->_columns[$tableKey]['name'] = $field['table_name'];
      $this->_columns[$tableKey]['fields'] = [];
      $this->_columns[$tableKey]['filters'] = [];
      $this->_columns[$tableKey]['join_filters'] = [];
      $this->_columns[$tableKey]['group_bys'] = [];
      $this->_columns[$tableKey]['order_bys'] = [];
      $this->_columns[$tableKey]['aggregates'] = [];
      $this->_columns[$tableKey]['prefix_label'] = $field['prefix_label'];
      $this->_columns[$tableKey]['prefix'] = $prefix;
      $this->_columns[$tableKey]['table_name'] = $currentTable;
      $this->_columns[$tableKey]['alias'] = $prefix . $currentTable;
      $this->_columns[$tableKey]['extends_table'] = $prefix . CRM_Core_DAO_AllCoreTables::getTableForClass(CRM_Core_DAO_AllCoreTables::getFullName($entity));
    }
  }

  /**
   * Overriddes function in base class.
   *
   * This function is overridden because of custom JOINs for the
   * Case activity pivot report that are not available in base class.
   *
   * @return array
   *   Available Joins.
   */
  public function getAvailableJoins() {
    $availableJoins = parent::getAvailableJoins();

    $joins = [
      'relationship_from_case' => [
        'callback' => 'joinRelationshipFromCase',
      ],
      'case_role_contact' => [
        'callback' => 'joinCaseRolesContact',
      ],
      'case_tags' => [
        'callback' => 'joinEntityTagFromCase',
      ],
    ];

    return array_merge($availableJoins, $joins);
  }

  /**
   * Overrides function in base class.
   *
   * Function  overridden to allow NULL values in the results rows to
   * show as 'NULL' rather than as an empty string.
   *
   * @param array $rows
   *   Result rows.
   */
  public function alterRollupRows(array &$rows) {
    array_walk($rows, [$this, 'replaceNullRowValues']);
    if (count($rows) === 1) {
      // If the report only returns one row there is no rollup.
      return;
    }
    $groupBys = array_reverse(array_fill_keys(array_keys($this->_groupByArray), NULL));
    $firstRow = reset($rows);
    foreach ($groupBys as $field => $groupBy) {
      $fieldKey = isset($firstRow[$field]) ? $field : str_replace([
        '_YEAR',
        '_MONTH',
      ], '_start', $field);
      if (isset($firstRow[$fieldKey])) {
        unset($groupBys[$field]);
        $groupBys[$fieldKey] = $firstRow[$fieldKey];
      }
    }
    $groupByLabels = array_keys($groupBys);

    $altered = [];
    $fieldsToUnSetForSubtotalLines = [];
    // On this first round we'll get a list of keys that are not
    // groupbys or stats.
    foreach (array_keys($firstRow) as $rowField) {
      if (!array_key_exists($rowField, $groupBys) && substr($rowField, -4) != '_sum' && !substr($rowField, -7) != '_count') {
        $fieldsToUnSetForSubtotalLines[] = $rowField;
      }
    }

    $statLayers = count($this->_groupByArray);

    if (count($this->_statFields) == 0) {
      return;
    }

    foreach (array_keys($rows) as $rowNumber) {
      $nextRow = CRM_Utils_Array::value($rowNumber + 1, $rows);
      if ($nextRow === NULL && empty($this->rollupRow)) {
        $this->updateRollupRow($rows[$rowNumber], $fieldsToUnSetForSubtotalLines);
      }
      else {
        $this->alterRowForRollup($rows[$rowNumber], $nextRow, $groupBys, $rowNumber, $statLayers, $groupByLabels, $altered, $fieldsToUnSetForSubtotalLines);
      }
    }
  }

  /**
   * Overrides function in base class.
   *
   * Overridden to allow the alterRollupRows function use this function
   * since the original function in base class is private and the
   * `alterRollupRows` won't work without this.
   *
   * @param array $row
   *   Result row.
   * @param mixed $nextRow
   *   Result next row.
   * @param array $groupBys
   *   Group bys.
   * @param mixed $rowNumber
   *   Row number.
   * @param mixed $statLayers
   *   Statistic layers.
   * @param mixed $groupByLabels
   *   Group by labels.
   * @param mixed $altered
   *   Altered.
   * @param mixed $fieldsToUnSetForSubtotalLines
   *   Fields to unset.
   */
  private function alterRowForRollup(array &$row, $nextRow, array &$groupBys, $rowNumber, $statLayers, $groupByLabels, $altered, $fieldsToUnSetForSubtotalLines) {
    foreach ($groupBys as $field => $groupBy) {
      if (($rowNumber + 1) < $statLayers) {
        continue;
      }
      if (empty($row[$field]) && empty($row['is_rollup'])) {
        $valueIndex = array_search($field, $groupBys) + 1;
        if (!isset($groupByLabels[$valueIndex])) {
          return;
        }
        $groupedValue = $groupByLabels[$valueIndex];
        if (!($nextRow) || $nextRow[$groupedValue] != $row[$groupedValue]) {
          $altered[$rowNumber] = TRUE;
          $this->updateRollupRow($row, $fieldsToUnSetForSubtotalLines);
        }
      }
      $groupBys[$field] = $row[$field];
    }
  }

  /**
   * Replace NULL row values with the 'NULL' keyword.
   */
  private function replaceNullRowValues(&$row, $key) {
    foreach ($row as $field => $value) {
      if (is_null($value)) {
        $row[$field] = 'NULL';
      }
    }
  }

  /**
   * Get used options for column.
   *
   * @param string $dbAlias
   *   Db alias.
   * @param string $fieldName
   *   Field name.
   *
   * @return array
   *   Used options for column.
   */
  private function getUsedOptions($dbAlias, $fieldName) {
    if (!empty($this->usedOptions[$dbAlias])) {
      return $this->usedOptions[$dbAlias];
    }

    if (!empty($this->_having)) {
      $having = "{$this->_having} AND COUNT(*) > 0";
    }
    else {
      $having = "HAVING COUNT(*) > 0";
    }
    $query = "SELECT {$dbAlias} {$this->_from} {$this->_where} GROUP BY {$dbAlias} {$having}";
    $dao = CRM_Core_DAO::executeQuery($query);
    $result = $dao->fetchAll();
    $validOptions = [];
    foreach ($result as $option) {
      if (!empty($option[$fieldName])) {
        if (strpos($option[$fieldName], CRM_Core_DAO::VALUE_SEPARATOR) !== FALSE) {
          $multiOptions = explode(
            CRM_Core_DAO::VALUE_SEPARATOR,
            trim($option[$fieldName], CRM_Core_DAO::VALUE_SEPARATOR)
          );
          foreach ($multiOptions as $opt) {
            $validOptions[$opt] = TRUE;
          }
        }
        else {
          $validOptions[$option[$fieldName]] = TRUE;
        }
      }
    }
    $this->usedOptions[$dbAlias] = $validOptions;
    return $validOptions;
  }

  /**
   * Build the report query.
   *
   * @param bool $applyLimit
   *   Limit should be applied or not.
   *
   * @return string
   *   Report query.
   */
  public function buildQuery($applyLimit = TRUE) {
    if (empty($this->_params)) {
      $this->_params = $this->controller->exportValues($this->_name);
    }
    $this->buildGroupTempTable();
    $this->storeJoinFiltersArray();
    $this->storeWhereHavingClauseArray();
    $this->storeGroupByArray();
    $this->storeOrderByArray();
    $this->select();
    $this->from();
    $this->where();
    $this->extendedCustomDataFrom();
    $this->extendedWhereForContactReferenceFields();
    $this->aggregateSelect();

    if ($this->isInProcessOfPreconstraining()) {
      $this->generateTempTable();
      $this->_preConstrained = TRUE;
      $this->select();
      $this->from();
      $this->extendedCustomDataFrom();
      $this->constrainedWhere();
      $this->aggregateSelect();
    }
    $this->orderBy();
    $this->groupBy();

    if ($applyLimit && !CRM_Utils_Array::value('charts', $this->_params)) {
      if (!empty($this->_params['number_of_rows_to_render'])) {
        $this->_dashBoardRowCount = $this->_params['number_of_rows_to_render'];
      }
      $this->limit();
    }

    CRM_Utils_Hook::alterReportVar('sql', $this, $this);
    $sql = "{$this->_select} {$this->_from} {$this->_where} {$this->_groupBy} {$this->_having} {$this->_orderBy} ";
    if (!$this->_rollup) {
      $sql .= $this->_limit;
    }

    return $sql;
  }

  /**
   * Get custom fields.
   *
   * @param mixed $extends
   *   Extend list.
   *
   * @return array
   *   Custom data.
   */
  protected function getCustomDataDaos($extends) {
    $extendsKey = implode(',', $extends);
    if (isset($this->customDataDAOs[$extendsKey])) {
      return $this->customDataDAOs[$extendsKey];
    }
    $customGroupWhere = '';
    if (!$this->userHasAllCustomGroupAccess()) {
      $permissionedCustomGroupIDs = CRM_ACL_API::group(CRM_Core_Permission::VIEW, NULL, 'civicrm_custom_group', NULL, NULL);
      if (empty($permissionedCustomGroupIDs)) {
        return [];
      }
      $customGroupWhere = "cg.id IN (" . implode(',', $permissionedCustomGroupIDs) . ") AND";
    }
    $extendsMap = [];
    $extendsEntities = array_flip($extends);
    foreach (array_keys($extendsEntities) as $extendsEntity) {
      if (in_array($extendsEntity, [
        'Individual',
        'Household',
        'Organziation',
      ])) {
        $extendsEntities['Contact'] = TRUE;
        unset($extendsEntities[$extendsEntity]);
      }
    }
    foreach ($this->_columns as $table => $spec) {
      $entityName = (isset($spec['bao']) ? CRM_Core_DAO_AllCoreTables::getBriefName(str_replace('BAO', 'DAO', $spec['bao'])) : '');
      if ($entityName && in_array($entityName, $extendsEntities)) {
        $extendsMap[$entityName][$spec['prefix']] = $spec['prefix_label'];
      }
    }
    $extendsString = implode("','", $extends);
    $sql = "
        SELECT cg.table_name, cg.title, cg.extends, cf.id as cf_id, cf.label,
               cf.column_name, cf.data_type, cf.html_type, cf.option_group_id, cf.time_format, cf.filter
        FROM   civicrm_custom_group cg
        INNER  JOIN civicrm_custom_field cf ON cg.id = cf.custom_group_id
        WHERE cg.extends IN ('" . $extendsString . "') AND
          {$customGroupWhere}
          cg.is_active = 1 AND
          cf.is_active = 1 AND
          cf.is_searchable = 1
          ORDER BY cg.weight, cf.weight";
    $customDAO = CRM_Core_DAO::executeQuery($sql);

    $curTable = NULL;
    $fields = [];
    while ($customDAO->fetch()) {
      $entityName = $customDAO->extends;
      if (in_array($entityName, ['Individual', 'Household', 'Organization'])) {
        $entityName = 'Contact';
      }
      foreach ($extendsMap[$entityName] as $prefix => $label) {
        $fields[$prefix . $customDAO->column_name] = [
          'title' => $customDAO->title,
          'extends' => $customDAO->extends,
          'id' => $customDAO->cf_id,
          'label' => $customDAO->label,
          'table_label' => $customDAO->title,
          'column_name' => $customDAO->column_name,
          'data_type' => $customDAO->data_type,
          'dataType' => $customDAO->data_type,
          'html_type' => $customDAO->html_type,
          'option_group_id' => $customDAO->option_group_id,
          'time_format' => $customDAO->time_format,
          'prefix' => $prefix,
          'table_key' => $prefix . $customDAO->table_name,
          'prefix_label' => $label,
          'table_name' => $customDAO->table_name,
          'filter' => $customDAO->filter,
        ];
        $fields[$prefix . $customDAO->column_name]['type'] = $this->getFieldType($fields[$prefix . $customDAO->column_name]);
      }
    }
    $this->customDataDAOs[$extendsKey] = $fields;

    return $fields;
  }

  /**
   * Returns options for a contact reference field.
   *
   * @param array $spec
   *   Specifications.
   * @param array $usedIds
   *   List of used contact ids.
   *
   * @return array
   *   Contact column options.
   */
  public function getContactColumnOptions(array $spec, array $usedIds) {
    $options = $usedFilters = [];
    parse_str($spec['filter'], $usedFilters);
    unset($usedFilters['action']);
    $usedFilters['return'] = ['id', 'display_name'];
    $usedFilters['sequential'] = 1;
    $usedFilters['options'] = ['limit' => 0];
    $usedFilters['id'] = ['IN' => $usedIds];
    try {
      $contacts = civicrm_api3(
        'Contact',
        'get',
        $usedFilters
      );
      if ($contacts['is_error'] === 0) {
        $contacts = CRM_Utils_Array::value('values', $contacts);
        foreach ($contacts as $contact) {
          $options[$contact['id']] = $contact['display_name'];
        }
      }
    }
    catch (Throwable $ex) {
    }

    return $options;
  }

  /**
   * Restrict rows to only used options in where clause.
   */
  public function extendedWhereForContactReferenceFields() {
    $rowFields = $this->getAggregateFieldSpec('row');
    if (!empty($rowFields)) {
      foreach ($rowFields as $field => $fieldDetails) {
        if (CRM_Utils_Array::value('data_type', $fieldDetails) === 'ContactReference') {
          $usedOptions = implode(
            "','",
            array_keys($this->getUsedOptions($fieldDetails['dbAlias'], $fieldDetails['name']))
          );
          $this->_where .= " AND {$fieldDetails['dbAlias']} IN ('{$usedOptions}') ";
        }
      }
    }
  }

  /**
   * Return used column options.
   *
   * @param array $spec
   *   Specifications.
   * @param string $fieldName
   *   Field name.
   *
   * @return array
   *   Used column options.
   */
  private function getFieldOptions(array $spec, $fieldName) {
    if (CRM_Utils_Array::value('data_type', $spec) === 'ContactReference') {
      $this->_aggregatesIncludeNULL = FALSE;
      $usedIds = array_keys($this->getUsedOptions($spec['dbAlias'], $fieldName));
      if (empty($usedIds)) {
        return [];
      }

      return $this->getContactColumnOptions($spec, $usedIds);
    }

    return array_intersect_key(
      $this->getCustomFieldOptions($spec),
      $this->getUsedOptions($spec['dbAlias'], $fieldName)
    );
  }

  /**
   * Build custom data from clause.
   *
   * Overridden to support custom data for multiple entities of the same type.
   */
  public function extendedCustomDataFrom() {
    foreach ($this->getMetadataByType('metadata') as $prop) {
      $table = $prop['table_name'];
      if (empty($prop['extends']) || !$this->isCustomTableSelected($table)) {
        continue;
      }

      $baseJoin = CRM_Utils_Array::value($prop['extends'], $this->_customGroupExtendsJoin, "{$this->_aliases[$prop['extends_table']]}.id");

      $customJoin = is_array($this->_customGroupJoin) ? $this->_customGroupJoin[$table] : $this->_customGroupJoin;
      $tableKey = CRM_Utils_Array::value('prefix', $prop) . $prop['table_name'];
      if (!stristr($this->_from, ' ' . $this->_aliases[$tableKey] . ' ')) {
        // Protect against conflict with selectableCustomFrom.
        $this->_from .= "
{$customJoin} {$prop['table_name']} {$this->_aliases[$tableKey]} ON {$this->_aliases[$tableKey]}.entity_id = {$baseJoin}";
      }
      // Handle for ContactReference.
      if (array_key_exists('fields', $prop)) {
        foreach ($prop['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('dataType', $field) ==
            'ContactReference'
          ) {
            $customFieldID = CRM_Core_BAO_CustomField::getKeyID($fieldName);
            if (!$customFieldID) {
              // Seems it can be passed with wierd things appended...
              continue;
            }
            $columnName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Core_BAO_CustomField::getKeyID($fieldName), 'column_name');
            $this->_from .= "
LEFT JOIN civicrm_contact {$field['alias']} ON {$field['alias']}.id = {$this->_aliases[$tableKey]}.{$columnName} ";
          }
        }
      }
    }
  }

  /**
   * Add Select for pivot chart style report.
   *
   * @param string $fieldName
   *   Field name.
   * @param string $dbAlias
   *   Db alias.
   * @param array $spec
   *   Specifications.
   */
  public function addColumnAggregateSelect($fieldName, $dbAlias, array $spec) {
    if (empty($fieldName)) {
      $this->addAggregateTotal($fieldName);
      return;
    }
    $spec['dbAlias'] = $dbAlias;
    $options = $this->getFieldOptions($spec, $fieldName);

    if (!empty($this->_params[$fieldName . '_value'])
      && CRM_Utils_Array::value($fieldName . '_op', $this->_params) == 'in'
    ) {
      $options['values'] = array_intersect_key($options, array_flip($this->_params[$fieldName . '_value']));
    }

    if (empty($options)) {
      return;
    }
    $filterSpec = [
      'field' => ['name' => $fieldName],
      'table' => ['alias' => $spec['table_name']],
    ];

    if ($this->getFilterFieldValue($spec)) {
      // For now we will literally just handle IN.
      if ($filterSpec['field']['op'] == 'in') {
        $options = array_intersect_key($options, array_flip($filterSpec['field']['value']));
        $this->_aggregatesIncludeNULL = FALSE;
      }
    }

    $aggregates = [];
    foreach ($options as $optionValue => $optionLabel) {
      if ($optionLabel == '- select -') {
        continue;
      }

      $fieldAlias = str_replace([
        '-',
        '+',
        '\/',
        '/',
        ')',
        '(',
      ], '_', "{$fieldName}_" . strtolower(str_replace(' ', '', $optionValue)));

      $selectSql = $this->getColumnSqlAggregateExpression($spec, $dbAlias, $fieldAlias, $optionValue, $optionLabel);
      $aggregateExpression = rtrim($selectSql, "AS {$fieldAlias} ");
      $aggregateExpression = ltrim($aggregateExpression, " , ");

      $aggregates[] = $aggregateExpression;
      $this->_select .= $selectSql;
      $this->_columnHeaders[$fieldAlias] = [
        'title' => !empty($optionLabel) ? $optionLabel : 'NULL',
        'type' => CRM_Utils_Type::T_INT,
      ];
      $this->_statFields[] = $fieldAlias;
    }

    if ($this->_aggregatesAddTotal) {
      $this->addAggregateTotalField($fieldName, $aggregates);
    }
  }

  /**
   * Returns the SQL aggregate expression for a selected column field.
   *
   * The overral expression will depend on the data aggregate function used,
   * the field to aggregate on (if applicable).
   *
   * @param array $spec
   *   Specification.
   * @param string $dbAlias
   *   Db alias.
   * @param string $fieldAlias
   *   Field alias.
   * @param mixed $optionValue
   *   Option value.
   * @param string $optionLabel
   *   Option label.
   *
   * @return string
   *   SQL expression.
   */
  private function getColumnSqlAggregateExpression(array $spec, $dbAlias, $fieldAlias, $optionValue, $optionLabel) {
    $dataFunction = $this->_params['data_function'];
    $field = $dbAlias;
    $value = $optionValue;
    $operator = '=';

    if (!empty($spec['htmlType']) &&
      in_array($spec['htmlType'], ['CheckBox', 'MultiSelect'])) {
      $value = "%" . CRM_Core_DAO::VALUE_SEPARATOR . $optionValue . CRM_Core_DAO::VALUE_SEPARATOR . "%";
      $operator = 'LIKE';
    }

    if (!empty($spec['html']['type']) && $spec['html']['type'] == 'Select Date') {
      $dateGrouping = $this->_params['aggregate_column_date_grouping'];
      $field = "DATE_FORMAT({$dbAlias}, '{$this->dateSqlGrouping[$dateGrouping]}')";
    }

    if (is_null($optionLabel)) {
      $operator = 'IS NULL';
      $value = '';
    }

    if ($dataFunction === 'COUNT') {
      return $this->getSqlAggregateForCount($field, $value, $operator, $fieldAlias);
    }

    if ($dataFunction === 'COUNT UNIQUE') {
      return $this->getSqlAggregateForCountUnique($field, $value, $operator, $fieldAlias);
    }

    if ($dataFunction === 'SUM') {
      return $this->getSqlAggregateForSum($field, $value, $operator, $fieldAlias);
    }
  }

  /**
   * Returns the SQL expression for COUNT aggregate.
   *
   * @param string $field
   *   Field.
   * @param mixed $value
   *   Value.
   * @param string $operator
   *   SQL operator.
   * @param string $fieldAlias
   *   Field alias.
   *
   * @return string
   *   SQL statement.
   */
  protected function getSqlAggregateForCount($field, $value, $operator, $fieldAlias) {
    $value = (!empty($value) || $value == 0) ? "'{$value}'" : '';
    return " , SUM( CASE WHEN {$field} {$operator} $value THEN 1 ELSE 0 END ) AS $fieldAlias ";
  }

  /**
   * Returns the SQL expression for COUNT UNIQUE aggregate.
   *
   * @param string $field
   *   Field name.
   * @param mixed $value
   *   Field value.
   * @param string $operator
   *   SQL operator.
   * @param string $fieldAlias
   *   Field alias.
   *
   * @return string
   *   SQL statement.
   */
  protected function getSqlAggregateForCountUnique($field, $value, $operator, $fieldAlias) {
    $value = (!empty($value) || $value === 0) ? "'{$value}'" : '';
    $dataFunctionFieldAlias = $this->getDbAliasForAggregateOnField();

    return " , COUNT( DISTINCT CASE WHEN {$field} {$operator} $value THEN {$dataFunctionFieldAlias} END ) AS $fieldAlias ";
  }

  /**
   * Returns the SQL expression for SUM aggregate.
   *
   * @param string $field
   *   Field name.
   * @param mixed $value
   *   Field value.
   * @param string $operator
   *   SQL operator.
   * @param string $fieldAlias
   *   Field alias.
   *
   * @return string
   *   SQL aggregate.
   */
  protected function getSqlAggregateForSum($field, $value, $operator, $fieldAlias) {
    $value = (!empty($value) || $value == 0) ? "'{$value}'" : '';
    $dataFunctionFieldAlias = $this->getDbAliasForAggregateOnField();

    return " , SUM( CASE WHEN {$field} {$operator} $value THEN {$dataFunctionFieldAlias} ELSE 0 END ) AS $fieldAlias ";
  }

  /**
   * Returns the db Alias for the field on which to aggregate on.
   *
   * @return string
   *   DB alias.
   */
  private function getDbAliasForAggregateOnField() {
    $dataFunctionField = $this->_params['data_function_field'];
    $specs = $this->getMetadataByType('metadata')[$dataFunctionField];

    return $specs['dbAlias'];
  }

  /**
   * Overrides function in base class.
   *
   * This function is overridden because we need to extend
   * the functionality by providing a function to fetch options
   * when a date field is selected as a column header field.
   *
   * @param array $spec
   *   Specifications.
   *
   * @return array
   *   Custom field options.
   */
  protected function getCustomFieldOptions(array $spec) {
    $options = [];
    if (!empty($spec['options'])) {
      return $spec['options'];
    }

    if ($spec['type'] == CRM_Report_Form::OP_DATE) {
      return $this->getDateColumnOptions($spec);
    }

    // Data type is set for custom fields but not core fields.
    if (CRM_Utils_Array::value('data_type', $spec) == 'Boolean') {
      $options = [
        'values' => [
          0 => ['label' => 'No', 'value' => 0],
          1 => ['label' => 'Yes', 'value' => 1],
        ],
      ];
    }
    elseif (!empty($spec['options'])) {
      foreach ($spec['options'] as $option => $label) {
        $options['values'][$option] = [
          'label' => $label,
          'value' => $option,
        ];
      }
    }
    else {
      if (empty($spec['option_group_id'])) {
        throw new Exception('currently column headers need to be radio or select');
      }
      $options = civicrm_api('option_value', 'get', [
        'version' => 3,
        'options' => ['limit' => 50],
        'option_group_id' => $spec['option_group_id'],
      ]);
    }

    return $options['values'];
  }

  /**
   * Returns options for a date field when selected as a column header.
   *
   * @param array $spec
   *   Specifications.
   *
   * @return array
   *   Date column options.
   */
  public function getDateColumnOptions(array $spec) {
    $this->from();
    $this->where();
    $dateGrouping = $this->_params['aggregate_column_date_grouping'];
    $select = "SELECT DISTINCT DATE_FORMAT({$spec['dbAlias']}, '{$this->dateSqlGrouping[$dateGrouping]}') as date_grouping";
    $sql = "{$select} {$this->_from} {$this->_where} ORDER BY date_grouping ASC";
    if (!$this->_rollup) {
      $sql .= $this->_limit;
    }

    $result = CRM_Core_DAO::executeQuery($sql);
    $options = [];
    while ($result->fetch()) {
      $options[$result->date_grouping] = $result->date_grouping;
    }

    return $options;
  }

  /**
   * Adds the SQl expression for the total aggregate.
   *
   * Adds for the column fields for each row in the result set.
   *
   * @param string $fieldName
   *   Field name.
   * @param array $aggregates
   *   Aggregates.
   */
  protected function addAggregateTotalField($fieldName, array $aggregates) {
    $fieldAlias = "{$fieldName}_total";
    $sumOfAggregates = implode(' + ', $aggregates);
    $this->_select .= ', ' . "{$sumOfAggregates} as {$fieldAlias}";
    $this->_columnHeaders[$fieldAlias] = [
      'title' => ts('Total'),
      'type' => CRM_Utils_Type::T_INT,
    ];

    $this->_statFields[] = $fieldAlias;
  }

  /**
   * Overrides function in base class.
   *
   * This function is overridden to allow date fields to be part
   * of fields to be selected in the column header fields which is not
   * possible in the original function in base class.
   *
   * @param array $specs
   *   Specifications.
   * @param string $tableName
   *   Table name.
   * @param string|null $daoName
   *   DAO name.
   * @param string|null $tableAlias
   *   Table alias.
   * @param array $defaults
   *   Defaults.
   * @param array $options
   *   Options.
   *
   * @return array
   *   Column lists.
   */
  protected function buildColumns(array $specs, $tableName, $daoName = NULL, $tableAlias = NULL, array $defaults = [], array $options = []) {

    if (!$tableAlias) {
      $tableAlias = str_replace('civicrm_', '', $tableName);
    }
    $types = [
      'filters',
      'group_bys',
      'order_bys',
      'join_filters',
      'aggregate_columns',
      'aggregate_rows',
    ];
    $columns = [$tableName => array_fill_keys($types, [])];
    if (!empty($daoName)) {
      $columns[$tableName]['bao'] = $daoName;
    }
    $columns[$tableName]['alias'] = $tableAlias;
    $exportableFields = $this->getMetadataForFields(['dao' => $daoName]);

    foreach ($specs as $specName => $spec) {
      $spec['table_key'] = $tableName;
      unset($spec['default']);
      if (empty($spec['name'])) {
        $spec['name'] = $specName;
      }
      if (empty($spec['dbAlias'])) {
        $spec['dbAlias'] = $tableAlias . '.' . $spec['name'];
      }
      $daoSpec = CRM_Utils_Array::value($spec['name'], $exportableFields, CRM_Utils_Array::value($tableAlias . '_' . $spec['name'], $exportableFields, []));
      $spec = array_merge($daoSpec, $spec);
      if (!isset($columns[$tableName]['table_name']) && isset($spec['table_name'])) {
        $columns[$tableName]['table_name'] = $spec['table_name'];
      }

      if (!isset($spec['operatorType'])) {
        $spec['operatorType'] = $this->getOperatorType($spec['type'], $spec);
      }
      foreach (array_merge($types, ['fields']) as $type) {
        if (isset($options[$type]) && !empty($spec['is_' . $type])) {
          // Options can change TRUE to FALSE for a field, but not vice versa.
          $spec['is_' . $type] = $options[$type];
        }
        if (!isset($spec['is_' . $type])) {
          $spec['is_' . $type] = FALSE;
        }
      }

      $fieldAlias = (empty($options['no_field_disambiguation']) ? $tableAlias . '_' : '') . $specName;
      $spec['alias'] = $tableName . '_' . $fieldAlias;
      if ($this->isPivot && (!empty($spec['options']) || $spec['operatorType'] == CRM_Report_Form::OP_DATE)) {
        $spec['is_aggregate_columns'] = TRUE;
        $spec['is_aggregate_rows'] = TRUE;

        if ($spec['operatorType'] == CRM_Report_Form::OP_DATE) {
          $this->aggregateDateFields[] = $fieldAlias;
        }
      }
      $columns[$tableName]['metadata'][$fieldAlias] = $spec;
      $columns[$tableName]['fields'][$fieldAlias] = $spec;
      if (isset($defaults['fields_defaults']) && in_array($spec['name'], $defaults['fields_defaults'])) {
        $columns[$tableName]['metadata'][$fieldAlias]['is_fields_default'] = TRUE;
      }

      if (empty($spec['is_fields']) || (isset($options['fields_excluded']) && in_array($specName, $options['fields_excluded']))) {
        $columns[$tableName]['fields'][$fieldAlias]['no_display'] = TRUE;
      }

      if (!empty($spec['is_filters']) && !empty($spec['statistics']) && !empty($options) && !empty($options['group_by'])) {
        foreach ($spec['statistics'] as $statisticName => $statisticLabel) {
          $columns[$tableName]['filters'][$fieldAlias . '_' . $statisticName] = array_merge($spec, [
            'title' => ts('Aggregate filter : ') . $statisticLabel,
            'having' => TRUE,
            'dbAlias' => $tableName . '_' . $fieldAlias . '_' . $statisticName,
            'selectAlias' => "{$statisticName}({$tableAlias}.{$spec['name']})",
            'is_fields' => FALSE,
            'is_aggregate_field_for' => $fieldAlias,
          ]);
          $columns[$tableName]['metadata'][$fieldAlias . '_' . $statisticName] = $columns[$tableName]['filters'][$fieldAlias . '_' . $statisticName];
        }
      }

      foreach ($types as $type) {
        if (!empty($spec['is_' . $type])) {
          if ($type === 'join_filters') {
            $fieldAlias = 'join__' . $fieldAlias;
          }
          $columns[$tableName][$type][$fieldAlias] = $spec;
          if (isset($defaults[$type . '_defaults']) && isset($defaults[$type . '_defaults'][$spec['name']])) {
            $columns[$tableName]['metadata'][$fieldAlias]['default'] = $defaults[$type . '_defaults'][$spec['name']];
          }
        }
      }
    }
    $columns[$tableName]['prefix'] = isset($options['prefix']) ? $options['prefix'] : '';
    $columns[$tableName]['prefix_label'] = isset($options['prefix_label']) ? $options['prefix_label'] : '';
    if (isset($options['group_title'])) {
      $groupTitle = $options['group_title'];
    }
    else {
      // We can make one up but it won't be translated....
      $groupTitle = ucfirst(str_replace('_', ' ', str_replace('civicrm_', '', $tableName)));
    }
    $columns[$tableName]['group_title'] = $groupTitle;

    return $columns;
  }

  /**
   * Overrides function in base class.
   *
   * Function is overrridden to allow row total to be re-calculated
   * since the SQL WITH ROLLUP Group function does not yield reliable
   * results for the row totals based on new Data aggregate functions
   * introduced.
   *
   * @param array $rows
   *   Result rows.
   * @param bool $pager
   *   Pager.
   */
  public function formatDisplay(array &$rows, $pager = TRUE) {
    // Set pager based on if any limit was applied in the query.
    if ($pager) {
      $this->setPager();
    }

    // Unset columns not to be displayed.
    foreach ($this->_columnHeaders as $key => $value) {
      if (!empty($value['no_display'])) {
        unset($this->_columnHeaders[$key]);
      }
    }

    // Unset columns not to be displayed.
    if (!empty($rows)) {
      foreach ($this->_noDisplay as $noDisplayField) {
        foreach ($rows as $rowNum => $row) {
          unset($this->_columnHeaders[$noDisplayField]);
        }
      }
    }

    // Build array of section totals.
    $this->sectionTotals();

    // Adjust row total.
    $this->adjustRowTotal($rows);

    // Process grand-total row.
    $this->grandTotal($rows);

    // Use this method for formatting rows for display purpose.
    $this->alterDisplay($rows);
    CRM_Utils_Hook::alterReportVar('rows', $rows, $this);

    // Use this method for formatting custom rows for display purpose.
    $this->alterCustomDataDisplay($rows);
  }

  /**
   * Use the options for the field to map the display value.
   *
   * @param string $value
   *   Value of the field.
   * @param array $row
   *   Row display values.
   * @param string $selectedField
   *   Selected field.
   * @param string $criteriaFieldName
   *   Criteria field name.
   * @param array $specs
   *   Specifications of the column.
   *
   * @return string
   *   Label of the option ids.
   */
  public function alterFromOptions($value, array &$row, $selectedField, $criteriaFieldName, array $specs) {
    if ($specs['data_type'] == 'ContactReference') {
      if (!empty($row[$selectedField]) && $row[$selectedField] !== 'NULL') {
        return CRM_Contact_BAO_Contact::displayName($row[$selectedField]);
      }
      return $row[$selectedField];
    }
    $value = trim($value, CRM_Core_DAO::VALUE_SEPARATOR);
    $options = $this->getCustomFieldOptions($specs);
    if (strpos($value, CRM_Core_DAO::VALUE_SEPARATOR) === FALSE) {
      return CRM_Utils_Array::value($value, $options, $value);
    }
    else {
      $values = explode(CRM_Core_DAO::VALUE_SEPARATOR, $value);
      $labels = [];
      foreach ($values as $val) {
        $labels[] = CRM_Utils_Array::value($val, $options, $val);
      }
      return implode(' | ', $labels);
    }
  }

  /**
   * Adjusts row total.
   *
   * Since we have introduced other data aggregate functions like COUNT UNIQUE,
   * SUM,the SQL WITH ROLLUP Group function does not yield reliable results
   * for the row totals.
   * This function sums the individual column totals and adjusts the total
   * accordingly.
   *
   * @param array $rows
   *   Result rows.
   */
  private function adjustRowTotal(array &$rows) {
    if (empty($rows)) {
      return;
    }
    // The rollup row is the last row.
    end($rows);
    $rollupRowKey = key($rows);
    reset($rows);
    $rollupRow = $rows[$rollupRowKey];
    unset($rows[$rollupRowKey]);
    $adjustedRollup = [];
    foreach ($rollupRow as $key => $value) {
      $adjustedRollup[$key] = array_sum(array_column($rows, $key));
    }

    $rows[$rollupRowKey] = $adjustedRollup;
  }

  /**
   * Overriden so we can add some more default values.
   *
   * @param bool $freeze
   *   TO freeze or not.
   *
   * @return array
   *   Default values.
   */
  public function setDefaultValues($freeze = TRUE) {
    parent::setDefaultValues();
    if (empty($this->_id)) {
      $this->_defaults['data_function'] = 'COUNT';
      $this->_defaults['aggregate_column_date_grouping'] = 'month';
      $suffix = $this->_aliases[$this->_baseTable] == 'civicrm_contact' ? '_contact_id' : '_id';
      $this->_defaults['data_function_field'] = $this->_aliases[$this->_baseTable] . $suffix;
      $this->_defaults['charts'] = FALSE;
    }

    return $this->_defaults;
  }

  /**
   * Overrides function in base class.
   *
   * Overridden so that when custom fields are selected to be aggregated on,
   * the SQL joins for the custom field table will be included in
   * the overral query.
   *
   * @param string $table
   *   Table name.
   *
   * @return bool
   *   If custom table or not.
   */
  protected function isCustomTableSelected($table) {
    $selected = array_merge(
      $this->getSelectedFilters(),
      $this->getSelectedFields(),
      $this->getSelectedOrderBys(),
      $this->getSelectedAggregateRows(),
      $this->getSelectedDataFunctionField(),
      $this->getSelectedAggregateColumns(),
      $this->getSelectedGroupBys()
    );
    foreach ($selected as $spec) {
      if ($spec['table_name'] == $table) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Returns the metadata for the selected data function field.
   *
   * @return array
   *   Selected data field.
   */
  protected function getSelectedDataFunctionField() {
    $metadata = $this->getMetadataByType('metadata');
    if (empty($this->_params['data_function_field']) || !isset($metadata[$this->_params['data_function_field']])) {
      return [];
    }

    return [$this->_params['data_function_field'] => $metadata[$this->_params['data_function_field']]];
  }

  /**
   * Overrides function in base class.
   *
   * Overridden so that the template file name is gotten from the
   * extended report class within Civicase.
   *
   * @return string
   *   Template file name.
   */
  public function getTemplateFileName() {
    $defaultTpl = parent::getTemplateFileName();

    if (in_array($this->_outputMode, ['print', 'pdf'])) {
      if ($this->_params['templates']) {
        $defaultTpl = 'CRM/Civicase/Form/Report/CustomTemplates/' . $this->_params['templates'] . '.tpl';
      }
    }

    if (!CRM_Utils_File::isIncludable('templates/' . $defaultTpl)) {
      $defaultTpl = 'CRM/Report/Form.tpl';
    }

    if ($this->filterPane) {
      $defaultTpl = 'CRM/Report/Form/Tabs/FilterPane.tpl';
    }

    return $defaultTpl;
  }

  /**
   * Overridden to allow date row date fields to be grouped on month/year.
   *
   * @param string $tableAlias
   *   Table alias.
   * @param array $selectedField
   *   Selected field.
   * @param string $fieldAlias
   *   Field alias.
   * @param string $title
   *   Title.
   */
  protected function addRowHeader($tableAlias, array $selectedField, $fieldAlias, $title = '') {
    if (empty($tableAlias)) {
      // Add a fake value just to save lots of code to calculate whether
      // a comma is required later.
      $this->_select = 'SELECT 1 ';
      $this->_rollup = NULL;
      $this->_noGroupBY = TRUE;
      return;
    }

    $this->_select = "SELECT {$selectedField['dbAlias']} as $fieldAlias ";
    if ($selectedField['type'] == CRM_Report_Form::OP_DATE) {
      $dateGrouping = $this->_params['aggregate_row_date_grouping'];
      if (!empty($dateGrouping)) {
        $this->_select = "SELECT DATE_FORMAT({$selectedField['dbAlias']}, '{$this->dateSqlGrouping[$dateGrouping]}') as $fieldAlias";
      }
    }

    if (!in_array($fieldAlias, $this->_groupByArray)) {
      $this->_groupByArray[] = $fieldAlias;
    }
    $this->_groupBy = "GROUP BY $fieldAlias " . $this->_rollup;
    $this->_columnHeaders[$fieldAlias] = ['title' => $title];
    $key = array_search($fieldAlias, $this->_noDisplay);
    if (is_int($key)) {
      unset($this->_noDisplay[$key]);
    }
  }

  /**
   * Overrides function in base class.
   *
   * This function is overridden so that we can a report class
   * can define additional extra filters and modify the where clause.
   */
  public function storeWhereHavingClauseArray() {
    $filters = $this->getSelectedFilters();
    foreach ($filters as $filterName => $field) {
      if (!empty($field['pseudofield'])) {
        continue;
      }
      $clause = NULL;
      $clause = $this->generateFilterClause($field, $filterName);
      if (!empty($clause)) {
        $this->whereClauses[$filterName] = $clause;
        if (CRM_Utils_Array::value('having', $field)) {
          $this->_havingClauses[$filterName] = $clause;
        }
        else {
          $this->_whereClauses[] = $clause;
        }
      }
    }

    $this->addAdditionalFiltersToWhereClause();
  }

  /**
   * Overrides function in base class.
   *
   * This function is overridden so as to allow the extending report
   * class to provide the filters template to use for the filters.
   *
   * Also overridden to allow fields extending contacts, i.e custom
   * fields and contact fields to be sorted into a separate array so that
   * when more than one contact entity is joined to the report, the filter
   * fields can be organized and displayed per contact entity.
   */
  public function addFilters() {
    foreach (['filters', 'join_filters'] as $filterString) {
      $filters = $filterGroups = [];
      $filterExtendsContactGroup = [];
      $filtersGroupedByTableKeys = [];
      $filterPaneGroups = [];
      $count = 1;
      foreach ($this->getMetadataByType($filterString) as $fieldName => $field) {
        $table = $field['table_name'];
        $groupTitle = $this->_columns[$field['table_key']]['group_title'];
        $paneName = preg_replace("/[^A-Z0-9_-]/i", '', $groupTitle);
        $filterExtendsContact = FALSE;
        if ($filterString === 'filters') {
          if ($this->filterPane && $this->filterPane != $paneName) {
            continue;
          }
          $filterExtendsContact = (!empty($field['extends']) &&
              in_array($field['extends'], [
                'Individual',
                'Household',
                'Organization',
              ])) ||
            $field['table_name'] == 'civicrm_contact';
          $filterGroups[$table] = [
            'group_title' => $groupTitle,
            'pane_name' => $paneName,
            'use_accordian_for_field_selection' => TRUE,
            'group_extends_contact' => $filterExtendsContact,
          ];
          if (!empty($_POST["hidden_{$paneName}"]) ||
            CRM_Utils_Array::value("hidden_{$paneName}", $this->_formValues)
          ) {
            $filterGroups[$table]['open'] = 'true';
          }
          $filterPaneGroups[$paneName] = [
            'table_name' => $table,
            'group_extends_contact' => $filterExtendsContact,
          ];
          if ($filterExtendsContact) {
            $filterExtendsContactGroup[$field['table_key']] = [
              'group_field_label' => !empty($this->_columns[$field['table_key']]['prefix_label']) ? $this->_columns[$field['table_key']]['prefix_label'] : '',
            ];
          }
        }
        $prefix = ($filterString === 'join_filters') ? 'join_filter_' : '';
        $filters[$table][$prefix . $fieldName] = $field;
        if ($filterExtendsContact) {
          $filtersGroupedByTableKeys[$table][$field['table_key']][$prefix . $fieldName] = $field;
        }
        if ($filterGroups[$table]['open'] == 'true' || $this->filterPane && $this->filterPane == $paneName) {
          $this->addFilterFieldsToReport($field, $fieldName, $table, $count, $prefix);
        }
      }

      if (!empty($filters) && $filterString == 'filters') {
        $this->tabs['Filters'] = [
          'title' => ts('Filters'),
          'tpl' => $this->getFiltersTemplateName(),
          'div_label' => 'set-filters',
        ];
        $this->assign('filterGroups', $filterGroups);
        $this->assign('filterExtendsContactGroup', $filterExtendsContactGroup);
        $this->assign('filtersGroupedByTableSets', $filtersGroupedByTableKeys);
        $this->assign('filterPaneGroups', $filterPaneGroups);
        $this->assign('currentPath', CRM_Utils_System::currentPath());
      }
      $this->assign($filterString, $filters);
    }
  }

  /**
   * Overrides function in base class.
   *
   * This function is overridden so that the additional filters provided by
   * report class extending this class will be part of the statistics filter
   * array and the label and values will be visible on the report UI.
   *
   * Also data function and data aggregate field are added to the
   * groups statistics array.
   *
   * @return array
   *   Statistics data.
   */
  public function statistics(&$rows) {
    $stats = parent::statistics($rows);

    foreach ($this->getAdditionalFilterFields() as $key => $value) {
      if (!empty($this->_params[$key])) {
        $stats['filters'][] = [
          'title' => $value['label'],
          'value' => 'is equal to ' . $this->_params[$key],
        ];
      }
    }

    $stats['groups'][] = [
      'title' => 'Aggregate Function',
      'value' => $this->_params['data_function'],
    ];

    if ($this->_params['data_function'] !== 'COUNT') {
      $stats['groups'][] = [
        'title' => 'Aggregate Field On',
        'value' => $this->getTitleForAggregateOnField(),
      ];
    }

    return $stats;
  }

  /**
   * Allows the selected field to be altered.
   *
   * The ID is replaced with the field option label for display
   * in the results set.
   *
   * @param mixed $value
   *   Value data.
   * @param array $row
   *   Result row.
   * @param string $selectedField
   *   Selected field.
   * @param mixed $fieldAlterMap
   *   Field alter map.
   * @param array $fieldSpecs
   *   Field specifications.
   *
   * @return mixed
   *   Altered row field display.
   */
  protected function alterGenericSelect($value, array $row, $selectedField, $fieldAlterMap, array $fieldSpecs) {
    return $this->alterRowFieldDisplay($value, $fieldSpecs);
  }

  /**
   * Allows the a field to be altered.
   *
   * The field ID replaced with the field label for display
   * in the results set.
   *
   * @param mixed $value
   *   Value data.
   * @param array $fieldSpecs
   *   Field specs.
   *
   * @return mixed
   *   Altered row display.
   */
  private function alterRowFieldDisplay($value, array $fieldSpecs) {
    if (empty($fieldSpecs['options'])) {
      return 'NULL';
    }

    $options = $fieldSpecs['options'];
    $value = trim($value, CRM_Core_DAO::VALUE_SEPARATOR);
    return CRM_Utils_Array::value($value, $options, 'NULL');
  }

  /**
   * Returns the field title for the aggregate on field.
   *
   * @return string
   *   Aggregate field title.
   */
  private function getTitleForAggregateOnField() {
    $dataFunctionField = $this->_params['data_function_field'];
    $specs = $this->getMetadataByType('metadata')[$dataFunctionField];

    return $specs['title'];
  }

  /**
   * Overridden for base form class.
   *
   * Overridden so that when filter panes are opened, only filter fields
   * related to such pane is open a quick exit can be done and the form
   * is suppressed rather than loading the whole form regions.
   */
  public function buildQuickForm() {
    $this->filterPane = CRM_Utils_Array::value('filterPane', $_GET);
    if (!$this->filterPane) {
      parent::buildQuickForm();
    }
    else {
      $this->filterPane = CRM_Utils_Array::value('filterPane', $_GET);
      if ($this->filterPane) {
        $this->addFilters();
      }
      $this->add('hidden', "hidden_{$this->filterPane}", 1);
      $this->assign('filterPane', $this->filterPane);
      $this->assign('suppressForm', TRUE);
    }
  }

  /**
   * Setter for $_params.
   *
   * Overridden from base file so that we can add opened pane hidden values
   * to form values to be stored for report instances. This will ensure that
   * when next the report instance is opened, those panes are automatically
   * expanded.
   *
   * @param array $params
   *   Params.
   */
  public function setParams(array $params) {
    if (empty($params)) {
      $this->_params = $params;
      return;
    }
    $extendedFieldKeys = $this->getConfiguredFieldsFlatArray();
    if (!empty($extendedFieldKeys)) {
      $fields = $params['fields'];
      if (isset($this->_formValues['extended_fields'])) {
        foreach ($this->_formValues['extended_fields'] as $index => $extended_field) {
          $fieldName = $extended_field['name'];
          if (!isset($fields[$fieldName])) {
            unset($this->_formValues['extended_fields'][$index]);
          }
        }
        $fieldsToAdd = array_diff_key($fields, $extendedFieldKeys);
        foreach (array_keys($fieldsToAdd) as $fieldName) {
          $this->_formValues['extended_fields'][] = [
            'name' => $fieldName,
            'title' => $this->getMetadataByType('fields')[$fieldName]['title'],
          ];
        }
        // We use array_merge to re-index from 0.
        $params['extended_fields'] = array_merge($this->_formValues['extended_fields']);
      }
    }
    $params['order_bys'] = $params['extended_order_bys'] = $this->getConfiguredOrderBys($params);
    // Renumber from 0.
    $params['extended_order_bys'] = array_merge($params['extended_order_bys']);

    $paneValues = array_filter($this->_submitValues, function ($key) {
      return strpos($key, 'hidden_') === 0;
    }, ARRAY_FILTER_USE_KEY);
    $this->_params = array_merge($params, $paneValues);
  }

}
