<?php

class CRM_Ode_OdeAPIWrapper {

  /**
   * Alter the result before returning it to the caller.
   */
  public static function RESPOND(\Civi\API\Event\RespondEvent $event) {
    $request = $event->getAPIRequestSig();
    $apiRequest = $event->getAPIRequest();
    $result = $event->getResponse();

    switch ($request) {
      case '3.siteemailaddress.get':
        self::optionValueV3Respond($event->getAPIKernel(), $apiRequest['params'], $result, $apiRequest['action'], $apiRequest['entity'], $apiRequest['version']);
        $event->setResponse($result);
        break;

      case '4.siteemailaddress.get':
        $parsedResult = self::siteEmailAddressRespond($apiRequest, $result);
        $result->exchangeArray($parsedResult);
        $result->count = count($parsedResult);
        $event->setResponse($result);
        break;

    }
  }


  protected static function optionValueV3Respond($apiKernel, &$params, &$result, $action, $entity, $version) {
    $domainEmails = array();
    foreach ($result['values'] as $key => $values) {
      if (array_key_exists('email', $values)) {
        $domainEmails[$key] = array('text' => "Test <" . $values['email'] . ">");
      }
    }
    $suppressEmails = ode_suppressEmails($domainEmails, TRUE);
    $result['values'] = array_intersect_key($result['values'], $suppressEmails);
    $result['count'] = count($result['values']);
  }

  protected static function siteEmailAddressRespond($apiRequest, $result): array {
    $domainEmails = [];
    $records = $result->getArrayCopy();
    foreach ($records as $key => $siteEmailAddress) {
      $domainEmails[$key] = ['text' => "Test <" . $siteEmailAddress['email'] . ">"];
    }
    $suppressEmails = ode_suppressEmails($domainEmails, TRUE);
    return array_values(array_intersect_key($records, $suppressEmails));
  }

}
