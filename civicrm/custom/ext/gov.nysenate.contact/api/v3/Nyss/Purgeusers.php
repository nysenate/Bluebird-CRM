<?php

/**
 * Nyss.Purgeusers API specification (optional)
 * #14699
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_purgeusers_spec(&$spec) {
  $spec['time'] = [
    'title' => 'Time period',
    'type' => CRM_Utils_Type::T_STRING,
    'description' => 'Time period before which to purge users who have not logged in.',
  ];

  $spec['username'] = [
    'title' => 'Username',
    'type' => CRM_Utils_Type::T_STRING,
    'description' => 'Username to delete. The user must meet the other requirements. This is primarily used for testing.'
  ];

  $spec['dryrun'] = [
    'title' => 'Dry run',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'options' => [1 => 'Yes', 0 => 'No'],
    'api.default' => 1,
  ];
}

/**
 * Nyss.Purgeusers API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_purgeusers($params) {
  $users = _nyss_getUsers($params);

  foreach ($users as $uid => $name) {
    if (empty($params['dryrun'])) {
      user_delete($uid);
    }
  }

  return civicrm_api3_create_success($users, $params, 'Nyss', 'purgeusers');
}

function _nyss_getUsers($params) {
  $users = entity_load('user');
  $time = strtotime(CRM_Utils_Array::value('time', $params, '-1 year'));
  //Civi::log()->debug(__FUNCTION__, ['users' => $users]);

  $usersPurge = [];
  foreach ($users as $user) {
    //skip anonymous and root user
    if ($user->uid != 0 && $user->uid != 1 && (empty($params['username']) || $user->name == $params['username'])) {
      $isAuthorized = _nyss_checkUserAuth($user);

      if (!$isAuthorized || $user->access < $time) {
        $usersPurge[$user->uid] = $user->name;
      }
    }
  }

  //Civi::log()->debug(__FUNCTION__, ['$usersPurge' => $usersPurge]);
  return $usersPurge;
}

function _nyss_checkUserAuth($user) {
  $modLdapAuthorization = drupal_get_path('module', 'ldap_authorization');
  require_once($modLdapAuthorization.'/ldap_authorization.inc');

  $consumers = ldap_authorization_get_consumers();
  $new_authorizations = [];
  foreach ($consumers as $consumer_type => $consumer) {
    list($new_authorizations_i, $notifications_i) = _ldap_authorizations_user_authorizations($user, 'query', $consumer_type, NULL);
    $new_authorizations = $new_authorizations + $new_authorizations_i;
  }

  /*Civi::log()->debug(__FUNCTION__, [
    '$user' => $user,
    '$new_authorizations' => $new_authorizations,
  ]);*/

  return !empty($new_authorizations['drupal_role']);
}
