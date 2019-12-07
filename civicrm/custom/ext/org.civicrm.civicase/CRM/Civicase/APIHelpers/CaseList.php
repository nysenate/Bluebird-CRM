<?php

/**
 * Implements helper methods to obtain list of cases and columns allowed to be
 * viewed on case lists for dashboard, searches and contacts.
 */
class CRM_Civicase_APIHelpers_CaseList implements API_Wrapper {

  /**
   * Returns list of allowed headers that can be shown on case lists.
   *
   * @return array
   */
  public function getAllowedHeaders() {
    return array(
      'values' => array(
        array(
          'name' => 'next_activity',
          'label' => ts('Next Activity'),
          'sort' => 'next_activity',
          'display_type' => 'activity_card',
        ),
        array(
          'name' => 'subject',
          'label' => ts('Subject'),
          'sort' => 'subject',
          'display_type' => 'default',
        ),
        array(
          'name' => 'status',
          'label' => ts('Status'),
          'sort' => 'status_id.label',
          'display_type' => 'status_badge',
        ),
        array(
          'name' => 'case_type',
          'label' => ts('Type'),
          'sort' => 'case_type_id.title',
          'display_type' => 'default',
        ),
        array(
          'name' => 'manager',
          'label' => ts('Case Manager'),
          'sort' => 'case_manager.sort_name',
          'display_type' => 'contact_reference',
        ),
        array(
          'name' => 'start_date',
          'label' => ts('Start Date'),
          'sort' => 'start_date',
          'display_type' => 'date',
        ),
        array(
          'name' => 'modified_date',
          'label' => ts('Last Updated'),
          'sort' => 'modified_date',
          'display_type' => 'date',
        ),
        array(
          'name' => 'myRole',
          'label' => ts('My Role'),
          'sort' => 'my_role.label_b_a',
          'display_type' => 'multiple_values',
        ),
      )
    );
  }

  /**
   * Returns list of cases to be shown on case lists.
   *
   * @param array $params
   *   Parameters array for the API call
   *
   * @return array
   *   Result with the list of cases to be shown
   */
  public function getCaseList($params) {
    $loggedContactID = CRM_Core_Session::singleton()->getLoggedInContactID();

    $defaultAPIReturnedColumns = array(
      'subject', 'case_type_id', 'status_id', 'is_deleted', 'start_date',
      'modified_date', 'contacts', 'activity_summary', 'category_count',
      'tag_id.name', 'tag_id.color', 'tag_id.description',
    );
    $params['return'] = (isset($params['return']) ? array_merge($defaultAPIReturnedColumns, $params['return']) : $defaultAPIReturnedColumns);
    $cases = civicrm_api3('Case', 'getdetails', $params);

    foreach ($cases['values'] as &$case) {
      foreach ($case['contacts'] as $contact) {
        if ($contact['manager'] == 1) {
          $case['manager'] = $contact;
        }

        if ($loggedContactID == $contact['contact_id']) {
          $case['myRole'][] = $contact['role'];
        }
      }

      $case['next_activity'] = CRM_Utils_Array::value(0, $case['activity_summary']['next']);
    }

    return $cases;
  }

  /**
   * @inheritdoc
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * @inheritdoc
   */
  public function toApiOutput($apiRequest, $result) {
    if ($apiRequest['action'] === 'getcaselistheaders') {
      if (
        CRM_Core_Permission::check('basic case information') &&
        !CRM_Core_Permission::check('administer CiviCase') &&
        !CRM_Core_Permission::check('access my cases and activities') &&
        !CRM_Core_Permission::check('access all cases and activities')
      ) {
        foreach ($result['values'] as $key => $header) {
          if ($header['name'] === 'next_activity') {
            unset($result['values'][$key]);
          }
        }
      }
    }

    return $result;
  }

}
