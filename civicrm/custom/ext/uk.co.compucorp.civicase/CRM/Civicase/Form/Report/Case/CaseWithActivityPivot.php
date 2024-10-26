<?php

/**
 * Class CRM_Extendedreport_Form_Report_Case_CaseWithActivityPivot.
 */
class CRM_Civicase_Form_Report_Case_CaseWithActivityPivot extends CRM_Civicase_Form_Report_BaseExtendedReport {

  /**
   * Report base table.
   *
   * @var string
   */
  protected $_baseTable = 'civicrm_case';

  /**
   * Whether to Skip ACL.
   *
   * @var bool
   */
  protected $skipACL = FALSE;

  /**
   * Custom group aggregates.
   *
   * @var bool
   */
  protected $_customGroupAggregates = TRUE;

  /**
   * Whether aggregates include NULL.
   *
   * @var bool
   */
  protected $_aggregatesIncludeNULL = TRUE;

  /**
   * Aggregates and Total.
   *
   * @var bool
   */
  protected $_aggregatesAddTotal = TRUE;

  /**
   * With Rollup.
   *
   * @var string
   */
  protected $_rollup = 'WITH ROLLUP';

  /**
   * Temp table.
   *
   * @var string
   */
  protected $_temporary = ' TEMPORARY ';

  /**
   * Aggregate add percentage.
   *
   * @var bool
   */
  protected $_aggregatesAddPercentage = TRUE;

  /**
   * To drill down report.
   *
   * @var array
   */
  public $_drilldownReport = [];

  /**
   * If report is Pivot.
   *
   * @var bool
   */
  protected $isPivot = TRUE;

  /**
   * If no fields.
   *
   * @var bool
   */
  protected $_noFields = TRUE;

  /**
   * Potential criteria.
   *
   * @var array
   */
  protected $_potentialCriteria = [];

  /**
   * Case contact meta data.
   *
   * @var array
   */
  protected $caseRoleContactMetaData = [];

  /**
   * Tag filter table name.
   *
   * @var string
   */
  protected $_tagFilterTable = 'civicrm_case';

