<?php

#[CRM_NYSS_Attribute_IssueRefs('17369')]
class CRM_NYSS_Case_APIWrapper {

  public static function respond($event) {
    $request = $event->getApiRequestSig();
    $apiRequest = $event->getApiRequest();
    $result = $event->getResponse();

    switch($request) {
      case '3.case.getlist':
        // NYSS 17155
        $new_result = self::change_case_select_label($event);
        $event->setResponse($new_result);
        break;
      case '3.casecontact.get':
        // NYSS 17369
        if (isset($apiRequest['params']['search_field_fallback'])) {
          self::extend_case_contact_search($event);
        }
    }
  }

  /**
   * Show Case ID and Subject in Case select form fields label. Alter the description
   * to avoid repetition.
   * @see NYSS 17155
   */
  public static function change_case_select_label($event) {
    $result = $event->getResponse();
    for ($i=0; $i<sizeof($result['values']??[]); $i++) {
      $desc_1 = $result['values'][$i]['description'][1];
      $new_label = 'Case #' . $result['values'][$i]['id'] . " - $desc_1";
      $result['values'][$i]['description'][0] = preg_replace('/^#\d+\:\s+/','',$result['values'][$i]['description'][0]);
      unset($result['values'][$i]['description'][1]);
      $result['values'][$i]['description'][] = $result['values'][$i]['label'];
      $result['values'][$i]['label'] = $new_label;
    }
    return $result;
  }

  #[CRM_NYSS_Attribute_IssueRefs('17369')]
  public static function extend_case_contact_search($event) {
    $result= $event->getResponse();
    $apiRequest = $event->getApiRequest();
    $params = $apiRequest['params'];
    // NYSS #17369
    // Replicate search_field_fallback behavior from...
    // civicrm/api/v3/Generic/Getlist.php _civicrm_api3_generic_getlist_get_result
    // because the Case Contact API doesn't provide a search_field_fallback.
    if (isset($params['contact_id.sort_name']['LIKE']) &&
        isset($params['search_field_fallback']) &&
        $result['count'] < $apiRequest['params']['options']['limit']) {
      //Civi::log()->debug('Result Count', [count($result)]);
      $apiRequest['params'][$params['search_field_fallback']] = ['LIKE' => $params['contact_id.sort_name']['LIKE']];
      if ($apiRequest['params']['options']['sort'] === 'contact_id.sort_name') {
        $apiRequest['params']['options']['sort'] = $params['search_field_fallback'];
      }
      $apiRequest['params']['contact_id.sort_name'] = ['NOT LIKE' => $apiRequest['params']['contact_id.sort_name']['LIKE']];
      $apiRequest['params']['options']['limit'] -= $result['count'];
      // Remove search_field_fallback to prevent recursion (I assume it might recurse)
      unset($apiRequest['params']['search_field_fallback']);

      $result2 = civicrm_api3($apiRequest['entity'], 'get', $apiRequest['params']);
      $result['values'] = array_merge($result['values'], $result2['values']);
      $result['count'] = count($result['values']);
      $event->setResponse($result);
    }
  }
}
