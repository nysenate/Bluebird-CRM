<?php

/**
 * Case role creation post process class.
 */
class CRM_Civicase_Service_CaseRoleCreationPostProcess extends CRM_Civicase_Service_CaseRoleCreationBase {

  /**
   * Function that handles post processing for a case related relationship.
   *
   * For when the single Case role  setting is active, on creation
   * of a case role, all past case roles for that relationship type is set to
   * inactive and the new case role is re-assigned.
   *
   * When the single case is inactive and the `reassign_rel_id` parameter is
   * passed, the relationship Id passed is set inactive.
   *
   * In all scenarios, the Assign Case Role activity is created with the
   * appropriate parameters.
   *
   * @param array $requestParams
   *   API request parameters.
   * @param array $responseParams
   *   API response parameters.
   */
  public function onCreate(array $requestParams, array $responseParams) {
    $currentRelContactName = $this->getContactNames([$requestParams['params']['contact_id_b']]);
    $previousRelContactName = NULL;
    $relTypeDetails = $this->getRelationshipTypeDetails($requestParams['params']['relationship_type_id']);
    if ($this->isSingleCaseRole && !$this->isMultiClient) {
      $existingRelationship = $this->getOtherActiveExistingRelationships($requestParams['params']['relationship_type_id'], $requestParams['params']['case_id'], $responseParams['id']);
      if ($existingRelationship) {
        $previousRelContacts = array_column($existingRelationship, 'contact_id_b');
        $previousRelContactName = $this->getContactNames($previousRelContacts);
      }
    }
    else {
      $reassignRelId = !empty($requestParams['params']['reassign_rel_id']) ? $requestParams['params']['reassign_rel_id'] : [];
      if ($reassignRelId) {
        $existingRelationship = $this->getRelationshipDetails($reassignRelId);
        $this->validateReassignRelationship($existingRelationship[0]['relationship_type_id'], $requestParams['params']['relationship_type_id']);
        $previousRelContacts = array_column($existingRelationship, 'contact_id_b');
        $previousRelContactName = $this->getContactNames($previousRelContacts);
      }
    }

    if (!empty($existingRelationship)) {
      $endDate = !empty($requestParams['params']['start_date'])
        ? $requestParams['params']['start_date']
        : date('Y-m-d');

      $this->setRelationshipsInactive(
        array_column($existingRelationship, 'id'),
        $endDate
      );
    }

    $activitySubject = $this->getActivitySubjectOnCreate($currentRelContactName, $relTypeDetails, $previousRelContactName);
    $this->createCaseActivity($requestParams['params']['case_id'], 'Assign Case Role', $activitySubject);
  }

  /**
   * Get relationship details.
   *
   * @param int $relId
   *   Relationship Id.
   *
   * @return array
   *   Relationship details.
   */
  private function getRelationshipDetails($relId) {
    $result = civicrm_api3('Relationship', 'get', [
      'sequential' => 1,
      'id' => $relId,
    ]);

    if (empty($result['values'])) {
      return [];
    }

    return $result['values'];
  }

  /**
   * Get relationship type details.
   *
   * @param int $relTypeId
   *   Relationship type Id.
   *
   * @return array
   *   Relationship Type details.
   */
  private function getRelationshipTypeDetails($relTypeId) {
    $result = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'id' => $relTypeId,
    ]);

    if (empty($result['values'])) {
      return [];
    }

    return $result['values'][0];
  }

  /**
   * Returns a concatenated string of contact names.
   *
   * @param array $contactIds
   *   Array of contact Ids.
   *
   * @return string
   *   Contact names.
   */
  private function getContactNames(array $contactIds) {
    $result = civicrm_api3('Contact', 'get', [
      'sequential' => 1,
      'return' => ['display_name'],
      'id' => ['IN' => $contactIds],
    ]);

    if (empty($result['values'])) {
      return '';
    }

    $contactName = '';
    foreach ($result['values'] as $contactDetails) {
      $contactName .= $contactDetails['display_name'] . ', ';
    }

    return rtrim($contactName, ', ');
  }

  /**
   * Returns relationship data.
   *
   * Returns relationship data for the case and relationship type ID excluding
   * the current relationship Id just added.
   *
   * @param int $relTypeId
   *   Relationship type Id.
   * @param int $caseId
   *   Case Id.
   * @param int $currentRelId
   *   Current relationship Id.
   *
   * @return array
   *   Relationships data.
   */
  private function getOtherActiveExistingRelationships($relTypeId, $caseId, $currentRelId) {
    $result = civicrm_api3('Relationship', 'get', [
      'sequential' => 1,
      'case_id' => $caseId,
      'relationship_type_id' => $relTypeId,
      'id' => ['!=' => $currentRelId],
      'is_active' => 1,
    ]);

    return $result['values'];
  }

  /**
   * Set the relationship Id's inactive using the given end date.
   *
   * @param array $relIds
   *   Relationship Ids.
   * @param string $endDate
   *   End date to use when setting the relationships as innactive.
   */
  private function setRelationshipsInactive(array $relIds, string $endDate) {
    foreach ($relIds as $relId) {
      civicrm_api3('Relationship', 'create', [
        'id' => $relId,
        'is_active' => 0,
        'end_date' => $endDate,
        'skip_post_processing' => 1,
      ]);
    }
  }

  /**
   * Creates the case activity.
   *
   * @param int $caseId
   *   Case Id.
   * @param string $activityType
   *   Activity Type.
   * @param string $subject
   *   Activity Subject.
   */
  private function createCaseActivity($caseId, $activityType, $subject) {
    civicrm_api3('Activity', 'create', [
      'case_id' => $caseId,
      'target_contact_id' => $this->getCaseClients($caseId),
      'status_id' => 'Completed',
      'activity_type_id' => $activityType,
      'subject' => $subject,
      'source_contact_id' => CRM_Core_Session::getLoggedInContactID(),
    ]);
  }

  /**
   * Gets the case clients for a case.
   *
   * @param int $caseId
   *   Case Id.
   *
   * @return array
   *   Array of case client contact Id's.
   */
  private function getCaseClients($caseId) {
    $result = civicrm_api3('CaseContact', 'get', [
      'case_id' => $caseId,
    ]);

    $clients = [];
    foreach ($result['values'] as $caseContact) {
      $clients[] = $caseContact['contact_id'];
    }

    return $clients;
  }

  /**
   * Return Activity Subject.
   *
   * @param string $currentContactName
   *   Contact name B of relationship just added.
   * @param array $relTypeDetails
   *   Relationship Type data.
   * @param string|null $previousContactName
   *   Contact name B of previous relationship.
   *
   * @return string
   *   Activity subject.
   */
  private function getActivitySubjectOnCreate($currentContactName, array $relTypeDetails, $previousContactName = NULL) {
    if (empty($previousContactName)) {
      return "{$currentContactName} added as {$relTypeDetails['label_b_a']}";
    }

    return "{$currentContactName} replaced {$previousContactName} as {$relTypeDetails['label_b_a']}";
  }

  /**
   * Validates that the Relationship Type Id of the two relationships match.
   *
   * @param int $reassignRelTypeId
   *   Relationship type Id of reasssigned relationship.
   * @param int $newRelTypeId
   *   Relationship type Id of the new relationship.
   */
  private function validateReassignRelationship($reassignRelTypeId, $newRelTypeId) {
    $isSameRelationshipType = $reassignRelTypeId == $newRelTypeId;

    if (!$isSameRelationshipType) {
      throw new Exception('The relationship type Id of the role to reassign must match the new relationship type Id');
    }
  }

}
