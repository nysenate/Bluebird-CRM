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
}

function reports_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  /*Civi::log()->debug('reports_civicrm_validateForm', [
    'formName' => $formName,
    //'form' => $form,
    'fields' => $fields,
    'errors' => $errors,
  ]);*/
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

        default:
      }

      break;

    default:
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
      'custom_56' => [
        'name' => 'neighborhood_56',
        'title' => 'Neighborhood',
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
      'custom_56' => [
        'name' => 'neighborhood_56',
        'title' => 'Neighborhood',
        'operator' => 'like',
        'type' => CRM_Report_Form::OP_STRING,
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
