<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

class CRM_Contact_Form_Search_Custom_WebActivityStream
  extends CRM_Contact_Form_Search_Custom_Base
  implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;
  protected $_columns;


  function __construct(&$formValues) {
    parent::__construct($formValues);

    $this->_columns = array(
      ts('&nbsp;') => 'contact_type',
      ts('Name') => 'sort_name' ,
      ts('Type') => 'type',
      ts('Date') => 'created_date',
      ts('Details') => 'details',
    );
  }


  function buildForm(&$form) {
    $this->setTitle('Website Activity Stream Search');

    $form->add('text', 'sort_name', ts('Contact Name'), array('size' => 20));

    $type = array(
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
    );
    $form->add('select', 'type', ts('Type'), $type, false);
    
    $form->addDate('start_date', ts('Date from'), false, array('formatType' => 'birth'));
    $form->addDate('end_date', ts('Date to'), false, array('formatType' => 'birth'));

    $resetUrl = CRM_Utils_System::url('civicrm/contact/search/custom', 'csid=18&reset=1');
    $form->assign('resetUrl', $resetUrl);

    $form->setDefaults($this->setDefaultValues());
    $form->addFormRule(array('CRM_Contact_Form_Search_Custom_WebActivityStream', 'formRule'), $this);
  }//buildForm
  

  function formRule($fields) {
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
      $where[] = "sort_name LIKE '{$sortName}'";
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
    $dao = CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
    return $dao->N;
  }


  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom/WebActivityStream.tpl';
  }


  function setDefaultValues() {
    $defaults = array(
      'action_type' => 3,
    );
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
