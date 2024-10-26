<?php

/**
 * Add bulk email as an activity to all the selected cases.
 */
class CRM_Civicase_Hook_PostProcess_AttachEmailActivityToAllCases {

  /**
   * Add bulk email as an activity to all the selected cases.
   *
   * @param string $formName
   *   The class name of the submitted form.
   * @param object $form
   *   The submitted form instance.
   */
  public function run($formName, $form) {
    if (!$this->shouldRun($formName)) {
      return;
    }

    $this->addActivityToAllSelectedCases();
  }

  /**
   * Add bulk email as an activity to all the selected cases.
   */
  private function addActivityToAllSelectedCases() {
    try {
      $firstCaseId = CRM_Utils_Request::retrieve('caseid', 'Integer');
      $allCaseIds = array_diff(
        explode(',', CRM_Utils_Request::retrieve('allCaseIds', 'CommaSeparatedIntegers')),
        [$firstCaseId]
      );
      $activity = civicrm_api3('Activity', 'get', [
        'case_id' => $firstCaseId,
        'activity_type_id' => 'Email',
        'source_contact_id' => CRM_Core_Session::getLoggedInContactID(),
        'options' => [
          'limit' => 1,
          'sort' => "id DESC",
        ],
        'sequential' => 1,
        'return' => ['id'],
      ]);
    }
    catch (Throwable $ex) {
      return;
    }
    $activityId = !empty($activity['id']) ? $activity['id'] : NULL;
    if ($activityId) {
      foreach ($allCaseIds as $caseId) {
        $caseActivity = new CRM_Case_DAO_CaseActivity();
        $caseActivity->case_id = $caseId;
        $caseActivity->activity_id = $activityId;
        $caseActivity->save();
      }
    }
  }

  /**
   * Check whether the hook should run or not.
   *
   * @param string $formName
   *   The name for the current form.
   *
   * @return bool
   *   Whether the hook should run or not.
   */
  private function shouldRun($formName) {
    return $formName === CRM_Contact_Form_Task_Email::class &&
      !empty(CRM_Utils_Array::value('allCaseIds', $_GET, '0')) &&
      !empty(CRM_Utils_Array::value('caseid', $_GET, '0'));
  }

}
