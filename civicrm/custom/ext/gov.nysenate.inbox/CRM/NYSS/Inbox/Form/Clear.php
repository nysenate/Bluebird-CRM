<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_NYSS_Inbox_Form_Clear extends CRM_Core_Form {
  public function buildQuickForm() {
    CRM_NYSS_Inbox_BAO_Inbox::addResources();

    //get details about record
    $id = CRM_Utils_Request::retrieve('id', 'Positive');
    $this->add('hidden', 'row_id', $id);

    $details = CRM_NYSS_Inbox_BAO_Inbox::getDetails($id);
    $this->assign('details', $details);

    // add form elements
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Clear'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    if (empty($values['row_id'])) {
      CRM_Core_Session::setStatus('Unable to clear this message.');
      return;
    }

    CRM_NYSS_Inbox_BAO_Inbox::clearMessages(array($values['row_id']));
    CRM_Core_Session::setStatus('Message has been cleared.');

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
