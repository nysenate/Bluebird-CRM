<?php

use CRM_Contactlayout_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Contactlayout_Form_Inline_ProfileBlock extends CRM_Profile_Form_Edit {

  /**
   * Form for editing profile blocks
   */
  public function preProcess() {
    $relatedContactId = CRM_Utils_Request::retrieveValue('rel_cid', 'Positive', NULL, FALSE);
    $viewedContactId = CRM_Utils_Request::retrieveValue('cid', 'Positive', NULL, TRUE);
    $contactId = $relatedContactId ? $relatedContactId : $viewedContactId;

    if (!empty($contactId)) {
      $this->set('id', $contactId);
    }
    parent::preProcess();
    // Suppress profile status messages like the double-opt-in warning
    CRM_Core_Session::singleton()->getStatus(TRUE);
  }

  public function buildQuickForm(): void {
    parent::buildQuickForm();
    $buttons = array(
      array(
        'type' => 'upload',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ),
    );
    $this->addButtons($buttons);
    $this->assign('help_pre', $this->_ufGroup['help_pre'] ?? NULL);
    $this->assign('help_post', $this->_ufGroup['help_post'] ?? NULL);

    // Special handling for contact id element
    if ($this->elementExists('id')) {
      $cidElement = $this->getElement('id');
      $cidElement->freeze();
      $cidElement->setValue($this->_id);
    }

    // Special handling for groups
    if ($this->elementExists('group')) {
      $groupElement = $this->getElement('group');
      $label = $groupElement->getLabel();
      $frozen = $groupElement->isFrozen();
      $this->removeElement('group');
      $groups = CRM_Contact_BAO_Group::getGroupsHierarchy(CRM_Core_PseudoConstant::group(), NULL, '&nbsp;&nbsp;', TRUE);
      $groupElement = $this->add('select', 'group', $label, $groups, FALSE, ['class' => 'crm-select2', 'multiple' => TRUE]);
      if ($frozen) {
        $groupElement->freeze();
      }
    }

    // Add tag sets (profiles in core don't support this)
    if ($this->elementExists('tag')) {
      $parentNames = CRM_Core_BAO_Tag::getTagSet('civicrm_contact');
      CRM_Core_Form_Tag::buildQuickForm($this, $parentNames, 'civicrm_contact', $this->_id, FALSE, TRUE);
    }

    // Special handling for employer
    if ($this->elementExists('current_employer')) {
      $employerField = $this->getElement('current_employer');
      $frozen = $employerField->isFrozen();
      $employerField = $this->addEntityRef('current_employer', $employerField->getLabel(), [
        'create' => TRUE,
        'multiple' => TRUE,
        'api' => ['params' => ['contact_type' => 'Organization']],
      ]);
      if ($frozen) {
        $employerField->freeze();
      }
    }
  }

  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    if ($this->elementExists('group')) {
      $defaults['group'] = array_column(CRM_Contact_BAO_GroupContact::getContactGroup($this->_id, 'Added'), 'group_id');
    }
    if ($this->elementExists('current_employer')) {
      $employers = self::getEmployers($this->_id);
      $defaults['current_employer'] = array_column($employers, 'contact_id');
    }
    return $defaults;
  }

  /**
   * Save profiles
   *
   * @throws \CRM_Core_Exception
   */
  public function postProcess() {
    $values = $origValues = $this->controller->exportValues($this->_name);
    // Ignore value from contact id field
    unset($values['id']);
    $values['contact_id'] = $cid = $this->_id;
    $values['profile_id'] = $this->_gid;

    // Action is needed for tag postprocess
    $this->_action = CRM_Core_Action::UPDATE;

    $fields = civicrm_api3('Contact', 'getfields')['values'];

    $this->processEmployer($values);
    if (isset($values['group'])) {
      $this->processGroups($values);
    }
    // Process image
    if (!empty($values['image_URL'])) {
      CRM_Contact_BAO_Contact::processImageParams($values);
    }
    // Reformat checkbox values
    foreach ($fields as $name => $info) {
      if (($info['html_type'] ?? NULL) === 'CheckBox' && !empty($values[$name]) && is_array($values[$name])) {
        $values[$name] = array_keys(array_filter($values[$name]));
      }
    }

    civicrm_api3('Profile', 'submit', $values);

    // Save tagsets (not handled by profile api)
    if (!empty($values['contact_taglist'])) {
      CRM_Core_Form_Tag::postProcess($values['contact_taglist'], $cid, 'civicrm_contact', $this);
    }

    // Refresh tabs affected by this profile
    foreach (['tag', 'group', 'note'] as $set) {
      if (isset($origValues[$set])) {
        $this->ajaxResponse['updateTabs']["#tab_$set"] = CRM_Contact_BAO_Contact::getCountComponent($set, $this->_id);
      }
    }

    // These are normally performed by CRM_Contact_Form_Inline postprocessing but this form doesn't inherit from that class.
    CRM_Core_BAO_Log::register($cid,
      'civicrm_contact',
      $cid
    );
    $this->ajaxResponse = array_merge(
      CRM_Contact_Form_Inline::renderFooter($cid),
      $this->ajaxResponse,
      CRM_Contact_Form_Inline_Lock::getResponse($cid)
    );
  }

  /**
   * @param int $cid
   * @return array
   * @throws \CRM_Core_Exception
   */
  public static function getEmployers($cid) {
    $relationships = Civi\Api4\Relationship::get()
      ->setSelect(['contact_id_b', 'contact_id_b.display_name'])
      ->setCheckPermissions(FALSE)
      ->addWhere('is_active', '=', '1')
      ->addWhere('contact_id_a', '=', $cid)
      ->addWhere('relationship_type_id.name_a_b', '=', 'Employee of')
      ->addWhere('is_current', '=', TRUE)
      ->execute();
    $results = [];
    foreach ($relationships as $relationship) {
      $results[] = [
        'id' => $relationship['id'],
        'contact_id' => $relationship['contact_id_b'],
        'display_name' => $relationship['contact_id_b.display_name'],
      ];
    }
    return $results;
  }

  /**
   * Handles setting one or more employers for a contact.
   *
   * @param $values
   * @throws \CRM_Core_Exception
   */
  public function processEmployer(&$values) {
    if (isset($values['current_employer'])) {
      if (is_string($values['current_employer'])) {
        $values['current_employer'] = $values['current_employer'] ? explode(',', $values['current_employer']) : [];
      }
      $existingEmployers = array_column(self::getEmployers($this->_id), 'contact_id', 'id');
      foreach ($existingEmployers as $id => $employer) {
        if (!in_array($employer, $values['current_employer'])) {
          Civi\Api4\Relationship::update()
            ->addWhere('id', '=', $id)
            ->setCheckPermissions(FALSE)
            ->addValue('is_active', '0')
            ->execute();
        }
      }
      $employerRelationshipType = Civi\Api4\RelationshipType::get()
        ->setSelect(["id"])
        ->addWhere("name_a_b", "=", "Employee of")
        ->execute()
        ->first()['id'];
      foreach (array_values($values['current_employer']) as $i => $employer) {
        if (!in_array($employer, $existingEmployers)) {
          Civi\Api4\Relationship::create()
            ->setCheckPermissions(FALSE)
            ->addValue('relationship_type_id', $employerRelationshipType)
            ->addValue('contact_id_a', $this->_id)
            ->addValue('contact_id_b', $employer)
            ->execute();
        }
        // Set first org as "current employer" since CiviCRM only allows one
        if (!$i) {
          CRM_Contact_BAO_Contact_Utils::setCurrentEmployer([$this->_id => $employer]);
        }
      }
      // Refresh relationship tab
      $this->ajaxResponse['updateTabs']['#tab_rel'] = CRM_Contact_BAO_Contact::getCountComponent('rel', $this->_id);
      unset($values['current_employer']);
    }
  }

  public function processGroups(&$values) {
    $currentGroups = array_column(CRM_Contact_BAO_GroupContact::getContactGroup($this->_id, 'Added'), 'group_id');
    $submitted = $values['group'] ?: [];
    $toAdd = array_diff($submitted, $currentGroups);
    $toRemove = array_diff($currentGroups, $submitted);
    if ($toAdd) {
      $updated = Civi\Api4\GroupContact::update()
        ->setCheckPermissions(FALSE)
        ->setReload(TRUE)
        ->setMethod(E::ts('Admin'))
        ->addValue('status', 'Added')
        ->addWhere('contact_id', '=', $this->_id)
        ->addWhere('group_id', 'IN', $toAdd)
        ->execute();
      $updated = array_column((array) $updated, 'group_id');
      foreach (array_diff($toAdd, $updated) as $groupId) {
        Civi\Api4\GroupContact::create()
          ->setCheckPermissions(FALSE)
          ->setMethod(E::ts('Admin'))
          ->addValue('status', 'Added')
          ->addValue('contact_id', $this->_id)
          ->addValue('group_id', $groupId)
          ->execute();
      }
    }
    if ($toRemove) {
      Civi\Api4\GroupContact::update()
        ->setCheckPermissions(FALSE)
        ->setMethod(E::ts('Admin'))
        ->addValue('status', 'Removed')
        ->addWhere('contact_id', '=', $this->_id)
        ->addWhere('group_id', 'IN', $toRemove)
        ->execute();
    }
    unset($values['group']);
  }

}
