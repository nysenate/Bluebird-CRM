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
 * form helper class for custom data section 
 */
class CRM_Contact_Form_Inline_CustomData extends CRM_Core_Form {

  /**
   * contact id of the contact that is been viewed
   */
  public $_contactId;

  /**
   * custom group id 
   *
   * @int
   * @access public
   */
  public $_groupID;

  /**
   * entity type of the table id
   *
   * @var string
   */
  protected $_entityType;

  /**
   * call preprocess
   */
  public function preProcess() {
    //get all the existing email addresses
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE, NULL, $_REQUEST);
    $this->assign('contactId', $this->_contactId);
    $this->_groupID = CRM_Utils_Request::retrieve('groupID', 'Positive', $this, TRUE, NULL, $_REQUEST);
    $this->assign( 'customGroupId', $this->_groupID ); 

    $type = CRM_Contact_BAO_Contact::getContactType($this->_contactId);
    $subType = CRM_Contact_BAO_Contact::getContactSubType($this->_contactId, ',');
    CRM_Custom_Form_CustomData::preProcess( $this, null, $subType, 1,
      $type, $this->_contactId );
  }

  /**
   * build the form elements for an email object
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    CRM_Custom_Form_CustomData::buildQuickForm($this);

    $buttons = array(
      array(
        'type' => 'upload',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    );

    $this->addButtons($buttons);
  }

  /**
   * Override default cancel action
   */
  function cancelAction() {
    $response = array('status' => 'cancel');
    echo json_encode($response);
    CRM_Utils_System::civiExit();
  }

  /**
   * set defaults for the form
   *
   * @return void
   * @access public
   */
  public function setDefaultValues() {
    $defaults = array();
    $params = array(
      'id' => $this->_contactId
    );

    $defaults = CRM_Custom_Form_CustomData::setDefaultValues($this);

    return $defaults;
  }

  /**
   * process the form
   *
   * @return void
   * @access public
   */
  public function postProcess() {
    // need to process / save custom data 
    // Get the form values and groupTree
    $params = $this->controller->exportValues($this->_name);
    CRM_Core_BAO_CustomValueTable::postProcess($params,
      $this->_groupTree[$this->_groupID]['fields'],
      'civicrm_contact',
      $this->_contactId,
      $this->_entityType
    );

    // reset the group contact cache for this group
    CRM_Contact_BAO_GroupContactCache::remove();

    $response = array('status' => 'save');
    $this->postProcessHook();
    echo json_encode($response);
    CRM_Utils_System::civiExit();
  }
}

