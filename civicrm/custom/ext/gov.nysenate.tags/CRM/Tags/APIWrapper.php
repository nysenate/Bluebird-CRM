<?php

/**
 * Class CRM_Tags_APIWrapper
 *
 * Originally created to support leg position selection in contact record
 * Unused as we are handling the API configuration via the entityRef hook
 * Leaving in place in case we need to resurrect at some point...
 */

class CRM_Tags_APIWrapper {
  /**
   * Callback to wrap completetransaction API calls.
   */
  public static function PREPARE($event) {
    $request = $event->getApiRequestSig();
    //Civi::log()->debug(__FUNCTION__, ['request' => $request]);

    switch ($request) {
      // Wrap completetransaction in the v3 API.
      // Doesn't exist yet in the v4 API.
      case '3.tag.get':
        $event->wrapAPI(['CRM_Tags_APIWrapper', 'completeTransaction']);
        break;
      default:
    }
  }

  /**
   * <insert appropriate docs here>
   * @param array $apiRequest
   * @param array $callsame - function callback see \Civi\Api\Provider\WrappingProvider
   *
   * #14336
   */
  public static function completeTransaction($apiRequest, $callsame) {
    //Civi::log()->debug(__FUNCTION__, ['$apiRequest' => $apiRequest]);

    if (!empty($apiRequest['params']['name']['LIKE'])
      && !str_starts_with($apiRequest['params']['name']['LIKE'], '%')
    ) {
      $apiRequest['params']['name']['LIKE'] = '%'.$apiRequest['params']['name']['LIKE'];
    }

    //fix for tag search for numerical values
    if (!empty($apiRequest['params']['options']['offset']) &&
      $apiRequest['params']['options']['offset'] < 0
    ) {
      $apiRequest['params']['options']['offset'] = 0;
    }

    return $callsame($apiRequest);
  }
}
