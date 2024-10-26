<?php

/**
 * Alters permissions checks.
 */
class CRM_Civicase_Hook_PermissionCheck_ActivityPageView {

  /**
   * Permission checks for activity page view.
   *
   * @param string $permission
   *   Permission name.
   * @param bool $granted
   *   Whether permission is granted or not.
   * @param int|null $contactId
   *   The contact ID to check permission for.
   */
  public function run($permission, &$granted, $contactId) {
    if (!$this->shouldRun($permission)) {
      return;
    }

    $this->validatePermission($granted);
  }

  /**
   * Determines if the hook will run.
   *
   * @param string $permission
   *   Permission name.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($permission) {
    if ($permission == 'access all cases and activities' || $permission == 'access my cases and activities') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Validates permission.
   *
   * Checking if page being viewed is activity detail page
   * and if current logged in user is locked out from cases associated to the
   * activity.
   *
   * @param bool $granted
   *   Whether permission is granted or not.
   */
  private function validatePermission(&$granted) {
    if ($this->isAccessingActivityPageView() && $this->isCurrentUserLockedOut()) {
      $granted = FALSE;
    }
  }

  /**
   * Checks request to see if page being accessed is an activity detail view.
   *
   * @return bool
   *   Return Value.
   */
  private function isAccessingActivityPageView() {
    $action = CRM_Utils_Request::retrieveValue('action', 'String');
    $activityID = CRM_Utils_Request::retrieveValue('id', 'Integer');

    if ($action == CRM_Core_Action::VIEW && intval($activityID) > 0) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Checks if user is locked out.
   *
   * Uses activity being viewed and logged in user to see if user is locked out
   * of case.
   *
   * @return bool
   *   Return Value.
   */
  private function isCurrentUserLockedOut() {
    $loggedContactID = CRM_Core_Session::singleton()->getLoggedInContactID();
    $activityID = CRM_Utils_Request::retrieveValue('id', 'Integer');

    $result = civicrm_api3('CaseContactLock', 'get', [
      'contact_id' => $loggedContactID,
      'api.Activity.getcount' => [
        'case_id' => '$value.case_id',
        'id' => $activityID,
      ],
      'options' => ['limit' => 0],
    ]);

    foreach ($result['values'] as $currentCase) {
      if (intval($currentCase['api.Activity.getcount']) > 0) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
