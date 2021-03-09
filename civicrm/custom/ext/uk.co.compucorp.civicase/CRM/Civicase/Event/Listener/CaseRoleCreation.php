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

    $action = !empty($apiRequest['params']['id']) ? 'update' : 'create';
    if ($action == 'create') {
      $caseCreationPreProcess = new CaseRoleCreationPreProcess();
      $caseCreationPreProcess->onCreate($apiRequest);
      $event->setApiRequest($apiRequest);
    }
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
