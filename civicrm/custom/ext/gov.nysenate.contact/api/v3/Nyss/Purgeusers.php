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

  $modLdapAuthorization = drupal_get_path('module', 'ldap_authentication');
  require_once($modLdapAuthorization.'/LdapAuthenticationConf.class.php');

  $auth_conf = ldap_authentication_get_valid_conf();
  //Civi::log()->debug(__FUNCTION__, ['$auth_conf' => $auth_conf]);

  $ldap_server = $auth_conf->enabledAuthenticationServers['nyss_ldap'];

  $usersPurge = [];
  foreach ($users as $user) {
    //skip anonymous and root user
    if ($user->uid != 0 && $user->uid != 1 && (empty($params['username']) || $user->name == $params['username'])) {
      $isAuthorized = _nyss_checkUserAuth($user, $auth_conf, $ldap_server);

      if (!$isAuthorized || $user->access < $time) {
        $usersPurge[$user->uid] = $user->name;
      }
    }
  }

  //Civi::log()->debug(__FUNCTION__, ['$usersPurge' => $usersPurge]);
  return $usersPurge;
}

function _nyss_checkUserAuth($user, $auth_conf, $ldap_server) {
  //Civi::log()->debug(__FUNCTION__, ['$user' => $user]);

  $ldap_user = $ldap_server->userUserNameToExistingLdapEntry($user->name);
  $bbcfg = get_bluebird_instance_config();

  global $_name, $_ldap_user_entry;
  $_name = $user->name;
  $_ldap_user_entry = $ldap_user;

  $include = file_get_contents($bbcfg['base_dir'].'/civicrm/scripts/ldap_group_check.inc');
  $include = str_replace('<?php', '', $include);
  $include = str_replace('?>', '', $include);

  $code = "
    global \$_name;
    global \$_ldap_user_entry;
    $include
  ";
  //Civi::log()->debug(__FUNCTION__, ['$code' => $code]);

  ob_start();
  print eval($code);
  $result = ob_get_contents();
  ob_end_clean();

  $_name = NULL;
  $_ldap_user_entry = NULL;

  //Civi::log()->debug(__FUNCTION__, ['$result' => $result]);
  return (boolean) $result;
}
