<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_NYSS_Inbox_Form_Process extends CRM_Core_Form {
  public function buildQuickForm() {
    CRM_NYSS_Inbox_BAO_Inbox::addResources('process');

    //get details about record
    $id = CRM_Utils_Request::retrieve('id', 'Positive');
    $this->add('hidden', 'id', $id);

    $details = CRM_NYSS_Inbox_BAO_Inbox::getDetails($id);
    $this->assign('details', $details);
    $this->add('hidden', 'activity_id', $details['activity_id']);
    $this->add('hidden', 'current_assignee', $details['matched_to']);

    //assignment form elements
    $this->addEntityRef('assignee', 'Select Assignee', array(
      'api' => array(
        'params' => array('contact_type' => 'Individual'),
      ),
      'create' => TRUE,
    ), FALSE);

    //tag form elements
    $this->addEntityRef('contact_keywords', 'Keywords', array(
      'entity' => 'tag',
      'multiple' => TRUE,
      'create' => TRUE,
      'api' => array('params' => array('parent_id' => 296)),
      'data-entity_table' => 'civicrm_contact',
      'data-entity_id' => NULL,
      'class' => "crm-contact-tagset",
    ), FALSE);

    CRM_Core_Resources::singleton()->addVars('NYSS', array('matched_to' => $details['matched_to']));
    $this->addEntityRef('contact_positions', 'Positions', array(
      'entity' => 'nyss_tags',
      'multiple' => TRUE,
      'create' => FALSE,
      'api' => array('params' => array('parent_id' => 292)),
      'class' => "crm-contact-tagset",
    ), FALSE);

    $this->addEntityRef('activity_keywords', 'Keywords', array(
      'entity' => 'tag',
      'multiple' => TRUE,
      'create' => TRUE,
      'api' => array('params' => array('parent_id' => 296)),
      'data-entity_table' => 'civicrm_activity',
      'data-entity_id' => NULL,
      'class' => "crm-activity-tagset",
    ), FALSE);

    //edit activity form elements
    $staffGroupID = civicrm_api3('group', 'getvalue', array('name' => 'Office_Staff', 'return' => 'id'));
    $this->addEntityRef('activity_assignee', 'Assign Activity to', array(
      'api' => array(
        'params' => array(
          'contact_type' => 'Individual',
          'group' => $staffGroupID,
        ),
      ),
      'create' => FALSE,
    ), FALSE);
    $statusTypes = CRM_Core_PseudoConstant::activityStatus();
    $this->add('select', 'activity_status', 'Status',
      array('' => '- select status -') + $statusTypes, FALSE);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Process'),
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
    //Civi::log()->debug('postProcess', array('values' => $values, '$_REQUEST' => $_REQUEST));

    if (empty($values['id'])) {
      CRM_Core_Session::setStatus('Unable to process this message.');
      return;
    }

    $msg = CRM_NYSS_Inbox_BAO_Inbox::processMessages($values);
    $msg = (!empty($msg)) ? implode('<br />', $msg) : 'Message(s) has been processed.';
    CRM_Core_Session::setStatus($msg);

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
