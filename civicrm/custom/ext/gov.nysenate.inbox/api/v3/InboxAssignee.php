<?php
/**
 * API functions to support inbox polling feature
 */

/**
 * Adjust Metadata for Get action
 *
 * The metadata is used for setting defaults, documentation, validation, aliases, etc.
 *
 * @param array $params
 */
function _civicrm_api3_inbox_assignee_get_spec(&$params) {
}

/**
 * Returns array of project contacts matching a set of one or more properties
 *
 * @param array $params  Array of one or more valid
 *                       property_name=>value pairs.
 *
 * @return array  Array of matching project contacts
 * {@getfields volunteer_project_contact_get}
 * @access public
 */
function civicrm_api3_inbox_assignee_contact_get($params) {
  Civi::log()->debug('civicrm_api3_inbox_assignee_contact_get', array('params' => $params));
  $sql = "
    
  ";

  $result = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  if (!empty($result['values'])) {
    foreach ($result['values'] as &$projectContact) {
      //In some contexts we are passing 'return' => 'contact_id' in with $params
      //In this case, there is no relationship_type_id returned as part of the results set above
      //Following that, when you pass a null value into getsingle, it finds 3 results and errors out
      //This solution was created to fall back on relationship_type_id if present in
      //$params, and if not, skip loading the relationship type label.
      $rType = false;
      $rType = (array_key_exists("relationship_type_id", $params) ) ? $params['relationship_type_id'] : $rType;
      $rType = (array_key_exists("relationship_type_id", $projectContact) ) ? $projectContact['relationship_type_id'] : $rType;

      if ($rType) {
        $optionValue = civicrm_api3('OptionValue', 'getsingle', array(
          'option_group_id' => CRM_Volunteer_BAO_ProjectContact::RELATIONSHIP_OPTION_GROUP,
          'value' => $rType
        ));

        $projectContact['relationship_type_label'] = $optionValue['label'];
        $projectContact['relationship_type_name'] = $optionValue['name'];
      }
    }
  }
  return $result;

}

/**
 * Set the default getList behavior to return a list of contact IDs labeled by
 * contact sort names.
 *
 * @param array $request
 *   The parameters passed to the sub-API call (i.e., the parameters to the get
 *   call underlying the getList call). These are passed to getList in
 *   $params['params'].
 * @return array
 *   Despite the fact that $request represents a subset of the parameters passed
 *   to getList, the return of this function is merged with the getList params
 *   in their entirety.
 */
function _civicrm_api3_inbox_assignee_contact_getlist_defaults(&$request) {
  return array(
    'id_field' => 'contact_id',
    'label_field' => 'contact_id.sort_name',
    'search_field' => 'contact_id.sort_name',
  );
}
