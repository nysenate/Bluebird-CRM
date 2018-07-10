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

class CRM_Contact_Form_Search_Custom_TagGroupLog
  extends CRM_Contact_Form_Search_Custom_Base
  implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;
  protected $_columns;


  function __construct(&$formValues) {
    parent::__construct($formValues);

    $this->_columns = array(
      ts(' ') => 'contact_type',
      ts('Name') => 'sort_name' ,
      //ts('Street Address') => 'street_address',
      //ts('City') => 'city',
      //ts('State') => 'state',
      //ts('Postal Code') => 'postal_code',
      //ts('Email') => 'email',
      //ts('Phone') => 'phone',
      ts('Log Date') => 'log_date',
      ts('Log Details') => 'log_details',
      ts('Altered By') => 'altered_by',
    );
  }


  function buildForm(&$form) {
    $this->setTitle('Tag/Group Changelog Search');

    $searchType = array(
      '1' => ts('Tags'),
      '2' => ts('Groups'),
    );
    $form->addRadio('search_type', ts('Search Type'), $searchType, NULL, '&nbsp;', TRUE);

    //construct tags/groups
    $groups = CRM_Core_PseudoConstant::group();

    $tags = CRM_Core_BAO_Tag::getTags();
    $keywords = CRM_Core_BAO_Tag::getTagsUsedFor($usedFor = array('civicrm_contact'),
      $buildSelect = true,
      $all = false,
      $parentId = 296
    );
    asort($keywords);
    if ($keywords) {
      //lets indent keywords
      foreach ($keywords as $key => $keyword) {
        $keywords[$key] = '&nbsp;&nbsp;'.$keyword;
      }
      $tags = $tags + array ('296' => 'Keywords') + $keywords;
    }

    $legpos = CRM_Core_BAO_Tag::getTagsUsedFor($usedFor = array('civicrm_contact'),
      $buildSelect = true,
      $all = false,
      $parentId = 292
    );
    asort($legpos);
    if ($legpos) {
      //lets indent leg positions
      foreach ($legpos as $key => $pos) {
        $legpos[$key] = '&nbsp;&nbsp;'.$pos;
      }
      $tags = $tags + array ('292' => 'Legislative Positions') + $legpos;
    }

    $select2style = array(
      'multiple' => TRUE,
      'style' => 'width: 100%; max-width: 60em;',
      'class' => 'crm-select2',
      'placeholder' => ts('- select -'),
    );

    $form->add('select', 'tag',
      ts('Tag(s)'),
      $tags,
      FALSE,
      $select2style
    );

    $form->add('select', 'group',
      ts('Group(s)'),
      $groups,
      FALSE,
      $select2style
    );
    
    $form->addDate('start_date', ts('Date from'), false, array('formatType' => 'birth'));
    $form->addDate('end_date', ts('Date to'), false, array('formatType' => 'birth'));

    $actionType = array(
      '1' => ts('Added'),
      '2' => ts('Removed/Deleted'),
      '3' => ts('Both'),
    );
    $form->addRadio('action_type', ts('Action Type'), $actionType, NULL, '&nbsp;', TRUE);

    $form->add('text', 'altered_by', ts('Altered By'), array());
    
    $formfields = array(
      'start_date',
      'end_date',
      'search_type',
      'tag',
      'group',
      'action_type',
      'altered_by',
    );
    $form->assign('elements', $formfields);
    
    $form->add('hidden', 'form_message');

    $resetUrl = CRM_Utils_System::url('civicrm/contact/search/custom', 'csid=17&reset=1');
    $form->assign('resetUrl', $resetUrl);

    $form->setDefaults($this->setDefaultValues());
    $form->addFormRule(array('CRM_Contact_Form_Search_Custom_TagGroupLog', 'formRule'), $this);

    //9990
    $formValues = $form->get('formValues');
    if (!empty($formValues)) {
      $qfKey = $form->get('qfKey');
      CRM_Core_BAO_Cache::setItem($formValues, 'CRM_Contact_Form_Search_Custom_TagGroupLog', $qfKey);
      $quickExportUrl = CRM_Utils_System::url('civicrm/search/custom/taggroup/quickexport',
        http_build_query(array('qfKey' => $qfKey)));
      $form->assign('quickExportUrl', $quickExportUrl);
    }
  }//buildForm
  

  function formRule($fields) {
    $errors = [];

    if ($fields['search_type'] == 1 && empty($fields['tag'])) {
      //$errors['form_message'] = ts('Please select at least one tag.');
    }
    elseif ($fields['search_type'] == 2 && empty($fields['group'])) {
      //$errors['form_message'] = ts('Please select at least one group.');
    }
        
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
    $log_details = '';
    switch ($this->_formValues['search_type']) {
      case 1:
        $log_details = "CONCAT(tag.name, ' (',
          CASE WHEN log_et.log_action = 'Insert' THEN 'Added'
          WHEN log_et.log_action = 'Delete' THEN 'Removed'
          ELSE log_et.log_action END,
        ')')";
        break;

      case 2:
        $log_details = "CONCAT(grp.title, ' (',
          CASE WHEN log_et.log_action = 'Insert' THEN 'Added'
          WHEN log_et.log_action = 'Delete' THEN 'Deleted'
          WHEN log_et.log_action = 'Update' THEN log_et.status
          ELSE log_et.log_action END, ')')";
        break;
    }

    if ($justIDs) {
      $selectClause = "contact_a.id as contact_id";
      $sort = 'contact_a.id';
    }
    else {
      $selectClause = "
        contact_a.id as contact_id,
        contact_a.sort_name as sort_name,
        contact_a.contact_type as contact_type,
        log_et.log_date,
        {$log_details} as log_details,
        ab.display_name as altered_by
      ";
    }
    
    //CRM_Core_Error::debug('select',$selectClause); exit();
    $sql = $this->sql($selectClause, $offset, $rowcount, $sort,
                      $includeContactIDs, null);
    //CRM_Core_Error::debug('$sql',$sql); exit();
    return $sql;
  }

    
  function from() {
    //CRM_Core_Error::debug_var('$this->_formValues', $this->_formValues);

    $bbconfig = get_bluebird_instance_config();
    //CRM_Core_Error::debug_var('$bbconfig', $bbconfig);

    $logDB = $bbconfig['db.log.prefix'].$bbconfig['db.basename'];

    switch ($this->_formValues['search_type']) {
      case 1:
        $from = "
          FROM civicrm_contact contact_a
          JOIN {$logDB}.log_civicrm_entity_tag log_et
            ON contact_a.id = log_et.entity_id
            AND log_et.entity_table = 'civicrm_contact'
            AND log_et.log_action != 'Initialization'
          JOIN civicrm_tag tag
            ON log_et.tag_id = tag.id
          LEFT JOIN civicrm_contact ab
            ON log_et.log_user_id = ab.id
        ";
        break;

      case 2:
        $from = "
          FROM civicrm_contact contact_a
          JOIN {$logDB}.log_civicrm_group_contact log_et
            ON contact_a.id = log_et.contact_id
            AND log_et.log_action != 'Initialization'
          JOIN civicrm_group grp
            ON log_et.group_id = grp.id
          LEFT JOIN civicrm_contact ab
            ON log_et.log_user_id = ab.id
        ";
        break;
    }

    return $from;
  }//from


  function where($includeContactIDs = false) {
    //CRM_Core_Error::debug_var('formVals', $this->_formValues);

    $params = [];

    $start_date = CRM_Utils_Date::mysqlToIso(CRM_Utils_Date::processDate($this->_formValues['start_date']));
    $end_date = CRM_Utils_Date::mysqlToIso(CRM_Utils_Date::processDate($this->_formValues['end_date'], '235959'));
    
    //add filters by start/end date
    if ($start_date) {
      $where[] = "log_et.log_date >= '{$start_date}' ";
    }
    if ($end_date) {
      $where[] = "log_et.log_date <= '{$end_date}' ";
    }

    switch ($this->_formValues['search_type']) {
      case 1:
        $tags = implode(',', $this->_formValues['tag']);
        if (!empty($tags)) {
          $where[] = "log_et.tag_id IN ({$tags}) ";
        }
        else {
          $where[] = "log_et.tag_id IS NOT NULL ";
        }
        break;

      case 2:
        $groups = implode(',', $this->_formValues['group']);
        if (!empty($groups)) {
          $where[] = "log_et.group_id IN ({$groups}) ";
        }
        else {
          $where[] = "log_et.group_id IS NOT NULL ";
        }
        break;
    }

    switch ($this->_formValues['action_type']) {
      case 1:
        //condition on tag/group
        switch ($this->_formValues['search_type']) {
          case 1:
            $where[] = "(log_et.log_action = 'Insert') ";
            break;

          case 2:
            $where[] = "(log_et.log_action = 'Insert' OR (log_et.log_action = 'Update' AND log_et.status = 'Added')) ";
            break;
        }

        break;
      case 2:
        //condition on tag/group
        switch ($this->_formValues['search_type']) {
          case 1:
            $where[] = "(log_et.log_action = 'Delete') ";
            break;

          case 2:
            $where[] = "(log_et.log_action = 'Delete' OR (log_et.log_action = 'Update' AND log_et.status = 'Removed')) ";
            break;
        }

        break;
      case 3:
        //both - add no clause
        break;
    }

    if (!empty($this->_formValues['altered_by'])) {
      if (is_numeric($this->_formValues['altered_by'])) {
        $where[] = "ab.id = {$this->_formValues['altered_by']} ";
      }
      else {
        $where[] = "ab.sort_name LIKE '%{$this->_formValues['altered_by']}%' ";
      }
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
    return 'CRM/Contact/Form/Search/Custom/TagGroupLog.tpl';
  }


  function setDefaultValues() {
    $defaults = array(
      'action_type' => 3,
    );
    return $defaults;
  }


  function alterRow(&$row) {
    if (empty($_REQUEST['is_quick_export'])) {
      $row['contact_type'] =
        CRM_Contact_BAO_Contact_Utils::getImage($row['contact_type'],
          false,
          $row['contact_id']);
    }
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

    if (!empty($_REQUEST['qfKey'])) {
      $_REQUEST['is_quick_export'] = true;
      $qfKey = $_REQUEST['qfKey'];
      $formValues = CRM_Core_BAO_Cache::getItem('CRM_Contact_Form_Search_Custom_TagGroupLog', $qfKey);

      CRM_Export_BAO_Export::exportCustom($formValues['customSearchClass'],
        $formValues,
        'sort_name'
      );
    }
  }//quickExport
}
