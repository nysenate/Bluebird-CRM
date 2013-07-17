<?php
/*
** Project: BluebirdCRM
** Author: Ken Zalewski
** Organization: New York State Senate
** Date: 2013-05-31
** Revised: 2013-06-01
**
** This script is run by the Drupal 7 LDAP module to check that the
** username attempting to log in is a member of any one of the allowed
** LDAP login groups for this CRM instance.
** To use it, enter the following text as the value for the "PHP to Test
** for Allowed LDAP Users" configuration option:
**   require_once('../civicrm/scripts/ldap_group_check.inc');
**
** Per the documentation for the LDAP module, only two variables are made
** available to this script:
**   $_name - the username of the user attempting to log in
**   $_ldap_user_entry - the result object of an LDAP lookup on that user
**
** The following Bluebird configuration parameters must be set in the
** config file in order for group verification to succeed:
**   ldap.logingroups - comma-separated list of groups whose members are
**                      permitted to log in to this instance
**   ldap.logingroups.global - same as ldap.logingroups, but shared between
**                             all CRM instances
*/

// Get the list of group IDs that the user belongs to from the LDAP entry.
$userGroupIds = array();
$gidnumbers = $_ldap_user_entry['attr']['gidnumber'];
for ($i = 0; $i < $gidnumbers['count']; $i++) {
  $userGroupIds[] = $gidnumbers[$i];
}

// Get the list of group IDs that are allowed to log in to this instance.
// First, combine the global and instance-specific login groups from
// the Bluebird config file.
$bbconfig = get_bluebird_instance_config();
$gl_groups = preg_split('/[\s,]+/', $bbconfig['ldap.logingroups.global'], null, PREG_SPLIT_NO_EMPTY);
$in_groups = preg_split('/[\s,]+/', $bbconfig['ldap.logingroups'], null, PREG_SPLIT_NO_EMPTY);
$allowedGroups = array_merge($gl_groups, $in_groups);

if (count($allowedGroups) < 1) {
  error_log("There are no allowed login groups for this CRM instance; user [$_name] denied");
  echo 0;
}
else {
  // Given the array of allowed login groups, convert those groups into
  // group IDs by performing LDAP lookups on them.
  if (!$auth_conf) {
    $auth_conf = ldap_authentication_get_valid_conf();
  }
  $ldap_server = $auth_conf->enabledAuthenticationServers['nyss_ldap'];
  $res = $ldap_server->connect();
  if ($res != LdapServer::LDAP_SUCCESS) {
    error_log("Unable to connect to LDAP server");
    echo 0;
  }
  $res = $ldap_server->bind();
  if ($res != LdapServer::LDAP_SUCCESS) {
    error_log("Unable to bind to LDAP server anonymously");
    echo 0;
  }

  $groupFilter = '';
  foreach ($allowedGroups as $groupName) {
    $groupFilter .= "(cn=$groupName)";
  }
  $res = $ldap_server->search(null, "(&(objectclass=groupOfNames)(|$groupFilter))");

  // DO NOT call $ldap_server->disconnect() here.  Doing so would end up
  // closing the LDAP module's connection to this server, which causes all
  // logins to fail.

  $allowedGroupIds = array();
  for ($i = 0; $i < $res['count']; $i++) {
    $gid = $res[$i]['gidnumber'][0];
    $allowedGroupIds[] = $gid;
  }

  // Compare allowed group IDs to the user's group IDs to see if at
  // least one group ID is in common between both.
  $commonGroupIds = array_intersect($allowedGroupIds, $userGroupIds);

  // If at least one group ID in common, then permit the login.
  if (count($commonGroupIds) > 0) {
    error_log("User [$_name] is permitted to log in to this instance");
    echo 1;
  }
  else {
    error_log("User [$_name] is not allowed to log in to this instance; CRM allows groups: [".implode(',', $allowedGroupIds)."]; user is in groups [".implode(',', $userGroupIds)."]");
    echo 0;
  }
}
?>
