<?php

use CRM_Contactsummary_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Contactsummary_Form_Inline_ProfileBlock extends CRM_Profile_Form_Edit {

  /**
   * Form for editing profile blocks
   */
  public function preProcess() {
    if (!empty($_GET['cid'])) {
      $this->set('id', $_GET['cid']);
    }
    parent::preProcess();
  }

  public function buildQuickForm() {
    parent::buildQuickForm();
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
   * Save profiles
   *
   * @throws \CiviCRM_API3_Exception
   */
  public function postProcess() {
    $values = $this->exportValues();
    $values['contact_id'] = $cid = $this->_id;
    $values['profile_id'] = $this->_gid;
    $result = civicrm_api3('Profile', 'submit', $values);

    // These are normally performed by CRM_Contact_Form_Inline postprocessing but this form doesn't inherit from that class.
    CRM_Core_BAO_Log::register($cid,
      'civicrm_contact',
      $cid
    );
    $this->ajaxResponse = array_merge(
      CRM_Contact_Form_Inline::renderFooter($cid),
      $this->ajaxResponse,
      CRM_Contact_Form_Inline_Lock::getResponse($cid)
    );
  }

}
