<?php

use Civi\API\Event\PrepareEvent;
use Civi\API\Event\RespondEvent;
use CRM_Civicase_Service_CaseRoleCreationPostProcess as CaseRoleCreationPostProcess;
use CRM_Civicase_Service_CaseRoleCreationPreProcess as CaseRoleCreationPreProcess;

/**
 * Case role creation listener class.
 */
class CRM_Civicase_Event_Listener_CaseRoleCreation {

  /**
   * Runs the CaseRoleCreationPostProcess onCreate function.
   *
   * When the relationship to be created/updated is related to a case
   * the case role post process function is ran.
   *
   * @param \Civi\API\Event\RespondEvent $event
   *   API Respond Event Object.
   */
  public static function onRespond(RespondEvent $event) {
    $apiRequest = $event->getApiRequest();

    if ($apiRequest['version'] != 3) {
      return;
    }

    if (!self::shouldRun($apiRequest)) {
      return;
    }

    $caseId = self::getCaseId($apiRequest);
    if (empty($caseId)) {
      return;
    }

    $action = !empty($apiRequest['params']['id']) ? 'update' : 'create';
    $responseParams = (array) $event->getResponse();
    if ($action == 'create') {
      $caseCreationPostProcess = new CaseRoleCreationPostProcess();
      $caseCreationPostProcess->onCreate($apiRequest, $responseParams);
    }
  }

  /**
   * Runs the CaseRoleCreationPreProcess onCreate function.
   *
   * When the relationship to be created/updated is related to a case
   * the case role pre process function is ran.
   *
   * @param \Civi\API\Event\PrepareEvent $event
   *   API Prepare Event Object.
   */
  public static function onPrepare(PrepareEvent $event) {
    $apiRequest = $event->getApiRequest();
    if ($apiRequest['version'] != 3) {
      return;
    }

    if (!self::shouldRun($apiRequest)) {
      return;
    }

    $caseId = self::getCaseId($apiRequest);
    if (empty($caseId)) {
      return;
    }

    $relationshipId = self::getRelationshipIdFromRequestParams($apiRequest['params']);
    if ($relationshipId === NULL) {
      $caseCreationPreProcess = new CaseRoleCreationPreProcess();
      $caseCreationPreProcess->onCreate($apiRequest);
      $event->setApiRequest($apiRequest);
    }
    elseif (!isset($apiRequest['params']['id'])) {
      $apiRequest['params']['id'] = $relationshipId;
      $event->setApiRequest($apiRequest);
    }
  }

  /**
   * Return the Id of the relationship for the API request params.
   *
   * The code looks from the ID param, or try to find it by the other received
   * information.
   *
   * @param array $params
   *   Params received from the API request.
   *
   * @return int|null
   *   The ID of the relationship, or NULL in case of not being found.
   */
  private static function getRelationshipIdFromRequestParams(array $params) {
    if (isset($params['id']) && is_numeric($params['id'])) {
      return $params['id'];
    }

    $requiredFields = [
      'relationship_type_id',
      'case_id',
      'contact_id_a',
      'contact_id_b',
    ];

    foreach ($requiredFields as $field) {
      if (!isset($params[$field]) || !is_numeric($params[$field])) {
        return NULL;
      }
    }

    $relationship = civicrm_api3('Relationship', 'get', [
      'relationship_type_id' => $params['relationship_type_id'],
      'contact_id_a' => $params['contact_id_a'],
      'contact_id_b' => $params['contact_id_b'],
      'case_id' => $params['case_id'],
      'is_active' => 1,
      'return' => ['id'],
    ]);

    if ($relationship['count'] === 0) {
      return NULL;
    }

    return array_shift($relationship['values'])['id'];
  }

  /**
   * Determines if the processing will run.
   *
   * @param array $apiRequest
   *   Api request data.
   *
   * @return bool
   *   TRUE if processing should run, FALSE otherwise.
   */
  protected static function shouldRun(array $apiRequest) {
    return $apiRequest['entity'] == 'Relationship' && $apiRequest['action'] == 'create' && empty($apiRequest['params']['skip_post_processing']);
  }

  /**
   * Returns relationship Case Id.
   *
   * @param array $apiRequest
   *   Api Request.
   *
   * @return int|null
   *   Case Id.
   */
  private static function getCaseId(array $apiRequest) {
    if (!empty($apiRequest['params']['id'])) {
      $caseId = self::getRelationshipCase($apiRequest['params']['id']);
    }
    else {
      $caseId = !empty($apiRequest['params']['case_id']) ? $apiRequest['params']['case_id'] : NULL;
    }

    return $caseId;
  }

  /**
   * Gets the Case Id for a relationship.
   *
   * @param int $relId
   *   Relationship Id.
   *
   * @return int|null
   *   Case Id.
   */
  protected static function getRelationshipCase($relId) {
    $result = civicrm_api3('Relationship', 'get', [
      'sequential' => 1,
      'return' => ['case_id'],
      'id' => $relId,
    ]);

    return !empty($result['values'][0]['case_id']) ? $result['values'][0]['case_id'] : NULL;
  }

}
