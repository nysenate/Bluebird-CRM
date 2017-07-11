<?php

//http://sd99.crmdev.nysenate.gov/civicrm/nyss/testing?reset=1

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_NYSS_Testing_Form_RunTests extends CRM_Core_Form {
  public function buildQuickForm() {

    // add form elements
    $this->add(
      'select', // field type
      'test_type', // field name
      'Test Type', // field label
      array(
        'create_address' => 'Create Address',
        'create_address_with_sage' => 'Create Address trigger SAGE manually',
      ), // list of options
      TRUE // is required
    );
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

  public function postProcess() {
    $values = $this->exportValues();
    Civi::log()->debug('postProcess', array('values' => $values));

    switch ($values['test_type']) {
      case 'create_address':
        $response = $this->testCreateAddress();
        break;

      default:
    }

    Civi::log()->debug("RunTest: {$values['test_type']}", array('$response' => $response));
    CRM_Core_Error::debug("RunTest: {$values['test_type']}", $response);

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

  function testCreateAddress() {
    $params = array(
      'first_name' => 'Contact',
      'last_name' => 'RunTest',
      'street_address' => '47 Norwood Ave',
      'supplemental_addresss_1' => NULL,
      'city' => 'Albany',
      'state_province' => 'NY',
      'postal_code' => '12208',
    );
    $cid = CRM_NYSS_BAO_Integration_Website::createContact($params);

    $response = civicrm_api3('address', 'get', array(
      'contact_id' => $cid,
    ));

    return $response;
  }
}
