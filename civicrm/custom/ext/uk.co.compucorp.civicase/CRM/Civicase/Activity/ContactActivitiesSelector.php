<?php

/**
 * Class CRM_Civicase_Activity_ContactActivitiesSelector.
 */
class CRM_Civicase_Activity_ContactActivitiesSelector {

  const API_DEFAULT_LIMIT = 25;

  /**
   * Get Acitivities for a Contact.
   *
   * Returns all the activities for a given contact. The contact must be
   * either the creator, the client, or be one of the assignees for the
   * activity. Also, the activity should not be assigned to someone else
   * unless the contact is also an assignee.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   Activities.
   */
  public function getAllActivitiesForContact(array $params) {
    $newParams = $this->getParamsWithoutOffsetsAndLimits($params);

    $this->addAssigneeContactIdToReturnParams($newParams);

    $activities = civicrm_api3('Activity', 'get', $newParams);

    $this->filterOutActivitiesNotBelongingToContact($activities, $newParams);

    return $activities;
  }

  /**
   * Returns paginated activities for the given contact.
   *
   * Uses the limit and offset options.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   Activities.
   */
  public function getPaginatedActivitiesForContact(array $params) {
    $activities = $this->getAllActivitiesForContact($params);

    $this->paginateActivityRecords($activities, $params);

    return $activities;
  }

  /**
   * Returns the number of activities for the given contact.
   *
   * @param array $params
   *   Parameters.
   *
   * @return int
   *   Count.
   */
  public function getActivitiesForContactCount(array $params) {
    $activities = $this->getAllActivitiesForContact($params);

    return $activities['count'];
  }

  /**
   * Returns the original parameters, but without any offsets or limits.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   Paramaters.
   */
  private function getParamsWithoutOffsetsAndLimits(array $params) {
    $options = CRM_Utils_Array::value('options', $params, []);
    $options['limit'] = 0;
    $options['offset'] = 0;
    $params['options'] = $options;

    return $params;
  }

  /**
   * Adds the `assignee_contact_id` field to the return parameter.
   *
   * This field is necesary in order to properly filter the activities for the
   * contact and remove activities that have been delegated to someone else.
   *
   * @param array $params
   *   Parameters.
   */
  private function addAssigneeContactIdToReturnParams(array &$params) {
    $return = (array) CRM_Utils_Array::value('return', $params, []);
    $return[] = 'assignee_contact_id';
    $return = array_unique($return);
    $return = implode(',', $return);
    $params['return'] = $return;
  }

  /**
   * Removes activities assigned to other contacts.
   *
   * Removes any activities that have been assigned to another contact other
   * than the requested one. It also updates the activities count in order to
   * reflect the new value.
   *
   * @param array $activities
   *   Activities.
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   Activities.
   */
  private function filterOutActivitiesNotBelongingToContact(array &$activities, array $params) {
    $activities['values'] = array_filter($activities['values'], function ($activity) use ($params) {
      $hasNoAssignee = empty($activity['assignee_contact_id']);
      $isContactAssignedToActivity = in_array($params['contact_id'], $activity['assignee_contact_id']);

      return $hasNoAssignee || $isContactAssignedToActivity;
    });

    $activities['count'] = count($activities['values']);
  }

  /**
   * Paginates the activity records according to the limit and offset params.
   *
   * @param array $activities
   *   Activities.
   * @param array $params
   *   Paramaters.
   */
  private function paginateActivityRecords(array &$activities, array $params) {
    $options = CRM_Utils_Array::value('options', $params, []);
    $limit = CRM_Utils_Array::value('limit', $options, self::API_DEFAULT_LIMIT);
    $offset = CRM_Utils_Array::value('offset', $options, 0);
    $limit = $limit === 0 ? NULL : $limit;

    $activities['values'] = array_slice($activities['values'], $offset, $limit);
    $activities['count'] = count($activities['values']);
  }

}
