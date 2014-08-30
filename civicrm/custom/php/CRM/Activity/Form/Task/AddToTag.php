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
 * $Id: Email.php 26615 2010-03-21 21:05:35Z kurund $
 *
 */

/**
 * This class provides the functionality to email a group of
 * contacts.
 */
class CRM_Activity_Form_Task_AddToTag extends CRM_Activity_Form_Task {

  /**
   * Are we operating in "single mode", i.e. sending email to one
   * specific contact?
   *
   * @var boolean
   */
  public $_single = FALSE;

  /**
   * name of the tag
   *
   * @var string
   */
  protected $_name;

  /**
   * all the tags in the system
   *
   * @var array
   */
  protected $_tags;

  /**
   * build all the data structures needed to build the form
   *
   * @return void
   * @access public
   */
  function preProcess() {
    parent::preProcess();
  }

  /**
   * Build the form
   *
   * @access public
   *
   * @return void
   */
  public function buildQuickForm() {
    // add select for tag
    $this->_tags = CRM_Core_BAO_Tag::getTags('civicrm_activity');

    if ( !empty($this->_tags) ) {
      $this->assign('tagTreeExists', 1);

      foreach ($this->_tags as $tagID => $tagName) {
        $this->_tagElement = &$this->addElement('checkbox', "tag[$tagID]", NULL, $tagName);
      }
    }

    $parentNames = CRM_Core_BAO_Tag::getTagSet('civicrm_activity');
    CRM_Core_Form_Tag::buildQuickForm($this, $parentNames, 'civicrm_activity');

    $this->addDefaultButtons(ts('Tag Activities'));
  }

  function addRules() {
    $this->addFormRule(array('CRM_Activity_Form_Task_AddToTag', 'formRule'));
  }

  static function formRule($form, $rule) {
    $errors = array();
    if (empty($form['tag']) && empty($form['activity_taglist'])) {
      $errors['_qf_default'] = ts("Please select at least one tag.");
    }
    return $errors;
  }

  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    //CRM_Core_Error::debug_var('this', $this);
    CRM_Core_Error::debug_var('this->_activityHolderIds', $this->_activityHolderIds);

    //get the submitted values in an array
    $params = $this->controller->exportValues($this->_name);
    CRM_Core_Error::debug_var('$params', $params);

    $activityTags = $tagList = array();

    // check if activity tags exists
    if (CRM_Utils_Array::value('tag', $params)) {
      $activityTags = $params['tag'];
    }

    // check if tags are selected from taglists
    if (CRM_Utils_Array::value('activity_taglist', $params)) {
      foreach ($params['activity_taglist'] as $val) {
        if ($val) {
          if (is_numeric($val)) {
            $tagList[$val] = 1;
          }
          else {
            $tagIDs = explode(',', $val);
            if (!empty($tagIDs)) {
              foreach ($tagIDs as $tagID) {
                if (is_numeric($tagID)) {
                  $tagList[$tagID] = 1;
                }
              }
            }
          }
        }
      }
    }

    $tagSets = CRM_Core_BAO_Tag::getTagsUsedFor('civicrm_activity', FALSE, TRUE);
    //CRM_Core_Error::debug_var('$tagSets', $tagSets);

    foreach ($tagSets as $key => $value) {
      $this->_tags[$key] = $value['name'];
    }

    // merge contact and taglist tags
    $allTags = CRM_Utils_Array::crmArrayMerge($activityTags, $tagList);
    CRM_Core_Error::debug_var('$allTags', $allTags);

    $this->_name = array();
    foreach ($allTags as $key => $dnc) {
      $this->_name[] = $this->_tags[$key];

      list($total, $added, $notAdded) = CRM_Core_BAO_EntityTag::addEntitiesToTag($this->_activityHolderIds, $key, 'civicrm_activity');

      $status = array(ts('%count activity tagged', array('count' => $added, 'plural' => '%count activities tagged')));
      if ($notAdded) {
        $status[] = ts('%count activity already had this tag', array('count' => $notAdded, 'plural' => '%count activity already had this tag'));
      }
      $status = '<ul><li>' . implode('</li><li>', $status) . '</li></ul>';
      CRM_Core_Session::setStatus($status, ts("Added Tag <em>%1</em>", array(1 => $this->_tags[$key])), 'success', array('expires' => 0));
    }




  }
}

