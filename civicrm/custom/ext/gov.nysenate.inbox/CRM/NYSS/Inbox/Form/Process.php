<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_NYSS_Inbox_Form_Process extends CRM_Core_Form {

  /**
   * Pre process form.
   */
  public function preProcess() {
    $this->setAction(CRM_Core_Action::UPDATE + CRM_Core_Action::ADD);
  }

  public function buildQuickForm() {
    CRM_NYSS_Inbox_BAO_Inbox::addResources('process');

    if ($isMultiple = CRM_Utils_Request::retrieve('multi', 'Boolean')) {
      $this->add('hidden', 'is_multiple', $isMultiple);
      $this->assign('is_multiple', $isMultiple);

      $multiIds = $matchedIds = array();
      $multiIdPairs = CRM_Utils_Request::retrieve('ids', 'String');
      if (empty($multiIdPairs)) {
        return array(
          'is_error' => TRUE,
          'message' => 'Unable to process messages. Please try re-selecting records.'
        );
      }

      foreach ($multiIdPairs as $pair) {
        $pairParts = explode('-', $pair);

        /**
         * multiIds = array of key values LESS details
         *   - this is stored in hidden field to be passed to postProcess
         * multiIdsDetails = array of key values WITH details
         *   - this is assigned to the template so we can display details
         * matchedIds = array of just match contacts
         *   - to generate count and passed to javascript
         */

        //we shouldn't have unmatched records in the mix, but just in case...
        if ($pairParts[1] != 'unmatched') {
          $details = CRM_NYSS_Inbox_BAO_Inbox::getDetails($pairParts[0], $pairParts[1]);
          $data = [
            'row_id' => $pairParts[0],
            'matched_id' => $pairParts[1],
            'activity_id' => $details['activity_id'],
            'current_assignee' => $pairParts[1],
          ];
          $multiIds[] = $data;
          $multiIdsDetails[] = $data + array('details' => $details);
          $matchedIds[] = $pairParts[1];
        }
      }
      $this->assign('multiple_count', count($multiIdPairs));
      $this->assign('message_details', $multiIdsDetails);
      $this->add('hidden', 'multi_ids', json_encode($multiIds));
      CRM_Core_Resources::singleton()->addVars('NYSS', array('matched_id' => $matchedIds));
    }
    else {
      //get details about record
      $rowId = CRM_Utils_Request::retrieve('id', 'Positive');
      $matchedId = CRM_Utils_Request::retrieve('matched_id', 'Positive');

      $this->add('hidden', 'row_id', $rowId);
      $this->add('hidden', 'matched_id', $matchedId);

      $details = CRM_NYSS_Inbox_BAO_Inbox::getDetails($rowId, $matchedId);
      $this->assign('message_details', array(array('details' => $details)));
      $this->add('hidden', 'activity_id', $details['activity_id']);
      CRM_Core_Resources::singleton()->addVars('NYSS', array('matched_id' => $matchedId));
    }
    /*Civi::log()->debug('CRM_NYSS_Inbox_Form_Process', array(
      'isMultiple' => $isMultiple,
      '$multiIdPairs' => $multiIdPairs,
      'request' => $_REQUEST,
      '$multiIds' => $multiIds,
      '$details' => $details,
    ));*/

    //assignment form elements
    $this->addEntityRef('assignee', 'Select Assignee', array(
      'api' => array(
        'params' => array(
          'contact_type' => 'Individual',
        ),
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

    //tag tree
    $tags = CRM_Core_BAO_Tag::getColorTags('civicrm_contact');
    if (!empty($tags)) {
      $this->add('select2', 'tag', ts('Issue Codes'), $tags, FALSE, array('class' => 'huge', 'placeholder' => ts('- select -'), 'multiple' => TRUE));
    }

    //groups
    $allGroups = CRM_Core_PseudoConstant::group();
    $groupHierarchy = CRM_Contact_BAO_Group::getGroupsHierarchy($allGroups, NULL, '&nbsp;&nbsp;', TRUE);
    $this->add('select', 'group_id', 'Group(s)', $groupHierarchy, FALSE,
      array('id' => 'group_id', 'multiple' => 'multiple', 'class' => 'crm-select2 twenty')
    );

    //edit activity form elements
    $staffGroupID = civicrm_api3('group', 'getvalue', array('name' => 'Office_Staff', 'return' => 'id'));
    $this->addEntityRef('activity_assignee', 'Assign Activity to', array(
      'api' => array(
        'params' => array(
          'contact_type' => 'Individual',
          'group' => $staffGroupID,
        ),
        'add_wildcard' => TRUE,
      ),
      'create' => FALSE,
      'select' => array('minimumInputLength' => 0),
    ), FALSE);
    $statusTypes = CRM_Core_PseudoConstant::activityStatus();
    $this->add('select', 'activity_status', 'Status',
      array('' => '- select status -') + $statusTypes, FALSE);

    $this->addButtons(array(
      array(
        'type' => 'upload',
        'subName' => 'update',
        'name' => ts('Update'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'upload',
        'subName' => 'updateclear',
        'name' => ts('Update and Clear'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'upload',
        'subName' => 'clear',
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
    /*Civi::log()->debug('postProcess', array(
      'values' => $values,
      '$_REQUEST' => $_REQUEST,
    ));*/

    if ((empty($values['row_id']) || empty($values['matched_id'])) && !$values['is_multiple']) {
      CRM_Core_Session::setStatus('Unable to process this message.');
      return;
    }

    $actionName = $this->controller->getButtonName( );
    $class = get_class($this);
    $classElements = explode('_', $class);
    $classSub = end($classElements);
    //CRM_Core_Error::debug_var('actionName', $actionName);
    //CRM_Core_Error::debug_var('classSub', $classSub);

    $rows = array($values['row_id']);
    if ($values['is_multiple']) {
      $rows = CRM_NYSS_Inbox_BAO_Inbox::getMultiRowIds(json_decode($values['multi_ids'], TRUE));
    }

    switch ($actionName) {
      case "_qf_{$classSub}_upload_update":
        $msg = CRM_NYSS_Inbox_BAO_Inbox::processMessages($values);
        $msg = (!empty($msg)) ? implode('<br />', $msg) : 'Message(s) has been processed.';
        break;

      case "_qf_{$classSub}_upload_updateclear":
        $msg = CRM_NYSS_Inbox_BAO_Inbox::processMessages($values);
        CRM_NYSS_Inbox_BAO_Inbox::clearMessages($rows);
        $msg = (!empty($msg)) ? implode('<br />', $msg) : 'Message(s) has been processed and cleared.';
        break;

      case "_qf_{$classSub}_upload_clear":
        CRM_NYSS_Inbox_BAO_Inbox::clearMessages($rows);
        $msg = 'Message(s) has been cleared.';
        break;

      default:
        $msg = 'Unable to perform a processing action.';
    }

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

  function getDefaultContext() {
    return 'create';
  }

  function getDefaultEntity() {
    return 'GroupContact';
  }
}
