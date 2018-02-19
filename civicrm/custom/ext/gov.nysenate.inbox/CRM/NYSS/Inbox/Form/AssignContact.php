<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_NYSS_Inbox_Form_AssignContact extends CRM_Core_Form {
  public function buildQuickForm() {
    CRM_NYSS_Inbox_BAO_Inbox::addResources('assign');
    $summaryOverlayProfileId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'summary_overlay', 'id', 'name');
    CRM_Core_Resources::singleton()->addVars('NYSS',
      array('summaryOverlayProfileId' => $summaryOverlayProfileId));

    //get details about record
    $id = CRM_Utils_Request::retrieve('id', 'Positive');
    $this->add('hidden', 'id', $id);

    $details = CRM_NYSS_Inbox_BAO_Inbox::getDetails($id);
    $this->assign('details', $details);

    // add form elements
    //11623 dummy field for misdirecting auto-focus
    $this->add('text', 'trick_autofocus', 'Trick Autofocus', array('autofocus' => TRUE));
    $this->addEntityRef('matches', 'Match Contacts', array(
      'api' => array(
        'params' => array('contact_type' => 'Individual'),
      ),
      'create' => TRUE,
      'multiple' => TRUE,
    ), TRUE);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Assign Matched Contact'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ));

    if (!empty($details['matched_ids'])) {
      $this->setDefaults(array(
        'matches' => $details['matched_ids'],
      ));
    }

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();

    $this->addFormRule(array('CRM_NYSS_Inbox_Form_AssignContact', 'formRule'), $this);
  }

  public static function formRule($fields, $files, $self) {
    /*Civi::log()->debug('', array(
      'fields' => $fields,
      '$_REQUEST' => $_REQUEST,
    ));*/

    $errors = array();
    foreach ($fields as $field => $value) {
      if (strpos($field, 'email-') !== FALSE) {
        if (!empty($value) && !CRM_Utils_Rule::email($value)) {
          $errors['qfKey'] = 'Please enter valid email addresses.';
        }
      }
    }

    return $errors;
  }

  public function postProcess() {
    $values = $this->exportValues();
    //Civi::log()->debug('AssignContact postProcess', array('values' => $values, '$_REQUEST' => $_REQUEST));

    $response = CRM_NYSS_Inbox_BAO_Inbox::assignMessage($values['id'], explode(',', $values['matches']));
    //Civi::log()->debug('AssignContact postProcess', array('$response' => $response));

    //determine if we need to update the email address
    foreach (explode(',', $values['matches']) as $matchId) {
      $email = CRM_Utils_Array::value('email-'.$matchId, $_REQUEST);
      $emailOrig = CRM_Utils_Array::value('emailorig-'.$matchId, $_REQUEST);

      if ($email != $emailOrig) {
        try {
          if (!empty($email)) {
            civicrm_api3('email', 'create', [
              'contact_id' => $matchId,
              'email' => $email,
              'is_primary' => TRUE,
            ]);
          }
          else {
            //allow an empty value to delete existing email record
            $primaryEmail = civicrm_api3('email', 'getsingle', array(
              'contact_id' => $matchId,
              'is_primary' => TRUE,
            ));

            if ($primaryEmail['email'] == $emailOrig) {
              civicrm_api3('email', 'delete', array(
                'id' => $primaryEmail['id'],
              ));
            }
          }
        }
        catch (CiviCRM_API3_Exception $e) {}
      }
    }

    $message = 'The message has been matched.';
    if (!empty($response['message'])) {
      $message = $response['message'];
    }

    CRM_Core_Session::setStatus($message);
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
