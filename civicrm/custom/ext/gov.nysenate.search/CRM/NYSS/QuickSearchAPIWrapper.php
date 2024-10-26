<?php

class CRM_NYSS_QuickSearchAPIWrapper implements API_Wrapper {
  /**
   * @param array $apiRequest
   * @return array
   *
   * 14379
   */
  public function fromApiInput($apiRequest) {
    //Civi::log()->debug(__FUNCTION__, ['apiRequest' => $apiRequest]);

    //change api call
    $apiRequest['entity'] = 'Case';
    $apiRequest['action'] = 'get';
    $apiRequest['function'] = 'CRM_NYSS_BAO_NYSS::getQuickSearchCaseId';

    //remove unused params
    //unset($apiRequest['params']['name']);
    unset($apiRequest['params']['field_name']);
    unset($apiRequest['params']['table_name']);

    return $apiRequest;
  }

  public function toApiOutput($apiRequest, $result) {
    /*Civi::log()->debug(__FUNCTION__, [
      'apiRequest' => $apiRequest,
      'result' => $result,
    ]);*/

    return $result;
  }
}
