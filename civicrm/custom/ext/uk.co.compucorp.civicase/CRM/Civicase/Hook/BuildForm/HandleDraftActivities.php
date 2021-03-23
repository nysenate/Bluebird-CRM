<?php

/**
 * HandleDraftActivities BuildForm Hook Class.
 */
class CRM_Civicase_Hook_BuildForm_HandleDraftActivities {

  const PDF_LETTER_ACTIVITY_TYPE = 'Print PDF Letter';
  const EMAIL_ACTIVITY_TYPE = 'Email';
  const SPECIAL_TYPES = [
    self::PDF_LETTER_ACTIVITY_TYPE,
    self::EMAIL_ACTIVITY_TYPE,
  ];

  const PDF_LETTER_FORM_NAME = 'CRM_Contact_Form_Task_PDF';
  const EMAIL_FORM_NAME = 'CRM_Contact_Form_Task_Email';
  const SPECIAL_FORMS = [
    self::PDF_LETTER_FORM_NAME,
    self::EMAIL_FORM_NAME,
  ];

  /**
   * Adds Save Draft button.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param string $formName
   *   Form Name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    if (!$this->shouldRun($form, $formName)) {
      return;
    }

    $this->addSaveDraftButton($form, $formName);
  }

  /**
   * Adds the Save Draft button in the form.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param string $formName
   *   Form Name.
   */
  private function addSaveDraftButton(CRM_Core_Form &$form, $formName) {
    $activityTypeId = $form->getVar('_activityTypeId');
    if ($activityTypeId) {
      $activityType = civicrm_api3('OptionValue', 'getvalue', [
        'return' => "name",
        'option_group_id' => "activity_type",
        'value' => $activityTypeId,
      ]);
    }
    else {
      $activityType = $formName == self::PDF_LETTER_FORM_NAME ? self::PDF_LETTER_ACTIVITY_TYPE : 'Email';
    }
    $id = $form->getVar('_activityId');
    $status = NULL;
    if ($id) {
      $status = civicrm_api3('Activity', 'getsingle', [
        'id' => $id,
        'return' => 'status_id.name',
      ]);
      $status = $status['status_id.name'];
    }
    $checkParams = [
      'option_group_id' => 'activity_type',
      'grouping' => ['LIKE' => '%communication%'],
      'value' => $activityTypeId,
    ];
    if (in_array($activityType, self::SPECIAL_TYPES) ||
      ($activityTypeId && civicrm_api3('OptionValue', 'getcount', $checkParams))) {
      $hideDraftButton = CRM_Utils_Request::retrieve('hideDraftButton', 'Boolean', $form);

      if (($form->_action & (CRM_Core_Action::ADD + CRM_Core_Action::UPDATE)) && !$hideDraftButton) {
        $buttonGroup = $form->getElement('buttons');
        $buttons = $buttonGroup->getElements();
        $buttons[] = $form->createElement('submit', $form->getButtonName('refresh'), ts('Save Draft'), [
          'crm-icon' => 'fa-pencil-square-o',
          'class' => 'crm-form-submit',
        ]);
        $buttonGroup->setElements($buttons);
        $form->addGroup($buttons, 'buttons');
        $form->setDefaults(['status_id' => 2]);
      }
      if ($status == 'Draft' && ($form->_action & CRM_Core_Action::VIEW)) {
        if (in_array($activityType, self::SPECIAL_TYPES)) {
          $atype = $activityType == self::EMAIL_ACTIVITY_TYPE ? 'email' : 'pdf';
          $caseId = civicrm_api3('Activity', 'getsingle', [
            'id' => $id,
            'return' => 'case_id',
          ]);
          $composeUrl = CRM_Utils_System::url("civicrm/activity/$atype/add", [
            'action' => 'add',
            'reset' => 1,
            'caseId' => $caseId['case_id'][0],
            'context' => 'standalone',
            'draft_id' => $id,
          ]);
          $buttonMarkup = '<a class="button" href="' . $composeUrl . '"><i class="crm-i fa-pencil-square-o"></i> &nbsp;' . ts('Continue Editing') . '</a>';
          $form->assign('activityTypeDescription', $buttonMarkup);
        }
        else {
          $form->assign('activityTypeDescription', '<i class="crm-i fa-pencil-square-o"></i> &nbsp;' . ts('Saved as a Draft'));
        }
      }
    }
    // Form email/print activities, set defaults from the original draft
    // activity (which will be deleted on submit)
    if (in_array($formName, self::SPECIAL_FORMS) && !empty($_GET['draft_id'])) {
      $draft = civicrm_api3('Activity', 'get', [
        'id' => $_GET['draft_id'],
        'check_permissions' => TRUE,
        'sequential' => TRUE,
      ]);
      $form->setVar('_activityId', $_GET['draft_id']);
      if (isset($draft['values'][0])) {
        $draft = $draft['values'][0];
        if (in_array($formName, self::SPECIAL_FORMS)) {
          $draft['html_message'] = CRM_Utils_Array::value('details', $draft);
        }
        // Set defaults for to email addresses.
        if ($formName == self::EMAIL_FORM_NAME) {
          $cids = CRM_Utils_Array::value('target_contact_id', civicrm_api3('Activity', 'getsingle', [
            'id' => $draft['id'],
            'return' => 'target_contact_id',
          ]));
          if ($cids) {
            $toContacts = civicrm_api3('Contact', 'get', [
              'id' => ['IN' => $cids],
              'return' => ['email', 'sort_name'],
            ]);
            $toArray = [];
            foreach ($toContacts['values'] as $cid => $contact) {
              $toArray[] = [
                'text' => '"' . $contact['sort_name'] . '" <' . $contact['email'] . '>',
                'id' => "$cid::{$contact['email']}",
              ];
            }
            $form->assign('toContact', json_encode($toArray));
          }
        }
        $form->setDefaults($draft);
      }
    }
  }

  /**
   * Check whether the form is for PDF of Email activity.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param string $formName
   *   Form Name.
   *
   * @return bool
   *   Whether the hook should run or not.
   */
  private function shouldRun(CRM_Core_Form $form, $formName) {
    return is_a($form, 'CRM_Activity_Form_Activity') || in_array($formName, self::SPECIAL_FORMS);
  }

}
