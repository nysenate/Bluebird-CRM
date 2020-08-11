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
      ['summaryOverlayProfileId' => $summaryOverlayProfileId]);

    //get details about record
    $id = CRM_Utils_Request::retrieve('id', 'Positive');
    $this->add('hidden', 'id', $id);

    $details = CRM_NYSS_Inbox_BAO_Inbox::getDetails($id);
    $this->assign('details', $details);
    //Civi::log()->debug(__FUNCTION__, ['details' => $details]);

    // add form elements
    //11623 dummy field for misdirecting auto-focus
    $this->add('text', 'trick_autofocus', 'Trick Autofocus', ['autofocus' => TRUE]);
    $this->addEntityRef('matches', 'Match Contacts', [
      'create' => TRUE,
      'multiple' => TRUE,
    ], TRUE);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => ts('Assign Matched Contact'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ],
    ]);

    if (!empty($details['matched_ids'])) {
      $this->setDefaults([
        'matches' => $details['matched_ids'],
      ]);
    }

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();

    $this->addFormRule(['CRM_NYSS_Inbox_Form_AssignContact', 'formRule'], $this);
  }

  public static function formRule($fields, $files, $self) {
    /*Civi::log()->debug('', array(
      'fields' => $fields,
      '$_REQUEST' => $_REQUEST,
    ));*/

    $errors = [];
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

    // determine if we need to update the email address or phone.
    foreach (explode(',', $values['matches']) as $matchId) {
      foreach (['email','phone'] as $type) {
        $new_val = CRM_Utils_Array::value("{$type}-" . $matchId, $_REQUEST);
        $orig_val = CRM_Utils_Array::value("{$type}orig-" . $matchId, $_REQUEST);
        if ($new_val != $orig_val) {
          try {
            if (!empty($new_val)) {
              $result = civicrm_api3($type, 'create', [
                'contact_id' => $matchId,
                $type => $new_val,
                'is_primary' => TRUE,
                'location_type_id' => "Home",
              ]);
              //Civi::log()->debug("AssignContact Create type=$type response", array('$result' => $result));
            }
            else {
              //allow an empty value to delete existing email record
              $primary = civicrm_api3($type, 'getsingle', [
                'contact_id' => $matchId,
                'is_primary' => TRUE,
              ]);
              if ($primary[$type] == $orig_val) {
                civicrm_api3($type, 'delete', [
                  'id' => $primary['id'],
                ]);
              }
            }
          }
          catch (CiviCRM_API3_Exception $e) {
          }
        }
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
    $elementNames = [];
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
