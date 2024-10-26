<?php

use Civi\API\Event\PrepareEvent;

/**
 * Activity Filter Event Listener Class.
 *
 * Enhance the `Activity.get` API by allowing the option `case_filter`.
 * This accepts any argument supported by `Case.getdetails`.
 *
 * @startCode
 * civicrm_api3('Activity', 'get', array(
 *   'case_filter' => array(
 *     'case_manager' => array('IN' => array(203)),
 *   ),
 * ));
 * @endCode
 */
class CRM_Civicase_Event_Listener_ActivityFilter {

  /**
   * Respond to prepare events when entity is activity and case filter present.
   *
   * Whenever there's a call for `Activity.get case_filter=...`, translate
   * the `case_filter=...` expression to a concrete list of `case_id=1,2,3,...`.
   *
   * @param \Civi\API\Event\PrepareEvent $e
   *   Prepare Event.
   */
  public static function onPrepare(PrepareEvent $e) {
    $apiRequest = $e->getApiRequest();

    if ($apiRequest['version'] != 3) {
      return;
    }

    // Only apply to `Activity.get case_filter=...`.
    if ($apiRequest['entity'] !== 'Activity'
      || $apiRequest['action'] !== 'get'
      || !isset($apiRequest['params']['case_filter'])
    ) {
      return;
    }
    if (isset($apiRequest['params']['case_id'])) {
      throw new API_Exception("case_filter and case_id are mutually exclusive");
    }

    self::updateParams($apiRequest['params']);

    $e->setApiRequest($apiRequest);
  }

  /**
   * Update the Case ID paramete with Case Ids.
   *
   * Translates `case_filter=...` expression to a concrete list of
   * `case_id=1,2,3,...`.
   *
   * @param array $params
   *   API parameters.
   */
  public static function updateParams(array &$params) {
    // Look up matching `case_id`.
    $caseParams = [
      'is_deleted' => 0,
      'options' => [
        'offset' => 0,
        'limit' => 0,
      ],
      'return' => ['id'],
    ];
    CRM_Utils_Array::extend($caseParams, $params['case_filter']);

    if (!empty($params['check_permissions'])) {
      $caseParams['check_permissions'] = $params['check_permissions'];
    }

    $caseResult = civicrm_api3('Case', 'getdetails', $caseParams);

    // Revise the Activity.get call.
    unset($params['case_filter']);
    // Add case ids to the query or else a bogus value to ensure no results.
    $params['case_id'] = empty($caseResult['values']) ? -1 : ['IN' => array_keys($caseResult['values'])];
  }

}
