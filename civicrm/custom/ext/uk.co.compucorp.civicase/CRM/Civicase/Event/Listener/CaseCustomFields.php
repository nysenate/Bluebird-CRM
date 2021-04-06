<?php

use Civi\API\Event\PrepareEvent;

/**
 * Case Custom Fields Load Listener Class.
 */
class CRM_Civicase_Event_Listener_CaseCustomFields {

  /**
   * Avoid unnecessary joins to custom Field tables.
   *
   * When no return parameters is passed, instead of returning all basic Case
   * fields in addition to the case custom date, only the case basic fields are
   * returned.
   *
   * By default civicrm will join to all custom field tables for an entity
   * in order to return all the custom fields for that entity in the case where
   * specific return parameters are not passed.
   *
   * This can cause issues in sites that have more than 61 custom fields
   * enabled for the Case entity as there is a Mysql 61 tables Join limit.
   *
   * Civi does not do the JOIN intelligently because there might just be
   * 20 related custom field set for a particular Case but civi joins to all
   * the custom field tables even the unnecessary ones.
   *
   * This change allows to pass the custom fields needed in return parameters
   * and will not return custom data otherwise.
   * Also the CustomTree.gettreevalues API will return exactly the related
   * custom data for an entity without any unnecessary join to all the
   * custom group tables.
   *
   * @param \Civi\API\Event\PrepareEvent $event
   *   API Prepare Event Object.
   */
  public static function loadOnDemand(PrepareEvent $event) {
    $apiRequest = $event->getApiRequest();
    if ($apiRequest['version'] != 3) {
      return;
    }

    if (!self::shouldRun($apiRequest)) {
      return;
    }

    if (empty($apiRequest['params']['return'])) {
      $supportedFields = array_keys(CRM_Case_DAO_Case::getSupportedFields());
      $supportedFields = array_merge($supportedFields, [
        'contact_id',
        'client_id',
      ]);
      if (!empty($apiRequest['params']['id'])) {
        $supportedFields = array_merge($supportedFields, [
          'contacts',
          'activities',
        ]);
      }
      $apiRequest['params']['return'] = $supportedFields;
      $event->setApiRequest($apiRequest);
    }
  }

  /**
   * Determines if the processing will run.
   *
   * @param array $apiRequest
   *   Api request data.
   *
   * @return bool
   *   TRUE if processing should run, FALSE otherwise.
   */
  protected static function shouldRun(array $apiRequest) {
    return $apiRequest['entity'] == 'Case' && $apiRequest['action'] == 'get';
  }

}
