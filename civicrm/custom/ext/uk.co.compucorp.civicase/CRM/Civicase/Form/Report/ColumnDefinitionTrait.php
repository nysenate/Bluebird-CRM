<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 9/1/18
 * Time: 1:05 AM
 */

/**
 * Trait CRM_Extendedreport_Form_Report_ColumnDefinitionTrait
 *
 * This trait serves to organise the long getColumns functions into one function.
 *
 * It is for code organisation & may or may not make the most sense long term.
 */
trait CRM_Civicase_Form_Report_ColumnDefinitionTrait {
  /*
 * Function to get Activity Columns
 * @param array $options column options
 */
  /**
   * @param array $options
   *
   * @return array
   */
  function getActivityColumns($options = []) {
    $defaultOptions = [
      'prefix' => '',
      'prefix_label' => '',
      'fields' => TRUE,
      'group_by' => FALSE,
      'order_by' => TRUE,
      'filters' => TRUE,
      'fields_defaults' => [],
      'filters_defaults' => [],
      'group_bys_defaults' => [],
      'order_by_defaults' => [],
    ];

    $options = array_merge($defaultOptions, $options);
    $defaults = $this->getDefaultsFromOptions($options);

    $spec = [
      'id' => [
        'name' => 'id',
        'title' => ts('Activity ID'),
        'is_group_bys' => $options['group_by'],
        'is_fields' => TRUE,
      ],
      'source_record_id' => [
        'name' => 'source_record_id',
        'title' => ts('Source Record ID'),
        'is_fields' => TRUE,
      ],
      'activity_type_id' => [
        'title' => ts('Activity Type'),
        'alter_display' => 'alterActivityType',
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_order_bys' => TRUE,
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'options' => CRM_Core_PseudoConstant::activityType(TRUE, TRUE),
        'name' => 'activity_type_id',
        'type' => CRM_Utils_Type::T_INT,
      ],
      'subject' => [
        'title' => ts('Subject'),
        'name' => 'subject',
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'crm_editable' => [
          'id_table' => 'civicrm_activity',
          'id_field' => 'id',
          'entity' => 'activity',
        ],

      ],
      'activity_date_time' => [
        'title' => ts('Activity Date'),
        'default' => TRUE,
        'name' => 'activity_date_time',
        'operatorType' => CRM_Report_Form::OP_DATE,
        'type' => CRM_Utils_Type::T_DATE,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_order_bys' => TRUE,
      ],
      'status_id' => [
        'title' => ts('Activity Status'),
        'name' => 'status_id',
        'type' => CRM_Utils_Type::T_STRING,
        'alter_display' => 'alterPseudoConstant',
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_order_bys' => TRUE,
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'options' => CRM_Core_PseudoConstant::activityStatus(),
        'crm_editable' => [
          'id_table' => 'civicrm_activity',
          'id_field' => 'id',
          'entity' => 'activity',
          'options' => $this->_getOptions('activity', 'activity_status_id'),
        ],
      ],
      'duration' => [
        'title' => ts('Activity Duration'),
        'name' => 'duration',
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      'details' => [
        'title' => ts('Activity Details'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'type' => CRM_Utils_Type::T_TEXT,
        'crm_editable' => [
          'id_table' => 'civicrm_activity',
          'id_field' => 'id',
          'entity' => 'activity',
        ],

      ],
      'result' => [
        'title' => ts('Activity Result'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'type' => CRM_Utils_Type::T_TEXT,
        'crm_editable' => [
          'id_table' => 'civicrm_activity',
          'id_field' => 'id',
          'entity' => 'activity',
        ],
      ],
      'is_current_revision' => [
        'type' => CRM_Utils_Type::T_BOOLEAN,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'title' => ts("Current Revision"),
        'name' => 'is_current_revision',
        'options' => ['' => '- select -', '1' => 'Yes', '0' => 'No',],
        'is_filters' => TRUE,
      ],
      'is_deleted' => [
        'type' => CRM_Report_Form::OP_INT,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'title' => ts("Is activity deleted"),
        'name' => 'is_deleted',
        'options' => ['' => '- select -', '0' => 'No', '1' => 'Yes',],
        'is_filters' => TRUE,
      ],

    ];
    return $this->buildColumns($spec, $options['prefix'] . 'civicrm_activity', 'CRM_Activity_DAO_Activity', NULL, $defaults, $options);
  }

  /**
   * Get columns for Case.
   *
   * @param $options
   *
   * @return array
   */
  function getCaseColumns($options) {
    $config = CRM_Core_Config::singleton();
    if (!in_array('CiviCase', $config->enableComponents)) {
      return ['civicrm_case' => ['fields' => [], 'metadata' => []]];
    }

    $spec = [
      'civicrm_case' => [
        'fields' => [
          'id' => [
            'title' => ts('Case ID'),
            'name' => 'id',
            'is_fields' => TRUE,
            'is_filters' => TRUE,
          ],
          'subject' => [
            'title' => ts('Case Subject'),
            'default' => TRUE,
            'is_fields' => TRUE,
            'is_filters' => TRUE,
          ],
          'status_id' => [
            'title' => ts('Case Status'),
            'default' => TRUE,
            'name' => 'status_id',
            'is_fields' => TRUE,
            'is_filters' => TRUE,
            'alter_display' => 'alterGenericSelect',
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Case_BAO_Case::buildOptions('case_status_id'),
            'type' => CRM_Utils_Type::T_INT,
          ],
          'case_type_id' => [
            'title' => ts('Case Type'),
            'is_fields' => TRUE,
            'is_filters' => TRUE,
            'alter_display' => 'alterGenericSelect',
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Case_BAO_Case::buildOptions('case_type_id'),
            'name' => 'case_type_id',
            'type' => CRM_Utils_Type::T_INT,
          ],
          'start_date' => [
            'title' => ts('Case Start Date'),
            'name' => 'start_date',
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
            'is_fields' => TRUE,
            'is_filters' => TRUE,
          ],
          'end_date' => [
            'title' => ts('Case End Date'),
            'name' => 'end_date',
            'is_fields' => TRUE,
            'is_filters' => TRUE,
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ],
          'created_date' => [
            'name' => 'created_date',
            'title' => ts('Case Created Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
            'is_fields' => TRUE,
            'is_filters' => TRUE,
          ],
          'modified_date' => [
            'name' => 'created_date',
            'title' => ts('Case Modified Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
            'is_fields' => TRUE,
            'is_filters' => TRUE,
          ],
          'is_deleted' => [
            'name' => 'is_deleted',
            'title' => ts('Case is in the Trash?'),
            'type' => CRM_Utils_Type::T_BOOLEAN,
            'is_fields' => TRUE,
            'is_filters' => TRUE,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => ['' => '- select -'] + CRM_Case_BAO_Case::buildOptions('is_deleted'),
          ],
        ],
      ],

    ];
    // Case is a special word in mysql so pass an alias to prevent it from using case.
    return $this->buildColumns($spec['civicrm_case']['fields'], $options['prefix'] . 'civicrm_case', 'CRM_Case_DAO_Case', 'case_civireport');
  }

  /**
   * @param array $options
   *
   * @return array
   */
  function getContactColumns($options = []) {
    $defaultOptions = [
      'prefix' => '',
      'prefix_label' => '',
      'group_by' => TRUE,
      'order_by' => TRUE,
      'filters' => TRUE,
      'fields' => TRUE,
      'custom_fields' => ['Individual', 'Contact', 'Organization'],
      'fields_defaults' => ['display_name', 'id'],
      'filters_defaults' => [],
      'group_bys_defaults' => [],
      'order_by_defaults' => ['sort_name ASC'],
      'contact_type' => NULL,
    ];

    $options = array_merge($defaultOptions, $options);
    $orgOnly = FALSE;
    if (CRM_Utils_Array::value('contact_type', $options) == 'Organization') {
      $orgOnly = TRUE;
    }
    $tableAlias = $options['prefix'] . 'civicrm_contact';

    $spec = [
      $options['prefix'] . 'display_name' => [
        'name' => 'display_name',
        'title' => ts($options['prefix_label'] . 'Contact Name'),
        'label' => ts('Contact Name'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_order_bys' => TRUE,
      ],
      $options['prefix'] . 'contact_id' => [
        'name' => 'id',
        'title' => ts($options['prefix_label'] . 'Contact ID'),
        'label' => ts('Contact ID'),
        'alter_display' => 'alterContactID',
        'type' => CRM_Utils_Type::T_INT,
        'is_order_bys' => TRUE,
        'is_group_bys' => TRUE,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_contact_filter' => TRUE,
      ],
      $options['prefix'] . 'external_identifier' => [
        'name' => 'external_identifier',
        'title' => ts($options['prefix_label'] . 'External ID'),
        'label' => ts('External ID'),
        'type' => CRM_Utils_Type::T_INT,
        'is_fields' => TRUE,
      ],
      $options['prefix'] . 'sort_name' => [
        'name' => 'sort_name',
        'title' => ts($options['prefix_label'] . 'Contact Name (in sort format)'),
        'label' => ts('Contact Name (in sort format)'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_order_bys' => TRUE,
      ],
      $options['prefix'] . 'contact_type' => [
        'title' => ts($options['prefix_label'] . 'Contact Type'),
        'label' => ts('Contact Type'),
        'name' => 'contact_type',
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'options' => CRM_Contact_BAO_Contact::buildOptions('contact_type'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_group_bys' => TRUE,
      ],
      $options['prefix'] . 'contact_sub_type' => [
        'title' => ts($options['prefix_label'] . 'Contact Sub Type'),
        'label' => ts('Contact Sub Type'),
        'name' => 'contact_sub_type',
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'options' => CRM_Contact_BAO_Contact::buildOptions('contact_sub_type'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_group_bys' => TRUE,
      ],
    ];
    $individualFields = [
      $options['prefix'] . 'first_name' => [
        'name' => 'first_name',
        'title' => ts($options['prefix_label'] . 'First Name'),
        'label' => ts('First Name'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_order_bys' => TRUE,
      ],
      $options['prefix'] . 'middle_name' => [
        'name' => 'middle_name',
        'title' => ts($options['prefix_label'] . 'Middle Name'),
        'label' => ts('Middle Name'),
        'is_fields' => TRUE,
      ],
      $options['prefix'] . 'last_name' => [
        'name' => 'last_name',
        'title' => ts($options['prefix_label'] . 'Last Name'),
        'label' => ts('Last Name'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_order_bys' => TRUE,
      ],
      $options['prefix'] . 'nick_name' => [
        'name' => 'nick_name',
        'title' => ts($options['prefix_label'] . 'Nick Name'),
        'label' => ts('Nick Name'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'is_order_bys' => TRUE,
      ],
      $options['prefix'] . 'gender_id' => [
        'name' => 'gender_id',
        'title' => ts($options['prefix_label'] . 'Gender'),
        'label' => ts('Gender'),
        'options' => CRM_Contact_BAO_Contact::buildOptions('gender_id'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'alter_display' => 'alterGenderID',
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'birth_date' => [
        'name' => 'birth_date',
        'title' => ts($options['prefix_label'] . 'Birth Date'),
        'label' => ts('Birth Date'),
        'operatorType' => CRM_Report_Form::OP_DATE,
        'type' => CRM_Utils_Type::T_DATE,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'age' => [
        'name' => 'age',
        'title' => ts($options['prefix_label'] . 'Age'),
        'label' => ts('Age'),
        'dbAlias' => 'TIMESTAMPDIFF(YEAR, ' . $tableAlias . '.birth_date, CURDATE())',
        'type' => CRM_Utils_Type::T_INT,
        'is_fields' => TRUE,
      ],
      $options['prefix'] . 'do_not_email' => [
        'name' => 'do_not_email',
        'title' => ts($options['prefix_label'] . 'Do Not Email'),
        'label' => ts('Do Not Email'),
        'type' => CRM_Utils_Type::T_BOOLEAN,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => ['' => '- select -', '1' => 'Yes', '0' => 'No',],
        'is_fields' => TRUE,
        'is_filters' => TRUE
      ],
      $options['prefix'] . 'do_not_phone' => [
        'name' => 'do_not_phone',
        'title' => ts($options['prefix_label'] . 'Do Not Phone'),
        'label' => ts('Do Not Phone'),
        'type' => CRM_Utils_Type::T_BOOLEAN,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => ['' => '- select -', '1' => 'Yes', '0' => 'No',],
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'do_not_mail' => [
        'name' => 'do_not_mail',
        'title' => ts($options['prefix_label'] . 'Do Not Mail'),
        'label' => ts('Do Not Mail'),
        'type' => CRM_Utils_Type::T_BOOLEAN,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => ['' => '- select -', '1' => 'Yes', '0' => 'No',],
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'do_not_sms' => [
        'name' => 'do_not_sms',
        'title' => ts($options['prefix_label'] . 'Do Not SMS'),
        'label' => ts('Do Not SMS'),
        'type' => CRM_Utils_Type::T_BOOLEAN,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => ['' => '- select -', '1' => 'Yes', '0' => 'No',],
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'do_not_trade' => [
        'name' => 'do_not_trade',
        'title' => ts($options['prefix_label'] . 'Do Not Trade'),
        'label' => ts('Do Not Trade'),
        'type' => CRM_Utils_Type::T_BOOLEAN,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => ['' => '- select -', '1' => 'Yes', '0' => 'No',],
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'is_opt_out' => [
        'name' => 'is_opt_out',
        'title' => ts($options['prefix_label'] . 'No Bulk Emails (User Opt Out)'),
        'label' => ts('No Bulk Emails (User Opt Out)'),
        'type' => CRM_Utils_Type::T_BOOLEAN,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => ['' => '- select -', '1' => 'Yes', '0' => 'No',],
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'preferred_communication_method' => [
        'name' => 'preferred_communication_method',
        'title' => ts($options['prefix_label'] . 'Preferred Communication Method'),
        'label' => ts('Preferred Communication Method'),
        'options' => CRM_Contact_BAO_Contact::buildOptions('preferred_communication_method'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'alter_display' => 'alterGenericSelect',
        'type' => CRM_Utils_Type::T_STRING,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'preferred_language' => [
        'name' => 'preferred_language',
        'title' => ts($options['prefix_label'] . 'Preferred Language'),
        'label' => ts('Preferred Language'),
        'options' => CRM_Contact_BAO_Contact::buildOptions('preferred_language'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'alter_display' => 'alterGenericSelect',
        'type' => CRM_Utils_Type::T_STRING,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'preferred_mail_format' => [
        'name' => 'preferred_mail_format',
        'title' => ts($options['prefix_label'] . 'Preferred Mail Format'),
        'label' => ts('Preferred Mail Format'),
        'options' => CRM_Contact_BAO_Contact::buildOptions('preferred_mail_format'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'alter_display' => 'alterGenericSelect',
        'type' => CRM_Utils_Type::T_STRING,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'prefix_id' => [
        'name' => 'prefix_id',
        'title' => ts($options['prefix_label'] . 'Individual Prefix'),
        'label' => ts('Individual Prefix'),
        'options' => CRM_Contact_BAO_Contact::buildOptions('prefix_id'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'alter_display' => 'alterGenericSelect',
        'type' => CRM_Utils_Type::T_INT,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'suffix_id' => [
        'name' => 'preferred_mail_format',
        'title' => ts($options['prefix_label'] . 'Individual Suffix'),
        'label' => ts('Individual Suffix'),
        'options' => CRM_Contact_BAO_Contact::buildOptions('suffix_id'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'alter_display' => 'alterGenericSelect',
        'type' => CRM_Utils_Type::T_INT,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'communication_style_id' => [
        'name' => 'communication_style_id',
        'title' => ts($options['prefix_label'] . 'Communication Style'),
        'label' => ts('Communication Style'),
        'options' => CRM_Contact_BAO_Contact::buildOptions('communication_style_id'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'alter_display' => 'alterGenericSelect',
        'type' => CRM_Utils_Type::T_INT,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'is_deceased' => [
        'name' => 'is_deceased',
        'title' => ts($options['prefix_label'] . 'Deceased'),
        'label' => ts('Deceased'),
        'type' => CRM_Utils_Type::T_BOOLEAN,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => ['' => '- select -', '1' => 'Yes', '0' => 'No',],
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'deceased_date' => [
        'name' => 'deceased_date',
        'title' => ts($options['prefix_label'] . 'Deceased Date'),
        'label' => ts('Deceased Date'),
        'operatorType' => CRM_Report_Form::OP_DATE,
        'type' => CRM_Utils_Type::T_DATE,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'primary_contact_id' => [
        'name' => 'primary_contact_id',
        'title' => ts($options['prefix_label'] . 'Household Primary Contact ID'),
        'label' => ts('Household Primary Contact ID'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'is_deleted' => [
        'name' => 'is_deleted',
        'title' => ts($options['prefix_label'] . 'Contact is in Trash'),
        'label' => ts('Contact is in Trash'),
        'type' => CRM_Utils_Type::T_BOOLEAN,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => ['' => '- select -', '0' => 'No', '1' => 'Yes',],
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'created_date' => [
        'name' => 'created_date',
        'title' => ts($options['prefix_label'] . 'Created Date'),
        'label' => ts('Created Date'),
        'operatorType' => CRM_Report_Form::OP_DATE,
        'type' => CRM_Utils_Type::T_DATE,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
      $options['prefix'] . 'modified_date' => [
        'name' => 'modified_date',
        'title' => ts($options['prefix_label'] . 'Modified Date'),
        'label' => ts('Modified Date'),
        'operatorType' => CRM_Report_Form::OP_DATE,
        'type' => CRM_Utils_Type::T_DATE,
        'is_fields' => TRUE,
        'is_filters' => TRUE,
      ],
    ];

    if (!$orgOnly) {
      $spec = array_merge($spec, $individualFields);
    }

    if (!empty($options['custom_fields'])) {
      $this->_customGroupExtended[$options['prefix'] . 'civicrm_contact'] = [
        'extends' => $options['custom_fields'],
        'title' => $options['prefix_label'],
        'filters' => $options['filters'],
        'prefix' => $options['prefix'],
        'prefix_label' => $options['prefix_label'],
      ];
    }

    return $this->buildColumns($spec, $options['prefix'] . 'civicrm_contact', 'CRM_Contact_DAO_Contact', $tableAlias, $this->getDefaultsFromOptions($options), $options);
  }

  /**
   * Function to get Activity Columns.
   *
   * @param array $options column options
   *
   * @return array
   */
  function getLatestActivityColumns($options) {
    $defaultOptions = [
      'prefix' => '',
      'prefix_label' => '',
      'fields' => TRUE,
      'group_by' => FALSE,
      'order_by' => TRUE,
      'filters' => TRUE,
      'defaults' => [
        'country_id' => TRUE,
      ],
    ];
    $options = array_merge($defaultOptions, $options);
    $activityFields['civicrm_activity']['fields'] = [
      'activity_type_id' => [
        'title' => ts('Latest Activity Type'),
        'default' => FALSE,
        'type' => CRM_Utils_Type::T_STRING,
        'alter_display' => 'alterActivityType',
        'is_fields' => TRUE,
      ],
      'activity_date_time' => [
        'title' => ts('Latest Activity Date'),
        'default' => FALSE,
        'is_fields' => TRUE,
      ],
    ];
    return $this->buildColumns($activityFields['civicrm_activity']['fields'], $options['prefix'] . 'civicrm_activity', 'CRM_Activity_DAO_Activity');
  }

  /**
   * Returns the case tag column.
   *
   * @param array $options
   *   Options for generating the columns.
   *
   * @return array
   *   Generated columns.
   */
  public function getCaseTagColumns(array $options) {
    $defaultOptions = [
      'prefix' => '',
      'prefix_label' => '',
      'fields' => TRUE,
      'group_by' => FALSE,
      'order_by' => TRUE,
      'filters' => TRUE,
    ];
    $options = array_merge($defaultOptions, $options);
    $caseTagFields['civicrm_entity_tag']['fields'] = [
      'tag_id' => [
        'title' => ts('Case Tag'),
        'is_fields' => TRUE,
        'is_filters' => TRUE,
        'alter_display' => 'alterGenericSelect',
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'options' => $this->getCaseTags(),
        'name' => 'tag_id',
        'type' => CRM_Utils_Type::T_INT,
      ],
    ];
    return $this->buildColumns($caseTagFields['civicrm_entity_tag']['fields'], $options['prefix'] . 'civicrm_entity_tag', 'CRM_Core_DAO_EntityTag', NULL, [], $options);
  }

  /**
   * Returns tags applicable for cases.
   *
   * @return
   *   The case tags.
   */
  private function getCaseTags() {
    $result = civicrm_api3('Tag', 'get', [
      'used_for' => 'Cases',
    ]);

    if (empty($result['values'])) {
      return [];
    }

    return array_column($result['values'], 'name', 'id');
  }

}
