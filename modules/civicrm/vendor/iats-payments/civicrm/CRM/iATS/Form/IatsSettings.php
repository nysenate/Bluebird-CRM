<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_iATS_Form_IatsSettings extends CRM_Core_Form {
  function buildQuickForm() {

    // add form elements
    $this->add(
      'checkbox', // field type
      'receipt_recurring', // field name
      ts('Enable email receipting for each recurring contribution.')
    );
    $result = CRM_Core_BAO_Setting::getItem('iATS Payments Extension', 'iats_settings');
    $defaults = (empty($result)) ? array() : $result;
    $this->setDefaults($defaults);
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $values = $this->exportValues();
    foreach(array('qfKey','_qf_default','_qf_IatsSettings_submit','entryURL') as $key) {
      if (isset($values[$key])) {
        unset($values[$key]);
      }
    }
    CRM_Core_BAO_Setting::setItem($values, 'iATS Payments Extension', 'iats_settings');
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
