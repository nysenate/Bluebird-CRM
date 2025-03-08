<?php

require_once 'reports.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function reports_civicrm_config(&$config) {
  _reports_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function reports_civicrm_xmlMenu(&$files) {
  _reports_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function reports_civicrm_install() {
  _reports_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function reports_civicrm_uninstall() {
  _reports_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function reports_civicrm_enable() {
  _reports_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function reports_civicrm_disable() {
  _reports_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function reports_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _reports_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function reports_civicrm_managed(&$entities) {
  _reports_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function reports_civicrm_caseTypes(&$caseTypes) {
  _reports_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function reports_civicrm_angularModules(&$angularModules) {
_reports_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function reports_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _reports_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function reports_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('reports_civicrm_buildForm', array(
    '$formName' => $formName,
    '$form' => $form,
  ));*/

  if (strpos($formName, 'CRM_Report_Form_') !== FALSE) {
    CRM_Core_Resources::singleton()
      ->addStyleFile('gov.nysenate.reports', 'css/Reports.css');

    if ($form->elementExists('grouprole')) {
      $ele = &$form->getElement('grouprole');
      _reports_GroupRole($ele);
    }
  }

  //14267 include Address custom data
  if ($formName == 'CRM_Report_Form_Case_Summary') {
    $form->set('_customGroupExtends', ['Case', 'Address']);
  }

  if (strpos($formName, 'CRM_Report_Form_') !== FALSE) {
    CRM_Core_Resources::singleton()->addScriptUrl('/sites/all/modules/nyss_reports/js/reports.js');

    //set header/footer defaults
    $printHeaderRegion = CRM_Core_Region::instance('default-report-header', FALSE);
    $htmlHeader = ($printHeaderRegion) ? $printHeaderRegion->render('', FALSE) : '';
    $config = CRM_Core_Config::singleton();
    $defaults = [
      'report_header' => "<html>
        <head>
          <title>Bluebird Report</title>
          <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
          <style type=\"text/css\">@import url({$config->userFrameworkResourceURL}css/print.css);</style>
          {$htmlHeader}
        </head>
        <body><div id=\"crm-container\">",
      'report_footer' => '</div></body></html>',
    ];
    $form->setDefaults($defaults);

    CRM_Core_Resources::singleton()->addScript("
      cj('tr.crm-report-instanceForm-form-block-permission a.helpicon').remove();
    ");

    //11536 limit parent_id to reports branch
    if ($form->elementExists('parent_id')) {
      $ele =& $form->getElement('parent_id');
      foreach ($ele->_options as $k => $opt) {
        if ($opt['text'] == '- select -' || $opt['text'] == 'Reports') {}
        else {
          unset($ele->_options[$k]);
        }
      }
    }
  }
}

function reports_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  /*Civi::log()->debug('reports_civicrm_validateForm', [
    'formName' => $formName,
    //'form' => $form,
    'fields' => $fields,
    'errors' => $errors,
  ]);*/
}

function reports_civicrm_pageRun(&$page) {
  //Civi::log()->debug('pageRun', array('$page' => $page));

  //5166 remove contact logging detail report from template list
  if (is_a($page, 'CRM_Report_Page_TemplateList')) {
    CRM_Core_Resources::singleton()->addScript("
    cj('a[href=\"/civicrm/report/logging/contact/detail?reset=1\"]').parents('tr').remove();
    
    //4987
    cj('.crm-report-templateList-description').removeAttr('style');
  ");
  }
}

function reports_civicrm_queryObjects(&$queryObjects, $type) {
  if ($type == 'Report') {
    $queryObjects[] = new CRM_NYSS_Reports_BAO_Query();
  }
}

function reports_civicrm_alterReportVar($varType, &$var, &$object) {
  /*Civi::log()->debug('alterReportVar', [
    'varType' => $varType,
    'var' => $var,
    //'object' => $object,
  ]);*/

  $class = get_class($object);
  switch ($varType) {
    case 'columns':
      //common modifications
      _reports_Base_cols($var, $object);

      switch ($class) {
        case 'CRM_Report_Form_Case_Detail':
          _reports_CaseDetail_col($var, $object);
          break;

        case 'CRM_Report_Form_Case_Summary':
          _reports_CaseSummary_col($var, $object);
          break;

        case 'CRM_Report_Form_Mailing_Summary':
          _reports_MailingSummary_col($var, $object);
          break;

        case 'CRM_Report_Form_Activity':
          _reports_Activity_cols($var, $object);
          break;

        case 'CRM_Report_Form_ActivitySummary':
          _reports_ActivitySummary_cols($var, $object);
          break;

        case 'CRM_Report_Form_Case_Demographics':
          _reports_CaseDemographics_cols($var, $object);
          break;

        default:
      }

      if (array_key_exists('civicrm_address', $var)) {
        _reports_DistrictInfo_col($var, $object);
      }

      break;

    case 'sql':
      switch ($class) {
        case 'CRM_Report_Form_Contact_LoggingSummary':
          _reports_LoggingSummary_sql($var, $object);
          break;

        case 'CRM_Report_Form_Case_Detail':
          $object->optimisedForOnlyFullGroupBy = FALSE;
          _reports_CaseDetail_sql($var, $object);
          break;

        case 'CRM_Report_Form_Case_Summary':
          _reports_CaseSummary_sql($var, $object);
          break;

        default:
      }

      if (array_key_exists('civicrm_address', $var->getVar('_columns'))) {
        _reports_DistrictInfo_sql($var, $object);
      }

      break;

    case 'rows':
      switch ($class) {
        case 'CRM_Report_Form_Mailing_Summary':
          _reports_MailingSummary_rows($var, $object);
          break;

        case 'CRM_Report_Form_Contact_LoggingDetail':
          _reports_LoggingDetails_rows($var, $object);
          break;

        case 'CRM_Report_Form_Case_Summary':
          _reports_CaseSummary_rows($var, $object);
          break;

        case 'CRM_Report_Form_Activity':
          _reports_Activity_rows($var, $object);
          break;

        default:
      }

      break;

    default:
  }
}

function reports_civicrm_alterLogTables(&$logTableSpec) {
  //fix error when viewing log detail report; these shouldn't be referenced in this context
  if (strpos(current_path(), 'civicrm/report/instance') !== FALSE) {
    unset($logTableSpec['civicrm_batch']);
    unset($logTableSpec['civicrm_mailing_abtest']);
    unset($logTableSpec['civicrm_campaign']);
    unset($logTableSpec['civicrm_survey']);
    unset($logTableSpec['civicrm_event_carts']);
    unset($logTableSpec['civicrm_dedupe_exception']);
    unset($logTableSpec['civicrm_custom_group']);
    unset($logTableSpec['civicrm_tag']);
    unset($logTableSpec['civicrm_print_label']);
    unset($logTableSpec['civicrm_group']);
    unset($logTableSpec['civicrm_group_organization']);
    unset($logTableSpec['civicrm_contribution_page']);
    unset($logTableSpec['civicrm_membership_type']);
    unset($logTableSpec['civicrm_report_instance']);
    unset($logTableSpec['civicrm_uf_group']);
    unset($logTableSpec['civicrm_mailing']);
    unset($logTableSpec['civicrm_event']);
  }
}

function _reports_Base_cols(&$var, &$object) {
  if (isset($var['civicrm_address'])) {
    unset($var['civicrm_address']['fields']['country_id']);
    unset($var['civicrm_address']['fields']['county_id']);

    unset($var['civicrm_address']['filters']['country_id']);
    unset($var['civicrm_address']['filters']['county_id']);

    $var['civicrm_address']['order_bys']['street_number']['title'] = 'Street Number';
    //$var['civicrm_address']['order_bys']['street_unit'] = NULL;
  }
}

/**
 * @param $ele
 *
 * simplify the list of groups/roles in the Access tab
 * note: passed by reference
 */
function _reports_GroupRole(&$ele) {
  //Civi::log()->debug('', ['ele' => $ele]);

  $permittedRoles = [
    'Office Administrator',
    'Office Manager',
    'Staff',
    'Data Entry',
    'Volunteer',
    'Mailing Approver',
    'Mailing Creator',
    'Mailing Scheduler',
    'Mailing Viewer',
    'Manage Bluebird Inbox',
    'Analytics User',
    'Conference Services',
  ];

  foreach ($ele->_options as $key => &$opt) {
    if (!in_array($opt['text'], $permittedRoles)) {
      unset($ele->_options[$key]);
    }
  }
}

/*
 * 12173 see also CRM_NYSS_Reports_BAO_Query
 * this ensures tag/group logs aren't duplicated as contact entries
 * we need to do this because we trigger a modified_date update for those
 * objects which causes them to show up as contact updates
 */
function _reports_LoggingSummary_sql(&$var, &$object) {
  if ($var->getVar('currentLogTable') == 'log_civicrm_contact') {
    $from = $var->getVar('_from');
    $where = $var->getVar('_where');

    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $loggingDB = $dsn['database'];

    //change extra_join to LEFT JOIN
    $from = str_replace("INNER JOIN `{$loggingDB}`.log_civicrm_entity_tag",
      "LEFT JOIN `{$loggingDB}`.log_civicrm_entity_tag", $from);
    $from = str_replace("INNER JOIN `{$loggingDB}`.log_civicrm_group_contact",
      "LEFT JOIN `{$loggingDB}`.log_civicrm_group_contact", $from);
    $var->setVar('_from', $from);

    $where .= " AND extra_table.id IS NULL AND extra_table_2.id IS NULL";
    $var->setVar('_where', $where);
  }
}

function _reports_LoggingDetails_rows(&$var, &$object) {
  //11730 remove Modified/Created date from log detail report
  foreach ($var as $rowNum => $row) {
    if (strpos($row['field'][0], 'Modified Date') !== FALSE ||
      strpos($row['field'][0], 'Created Date') !== FALSE
    ) {
      unset($var[$rowNum]);
    }
  }
}

function _reports_CaseDetail_col(&$var, &$object) {
  $var['civicrm_tag'] = [
    'dao' => 'CRM_Core_DAO_Tag',
    'fields' => [
      'id' => [
        'required' => TRUE,
        'no_display' => TRUE,
      ],
      'name' => [
        'title' => ts('Tag Name'),
        'default' => TRUE,
        'no_repeat' => TRUE,
      ],
    ],
    'grouping' => 'case-fields',
  ];

  //13469
  $var['civicrm_address']['fields']['city'] = [];
  $var['civicrm_address']['fields']['postal_code'] = [];

  //4942 filter case roles
  $caseRoleIDs = [8, 13, 14, 15];
  $currentRoles = $var['civicrm_relationship']['filters']['case_role']['options'];
  $roles = array_intersect_key($currentRoles, array_flip($caseRoleIDs));
  $var['civicrm_relationship']['filters']['case_role']['options'] = $roles;

  //5102
  $var['civicrm_case']['order_bys'] = [
    'subject' => [
      'title' => ts('Subject'),
    ],
    'start_date' => [
      'title' => ts('Start Date'),
    ],
    'end_date' => [
      'title' => ts('End Date'),
    ],
    'status_id' => [
      'title' => ts('Case Status'),
    ],
    'case_type_name' => [
      'title' => ts('Case Type'),
    ],
  ];

  $var['civicrm_contact']['fields']['client_sort_name']['title'] = ts('Contact Name');
  $var['civicrm_contact']['filters']['sort_name']['title'] = ts('Contact Name');
  $var['civicrm_contact']['order_bys'] = [
    'sort_name' => [
      'title' => ts('Contact Name'),
    ],
  ];

  $var['civicrm_relationship']['order_bys'] = [
    'case_role' => [
      'title' => ts('Case Role(s)'),
      'name' => 'relationship_type_id',
    ],
  ];

  $var['civicrm_address']['fields']['city']['title'] = 'City';
  $var['civicrm_address']['fields']['postal_code']['title'] = 'Postal Code';
  unset($var['civicrm_address']['fields']['country_id']);
  unset($var['civicrm_address']['filters']['country_id']);
  $var['civicrm_address']['filters']['state_province_id'] = [
    'title' => ts( 'State/Province' ),
    'type' => CRM_Utils_Type::T_INT,
    'operatorType' => CRM_Report_Form::OP_MULTISELECT,
    'options' => CRM_Core_PseudoConstant::stateProvince(),
  ];
  $var['civicrm_address']['filters']['city'] = [
    'title' => 'City',
    'type' => CRM_Utils_Type::T_TEXT,
  ];
  $var['civicrm_address']['filters']['postal_code'] = [
    'title' => 'Postal Code',
    'type' => CRM_Utils_Type::T_TEXT,
  ];
  $var['civicrm_address']['order_bys']['street_address'] = [
    'title' => ts('Street Address'),
  ];
  $var['civicrm_address']['order_bys']['city'] = [
    'title' => ts('City'),
  ];
  $var['civicrm_address']['order_bys']['postal_code'] = [
    'title' => ts('Postal Code'),
  ];

  unset($var['civicrm_worldregion']);
  unset($var['civicrm_country']);
}

function _reports_CaseDetail_sql(&$var, &$object) {
  /*Civi::log()->debug('alterReportVar', array(
    'var' => $var,
    //'object' => $object,
  ));*/

  $from = $var->getVar('_from');
  $from .= "
    LEFT JOIN civicrm_entity_tag
      ON civicrm_entity_tag.entity_id = {$var->getVar('_aliases')['civicrm_case']}.id
      AND civicrm_entity_tag.entity_table LIKE '%civicrm_case%'
    LEFT JOIN civicrm_tag tag_civireport
      ON civicrm_entity_tag.tag_id = tag_civireport.id
  ";

  $var->setVar('_from', $from);

  $selectClauses = &$var->_selectClauses;
  foreach ($selectClauses as &$clause) {
    switch ($clause) {
      case 'tag_civireport.id as civicrm_tag_id':
        $clause = 'GROUP_CONCAT(DISTINCT tag_civireport.id) as civicrm_tag_id';
        break;
      case 'tag_civireport.name as civicrm_tag_name':
        $clause = 'GROUP_CONCAT(DISTINCT tag_civireport.name SEPARATOR ", ") as civicrm_tag_name';
        break;
      default:
    }
  }

  $select = $var->_select;
  $select = str_replace(', tag_civireport.id as civicrm_tag_id',
    ', GROUP_CONCAT(DISTINCT tag_civireport.id) as civicrm_tag_id', $select);
  $select = str_replace(', tag_civireport.name as civicrm_tag_name',
    ', GROUP_CONCAT(DISTINCT tag_civireport.name SEPARATOR ", ") as civicrm_tag_name', $select);
  $var->_select = $select;

  $groupBy = $var->_groupBy;
  $var->_groupBy = str_replace(', tag_civireport.id', '', $groupBy);
}

function _reports_CaseSummary_col(&$var, &$object) {
  //Civi::log()->debug(__FUNCTION__, ['var' => $var]);

  //12635
  $relTypes = CRM_Utils_Array::index(['name_a_b'], CRM_Core_PseudoConstant::relationshipType('name'));
  $var['civicrm_relationship']['filters']['relationship_type_id']['default'] = [$relTypes['Case Manager']['id']];

  //4940
  asort($var['civicrm_relationship']['filters']['relationship_type_id']['options']);

  //14267
  $var['civicrm_address'] = [
    'dao' => 'CRM_Core_DAO_Address',
    'fields' => [
      'id' => [
        'title' => ts('Address ID'),
        'no_display' => TRUE,
        'required' => TRUE,
      ],
      'street_address' => [
        'title' => ts('Street Address'),
      ],
      'supplemental_address_1' => [
        'title' => ts('Mailing Address'),
      ],
      'city' => [
        'title' => ts('City'),
      ],
      'state_province_id' => [
        'title' => ts('State/Province'),
      ],
      'postal_code' => [
        'title' => ts('Postal Code'),
      ],
    ],
    'grouping' => 'address-fields',
  ];

  //14378
  $var['civicrm_case']['order_bys']['id']['title'] = ts('Case ID');

  //11866 add relationship status field
  $var['civicrm_relationship']['filters']['is_active'] = [
    'title' => ts('Staff Relationship Status'),
    'operatorType' => CRM_Report_Form::OP_SELECT,
    'options' => [
      '' => ts('- Any -'),
      1 => ts('Active'),
      0 => ts('Inactive'),
    ],
    'type' => CRM_Utils_Type::T_INT,
    'default' => 1,
  ];
}

function _reports_CaseSummary_sql(&$var, &$object) {
  //Civi::log()->debug(__FUNCTION__, ['var' => $var]);

  //14267 - regenerate from, append address, then re-append custom data
  $object->from();
  $from = $var->getVar('_from');
  $from .= "
    LEFT JOIN civicrm_address address_civireport
      ON case_contact_civireport.contact_id = address_civireport.contact_id
      AND address_civireport.is_primary = 1
  ";
  $var->setVar('_from', $from);
  $object->customDataFrom();
}

function _reports_CaseSummary_rows(&$var, &$object) {
  //Civi::log()->debug(__FUNCTION__, ['var' => $var]);

  //14256
  foreach ($var as $rowNum => &$row) {
    //break out of loop if these fields are not even present
    if (!isset($row['civicrm_c2_client_name']) && !isset($row['civicrm_c2_id'])) {
      break;
    }

    //if values present, construct link to contact
    if (!empty($row['civicrm_c2_client_name']) && !empty($row['civicrm_c2_id'])) {
      $url = CRM_Utils_System::url('civicrm/contact/view',
        'reset=1&cid=' . $row['civicrm_c2_id'],
        $object->_absoluteUrl
      );
      $row['civicrm_c2_client_name_link'] = $url;
      $row['civicrm_c2_client_name_hover'] = ts('View Contact Record');
    }

    $object->alterDisplayAddressFields($row, $var, $rowNum, NULL, NULL);
  }

}

function _reports_CaseDemographics_cols(&$var, &$object) {
  $var['civicrm_contact']['order_bys'] = [
    'sort_name' => [
      'title' => ts('Contact Name'),
    ],
    'gender_id' => [
      'title' => ts('Gender'),
    ],
    'birth_date' => [
      'title' => ts('Birth Date'),
    ],
  ];

  $var['civicrm_email']['order_bys'] = [
    'email' => [
      'title' => ts('Email'),
    ],
  ];

  //4936
  $var['civicrm_address']['order_bys']= [
    'street_address' => [
      'title' => ts('Street Address'),
    ],
    'city' => [
      'title' => ts('City'),
    ],
    'postal_code' => [
      'title' => ts('Postal Code'),
    ],
    'state_province_id' => [
      'title' => ts('State/Province'),
    ],
  ];

  $var['civicrm_phone']['order_bys']= [
    'phone' => [
      'title' => ts('Phone'),
    ],
  ];

  unset($var['civicrm_activity']['fields']['id']['title']);
  unset($var['civicrm_case']['fields']['id']['title']);
  $var['civicrm_case']['fields']['id']['no_display'] = TRUE;

  $var['civicrm_case']['order_bys'] = [
    'start_date' =>
      ['title' => ts('Case Start'),],
    'end_date' =>
      ['title' => ts('Case End'),],
  ];

  unset($var['civicrm_value_attachments_5']);

  unset($var['civicrm_value_constituent_information_1']['fields']['professional_accreditations_16']);
  unset($var['civicrm_value_constituent_information_1']['fields']['interest_in_volunteering__17']);
  unset($var['civicrm_value_constituent_information_1']['fields']['active_constituent__18']);
  unset($var['civicrm_value_constituent_information_1']['fields']['friend_of_the_senator__19']);
  unset($var['civicrm_value_constituent_information_1']['fields']['skills_areas_of_interest_20']);
  unset($var['civicrm_value_constituent_information_1']['fields']['honors_and_awards_21']);
  unset($var['civicrm_value_constituent_information_1']['fields']['boe_date_of_registration_24']);
  unset($var['civicrm_value_constituent_information_1']['fields']['other_gender_45']);
  unset($var['civicrm_value_constituent_information_1']['fields']['ethnicity1_58']);
  unset($var['civicrm_value_constituent_information_1']['fields']['other_ethnicity_62']);

  $var['civicrm_value_constituent_information_1']['order_bys'] = [
    'voter_registration_status_23' => [
      'title' => ts('Voter Registration Status')
    ],
    'individual_category_42' => [
      'title' => ts('Individual Category')
    ],
    'contact_source_60' => [
      'title' => ts('Contact Source')
    ],
    'religion_63' => [
      'title' => ts('Religion')
    ],
    'record_type_61' => [
      'title' => ts('Record Type')
    ],
  ];

  unset($var['civicrm_value_contact_details_8']);
  unset($var['civicrm_value_website_profile_9']);
}

//12558
function _reports_DistrictInfo_col(&$var, &$object) {
  $var['civicrm_value_district_information_7'] = [
    'alias' => 'district_info',
    'grouping' => 'civicrm_value_district_information_7',
    'group_title' => 'District Information',
    'extends' => 'Address',
    'fields' => [
      'custom_46' => [
        'name' => 'congressional_district_46',
        'title' => 'Congressional District',
      ],
      'custom_47' => [
        'name' => 'ny_senate_district_47',
        'title' => 'Senate District',
      ],
      'custom_48' => [
        'name' => 'ny_assembly_district_48',
        'title' => 'Assembly District',
      ],
      'custom_49' => [
        'name' => 'election_district_49',
        'title' => 'Election District',
      ],
      'custom_50' => [
        'name' => 'county_50',
        'title' => 'County',
      ],
      'custom_51' => [
        'name' => 'county_legislative_district_51',
        'title' => 'County Legislative District',
      ],
      'custom_52' => [
        'name' => 'town_52',
        'title' => 'Town',
      ],
      'custom_53' => [
        'name' => 'ward_53',
        'title' => 'Ward',
      ],
      'custom_54' => [
        'name' => 'school_district_54',
        'title' => 'School District',
      ],
      'custom_55' => [
        'name' => 'new_york_city_council_55',
        'title' => 'New York City Council',
      ],
    ],
    'filters' => [
      'custom_46' => [
        'name' => 'congressional_district_46',
        'title' => 'Congressional District',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_INT,
      ],
      'custom_47' => [
        'name' => 'ny_senate_district_47',
        'title' => 'Senate District',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_INT,
      ],
      'custom_48' => [
        'name' => 'ny_assembly_district_48',
        'title' => 'Assembly District',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_INT,
      ],
      'custom_49' => [
        'name' => 'election_district_49',
        'title' => 'Election District',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_INT,
      ],
      'custom_50' => [
        'name' => 'county_50',
        'title' => 'County',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_INT,
      ],
      'custom_51' => [
        'name' => 'county_legislative_district_51',
        'title' => 'County Legislative District',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_INT,
      ],
      'custom_52' => [
        'name' => 'town_52',
        'title' => 'Town',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_STRING,
      ],
      'custom_53' => [
        'name' => 'ward_53',
        'title' => 'Ward',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_INT,
      ],
      'custom_54' => [
        'name' => 'school_district_54',
        'title' => 'School District',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_INT,
      ],
      'custom_55' => [
        'name' => 'new_york_city_council_55',
        'title' => 'New York City Council',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_INT,
      ],
    ],
  ];
}

function _reports_DistrictInfo_sql(&$var, &$object) {
  /*Civi::log()->debug('_reports_DistrictInfo_sql', array(
    'var' => $var,
    'object' => $object,
  ));*/

  $from = $var->getVar('_from');
  $aliases = $var->getVar('_aliases');

  if (strpos($from, 'civicrm_value_district_information_7') === FALSE) {
    $from .= "
      LEFT JOIN civicrm_value_district_information_7 {$aliases['civicrm_value_district_information_7']}
        ON {$aliases['civicrm_address']}.id = {$aliases['civicrm_value_district_information_7']}.entity_id
    ";
    $var->setVar('_from', $from);
  }
}

function _reports_MailingSummary_col(&$var, &$object) {
  unset($var['civicrm_mailing_event_unsubscribe']['fields']['unsubscribe_count']);

  $var['civicrm_mailing']['fields']['category']['title'] = 'Mailing Category';
}

function _reports_MailingSummary_rows(&$var, &$object) {
  //13187 mailing category
  $catOpts = _reports_CategoryOpts();
  foreach ($var as &$row) {
    $row['civicrm_mailing_category'] = CRM_Utils_Array::value($row['civicrm_mailing_category'], $catOpts);
  }
}

function _reports_CategoryOpts() {
  $mCats = [];
  $opts = CRM_Core_DAO::executeQuery("
    SELECT ov.label, ov.value
    FROM civicrm_option_value ov
    JOIN civicrm_option_group og
      ON ov.option_group_id = og.id
      AND og.name = 'mailing_categories'
    ORDER BY ov.label
  ");
  while ($opts->fetch()) {
    $mCats[$opts->value] = $opts->label;
  }

  return $mCats;
}

function _reports_Activity_cols(&$var, &$object) {
  //8396
  $var['civicrm_contact']['fields']['contact_tag_name'] = [
    'name' => 'id',
    'alias' => 'civicrm_contact_target',
    'dbAlias' => "civicrm_contact_target.id",
    'title' => ts('Contact Tags'),
  ];

  //7540/12067
  $var['civicrm_activity']['fields']['activity_type_id_orderby'] = $var['civicrm_activity']['fields']['activity_type_id'];
  $var['civicrm_activity']['fields']['activity_type_id_orderby']['name'] = 'activity_type_id';
  $var['civicrm_activity']['fields']['activity_type_id_orderby']['no_display'] = TRUE;
  $var['civicrm_activity']['fields']['activity_type_id']['required'] = FALSE;
  $var['civicrm_activity']['order_bys']['activity_type_id_orderby'] = $var['civicrm_activity']['order_bys']['activity_type_id'];
  $var['civicrm_activity']['order_bys']['activity_type_id_orderby']['dbAlias'] =
    str_replace('civicrm_activity_activity_type_id', 'civicrm_activity_activity_type_id_orderby', $var['civicrm_activity']['order_bys']['activity_type_id_orderby']['dbAlias']);
  unset($var['civicrm_activity']['order_bys']['activity_type_id']);

  $var['civicrm_activity']['fields']['activity_date_time_orderby'] = $var['civicrm_activity']['fields']['activity_date_time'];
  $var['civicrm_activity']['fields']['activity_date_time_orderby']['name'] = 'activity_date_time';
  $var['civicrm_activity']['fields']['activity_date_time_orderby']['no_display'] = TRUE;
  $var['civicrm_activity']['fields']['activity_date_time']['required'] = FALSE;
  $var['civicrm_activity']['order_bys']['activity_date_time_orderby'] = $var['civicrm_activity']['order_bys']['activity_date_time'];
  $var['civicrm_activity']['order_bys']['activity_date_time_orderby']['dbAlias'] = 'civicrm_activity_activity_date_time_orderby';
  unset($var['civicrm_activity']['order_bys']['activity_date_time']);

  $var['civicrm_activity']['fields']['details']['default'] = TRUE;

  //8396
  $var['civicrm_activity']['fields']['activity_tag_name'] = [
    'name' => 'id',
    'title' => ts('Activity Tags'),
  ];

  $var['civicrm_activity']['order_bys']['activity_subject'] = [
    'title' => ts('Activity Subject'),
    'dbAlias' => 'civicrm_activity_activity_subject',
  ];
  $var['civicrm_activity']['order_bys']['status_id'] = [
    'title' => ts('Activity Status'),
    'dbAlias' => 'civicrm_activity_status_id',
  ];
}

function _reports_ActivitySummary_cols(&$var, &$object) {
  //4921
  $activityTypes = CRM_Core_PseudoConstant::activityType(true, true, false, 'label', true);
  asort( $activityTypes );
  $var['civicrm_activity']['filters']['activity_type_id']['options'] = $activityTypes;
}

function _reports_Activity_rows(&$var, &$object) {
  //get activity and contact tags
  $object->_tags = CRM_Core_BAO_Tag::getTagsUsedFor(['civicrm_contact', 'civicrm_activity'], true, false, 296);
  //Civi::log()->debug('_nyss_reports_Activity_rows', array('object->_tags' => $object->_tags));

  foreach ($var as $rowNum => &$row) {
    //8396
    if (array_key_exists('civicrm_activity_activity_tag_name', $row)) {
      $actTags = CRM_Core_BAO_EntityTag::getTag($row['civicrm_activity_activity_tag_name'], 'civicrm_activity');
      foreach ($actTags as $k => &$v) {
        $v = $object->_tags[$k];
      }
      $row['civicrm_activity_activity_tag_name'] = implode(', ', $actTags);
    }

    if (array_key_exists('civicrm_contact_contact_tag_name', $row)) {
      $conTags = CRM_Core_BAO_EntityTag::getTag($row['civicrm_contact_contact_tag_name'], 'civicrm_contact');
      foreach ($conTags as $k => &$v) {
        //only display keywords; we retrieved entire list earlier, so if not in list, exclude
        if (isset($object->_tags[$k])) {
          $v = $object->_tags[$k];
        }
        else {
          unset($conTags[$k]);
        }
      }
      $row['civicrm_contact_contact_tag_name'] = implode(', ', $conTags);
    }
  }
}
