<?php

class CRM_Ode_OdeAPIWrapper implements API_Wrapper {
  /**
   * the wrapper contains a method that allows you to alter the parameters of the api request (including the action and the entity)
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * Alter the result before returning it to the caller.
   */
  public function toApiOutput($apiRequest, $result) {
    $domainEmails = array();
    foreach ($result['values'] as $key => $values) {
      $domainEmails[$key] = array('text' => $values['label']);
    }
    $suppressEmails = ode_suppressEmails($domainEmails, TRUE);
    $result['values'] = array_intersect_key($result['values'], $domainEmails);
    return $result;
  }

}
