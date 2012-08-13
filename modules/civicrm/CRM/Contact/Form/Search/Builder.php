<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * This class if for search builder processing
 */
class CRM_Contact_Form_Search_Builder extends CRM_Contact_Form_Search {

  /**
   * number of columns in where
   *
   * @var int
   * @access public
   */
  public $_columnCount;

  /**
   * number of blocks to be shown
   *
   * @var int
   * @access public
   */
  public $_blockCount;

  /**
   * Function to actually build the form
   *
   * @return None
   * @access public
   */
  public function preProcess() {
    $this->set('searchFormName', 'Builder');

    $this->set('context', 'builder');
    parent::preProcess();

    //get the block count
    $this->_blockCount = $this->get('blockCount');
    if (!$this->_blockCount) {
      $this->_blockCount = 3;
    }

    //get the column count
    $this->_columnCount = array();
    $this->_columnCount = $this->get('columnCount');

    for ($i = 1; $i < $this->_blockCount; $i++) {
      if ((!isset($this->_columnCount[$i])) || (!$this->_columnCount[$i])) {
        $this->_columnCount[$i] = 1;
      }
    }

    $this->_loadedMappingId = $this->get('savedMapping');

    if ($this->get('showSearchForm')) {
      $this->assign('showSearchForm', TRUE);
    }
    else {
      $this->assign('showSearchForm', FALSE);
    }
  }

  public function buildQuickForm() {
    //get the saved search mapping id
    $mappingId = NULL;
    if ($this->_ssID) {
      $mappingId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_SavedSearch', $this->_ssID, 'mapping_id');
    }

    CRM_Core_BAO_Mapping::buildMappingForm($this, 'Search Builder', $mappingId, $this->_columnCount, $this->_blockCount);

    parent::buildQuickForm();
  }

  /**
   * Add local and global form rules
   *
   * @access protected
   *
   * @return void
   */
  function addRules() {
    $this->addFormRule(array('CRM_Contact_Form_Search_Builder', 'formRule'));
  }

  /**
   * global validation rules for the form
   *
   * @param array $fields posted values of the form
   *
   * @return array list of errors to be posted back to the form
   * @static
   * @access public
   */
  static
  function formRule($values) {
    if (CRM_Utils_Array::value('addMore', $values) || CRM_Utils_Array::value('addBlock', $values)) {
      return TRUE;
    }
    $fields = array();
    $fields = CRM_Contact_BAO_Contact::exportableFields('All', FALSE, TRUE);

    $compomentFields = CRM_Core_Component::getQueryFields();
    $activityFields  = CRM_Activity_BAO_Activity::exportableFields();
    $compomentFields = array_merge($compomentFields, $activityFields);
    $fields          = array_merge($fields, $compomentFields);

    $fld = array();
    $fld = CRM_Core_BAO_Mapping::formattedFields($values, TRUE);

    $errorMsg = array();
    foreach ($fld as $k => $v) {
      if (!$v[1]) {
        $errorMsg["operator[$v[3]][$v[4]]"] = ts("Please enter the operator.");
      }
      else {
        // CRM-10338
        $v[2] = self::checkArrayKeyEmpty($v[2]);

        if (in_array($v[1], array('IS NULL', 'IS NOT NULL')) &&
          !empty($v[2])) {
          $errorMsg["value[$v[3]][$v[4]]"] = ts('Please clear your value if you want to use %1 operator.', array(1 => $v[1]));
        }
        elseif (($v[0] == 'group' || $v[0] == 'tag') &&
          !empty($v[2])) {
          $grpId = array_keys($v[2]);
          if (!key($v[2])) {
            $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter the value.");
          }

          if (count($grpId) > 1) {
            if ($v[1] != 'IN' && $v[1] != 'NOT IN') {
              $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter the valid value.");
            }
            foreach ($grpId as $val) {
              $error = CRM_Utils_Type::validate($val, 'Integer', FALSE);
              if ($error != $val) {
                $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter valid value.");
                break;
              }
            }
          }
          else {
            $error = CRM_Utils_Type::validate($grpId[0], 'Integer', FALSE);
            if ($error != $grpId[0]) {
              $errorMsg["value[$v[3]][$v[4]]"] = ts('Please enter valid %1 id.', array(1 => $v[0]));
            }
          }
        }
        elseif (substr($v[0], 0, 7) === 'do_not_' or substr($v[0], 0, 3) === 'is_') {
          if (isset($v[2])) {
            $v2 = array($v[2]);
            if (!isset($v[2])) {
              $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter the value.");
            }

            $error = CRM_Utils_Type::validate($v2[0], 'Integer', FALSE);
            if ($error != $v2[0]) {
              $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter valid value.");
            }
          }
          else {
            $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter the value.");
          }
        }
        elseif (($v[0] === 'sort_name' || $v[0] === 'display_name')) {
          $v2 = trim($v[2]);
          if (empty($v2)) {
            $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter the value.");
          }
        }
        else {
          if (substr($v[0], 0, 7) == 'custom_') {
            $type = $fields[$v[0]]['data_type'];

            // hack to handle custom data of type state and country
            if (in_array($type, array(
              'Country', 'StateProvince'))) {
              $type = "Integer";
            }
          }
          else {
            $fldName = $v[0];
            // FIXME: no idea at this point what to do with this,
            // FIXME: but definitely needs fixing.
            if (substr($v[0], 0, 13) == 'contribution_') {
              $fldName = substr($v[0], 13);
            }

            $fldValue = CRM_Utils_Array::value($fldName, $fields);
            $fldType = CRM_Utils_Array::value('type', $fldValue);
            $type = CRM_Utils_Type::typeToString($fldType);
            // Check Empty values for Integer Or Boolean Or Date type For operators other than IS NULL and IS NOT NULL.
            if (!in_array($v[1], array(
              'IS NULL', 'IS NOT NULL'))) {
              if ((($type == 'Int' || $type == 'Boolean') && !trim($v[2])) && $v[2] != '0') {
                $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter the value.");
              }
              elseif ($type == 'Date' && !trim($v[2])) {
                $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter the value.");
              }
            }
          }

          if ($type && empty($errorMsg)) {
            // check for valid format while using IN Operator
            if ($v[1] == 'IN') {
              $inVal = trim($v[2]);
              //checking for format to avoid db errors
              if ($type == 'Int') {
                if (!preg_match('/^[(]([A-Za-z0-9\,]+)[)]$/', $inVal)) {
                  $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter correct Data ( in valid format ).");
                }
              }
              else {
                if (!preg_match('/^[(]([A-Za-z0-9åäöÅÄÖüÜœŒæÆøØ\,\s]+)[)]$/', $inVal)) {
                  $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter correct Data ( in valid format ).");
                }
              }

              // Validate each value in parenthesis to avoid db errors
              if (empty($errorMsg)) {
                $parenValues = array();
                $parenValues = explode(',', trim($inVal, "(..)"));
                foreach ($parenValues as $val) {
                  $val = trim($val);
                  if (!$val && $val != '0') {
                    $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter the values correctly.");
                  }
                  if (empty($errorMsg)) {
                    $error = CRM_Utils_Type::validate($val, $type, FALSE);
                    if ($error != $val) {
                      $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter valid value.");
                    }
                  }
                }
              }
            }
            elseif (trim($v[2])) {
              //else check value for rest of the Operators
              $error = CRM_Utils_Type::validate($v[2], $type, FALSE);
              if ($error != $v[2]) {
                $errorMsg["value[$v[3]][$v[4]]"] = ts("Please enter valid value.");
              }
            }
          }
        }
      }
    }

    if (!empty($errorMsg)) {
      return $errorMsg;
    }

    return TRUE;
  }

