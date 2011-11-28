<?php

/**
 * $apiRequest is an array with keys:
 *  - entity: string
 *  - action: string
 *  - version: string
 *  - function: callback (mixed)
 *  - params: array, varies
 */
 
function civicrm_api3_generic_getfields($apiRequest) {
  return civicrm_api3_create_success(_civicrm_api_get_fields($apiRequest['entity']));
}

function civicrm_api3_generic_getcount($apiRequest) {
  $result = civicrm_api($apiRequest['entity'],'get',$apiRequest['params']);
  return $result['count'];
}

function civicrm_api3_generic_getsingle($apiRequest) {
  $apiRequest['params']['sequential'] = 1;//so the first entity is always result['values'][0]
  $result = civicrm_api ($apiRequest['entity'],'get',$apiRequest['params']);
  if ($result['is_error'] !== 0) {
    return $result;
  }
  if ($result['count'] === 1) {
    return $result['values'][0];
  }
  if ($result['count'] !== 1) {
    return civicrm_api3_create_error("Expected one ".$apiRequest['entity']." but found " .$result['count'], array ('count'=>$result['count']));
  }
  return civicrm_api3_create_error("Undefined behavior");
}

function civicrm_api3_generic_getvalue($apiRequest) {
  $apiRequest['params']['sequential'] = 1;
  $result=civicrm_api ($apiRequest['entity'],'get',$apiRequest['params']);
  if ($result['is_error'] !== 0) 
    return $result;
  if ($result['count'] !== 1) {
    $result = civicrm_api3_create_error("Expected one ".$apiRequest['entity']." but found " .$result['count'], array ('count'=>$result['count']));
    return $result;
  }

  // we only take "return=" as valid options
  if (CRM_Utils_Array::value('return', $apiRequest['params']) ){
    if (!isset ($result['values'][0][$apiRequest['params']['return']])) {
      return civicrm_api3_create_error("field ".$apiRequest['params']['return']. " unset or not existing", array ('invalid_field'=>$apiRequest['params']['return']));
    }

    return $result['values'][0][$apiRequest['params']['return']];
  }

  return civicrm_api3_create_error("missing param return=field you want to read the value of",array('error_type'=>'mandatory_missing','missing_param'=>'return'));
}

function civicrm_api3_generic_replace($apiRequest) {
  return _civicrm_api3_generic_replace($apiRequest['entity'], $apiRequest['params']);
}
