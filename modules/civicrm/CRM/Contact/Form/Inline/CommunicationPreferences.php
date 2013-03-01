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
 * form helper class for communication preferences inline edit section 
 */
class CRM_Contact_Form_Inline_CommunicationPreferences extends CRM_Core_Form {

  /**
   * contact id of the contact that is been viewed
   */
  public $_contactId;

  /**
   * contact type of the contact that is been viewed
   */
  public $_contactType;

  /**
   * call preprocess
   */
  public function preProcess() {
    //get all the existing email addresses
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE, NULL, $_REQUEST);
    $this->assign('contactId', $this->_contactId);

    // Get contact type if not set
    if (empty($this->_contactType)) {
      $this->_contactType = CRM_Contact_BAO_Contact::getContactType($this->_contactId);
    }
  }

  /**
   * build the form elements for an email object
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    CRM_Contact_Form_Edit_CommunicationPreferences::buildQuickForm( $this );
    $this->addFormRule(array('CRM_Contact_Form_Edit_CommunicationPreferences', 'formRule'), $this);
 
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

    $defaults = array();
    CRM_Contact_BAO_Contact::getValues( $params, $defaults );
    
    $this->_contactType = CRM_Utils_Array::value('contact_type', $defaults);
 
    if (!empty($defaults['preferred_language'])) {
      $languages = array_flip(CRM_Core_PseudoConstant::languages());
      $defaults['preferred_language'] = $languages[$defaults['preferred_language']];
    }

    // CRM-7119: set preferred_language to default if unset
    if (empty($defaults['preferred_language'])) {
      $config = CRM_Core_Config::singleton();
      $defaults['preferred_language'] = $config->lcMessages;
    }

    foreach (CRM_Contact_BAO_Contact::$_greetingTypes as $greeting) {
      $name = "{$greeting}_display";
      $this->assign($name, CRM_Utils_Array::value($name, $defaults));
    }
    return $defaults;
  }

  /**
   * process the form
   *
   * @return void
   * @access public
   */
  public function postProcess() {
    $params = $this->exportValues();

    // need to process / save communication preferences 
    
    // this is a chekbox, so mark false if we dont get a POST value
    $params['is_opt_out'] = CRM_Utils_Array::value('is_opt_out', $params, FALSE);
    $params['contact_type'] = $this->_contactType;
    $params['contact_id']   = $this->_contactId;
    CRM_Contact_BAO_Contact::create( $params );

    $response = array('status' => 'save');
    $this->postProcessHook();
    echo json_encode($response);
    CRM_Utils_System::civiExit();
  }
}

