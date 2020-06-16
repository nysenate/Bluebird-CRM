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

require_once 'CRM/Contact/Form/Search/Custom/Base.php';


class CRM_Contact_Form_Search_Custom_BirthdayByMonth
  extends CRM_Contact_Form_Search_Custom_Base
  implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;
  protected $_columns;


  function __construct(&$formValues) {
    parent::__construct($formValues);

    $this->_columns = [
      ts('&nbsp;') => 'contact_type', //NYSS 4899
      ts('Name') => 'sort_name' ,
      ts('Birth Date') => 'birth_date',
      ts('Age') => 'age',
      ts('Street Address') => 'street_address',
      ts('City') => 'city'
    ];
  }


  function buildForm(&$form) {
    $this->setTitle('Birthday Search');
    $month = [
      ''  => '- select month -',
      '1' => 'January',
      '2' => 'February',
      '3' => 'March',
      '4' => 'April',
      '5' => 'May' ,
      '6' => 'June',
      '7' => 'July',
      '8' => 'August',
      '9' => 'September',
      '10' => 'October',
      '11' => 'November',
      '12' => 'December'
    ];

    $form->add('select', 'birth_month', ts('Individual\'s Birth Month (1-12)'), $month, false);
    
    $form->add('text', 'year_start', ts('Birthday: year after'), array('size' => 4, 'maxlength' => 4));
    $form->add('text', 'year_end', ts('Birthday: year before'), array('size' => 4, 'maxlength' => 4));
    
    $form->add('text', 'day_start', ts('Birthday: day after'), array('size' => 2, 'maxlength' => 2));
    $form->add('text', 'day_end', ts('Birthday: day before'), array('size' => 2, 'maxlength' => 2));
    
    $form->add('text', 'age_start', ts('Age greater than'), array('size' => 3, 'maxlength' => 3));
    $form->add('text', 'age_end', ts('Age less than'), array('size' => 3, 'maxlength' => 3));
    
    //$form->addDate('start_date', ts('Birthday after (date)'), false, array('formatType' => 'birth'));
    //$form->addDate('end_date', ts('Birthday before (date)'), false, array('formatType' => 'birth'));
    $form->add('datepicker', 'start_date', ts('Birthday after (date)'), [], FALSE, ['time' => FALSE]);
    $form->add('datepicker', 'end_date', ts('Birthday before (date)'), [], FALSE, ['time' => FALSE]);
    
    $formfields = array(
      'start_date',
      'end_date',
      'age_start',
      'age_end',
      'birth_month',
      'year_start',
      'year_end',
      'day_start',
      'day_end'
    );
    $form->assign('elements', $formfields);
    
    $form->add('hidden', 'form_message');

    $form->setDefaults($this->setDefaultValues());
    $form->addFormRule(array('CRM_Contact_Form_Search_Custom_BirthdayByMonth', 'formRule'), $this);
  }
  

  function formRule($fields) {
    $errors = [];
    
    //days cannot be > 31
    if ((int)$fields['day_start'] > 31) {
      $errors['day_start'] = ts('Day after cannot be greater than 31.');
    }

    if ((int)$fields['day_end'] > 31) {
      $errors['day_end'] = ts('Day before cannot be greater than 31.');
    }
    
    //must select some criteria
    $criteriaexists = false;
    $criteria = $fields;
    unset($criteria['qfKey']);
    unset($criteria['_qf_default']);
    unset($criteria['_qf_Custom_refresh']);

    foreach ($criteria as $criterion) {
      if (!empty($criterion)) {
        $criteriaexists = true;
      }
    }
    if (!$criteriaexists) {
      $errors['form_message'] = ts('Please select some criteria.');
    }
        
    return empty($errors) ? true : $errors;
  }//formRule()


  function summary() {
    return null;
  }//summary()


  //NYSS 4536
  function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = FALSE) {
    return $this->all($offset, $rowcount, $sort, FALSE, TRUE);
  }


  function all($offset = 0, $rowcount = 0, $sort = NULL,
               $includeContactIDs = FALSE, $justIDs = FALSE) {
    //NYSS 4536
    if ($justIDs) {
      $selectClause = "contact_a.id as contact_id";
      $sort = 'contact_a.id';
    }
    else {
      $selectClause = "
      DISTINCT(contact_a.id) as contact_id,
        contact_a.sort_name as sort_name,
        contact_a.contact_type as contact_type,
        contact_a.birth_date as birth_date,
        (YEAR(CURDATE())-YEAR(birth_date)) - (RIGHT(CURDATE(),5)<RIGHT(birth_date,5)) AS age,
        addr.street_address,
        addr.city";
    }
             
    if (empty($sort)) {
      $sort = "ORDER BY birth_date asc";
    }
    
    //CRM_Core_Error::debug('select',$selectClause); exit();
    return $this->sql($selectClause, $offset, $rowcount, $sort, $includeContactIDs, null);
  }

    
  function from() {
    //NYSS
    $from = "
      FROM civicrm_contact contact_a
      LEFT JOIN civicrm_address addr
        ON addr.contact_id = contact_a.id
        AND addr.is_primary = 1
    ";
    return $from;
  }


  function where($includeContactIDs = false) {
    $params = [];
    
    $birth_month = CRM_Utils_Array::value('birth_month', $this->_formValues);
    $start_date  = CRM_Utils_Date::mysqlToIso(CRM_Utils_Date::processDate($this->_formValues['start_date']));
    $end_date  = CRM_Utils_Date::mysqlToIso(CRM_Utils_Date::processDate($this->_formValues['end_date']));
    
    //add filters by start/end date
    if ($start_date) {
      $where[] = "contact_a.birth_date >= '$start_date' ";
    }
    if ($end_date) {
      $where[] = "contact_a.birth_date <= '$end_date' ";
    }
    
    //add filter by month
    if ($birth_month) {
      $where[] = "MONTH( contact_a.birth_date ) = $birth_month ";
    }
    
    //add filters by start/end year
    if ($this->_formValues['year_start']) {
      $year_start = $this->_formValues['year_start'];
      $where[] = "YEAR( contact_a.birth_date ) >= '$year_start' ";
    }
    if ($this->_formValues['year_end']) {
      $year_end = $this->_formValues['year_end'];
      $where[] = "YEAR( contact_a.birth_date ) <= '$year_end' ";
    }
    
    //add filters by start/end day
    if ($this->_formValues['day_start']) {
      $day_start = $this->_formValues['day_start'];
      $where[] = "DAY( contact_a.birth_date ) >= '$day_start' ";
    }
    if ($this->_formValues['day_end']) {
      $day_end = $this->_formValues['day_end'];
      $where[] = "DAY( contact_a.birth_date ) <= '$day_end' ";
    }
    
    //add filters by start/end age
    if ($this->_formValues['age_start']) {
      $age_start = (int)$this->_formValues['age_start'];
      $where[] = "(DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth_date, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth_date, '00-%m-%d'))) >= $age_start ";
    }
    if ($this->_formValues['age_end']) {
      $age_end = (int)$this->_formValues['age_end'];
      $where[] = "(DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth_date, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth_date, '00-%m-%d'))) <= $age_end ";
    }
    
    //standard clauses
    $where[] = "is_deleted = 0 ";
    $where[] = "is_deceased = 0 ";
    
    if (!empty($where)) {
      $whereClause = implode(' AND ', $where);
    }
    else {
      $whereClause = '';
    }
    //CRM_Core_Error::debug($whereClause); exit();
    
    return $this->whereClause($whereClause, $params);
  }


  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom/BirthdayByMonth.tpl';
  }


  function setDefaultValues() {
    /*return array('birth_month' => 1,
    'start_date' => '1900-01-01');*/
  }


  //NYSS 4899
  function alterRow(&$row) {
    require_once 'CRM/Contact/BAO/Contact/Utils.php';
    $row['contact_type' ] =
      CRM_Contact_BAO_Contact_Utils::getImage($row['contact_type'],
        false,
        $row['contact_id']);
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
