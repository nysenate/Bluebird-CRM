<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_NYSS_Inbox_Form_Delete extends CRM_Core_Form {
  public function buildQuickForm() {
    CRM_NYSS_Inbox_BAO_Inbox::addResources();

    //get details about record
    $rowId = CRM_Utils_Request::retrieve('id', 'Positive');
    $matchedId = CRM_Utils_Request::retrieve('matched_id', 'String');
    if ($matchedId != 'unmatched') {
      //if not unmatched, verify value is a positive int
      $matchedId = CRM_Utils_Request::retrieve('matched_id', 'Positive');
    }

    $this->add('hidden', 'row_id', $rowId);
    $this->add('hidden', 'matched_id', $matchedId);

    $details = CRM_NYSS_Inbox_BAO_Inbox::getDetails($rowId, $matchedId);
    $this->assign('details', $details);

    // add form elements
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Delete Message'),
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
    //Civi::log()->debug('Delete postProcess', array('$values' => $values));

    if (empty($values['row_id'])) {
      CRM_Core_Session::setStatus('Unable to delete this message.');
      return;
    }

    $ids = array(
      array(
        'row_id' => $values['row_id'],
        'matched_id' => CRM_Utils_Array::value('matched_id', $values),
      )
    );

    CRM_NYSS_Inbox_BAO_Inbox::deleteMessages($ids);
    CRM_Core_Session::setStatus('Message has been deleted.');

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
