<?php

class CRM_Contact_Form_Search_Custom_TagContactLog
  extends CRM_Contact_Form_Search_Custom_Base
  implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;
  protected $_columns;
  public array $_entityTypes;
  public array $_tagTypes;
  public string $_currentEntityType;

  function __construct(&$formValues) {
    parent::__construct($formValues);

    $this->_columns = [
      ts('Tag Name') => 'tag_name',
      ts('Tag Count') => 'tag_count',
    ];

    $this->_entityTypes = [
      'contacts' => ts('Contacts'),
      'activities' => ts('Activities'),
      'cases' => ts('Cases'),
    ];

    $this->_tagTypes = [
      1 => ts('Keywords'),
      2 => ts('Issue Codes'),
      3 => ts('Legislative Positions'),
      4 => ts('Website Bills'),
      5 => ts('Website Committees'),
      6 => ts('Website Issues'),
      7 => ts('Website Petitions'),
    ];
  }

  function buildForm(&$form) {
    $this->setTitle('Tag Count Search');

    $form->add('select', 'entity', ts('Tag Entity'),
      $this->_entityTypes, TRUE, [
        'class' => 'crm-select2 huge',
        'multiple' => TRUE,
        'placeholder' => ts('- select -'),
      ]);


    $form->add('select',
      'tag_type',
      ts('Tag Type'),
      $this->_tagTypes,
      true
    );

    $form->add('datepicker', 'start_date', ts('Date from'), [], FALSE, ['time' => FALSE]);
    $form->add('datepicker', 'end_date', ts('Date to'), [], FALSE, ['time' => FALSE]);

    $actionType = [
      1 => ts('Added'),
      2 => ts('Removed/Deleted'),
    ];
    $form->addRadio('action_type', ts('Action Type'), $actionType, NULL, '&nbsp;', TRUE);

    $formfields = [
      'start_date',
      'end_date',
      'tag_type',
      'action_type',
    ];
    $form->assign('elements', $formfields);

    $form->add('hidden', 'form_message');

    $form->setDefaults($this->setDefaultValues());

    //9990
    $formValues = $form->get('formValues');
    if (!empty($formValues)) {
      $quickExportUrl = CRM_Utils_System::url('civicrm/search/custom/tagcontact/quickexport',
        http_build_query(['formValues' => $formValues]));
      $form->assign('quickExportUrl', $quickExportUrl);
    }
  }

  public function formRule($fields, $files, $self): bool|array {
    $errors = [];
    return empty($errors) ? true : $errors;
  }

  function summary() {
    return null;
  }

  function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = FALSE) {
    return $this->all($offset, $rowcount, $sort, FALSE, TRUE);
  }

  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    //Civi::log()->debug(__METHOD__, ['$this->_formValues' => $this->_formValues, 'sort' => $sort]);
    //NOTE: tag.id is being aliased as the contact_id to fake out the custom search model

    //we need to query each entity types separately and the join with a union query
    $unionQueryParts = [];

    //store the sort so we can add it later
    $orderBy = ($sort && is_object($sort)) ? $sort->orderBy() : $sort;
    $orderBySql = ($orderBy) ? "ORDER BY {$orderBy}" : NULL;
    //Civi::log()->debug(__METHOD__, ['orderBy' => $orderBy, 'orderBySql' => $orderBySql]);

    //reset $sort so it isn't applied to the inner query
    $sort = NULL;

    foreach ($this->_formValues['entity'] as $entity) {
      //we set this here so we can use it in from() to condition appropriately
      $this->_currentEntityType = $entity;

      if ($justIDs) {
        $selectClause = "tag.id as contact_id";
        $sort = 'tag.id';
        $group = 'GROUP BY contact_id';
      }
      else {
        switch ($entity) {
          case 'contacts':
            $selectClause = "
            contact_a.id as entity_id,
            tag.id as contact_id,
            tag.name as tag_name
          ";
            break;

          case 'activities':
            $selectClause = "
            a.id entity_id,
            tag.id as contact_id,
            tag.name as tag_name
          ";
            break;

          case 'cases':
            $selectClause = "
            c.id entity_id,
            tag.id as contact_id,
            tag.name as tag_name
          ";
            break;

          default:
        }

        $group = 'GROUP BY tag.id, tag_name, entity_id';
      }

      //build inner query
      $sql = $this->sql($selectClause,
        $offset, $rowcount, $sort,
        $includeContactIDs, $group
      );

      //remove LIMIT clause
      $unionQueryParts[] = str_replace('LIMIT 0, 50', '', $sql);
    }

    $unionQuery = implode("\n UNION ALL \n", $unionQueryParts);

    $sql = "
      SELECT contact_id, tag_name, COUNT(contact_id) tag_count
      FROM (
        {$unionQuery}
      ) base
      GROUP BY contact_id, tag_name
      {$orderBySql}
      LIMIT {$offset}, {$rowcount}
    ";

    //CRM_Core_Error::debug_var('$sql',$sql);
    return $sql;
  }


  function from() {
    //CRM_Core_Error::debug_var('$this->_formValues', $this->_formValues);

    $bbconfig = get_bluebird_instance_config();
    //CRM_Core_Error::debug_var('$bbconfig', $bbconfig);

    $logDB = $bbconfig['db.log.prefix'].$bbconfig['db.basename'];

    $parentTags = self::getParentTags();

    switch ($this->_formValues['tag_type']) {
      case 1:
        $parentId = array_search('Keywords', $parentTags);
        $tagTypeSql = "parent_id = {$parentId}";
        break;

      case 2:
        //issue codes need to be dealt with differently because they don't have a common parent
        $parentId = array_search('Issue Codes', $parentTags);
        $tagsetIDs = $parentTags;
        unset($tagsetIDs[$parentId]);
        $tagsetIDsList = implode(',', array_keys($tagsetIDs));
        $tagTypeSql = "parent_id IS NOT NULL AND parent_id NOT IN ({$tagsetIDsList})";
        break;

      case 3:
        $parentId = array_search('Positions', $parentTags);
        $tagTypeSql = "parent_id = {$parentId}";
        break;

      case 4:
        $parentId = array_search('Website Bills', $parentTags);
        $tagTypeSql = "parent_id = {$parentId}";
        break;

      case 5:
        $parentId = array_search('Website Committees', $parentTags);
        $tagTypeSql = "parent_id = {$parentId}";
        break;

      case 6:
        $parentId = array_search('Website Issues', $parentTags);
        $tagTypeSql = "parent_id = {$parentId}";
        break;

      case 7:
        $parentId = array_search('Website Petitions', $parentTags);
        $tagTypeSql = "parent_id = {$parentId}";
        break;
    }

    switch ($this->_currentEntityType) {
      case 'contacts':
        $from = "
          FROM civicrm_contact contact_a
          JOIN {$logDB}.log_civicrm_entity_tag log_et
            ON contact_a.id = log_et.entity_id
            AND log_et.entity_table = 'civicrm_contact'
            AND log_et.log_action != 'Initialization'
          JOIN civicrm_tag tag
            ON log_et.tag_id = tag.id
            AND tag.{$tagTypeSql}
        ";
        break;
      case 'activities':
        $from = "
          FROM civicrm_contact contact_a
          JOIN civicrm_activity_contact ac
            ON contact_a.id = ac.contact_id
            AND record_type_id = 3
          JOIN civicrm_activity a
            ON ac.activity_id = a.id
          JOIN {$logDB}.log_civicrm_entity_tag log_et
            ON a.id = log_et.entity_id
            AND log_et.entity_table = 'civicrm_activity'
            AND log_et.log_action != 'Initialization'
          JOIN civicrm_tag tag
            ON log_et.tag_id = tag.id
            AND tag.{$tagTypeSql}
        ";
        break;

      case 'cases':
        $from = "
          FROM civicrm_contact contact_a
          JOIN civicrm_case_contact cc
            ON contact_a.id = cc.contact_id
          JOIN civicrm_case c
            ON cc.case_id = c.id
          JOIN {$logDB}.log_civicrm_entity_tag log_et
            ON c.id = log_et.entity_id
            AND log_et.entity_table = 'civicrm_case'
            AND log_et.log_action != 'Initialization'
          JOIN civicrm_tag tag
            ON log_et.tag_id = tag.id
            AND tag.{$tagTypeSql}
        ";
        break;

      default:
    }

    return $from;
  }//from


  function where($includeContactIDs = false) {
    //CRM_Core_Error::debug('formVals', $this->_formValues);exit();
    $params = [];

    $start_date = CRM_Utils_Date::mysqlToIso(CRM_Utils_Date::processDate($this->_formValues['start_date']));
    $end_date = CRM_Utils_Date::mysqlToIso(CRM_Utils_Date::processDate($this->_formValues['end_date'], null, false, 'Ymd'));

    //add filters by start/end date
    if ($start_date) {
      $where[] = "log_et.log_date >= '$start_date' ";
    }
    if ($end_date) {
      $where[] = "log_et.log_date <= '$end_date 23:59:59' ";
    }

    switch ($this->_formValues['action_type']) {
      case 1:
        $where[] = "(log_et.log_action = 'Insert') ";
        break;
      case 2:
        $where[] = "(log_et.log_action = 'Delete') ";
        break;
    }

    switch ($this->_currentEntityType) {
      case 'activities':
        $where[] = "a.is_deleted = 0 ";
        break;
      case 'cases':
        $where[] = "c.is_deleted = 0 ";
        break;
      default:
    }

    //standard clauses
    $where[] = "contact_a.is_deleted = 0 ";
    $where[] = "contact_a.is_deceased = 0 ";

    if (!empty($where)) {
      $whereClause = implode(' AND ', $where);
    }
    else {
      $whereClause = '';
    }
    //CRM_Core_Error::debug_var('whereClause', $whereClause);

    return $this->whereClause($whereClause, $params);
  }


  function count() {
    $sql = $this->all();
    $dao = CRM_Core_DAO::executeQuery($sql);
    return $dao->N;
  }


  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom/TagContactLog.tpl';
  }


  function setDefaultValues() {
    $defaults = [
      'action_type' => 1,
      'entity' => 1,
    ];
    return $defaults;
  }


  function alterRow(&$row) {
    //CRM_Core_Error::debug_var('row', $row);
  }


  function setTitle($title) {
    if ($title) {
      CRM_Utils_System::setTitle($title);
    }
    else {
      CRM_Utils_System::setTitle(ts('Search'));
    }
  }


  //9990
  static function quickExport() {
    //CRM_Core_Error::debug_var('$_REQUEST', $_REQUEST);

    if (!empty($_REQUEST['formValues'])) {
      $_REQUEST['is_quick_export'] = true;
      $formValues = $_REQUEST['formValues'];

      CRM_Export_BAO_Export::exportCustom($formValues['customSearchClass'],
        $formValues,
        'sort_name'
      );
    }
  }//quickExport


  function getParentTags() {
    $parentTags = [];
    $dao = CRM_Core_DAO::executeQuery("
      SELECT id, name
      FROM civicrm_tag
      WHERE parent_id IS NULL
    ");

    while ($dao->fetch()) {
      $parentTags[$dao->id] = $dao->name;
    }

    return $parentTags;
  }//getParentTags
}