  /**
   * CRM_Civicase_Form_Report_Case_CaseWithActivityPivot constructor.
   */
  public function __construct() {
    $this->setCaseRolesContactMetaData();
    $this->_customGroupExtended['civicrm_case'] = [
      'extends' => ['Case'],
      'filters' => TRUE,
      'title' => ts('Case'),
    ];
    $this->_customGroupExtended['civicrm_activity'] = [
      'extends' => ['Activity'],
      'filters' => TRUE,
      'title' => ts('Activity'),
    ];

    $caseColumns = $this->getColumns('Case', ['fields' => FALSE]);
    $caseClientContactColumns = $this->getColumns('Contact', ['prefix_label' => 'Case Client - ', 'group_title' => 'Contacts']);
    $activityColumns = $this->_columns = $this->getColumns('Activity', ['fields' => FALSE]);
    $caseRolesContactColumns = $this->getCaseRolesContactColumns();
    $caseTagColumn = $this->getColumns('CaseTag', ['group_title' => 'Case Tags']);

    $this->_columns = $caseColumns + $caseClientContactColumns + $activityColumns + $caseRolesContactColumns + $caseTagColumn;
    $this->_columns['civicrm_case']['fields']['id']['required'] = TRUE;
    $this->_columns['civicrm_contact']['fields']['id']['required'] = TRUE;
    $this->_columns['civicrm_case']['fields']['id']['title'] = 'Case';
    $this->_columns['civicrm_contact']['fields']['gender_id']['no_display'] = TRUE;
    $this->_columns['civicrm_contact']['fields']['gender_id']['title'] = 'Gender';

    parent::__construct();
    $this->addAdditionFilterFields();
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   From clauses.
   */
  public function fromClauses() {
    return [
      'contact_from_case',
      'activity_from_case',
      'relationship_from_case',
      'case_role_contact',
      'case_tags',
    ];
  }

  /**
   * SQL condition to JOIN to the relationship table.
   *
   * It takes into consideration active relationships and only
   * joins to a relationship that is still active.
   * The as at date parameter when present will only join to case roles
   * that with the as at date between the case roles start and end dates.
   */
  public function joinRelationshipFromCase() {
    $date = !empty($this->_params['as_at_date']) ? $this->_params['as_at_date'] : date('Y-m-d');
    $activeStatus = !empty($this->_params['as_at_date']) ? "0, 1" : "1";
    $this->_from .= "
      LEFT JOIN civicrm_relationship crt
      ON (
        {$this->_aliases['civicrm_case']}.id = crt.case_id AND
        {$this->_aliases['civicrm_contact']}.id = crt.contact_id_a AND
        crt.is_active IN({$activeStatus}) AND
        (crt.start_date IS NULL OR crt.start_date <= '{$date}') AND
        (crt.end_date IS NULL OR crt.end_date >= '{$date}')
       )";
  }

  /**
   * SQL query condition to JOIN to the contact table.
   *
   * For each of the case roles contacts based on the
   * relationship the role has with the case client.
   */
  protected function joinCaseRolesContact() {
    foreach ($this->caseRoleContactMetaData as $data) {
      $tableAlias = $data['table_prefix'] . 'civicrm_contact';
      $this->_from .= "
      LEFT JOIN civicrm_contact $tableAlias
      ON (
         crt.contact_id_b = {$tableAlias}.id AND
         crt.relationship_type_id = {$data['relationship_type_id']}
      )";
    }
  }

  /**
   * Joins to the Entity Tag table from the case table.
   */
  public function joinEntityTagFromCase() {
    $this->_from .= "
      LEFT JOIN civicrm_entity_tag {$this->_aliases['civicrm_entity_tag']}
      ON ({$this->_aliases['civicrm_case']}.id = {$this->_aliases['civicrm_entity_tag']}.entity_id
      AND {$this->_aliases['civicrm_entity_tag']}.entity_table = '{$this->_tagFilterTable}')";
  }

  /**
   * Adds some meta information for Case Roles contacts for case types.
   *
   * This information will be used to build the columns and add the
   * tables needed to Join to the contact records for the contacts
   * having these case role relationship with the case client.
   */
  protected function setCaseRolesContactMetaData() {
    $caseRolesMetaData = [];
    $caseRolesData = $this->getCaseRoles();
    $relationshipTypeData = civicrm_api3('RelationshipType', 'get', [
      'label_b_a' => ['IN' => array_keys($caseRolesData)],
    ]);

    foreach ($relationshipTypeData['values'] as $relationshipType) {
      $tablePrefix = $this->getDbPrefixFromRoleName($relationshipType['label_b_a']);
      $caseRolesMetaData[$tablePrefix] = [
        'relationship_type_id' => $relationshipType['id'],
        'relationship_name' => $relationshipType['label_b_a'],
        'table_prefix' => $tablePrefix,
      ];
    }

    usort($caseRolesMetaData, function($a, $b) {
      return strcmp($a['relationship_name'], $b['relationship_name']);
    });
    $this->caseRoleContactMetaData = $caseRolesMetaData;
  }

  /**
   * Returns all case roles for active case types.
   *
   * @return array
   *  Case roles
   */
  private function getCaseRoles() {
    $result = civicrm_api3('CaseType', 'get', [
      'is_active' => 1,
      'return' => ['definition', 'id'],
    ]);

    $caseRolesData = [];
    foreach ($result['values'] as $value) {
      if (empty($value['definition']['caseRoles'])) {
        continue;
      }

      $caseRoles = $value['definition']['caseRoles'];
      foreach ($caseRoles as $caseRole) {
        $caseRolesData[$caseRole['name']] = $caseRole;
      }
    }

    return $caseRolesData;
  }

  /**
   * Adds additional filter fields.
   */
  protected function addAdditionFilterFields() {
    $this->add(
      'datepicker',
      'as_at_date',
      ts('As At Date'),
      ['size' => 35],
      FALSE,
      ['time' => FALSE]
    );
  }

  /**
   * Returns the Db prefix that will be used for Case Roles contact table.
   *
   * @param string $roleName
   *   The role name.
   *
   * @return string
   *   Db prefix.
   */
  private function getDbPrefixFromRoleName($roleName) {
    $stringArray = explode(' ', $roleName);

    $prefix = '';
    foreach ($stringArray as $value) {
      $prefix .= strtolower($value) . "_";
    }

    return preg_replace("/[^A-Z0-9_-]/i", '', $prefix);
  }

  /**
   * Returns the contact columns meta data for the case roles for case types.
   *
   * This allows to join to the contact table to get contact
   * and custom field information for contacts having relationships with
   * a case client for.
   *
   * Each case role data is added once and is not duplicated.
   *
   * @return array
   *   The case role contact columns.
   */
  private function getCaseRolesContactColumns() {
    $contactColumns = [];
    foreach ($this->caseRoleContactMetaData as $data) {
      $contactColumns += $this->getColumns(
        'Contact',
        [
          'prefix_label' => "{$data['relationship_name']} - ",
          'group_title' => "Contacts",
          'prefix' => $data['table_prefix'],
        ]
      );
    }

    return $contactColumns;
  }

  /**
   * Function that allows additional filter fields provided by this class.
   *
   * To be added to the where clause for the report.
   */
  protected function addAdditionalFiltersToWhereClause() {
    if (!empty($this->_params['as_at_date'])) {
      $asAtDate = $this->_params['as_at_date'];
      $this->_whereClauses[] =
        " {$this->_aliases['civicrm_case']}.start_date <= '{$asAtDate}' AND
         ({$this->_aliases['civicrm_case']}.end_date >= '{$asAtDate}' OR {$this->_aliases['civicrm_case']}.end_date IS NULL) ";
    }
  }

  /**
   * Returns additional filter fields provided by this report class.
   *
   * @return array
   *   Additional filter fields.
   */
  protected function getAdditionalFilterFields() {
    $fields = [
      'as_at_date' => [
        'label' => 'As At Date',
      ]
    ];

    return $fields;
  }

  /**
   * Returns the name of template file to use for the filters for this class.
   *
   * @return string
   *   Filters template name.
   */
  protected function getFiltersTemplateName() {
    return 'FiltersCiviCase';
  }

}
