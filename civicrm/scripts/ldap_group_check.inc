<?php
/*
** Project: BluebirdCRM
** Author: Ken Zalewski
** Organization: New York State Senate
** Date: 2013-05-31
** Revised: 2013-06-01
** Revised: 2018-08-31 - add support for Active Directory LDAP
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

define('LDAP_TYPE_AD', 1);
define('LDAP_TYPE_DOMINO', 2);
define('LDAP_TYPE_OPENLDAP', 3);

log_auth("Beginning NYSS login checks for user [$_name]", true);

// Determine if this is Domino LDAP or ActiveDirectory LDAP
if (isset($_ldap_user_entry['attr']['dominounid'])) {
  $ldap_type = LDAP_TYPE_DOMINO;
  $ldap_type_str = 'Domino';
  $group_idx = 'gidnumber';
}
else if (isset($_ldap_user_entry['attr']['samaccountname'])) {
  $ldap_type = LDAP_TYPE_AD;
  $ldap_type_str = 'ActiveDirectory';
  $group_idx = 'memberof';
}
else {
  log_auth("ERROR: Unable to determine LDAP type for user [$_name]");
  echo '0';
  return;
}

log_auth("LDAP type is [$ldap_type_str] and group index is [$group_idx]", true);

// Get the list of groups that the user belongs to from the LDAP entry.
// Groups of the form "CN=groupName,OU=orgUnit,DC=domain" will simply
// return "groupName".
$userGroups = array();
$groups = $_ldap_user_entry['attr'][$group_idx];
for ($i = 0; $i < $groups['count']; $i++) {
  $g = $groups[$i];
  if (strncasecmp($g, "CN=", 3) === 0) {
    $g = substr($g, 3);
  }
  $g_parts = explode(',', $g);
  $g = $g_parts[0];
  $userGroups[] = $g;
}

if (count($userGroups) == 0) {
  log_auth("Login denied; user [$_name] has no groups in LDAP");
  echo '0';
  return;
}

log_auth("Found ".count($userGroups)." group(s) for user [$_name]; groups: ".implode(',', $userGroups), true);

// Get the list of group IDs that are allowed to log in to this instance.
// First, combine the global and instance-specific login groups from
// the Bluebird config file.
$bbconfig = get_bluebird_instance_config();
$gl_groups = preg_split('/[\s,]+/', $bbconfig['ldap.logingroups.global'], null, PREG_SPLIT_NO_EMPTY);
$in_groups = preg_split('/[\s,]+/', $bbconfig['ldap.logingroups'], null, PREG_SPLIT_NO_EMPTY);
$allowedGroups = array_merge($gl_groups, $in_groups);

if (count($allowedGroups) < 1) {
  log_auth("There are no allowed login groups for this CRM instance; user [$_name] denied");
  echo 0;
  return;
}

if ($ldap_type == LDAP_TYPE_DOMINO) {
  $allowedGroups = convert_groups_to_ids($allowedGroups);
  if ($allowedGroups == null) {
    log_auth("Unable to convert Domino group names to IDs; user [$_name] denied");
    echo '0';
    return;
  }
}

log_auth('This CRM allows the following groups: '.implode(',', $allowedGroups), true);

// Compare allowed groups to the user's groups to see if at
// least one group is in common between both.
$commonGroups = array_intersect($allowedGroups, $userGroups);

log_auth("User [$_name] and CRM have the following groups in common: ".implode(',', $commonGroups), true);

// If at least one group in common, then permit the login.
if (count($commonGroups) > 0) {
  log_auth("User [$_name] is permitted to log in to this instance");
  echo '1';
}
else {
  log_auth("User [$_name] is not allowed to log in to this instance; CRM allows groups: [".implode(',', $allowedGroups)."]; user is in groups [".implode(',', $userGroups)."]");
  echo '0';
}


// Given an array of LDAP groups, convert those groups into group IDs
// by performing LDAP lookups on them.
// This function is specific to Domino LDAP, where group IDs are stored
// with each user record, rather than group names.
function convert_groups_to_ids($groups)
{
  if (!$auth_conf) {
    $auth_conf = ldap_authentication_get_valid_conf();
  }

  $ldap_server = $auth_conf->enabledAuthenticationServers['nyss_ldap'];

  $res = $ldap_server->connect();
  if ($res != LdapServer::LDAP_SUCCESS) {
    log_auth("Unable to connect to LDAP server");
    return null;
  }

  $res = $ldap_server->bind();
  if ($res != LdapServer::LDAP_SUCCESS) {
    log_auth("Unable to bind to LDAP server anonymously");
    return null;
  }

  $groupFilter = '';
  foreach ($groups as $groupName) {
    $groupFilter .= "(cn=$groupName)";
  }
  $res = $ldap_server->search(null, "(&(objectclass=groupOfNames)(|$groupFilter))");

  // DO NOT call $ldap_server->disconnect() here.  Doing so would end up
  // closing the LDAP module's connection to this server, which causes all
  // logins to fail.

  $groupIds = array();
  for ($i = 0; $i < $res['count']; $i++) {
    $gid = $res[$i]['gidnumber'][0];
    $groupIds[] = $gid;
  }

  return $groupIds;
} // convert_groups_to_ids()


function log_auth($msg, $debug = false)
{
  if ($debug == false || (defined('DEBUG_LOGIN') && DEBUG_LOGIN == true)) {
    error_log("LOGIN: $msg");
  }
} // log_auth()

?>
