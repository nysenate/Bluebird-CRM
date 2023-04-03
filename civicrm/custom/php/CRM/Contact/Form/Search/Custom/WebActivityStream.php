<?php

class CRM_Contact_Form_Search_Custom_WebActivityStream
  extends CRM_Contact_Form_Search_Custom_Base
  implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;
  protected $_columns;

  function __construct(&$formValues) {
    parent::__construct($formValues);

    $this->_columns = [
      ts('&nbsp;') => 'contact_type',
      ts('Name') => 'sort_name' ,
      ts('Type') => 'type',
      ts('Date') => 'created_date',
      ts('Details') => 'details',
    ];
  }


  function buildForm(&$form) {
    $this->setTitle('Website Activity Stream Search');

    $form->add('text', 'sort_name', ts('Contact Name'), ['size' => 20]);

    $type = [
      '' => '- select -',
      'Bill' => ts('Bill'),
      'Issue' => ts('Issue'),
      'Committee' => ts('Committee'),
      'Direct Message' => ts('Direct Message'),
      'Context Message' => ts('Context Message'),
      'Survey' => ts('Survey'),
      'Petition' => ts('Petition'),
      'Account' => ts('Account'),
      'Profile' => ts('Profile'),
    ];
    $form->add('select', 'type', ts('Type'), $type, false);
    
    $form->addDate('start_date', ts('Date from'), false, ['formatType' => 'birth']);
    $form->addDate('end_date', ts('Date to'), false, ['formatType' => 'birth']);

    $form->setDefaults($this->setDefaultValues());
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
      $selectClause = "contact_a.id as contact_id";
      $sort = 'contact_a.id';
    }
    else {
      $selectClause = "
        contact_a.id as contact_id,
        contact_a.sort_name as sort_name,
        contact_a.contact_type as contact_type,
        wa.type,
        wa.created_date,
        wa.details
      ";
    }
    
    //CRM_Core_Error::debug('select',$selectClause); exit();
    $sql = $this->sql($selectClause,
      $offset, $rowcount, $sort,
      $includeContactIDs, null
    );

    //CRM_Core_Error::debug_var('$sql', $sql);
    return $sql;
  }

    
  function from() {
    //CRM_Core_Error::debug_var('$this->_formValues', $this->_formValues);

    $from = "
      FROM civicrm_contact contact_a
      JOIN nyss_web_activity wa
        ON contact_a.id = wa.contact_id
    ";

    return $from;
  }//from


  function where($includeContactIDs = false) {
    //CRM_Core_Error::debug('formVals', $this->_formValues);exit();
    $params = [];

    $start_date = CRM_Utils_Date::mysqlToIso(CRM_Utils_Date::processDate($this->_formValues['start_date']));
    $end_date  = CRM_Utils_Date::mysqlToIso(CRM_Utils_Date::processDate($this->_formValues['end_date']));
    
    //add filters by start/end date
    if ($start_date) {
      $where[] = "wa.created_date >= '$start_date' ";
    }
    if ($end_date) {
      $where[] = "wa.created_date <= '$end_date' ";
    }

    if (!empty($this->_formValues['sort_name'])) {
      $sortName = CRM_Utils_Type::escape($this->_formValues['sort_name'], 'String');
      $where[] = "sort_name LIKE '%{$sortName}%'";
    }

    if (!empty($this->_formValues['type'])) {
      $where[] = "wa.type LIKE '{$this->_formValues['type']}'";
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
    return 'CRM/Contact/Form/Search/Custom/WebActivityStream.tpl';
  }


  function setDefaultValues() {
    $defaults = [
      'action_type' => 3,
    ];
    return $defaults;
  }


  function alterRow(&$row) {
    $row['contact_type' ] =
      CRM_Contact_BAO_Contact_Utils::getImage($row['contact_type'],
        false,
        $row['contact_id']);

    $row['created_date'] = date('m/d/Y g:i a', strtotime($row['created_date']));
  }


  function setTitle($title) {
    if ($title) {
      CRM_Utils_System::setTitle($title);
    }
    else {
      CRM_Utils_System::setTitle(ts('Search'));
    }
  }
}
