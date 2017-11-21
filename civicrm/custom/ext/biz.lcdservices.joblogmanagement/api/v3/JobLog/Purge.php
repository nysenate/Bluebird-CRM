<?php

/**
 * JobLog.Purge API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_job_log_Purge_spec(&$spec) {
  $spec['days_retained'] = array (
    'api.required' => 1,
    'api.default'  => 90,
    'title'        => ts('Days retained'),
    'type'         => CRM_Utils_Type::T_INT,
  );
  $spec['api_call'] = array (
    'api.default' => 'all',
    'title'       => ts('API call'),
    'type'        => CRM_Utils_Type::T_STRING,
  );
}

/**
 * JobLog.Purge API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_job_log_Purge($params) {
  if (CRM_Utils_Array::value('days_retained', $params)) {
    // sql to delete the job logs older than the day_retained setting value
    $deleteQuery = "DELETE FROM civicrm_job_log WHERE 1";
    $where = " AND run_time < (NOW() - INTERVAL {$params['days_retained']} DAY)";

    // Check if the param "api_call" is set and it's not set to 'all'
    if (CRM_Utils_Array::value('api_call', $params) &&
    $params['api_call'] != 'all') {
      $apiCall   = explode('.', $params['api_call']);
      $apiEntity = $apiCall[0];
      $apiAction = $apiCall[1];

      // Let's get the job_id based on the above info
      try {
        $result = civicrm_api3('Job', 'get', array(
          'sequential' => 1,
          'api_entity' => $apiEntity,
          'api_action' => $apiAction,
        ));
      }
      catch (CiviCRM_API3_Exception $e) {
        $error = $e->getMessage();
        CRM_Core_Error::debug_log_message($error);
      }

      // Throw error if the api_call wasn't found
      if (empty($result['values'])) {
        CRM_Core_Session::setStatus(ts('Could not find api_call by name "'.$params['api_call'].'". Please make sure it\'s in this format - [api_entity].[api_action]. For eg - job.version_check'), ts('Error'), 'error');
        return;
      }

      // Get the job_id from the API result and append it to query so it only deletes the log records specific to that job_type
      $jobID = $result['values'][0]['id'];
      if ($jobID) {
        $where .= " AND job_id = {$jobID}";
      }
    }

    // All done let's delete the job logs
    $deleteQuery .= $where;
    CRM_Core_DAO::executeQuery($deleteQuery);

    return civicrm_api3_create_success(1, $params, 'JobLog', 'Purge');
  } else {
    throw new API_Exception('Could not purge job logs');
  }
}

