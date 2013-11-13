<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * This class generates form components for DedupeRules
 *
 */
class CRM_Contact_Form_DedupeFind extends CRM_Admin_Form {

  /**
   * defined defaults
   *
   */

  public $_defaults;

  /**
   * Function to pre processing
   *
   * @return None
   * @access public
   */
  function preProcess() {
    $this->rgid = CRM_Utils_Request::retrieve('rgid', 'Positive', $this, FALSE, 0);
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {

    //NYSS 4053 - Allow crunching on import groups too!
    require_once 'api/api.php';
    $params = array('version'=>3, 'group_type'=>'imported_contacts');
    $result = civicrm_api('group', 'get', $params);
    $importGroups = array(''=>'- All Contacts -');
    foreach($result['values'] as $gid => $fields) {
      $importGroups[$gid] = $fields['title'];
    }
    $this->add('select', 'import_group_id', ts('OR Select Import Group'), $importGroups);

    $groupList = CRM_Core_PseudoConstant::group();
    $groupList[''] = ts('- All Contacts -');
    asort($groupList);

    $this->add('select', 'group_id', ts('Select Group'), $groupList);
    $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Continue'),
          'isDefault' => TRUE,
        ),
        //hack to support cancel button functionality
        array(
          'type' => 'submit',
          'name' => ts('Cancel'),
        ),
      )
    );
  }

  function setDefaultValues() {
    return $this->_defaults;
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    $values = $this->exportValues();
    if (CRM_Utils_Array::value('_qf_DedupeFind_submit', $_POST)) {
      //used for cancel button
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/deduperules', 'reset=1'));
      return;
    }
    if ($values['group_id']) {
      $url = CRM_Utils_System::url('civicrm/contact/dedupefind', "reset=1&action=update&rgid={$this->rgid}&gid={$values['group_id']}");
    }
    else {
      $url = CRM_Utils_System::url('civicrm/contact/dedupefind', "reset=1&action=update&rgid={$this->rgid}");
    }

    // NYSS 4053 - Now check multiple places for the group id.
    if ( $gid = CRM_Utils_Array::value('group_id',$values) ) {
      $url = CRM_Utils_System::url( 'civicrm/contact/dedupefind', "reset=1&action=update&rgid={$this->rgid}&gid=$gid" );
    }
    else if($gid = CRM_Utils_Array::value('import_group_id',$values)) {
      $url = CRM_Utils_System::url( 'civicrm/contact/dedupefind', "reset=1&action=update&rgid={$this->rgid}&gid=$gid" );
    }
    else {
      $url = CRM_Utils_System::url( 'civicrm/contact/dedupefind', "reset=1&action=update&rgid={$this->rgid}" );
    }
        
    CRM_Utils_System::redirect($url);
  }
}

