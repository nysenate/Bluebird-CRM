<?php

use Civi\Api4\CiviCase;
use CRM_NYSS_Case_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_NYSS_Case_Form_CreateCase extends CRM_Core_Form {

  public function buildQuickForm() {

    $this->add(
      'hidden',
      'contact_id',
      CRM_Utils_Request::retrieve('cid', 'Positive'),
    );

    $this->add(
      'text',
      'subject',
      'Subject',
      [],
      TRUE,
    );

    $this->add(
      'select',
      'case_type_id',
      'Case Type',
      CRM_Case_BAO_Case::buildOptions('case_type_id', 'create'),
      TRUE
    );

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ],
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    CiviCase::create(FALSE)
      ->setValues([
        'contact_id' => $values['contact_id'],
        'case_type_id' => $values['case_type_id'],
        'subject' => $values['subject'],
      ])
      ->execute();

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
