<?php

/**
 * Class CRM_Civicase_ActivityFilter
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
class CRM_Civicase_ActivityFilter {

  /**
   * Whenever there's a call for `Activity.get case_filter=...`, translate
   * the `case_filter=...` expression to a concrete list of `case_id=1,2,3,...`.
   *
   * @param \Civi\API\Event\PrepareEvent $e
   * @throws \API_Exception
   */
  public static function onPrepare(\Civi\API\Event\PrepareEvent $e) {
    $apiRequest = $e->getApiRequest();

    // Only apply to `Activity.get case_filter=...`
    if ($apiRequest['entity'] !== 'Activity'
      || $apiRequest['action'] !== 'get'
      || !isset($apiRequest['params']['case_filter'])
    ) {
      return;
    }
    if (isset($apiRequest['params']['case_id'])) {
      throw new API_Exception("case_filter and case_id are mutually exclusive");
    }

    // Look up matching `case_id`
    $caseParams = array(
      'is_deleted' => 0,
      'options' => array(
        'offset' => 0,
        'limit' => 0,
      ),
      'return' => array('id'),
    );
    CRM_Utils_Array::extend($caseParams, $apiRequest['params']['case_filter']);
    if (!empty($apiRequest['params']['check_permissions'])) {
      $caseParams['check_permissions'] = $apiRequest['params']['check_permissions'];
    }
    $caseResult = civicrm_api3('Case', 'getdetails', $caseParams);

    // Revise the Activity.get call
    unset($apiRequest['params']['case_filter']);
    // Add case ids to the query or else a bogus value to ensure no results
    $apiRequest['params']['case_id'] = empty($caseResult['values']) ? -1 : array('IN' => array_keys($caseResult['values']));
    $e->setApiRequest($apiRequest);
  }

}
