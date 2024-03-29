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
 * Base Search / View form for *all* listing of multiple contacts.
 */
class CRM_Contact_Form_Search_Basic extends CRM_Contact_Form_Search {

  /**
   * csv - common search values
   *
   * @var array
   */
  public static $csv = ['contact_type', 'group', 'tag'];

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $this->addSortNameField();

    $searchOptions = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
      'advanced_search_options'
    );

    if (!empty($searchOptions['contactType'])) {
      $contactTypes = ['' => ts('- any contact type -')] + CRM_Contact_BAO_ContactType::getSelectElements();
      $this->add('select', 'contact_type',
        ts('is...'),
        $contactTypes,
        FALSE,
        ['class' => 'crm-select2']
      );
    }

    // add select for groups
    // Get hierarchical listing of groups, respecting ACLs for CRM-16836.
    //NYSS 12137
    $groupHierarchy = CRM_Contact_BAO_Group::getGroupsHierarchy($this->_group, NULL, '&nbsp;&nbsp;');
    if (!empty($searchOptions['groups'])) {
      $this->addField('group', [
        'entity' => 'group_contact',
        'label' => ts('in'),
        'placeholder' => ts('- any group -'),
        'options' => $groupHierarchy,
        'type' => 'Select2',
      ]);
    }

    if (!empty($searchOptions['tags'])) {
      // tag criteria
      if (!empty($this->_tag)) {
        $this->addField('tag', [
          'entity' => 'entity_tag',
          'label' => ts('with'),
          'placeholder' => ts('- any tag -'),
        ]);
      }
    }

    parent::buildQuickForm();
  }

  /**
   * Set the default form values.
   *
   *
   * @return array
   *   the default array reference
   */
  public function setDefaultValues() {
    $defaults = [];

    $defaults['sort_name'] = $this->_formValues['sort_name'] ?? NULL;
    foreach (self::$csv as $v) {
      if (!empty($this->_formValues[$v]) && is_array($this->_formValues[$v])) {
        $tmpArray = array_keys($this->_formValues[$v]);
        $defaults[$v] = array_pop($tmpArray);
      }
      else {
        $defaults[$v] = '';
      }
    }

    if ($this->_context === 'amtg') {
      $defaults['task'] = CRM_Contact_Task::GROUP_ADD;
    }

    if ($this->_context === 'smog') {
      $defaults['group_contact_status[Added]'] = TRUE;
    }

    return $defaults;
  }

  /**
   * Add local and global form rules.
   */
  public function addRules() {
    $this->addFormRule(['CRM_Contact_Form_Search_Basic', 'formRule']);
  }

  /**
   * Processing needed for buildForm and later.
   */
  public function preProcess() {
    // SearchFormName is deprecated & to be removed - the replacement is for the task to
    // call $this->form->getSearchFormValues()
    // A couple of extensions use it.
    $this->set('searchFormName', 'Basic');

    parent::preProcess();
  }

  /**
   * @return array
   */
  public function &getFormValues() {
    return $this->_formValues;
  }

  /**
   * This method is called for processing a submitted search form.
   */
  public function postProcess() {
    $this->set('isAdvanced', '0');
    $this->set('isSearchBuilder', '0');

    // get user submitted values
    // get it from controller only if form has been submitted, else preProcess has set this
    if (!empty($_POST)) {
      $this->_formValues = $this->controller->exportValues($this->_name);
    }

    if (isset($this->_groupID) && empty($this->_formValues['group'])) {
      $this->_formValues['group'] = $this->_groupID;
    }
    elseif (isset($this->_ssID) && empty($_POST)) {
      // if we are editing / running a saved search and the form has not been posted
      $this->_formValues = CRM_Contact_BAO_SavedSearch::getFormValues($this->_ssID);

      //fix for CRM-1505
      if (CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_SavedSearch', $this->_ssID, 'mapping_id')) {
        $this->_params = CRM_Contact_BAO_SavedSearch::getSearchParams($this->_ssID);
      }
    }

    // we dont want to store the sortByCharacter in the formValue, it is more like
    // a filter on the result set
    // this filter is reset if we click on the search button
    if ($this->_sortByCharacter !== NULL && empty($_POST)) {
      if (strtolower($this->_sortByCharacter) == 'all') {
        $this->_formValues['sortByCharacter'] = NULL;
      }
      else {
        $this->_formValues['sortByCharacter'] = $this->_sortByCharacter;
      }
    }
    else {
      $this->_sortByCharacter = NULL;
    }

    $this->_params = CRM_Contact_BAO_Query::convertFormValues($this->_formValues);
    $this->_returnProperties = &$this->returnProperties();

    parent::postProcess();
  }

  /**
   * Add a form rule for this form.
   *
   * If Go is pressed then we must select some checkboxes and an action.
   *
   * @param array $fields
   * @param array $files
   * @param object $form
   *
   * @return array|bool
   */
  public static function formRule($fields, $files, $form) {
    // check actionName and if next, then do not repeat a search, since we are going to the next page
    if (array_key_exists('_qf_Search_next', $fields)) {
      if (empty($fields['task'])) {
        return ['task' => 'Please select a valid action.'];
      }

      if (CRM_Utils_Array::value('task', $fields) == CRM_Contact_Task::SAVE_SEARCH) {
        // dont need to check for selection of contacts for saving search
        return TRUE;
      }

      // if the all contact option is selected, ignore the contact checkbox validation
      if ($fields['radio_ts'] == 'ts_all') {
        return TRUE;
      }

      foreach ($fields as $name => $dontCare) {
        if (substr($name, 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX) {
          return TRUE;
        }
      }
      return ['task' => 'Please select one or more checkboxes to perform the action on.'];
    }
    return TRUE;
  }

  /**
   * Return a descriptive name for the page, used in wizard header
   *
   * @return string
   */

  /**
   * @return string
   */
  public function getTitle() {
    return ts('Find Contacts');
  }

}
