<?php

class CRM_NYSS_Mail_APIWrapper implements API_Wrapper {
  public function fromApiInput($apiRequest) {
    //Civi::log()->debug('', ['$apiRequest' => $apiRequest]);

    $apiRequest['params']['params']['group_type'] = ['LIKE' => "%3%"];
    return $apiRequest;
  }

  public function toApiOutput($apiRequest, $result) {
    return $result;
  }
}
