<?php

use CRM_Backup_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Backup_Form_Create extends CRM_Core_Form {
  public function buildQuickForm() {

    $this->add('text', 'file_name', 'File Name', ['placeholder' => '.zip'], TRUE);
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Create Backup'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ]
    ]);

    $this->setDefaults([
      'file_name' => date('Ymd-His')
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    $response = CRM_Backup_BAO::create($values['file_name']);

    if ($response) {
      $msg = "Backup file created successfully ({$values['file_name']}).";
      $type = 'success';
    }
    else {
      $msg = "Unable to create backup ({$values['file_name']}).";
      $type = 'error';
    }

    CRM_Core_Session::setStatus(E::ts($msg), 'Create Backup File', $type);

    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
