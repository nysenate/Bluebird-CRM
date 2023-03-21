<?php

class CRM_Mosaico_API4Wrappers_MosaicoTemplate implements API_Wrapper {

  /**
   * Add current domain_id in whereClause for the API request.
   */
  public function fromApiInput($apiRequest) {
    $domainId = CRM_Core_Config::domainID();

    $params = $apiRequest->getParams();
    $apiRequest->addWhere('domain_id', '=', $domainId);
    return $apiRequest;
  }

  public function toApiOutput($apiRequest, $result) {
    return $result;
  }

}
