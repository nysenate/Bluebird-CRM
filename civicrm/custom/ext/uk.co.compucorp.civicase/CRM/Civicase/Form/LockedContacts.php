<?php

/**
 * Form to add locked contacts to a case.
 */
class CRM_Civicase_Form_LockedContacts extends CRM_Core_Form {

  /**
   * Case ID.
   *
   * @var int
   */
  public $caseID = NULL;

  /**
   * Contacts locked out of given case.
   *
   * @var array
   */
  public $contacts = array();

  /**
   * @inheritdoc
   */
  public function preProcess() {
    $this->caseID = CRM_Utils_Request::retrieve('case_id', 'Int');
    $contactLocks = civicrm_api3('CaseContactLock', 'get', array(
      'sequential' => 1,
      'case_id' => $this->caseID,
    ));

    foreach($contactLocks['values'] as $currentLock) {
      $this->contacts[] = $currentLock['contact_id'];
    }
  }

  /**
   * @inheritdoc
   */
  public function buildQuickForm() {
    $this->addEntityRef('contacts', ts('Locked Contacts'), array(
      'multiple' => TRUE,
    ), FALSE);

    if (count($this->contacts) > 0) {
      $this->setDefaults(array(
        'contacts' => implode(',', $this->contacts)
      ));
    }

    $buttons = array(
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
      array(
        'type' => 'next',
        'name' => 'Save',
        'isDefault' => TRUE,
      ),
    );
    $this->addButtons($buttons);
  }

  /**
   * @inheritdoc
   */
  public function postProcess() {
    $values = $this->controller->exportValues();

    civicrm_api3('CaseContactLock', 'lockcases', array(
      'case_id' => $this->caseID,
      'contact_id' => explode(',', $values['contacts']),
    ));
  }

}
