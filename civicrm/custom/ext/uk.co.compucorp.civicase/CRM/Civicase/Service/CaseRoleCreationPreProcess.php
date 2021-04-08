<?php

/**
 * Case role creation pre-process class.
 */
class CRM_Civicase_Service_CaseRoleCreationPreProcess extends CRM_Civicase_Service_CaseRoleCreationBase {

  /**
   * Function that handles pre-processing for a case related relationship.
   *
   * @param array $requestParams
   *   API request parameters.
   */
  public function onCreate(array &$requestParams) {
    $this->processStartDate($requestParams);
    $this->processRelationshipId($requestParams);
  }

  /**
   * Process start_date param.
   *
   * Basically, when the single case role per type setting is on and the
   * multiclient case setting is off, the start date for a case related
   * relationship is set to today's date.
   *
   * @param array $requestParams
   *   API request parameters.
   */
  private function processStartDate(array &$requestParams) {
    if (
      $this->isSingleCaseRole && !$this->isMultiClient &&
      empty($requestParams['params']['start_date'])
    ) {
      $requestParams['params']['start_date'] = date('Y-m-d');
    }
  }

  /**
   * Adds a default value of false to relationship ID.
   *
   * The frontend code sends this parameter as false, then we fill it for
   * ensuring the same behaviour of the related post handlers.
   *
   * @param array $requestParams
   *   API request parameters.
   */
  private function processRelationshipId(array &$requestParams) {
    if (isset($requestParams['params']) && !array_key_exists('id', $requestParams['params'])) {
      $requestParams['params']['id'] = FALSE;
    }
  }

}
