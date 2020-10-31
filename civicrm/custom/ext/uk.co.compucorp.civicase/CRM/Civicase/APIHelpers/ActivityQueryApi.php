<?php

use CRM_Civicase_APIHelpers_GenericApi as GenericApiHelper;

/**
 * Class CRM_Civicase_APIHelpers_ActivityQueryApi.
 */
class CRM_Civicase_APIHelpers_ActivityQueryApi {

  /**
   * Validates the Case Query related API parameters.
   *
   * @param array $params
   *   API parameters.
   */
  public function validateParameters(array $params) {
    if (!empty($params['params']) && !empty($params['id'])) {
      throw new API_Exception("Please send either the params or Id");
    }

    if (empty($params['params']) && empty($params['id'])) {
      throw new API_Exception("Both params and Id cannot be empty");
    }
  }

  /**
   * Returns the parameters for making calls to Activity.get.
   *
   * @param array $params
   *   API parameters.
   *
   * @return array|string
   *   The parameters for making call to Activity.get.
   */
  public function getActivityGetRequestApiParams(array $params) {
    $genericApiHelper = new GenericApiHelper();
    $apiParams = '';

    if (!empty($params['id'])) {
      $apiParams = ['id' => ['IN' => $genericApiHelper->getParameterValue($params, 'id')]];
    }

    if (!empty($params['params'])) {
      $apiParams = $params['params'];
    }

    return $apiParams;
  }

}
