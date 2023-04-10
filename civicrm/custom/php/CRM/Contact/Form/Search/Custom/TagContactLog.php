<?php

class CRM_Contact_Form_Search_Custom_TagContactLog
  extends CRM_Contact_Form_Search_Custom_Base
  implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;
  protected $_columns;

  function __construct(&$formValues) {
    parent::__construct($formValues);

    $this->_columns = [
      ts('Tag Name') => 'tag_name',
      ts('Tag Count') => 'tag_count',
    ];
  }


  function buildForm(&$form) {
    $this->setTitle('Tag Count Search');

    $tagType = [
      '1' => ts('Keywords'),
      '2' => ts('Issue Codes'),
      '3' => ts('Legislative Positions'),
      '4' => ts('Website Bills'),
      '5' => ts('Website Committees'),
      '6' => ts('Website Issues'),
      '7' => ts('Website Petitions'),
    ];
    $form->add('select',
      'tag_type',
      ts('Tag Type'),
      $tagType,
      true
    );
    
    $form->addDate('start_date', ts('Date from'), false, ['formatType' => 'birth']);
    $form->addDate('end_date', ts('Date to'), false, ['formatType' => 'birth']);

    $actionType = [
      '1' => ts('Added'),
      '2' => ts('Removed/Deleted'),
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
  }//buildForm


  public function formRule($fields, $files, $self): bool|array {
    $errors = [];
    return empty($errors) ? true : $errors;
  }//formRule


  function summary() {
    return null;
  }


  function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = FALSE) {
    return $this->all($offset, $rowcount, $sort, FALSE, TRUE);
  }


  function all($offset = 0, $rowcount = 0, $sort = NULL,
               $includeContactIDs = FALSE, $justIDs = FALSE) {
    if ($justIDs) {
      $selectClause = "tag.id as contact_id";
      $sort = 'tag.id';
      $group = 'GROUP BY contact_id';
    }
    else {
      $selectClause = "
        tag.id as contact_id,
        tag.name as tag_name,
        COUNT(contact_a.id) as tag_count
      ";
      $sort = 'tag_name';
      $group = 'GROUP BY contact_id, tag_name';
    }
    
    //CRM_Core_Error::debug('select',$selectClause); exit();
    $sql = $this->sql($selectClause,
      $offset, $rowcount, $sort,
      $includeContactIDs, $group
    );
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

    $from = "
      FROM civicrm_contact contact_a
      JOIN {$logDB}.log_civicrm_entity_tag log_et
        ON contact_a.id = log_et.entity_id
        AND log_et.entity_table = 'civicrm_contact'
        AND log_et.log_action != 'Initialization'
      JOIN civicrm_tag tag
        ON log_et.tag_id = tag.id
        AND {$tagTypeSql}
    ";

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