  public function normalizeFormValues() {}

  public function &convertFormValues(&$formValues) {
    return CRM_Core_BAO_Mapping::formattedFields($formValues);
  }

  public function &returnProperties() {
    return CRM_Core_BAO_Mapping::returnProperties($this->_formValues);
  }

  /**
   * Process the uploaded file
   *
   * @return void
   * @access public
   */
  public function postProcess() {
    $this->set('isAdvanced', '2');
    $this->set('isSearchBuilder', '1');
    $this->set('showSearchForm', FALSE);

    $params = $this->controller->exportValues($this->_name);
    if (!empty($params)) {
      if (CRM_Utils_Array::value('addBlock', $params)) {
        $this->_blockCount = $this->_blockCount + 1;
        $this->set('blockCount', $this->_blockCount);
        $this->set('showSearchForm', TRUE);
        return;
      }

      for ($x = 1; $x <= $this->_blockCount; $x++) {
        $addMore = CRM_Utils_Array::value('addMore', $params);
        if (CRM_Utils_Array::value($x, $addMore)) {
          $this->_columnCount[$x] = $this->_columnCount[$x] + 1;
          $this->set('columnCount', $this->_columnCount);
          $this->set('showSearchForm', TRUE);
          return;
        }
      }

      $checkEmpty = NULL;
      foreach ($params['mapper'] as $key => $value) {
        foreach ($value as $k => $v) {
          if ($v[0]) {
            $checkEmpty++;
          }
        }
      }

      if (!$checkEmpty) {
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/search/builder', '_qf_Builder_display=true'));
      }
    }

    // get user submitted values
    // get it from controller only if form has been submitted, else preProcess has set this
    if (!empty($_POST)) {
      $this->_formValues = $this->controller->exportValues($this->_name);

      // set the group if group is submitted
      if (CRM_Utils_Array::value('uf_group_id', $this->_formValues)) {
        $this->set('id', $this->_formValues['uf_group_id']);
      }
      else {
        $this->set('id', '');
      }
    }

    // we dont want to store the sortByCharacter in the formValue, it is more like
    // a filter on the result set
    // this filter is reset if we click on the search button
    if ($this->_sortByCharacter !== NULL
      && empty($_POST)
    ) {
      if (strtolower($this->_sortByCharacter) == 'all') {
        $this->_formValues['sortByCharacter'] = NULL;
      }
      else {
        $this->_formValues['sortByCharacter'] = $this->_sortByCharacter;
      }
    }

    $this->_params = &$this->convertFormValues($this->_formValues);
    $this->_returnProperties = &$this->returnProperties();


    // CRM-10338 check if value is empty array
    foreach ( $this->_params as $k => $v ) {
      $this->_params[$k][2] = self::checkArrayKeyEmpty($v[2]);
    }

    parent::postProcess();
  }

  // CRM-10338
  // tags and groups use array keys for selection list.
  // if using IS NULL/NOT NULL, an array with no array key is created
  // convert that to simple null so processing can proceed
  function checkArrayKeyEmpty( $val ) {
    if ( is_array($val) ) {
      $v2empty = true;
      foreach ( $val as $vk => $vv ) {
        if ( !empty($vk) ) {
          $v2empty = false;
        }
      }
      if ( $v2empty ) {
        $val = null;
      }
    }
    return $val;
  }
}

