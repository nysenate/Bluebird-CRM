<?php

/**
 * DoctorWhen.Run API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_doctor_when_run_spec(&$spec) {
  $spec['tasks']['api.required'] = 1;
}

/**
 * DoctorWhen.Run API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_doctor_when_run($params) {
  $cleanups = new CRM_DoctorWhen_Cleanups();

  $options = CRM_Utils_Array::subset($params, array('tasks'));
  if (is_string($options['tasks'])) {
    $options['tasks'] = explode(',', $options['tasks']);
  }

  if (empty($options['tasks'])) {
    throw new API_Exception("The list of tasks must not be empty.");
  }
  elseif (in_array('*', $options['tasks'])) {
    $options['tasks'] = array_keys($cleanups->getAllActive());
  }

  $queueRunner = new CRM_Queue_Runner(array(
    'title' => ts('CiviCRM Cleanup Tasks'),
    'queue' => $cleanups->buildQueue($options),
  ));
  $queueResult = $queueRunner->runAll();
  if ($queueResult !== TRUE) {
    $errorMessage = CRM_Core_Error::formatTextException($queueResult['exception']);
    CRM_Core_Error::debug_log_message($errorMessage);
    throw $queueResult['exception']; // FIXME test
  }

  return civicrm_api3_create_success(array(), $params, 'DoctorWhen', 'run');

}
