<?php

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
    }
  }

  /**
   * Show Case ID and Subject in Case select form fields label. Alter the description
   * to avoid repetition.
   * @see NYSS 17155
   */
  public static function change_case_select_label($event) {
    $result = $event->getResponse();
    for ($i=0; $i<sizeof($result['values']); $i++) {
      $desc_1 = $result['values'][$i]['description'][1];
      $new_label = 'Case #' . $result['values'][$i]['id'] . " - $desc_1";
      $result['values'][$i]['description'][0] = preg_replace('/^#\d+\:\s+/','',$result['values'][$i]['description'][0]);
      unset($result['values'][$i]['description'][1]);
      $result['values'][$i]['description'][] = $result['values'][$i]['label'];
      $result['values'][$i]['label'] = $new_label;
    }
    return $result;
  }

}