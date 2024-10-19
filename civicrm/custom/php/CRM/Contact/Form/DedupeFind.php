<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */

/**
 * This class generates form components for DedupeRules.
 */
class CRM_Contact_Form_DedupeFind extends CRM_Admin_Form {

  /**
   * Indicate if this form should warn users of unsaved changes
   * @var bool
   */
  protected $unsavedChangesWarn = FALSE;

  /**
   * Dedupe rule group ID.
   *
   * @var int
   */
  protected $dedupeRuleGroupID;

  /**
   * Pre processing.
   *
   * @throws \CRM_Core_Exception
   */
  public function preProcess(): void {
    $this->dedupeRuleGroupID = CRM_Utils_Request::retrieve('rgid', 'Positive', $this, FALSE, 0);
  }

  /**
   * Build the form object.
   *
   * @throws \CRM_Core_Exception
   */
  public function buildQuickForm(): void {

    //NYSS 4053 - Allow crunching on import groups too!
    $params = array('version'=>3, 'group_type'=>'imported_contacts');
    $result = civicrm_api('group', 'get', $params);
    $importGroups = array(''=>'- All Contacts -');
    foreach($result['values'] as $gid => $fields) {
      $importGroups[$gid] = $fields['title'];
    }
    $this->add('select', 'import_group_id', ts('OR Select Import Group'), $importGroups);

    $groupList = CRM_Core_PseudoConstant::group();
    $groupList[''] = ts('- All Contacts -');
    asort($groupList);

    $this->add('select', 'group_id', ts('Select Group'), $groupList, FALSE, ['class' => 'crm-select2 huge']);
    if (Civi::settings()->get('dedupe_default_limit')) {
      $this->add('text', 'limit', ts('No of contacts to find matches for '));
    }
    $this->addButtons([
      [
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ],
      //hack to support cancel button functionality
      [
        'type' => 'submit',
        'class' => 'cancel',
        'icon' => 'fa-times',
        'name' => ts('Cancel'),
      ],
    ]);
  }

  /**
   * Set the default values for the form.
   *
   * @return array
   */
  public function setDefaultValues(): array {
    $this->_defaults['limit'] = Civi::settings()->get('dedupe_default_limit');
    return $this->_defaults;
  }

  /**
   * Process the form submission.
   */
  public function postProcess(): void {
    $values = $this->exportValues();
    if (!empty($_POST['_qf_DedupeFind_submit'])) {
      //used for cancel button
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/deduperules', 'reset=1'));
      return;
    }
    $url = CRM_Utils_System::url('civicrm/contact/dedupefind', 'reset=1&action=update&rgid=' . $this->getDedupeRuleGroupID());
    if ($values['group_id']) {
      $url .= "&gid={$values['group_id']}";
    }

    if (!empty($values['limit'])) {
      $url .= '&limit=' . $values['limit'];
    }

    // NYSS 4053 - Now check multiple places for the group id.
    if ($gid = CRM_Utils_Array::value('group_id',$values)) {
      $url = CRM_Utils_System::url( 'civicrm/contact/dedupefind', "reset=1&action=update&rgid={$this->rgid}&gid=$gid" );
    }
    elseif ($gid = CRM_Utils_Array::value('import_group_id',$values)) {
      $url = CRM_Utils_System::url( 'civicrm/contact/dedupefind', "reset=1&action=update&rgid={$this->rgid}&gid=$gid" );
    }
    else {
      $url = CRM_Utils_System::url( 'civicrm/contact/dedupefind', "reset=1&action=update&rgid={$this->rgid}" );
    }
        
    CRM_Utils_System::redirect($url);
  }

  /**
   * Get the rule group ID passed in by the url.
   *
   * @todo  - could this ever really be NULL - the retrieveValue does not
   * use $abort so maybe.
   *
   * @return int|null
   */
  public function getDedupeRuleGroupID(): ?int {
    return $this->dedupeRuleGroupID;
  }

}
