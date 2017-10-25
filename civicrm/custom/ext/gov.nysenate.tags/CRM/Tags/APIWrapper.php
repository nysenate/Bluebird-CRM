<?php

/**
 * Class CRM_Tags_APIWrapper
 *
 * Originally created to support leg position selection in contact record
 * Unused as we are handling the API configuration via the entityRef hook
 * Leaving in place in case we need to resurrect at some point...
 */

class CRM_Tags_APIWrapper {
  public function fromApiInput($apiRequest) {
    /*Civi::log()->debug('fromApiInput', array(
      '$apiRequest' => $apiRequest,
    ));*/
    //TODO should we be calling the nyss_tags api here?

    return $apiRequest;
  }

  /**
   * alter the result before returning it to the caller.
   */
  public function toApiOutput($apiRequest, $result) {
    /*Civi::log()->debug('toApiOutput', array(
      '$apiRequest' => $apiRequest,
      '$result' => $result,
    ));*/

    /*$result = civicrm_api3('nyss_tags', 'getlist', $apiRequest['params']);
    Civi::log()->debug('toApiOutput AFTER', array(
      '$apiRequest' => $apiRequest,
      '$result (after)' => $result,
    ));*/

    return $result;
  }
}
