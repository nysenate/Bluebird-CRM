<?php
// $Id: ldapgroups.inc,v 1.2 2009/08/25 13:53:20 miglius Exp $

/**
 * @file
 * ldapgroups include file.
 */

//////////////////////////////////////////////////////////////////////////////
// hook_user() functions

/**
 * Implements hook_user() login operation.
 */
function ldapgroups_user_login(&$account) {
  $authmap = user_get_authmaps($account->name);
  if (!isset($authmap['ldapauth'])) {
    // This user is not authenticated via lapauth.
    return;
  }

  // Setup the global $_ldapgroups_ldap object.
  if (!_ldapgroups_ldap_init($account))
    return;

  // First, we figure out the appropriate groups.
  $groups = _ldapgroups_detect_groups($account);

  // Apply groups restrictions.
  if (count($groups_allow = _ldapgroups_ldap_info($account, 'ldapgroups_groups')) > 0 && count(array_intersect($groups, $groups_allow)) == 0) {
    $account = user_load(0);
    return;
  }

  // Then, we take every mapped role from the user, later below
  // we'll grant back those deserved.
  $account->ldap_drupal_roles = isset($account->ldap_drupal_roles) ? $account->ldap_drupal_roles : array();
  foreach ($account->ldap_drupal_roles as $role) {
    _ldapgroups_deny_role($account, $role);
  }

  // Are there LDAP groups for the user?
  if ($groups === FALSE)
    return TRUE;

  // Next, we apply site-specific rules.
  $groups = _ldapgroups_filter($account, $groups);

  // At this point, the roles are in the full DN format.
  $roles = array();
  if (!empty($groups)) {
    $ldapgroups_mappings = _ldapgroups_ldap_info($account, 'ldapgroups_mappings');
    foreach ($groups as $group) {
      $role = _ldapgroups_mapping($account, $group);
      _ldapgroups_create_role($role);
      _ldapgroups_grant_role($account, $role);
      $roles[] = $role;
    }
  }

  // Store roles in the user object so we know which ones
  // were granted here.
  user_save($account, array('ldap_drupal_roles' => $roles));
}

//////////////////////////////////////////////////////////////////////////////
// Auxiliary functions

/**
 * Detect user groups from the LDAP.
 *
 * @param $user
 *   A user object.
 *
 * @return
 *   An array of user groups.
 */
function _ldapgroups_detect_groups($user) {
  global $_ldapgroups_ldap;

  // Nothing to do if the user is not LDAP authentified
  // or there are no groups configured.
  if (!(_ldapgroups_ldap_info($user, 'ldapgroups_in_dn') || _ldapgroups_ldap_info($user, 'ldapgroups_in_attr') || _ldapgroups_ldap_info($user, 'ldapgroups_as_entries')))
    return FALSE;

  // First try to connect with the stored user's DN and password.
  // If unsuccessful, connect with the BINDDN and BINDPW stored in the database for this config.
  $dn = isset($_SESSION['ldap_login']['dn']) ? $_SESSION['ldap_login']['dn'] : '';
  $pass = isset($_SESSION['ldap_login']['pass']) ? $_SESSION['ldap_login']['pass'] : '';

  // If I try to connect using a blank dn and pass, I dont get an error until ldap_read,
  // so I just check to see if they would be blank, based on ldap_forget_passwords, and
  // make it read from the database.
  if (LDAPAUTH_FORGET_PASSWORDS || !$_ldapgroups_ldap->connect($dn, $pass)) {
    $row2 = db_fetch_object(db_query("SELECT binddn, bindpw FROM {ldapauth} WHERE sid = %d", $_ldapgroups_ldap->getOption('sid')));
    $dn = $row2->binddn;
    $pass = $row2->bindpw;
    if (!$_ldapgroups_ldap->connect($dn, $pass)) {
      watchdog('ldapgroups', "User login: user %name data could not be read in the LDAP directory", array('%name' => $user->name), WATCHDOG_WARNING);
      return FALSE;
    }
  }

  // Strategy 1: group extracted from user's DN.
  $dn_groups = array();
  if (_ldapgroups_ldap_info($user, 'ldapgroups_in_dn')) {
    $pairs = explode(',', $user->ldap_dn);
    foreach ($pairs as $p) {
      $pair = explode('=', $p);
      if (drupal_strtolower(trim($pair[0])) == drupal_strtolower(_ldapgroups_ldap_info($user, 'ldapgroups_dn_attribute')))
        $dn_groups[] = trim($pair[1]);
    }
  }

  // Strategy 2: groups in user attributes.
  $attrib_groups = array();
  if (_ldapgroups_ldap_info($user, 'ldapgroups_in_attr')) {
    foreach (_ldapgroups_ldap_info($user, 'ldapgroups_attr') as $attribute)
      $attrib_groups = array_merge($attrib_groups, $_ldapgroups_ldap->retrieveMultiAttribute($user->ldap_dn, $attribute));
  }

  // Strategy 3: groups as entries.
  $entries_groups = array();
  $ldapgroups_entries_attribute = _ldapgroups_ldap_info($user, 'ldapgroups_entries_attribute');
  if (_ldapgroups_ldap_info($user, 'ldapgroups_as_entries')) {
    foreach (_ldapgroups_ldap_info($user, 'ldapgroups_entries') as $branch) {
      $entries = $_ldapgroups_ldap->search($branch, $ldapgroups_entries_attribute .'='. $user->ldap_dn, array($ldapgroups_entries_attribute));
      if (empty($entries) || $entries['count'] == 0)
        $entries = $_ldapgroups_ldap->search($branch, $ldapgroups_entries_attribute .'='. $user->name, array($ldapgroups_entries_attribute));
      foreach ($entries as $entry) {
        if (isset($entry['dn']))
          $entries_groups[] = $entry['dn'];
      }
    }
  }

  $_ldapgroups_ldap->disconnect();
  return array_unique(array_merge($dn_groups, $attrib_groups, $entries_groups));
}

/**
 * Grant a user with a role.
 *
 * @param $user
 *   A user object.
 * @param $rolename
 *   A name of the role.
 *
 * @return
 */
function _ldapgroups_grant_role($user, $rolename) {
  $result = db_query("SELECT * FROM {role} WHERE name = '%s'", $rolename);
  if ($row = db_fetch_object($result)) {
    $result = db_query("SELECT * FROM {users_roles} WHERE uid = %d AND rid = %d", $user->uid, $row->rid);
    if (!db_fetch_object($result)) {
      db_query("INSERT INTO {users_roles} (uid, rid) VALUES (%d, %d)", $user->uid, $row->rid);
    }
  }
}

/**
 * Deny a user with a role.
 *
 * @param $user
 *   A user object.
 * @param $rolename
 *   A name of the role.
 *
 * @return
 */
function _ldapgroups_deny_role($user, $rolename) {
  $result = db_query("SELECT * FROM {role} WHERE name = '%s'", $rolename);
  if ($row = db_fetch_object($result)) {
    $result = db_query("SELECT * FROM {users_roles} WHERE uid = %d AND rid = %d", $user->uid, $row->rid);
    if (db_fetch_object($result)) {
      db_query("DELETE FROM {users_roles} WHERE uid = %d AND rid = %d", $user->uid, $row->rid);
    }
  }
}

/**
 * Create a new role.
 *
 * @param $rolename
 *   A name of the role.
 *
 * @return
 */
function _ldapgroups_create_role($rolename) {
  $result = db_query("SELECT * FROM {role} WHERE name = '%s'", $rolename);
  if (!($row = db_fetch_object($result)))
    db_query("INSERT INTO {role} (name) VALUES ('%s')", $rolename);
}

/**
 * Filters groups only to a explicitely defined groups.
 *
 * @param $groups
 *   An array of the LDAP groups.
 *
 * @return
 *   An array of the filtered groups.
 */
function _ldapgroups_filter($account, $groups) {
  if (_ldapgroups_ldap_info($account, 'ldapgroups_mappings_filter') && count(_ldapgroups_ldap_info($account, 'ldapgroups_mappings') > 0)) {
    $groups_new = array();
    foreach ($groups as $group) {
      foreach (_ldapgroups_ldap_info($account, 'ldapgroups_mappings') as $group_approved => $role) {
        if (strcasecmp($group_approved, $group) == 0)
          $groups_new[] = $role;
      }
    }
    $groups = $groups_new;
  }

  if ($code = _ldapgroups_ldap_info($account, 'ldapgroups_filter_php'))
    $groups = drupal_eval($code);

  return $groups;
}

/**
 * Maps LDAP group name to a Drupal role.
 *
 * @param $user
 *   A user object.
 * @param $group
 *   A LDAP group name.
 *
 * @return
 *   An Drupal role.
 */
function _ldapgroups_mapping($user, $group) {
  $ldapgroups_mappings = _ldapgroups_ldap_info($user, 'ldapgroups_mappings');
  if (isset($ldapgroups_mappings[$group]))
    return $ldapgroups_mappings[$group];
  else if (preg_match('/^[^=]+=([^,]+),.*$/', $group, $matches))
    return $matches[1];
  else
    return $group;
}

/**
 * Initiates the LDAPInterfase class.
 *
 * @param $sid
 *   A server ID or user object.
 *
 * @return
 */
function _ldapgroups_ldap_init($sid) {
  global $_ldapgroups_ldap;

  if (!($sid = is_object($sid) ? (isset($sid->ldap_config) ? $sid->ldap_config : NULL) : $sid))
    return;

  static $servers = array();
  if (!isset($servers[$sid]))
    $servers[$sid] = db_fetch_object(db_query("SELECT * FROM {ldapauth} WHERE status = 1 AND sid = %d", $sid));

  if ($servers[$sid]) {
    $_ldapgroups_ldap = new LDAPInterface();
    $_ldapgroups_ldap->setOption('sid', $sid);
    $_ldapgroups_ldap->setOption('name', $servers[$sid]->name);
    $_ldapgroups_ldap->setOption('server', $servers[$sid]->server);
    $_ldapgroups_ldap->setOption('port', $servers[$sid]->port);
    $_ldapgroups_ldap->setOption('tls', $servers[$sid]->tls);
    $_ldapgroups_ldap->setOption('encrypted', $servers[$sid]->encrypted);
    $_ldapgroups_ldap->setOption('basedn', $servers[$sid]->basedn);
    $_ldapgroups_ldap->setOption('user_attr', $servers[$sid]->user_attr);
    return $_ldapgroups_ldap;
  }
}

/**
 * Retrieve the saved ldapgroups saved setting.
 *
 * @param $sid
 *   A server ID or user object.
 * @param $req
 *   An attribute name.
 *
 * @return
 *   The attribute value.
 */
function _ldapgroups_ldap_info($sid, $req) {
  if (!($sid = is_object($sid) ? (isset($sid->ldap_config) ? $sid->ldap_config : NULL) : $sid))
    return;

  static $servers = array();
  if (!isset($servers[$sid]))
    $servers[$sid] = db_fetch_object(db_query("SELECT * FROM {ldapauth} WHERE sid = %d", $sid));

  switch ($req) {
    case 'ldapgroups_in_dn':
      return $servers[$sid]->ldapgroups_in_dn;
    case 'ldapgroups_dn_attribute':
      return !empty($servers[$sid]->ldapgroups_dn_attribute) ? $servers[$sid]->ldapgroups_dn_attribute : LDAPGROUPS_DEFAULT_DN_ATTRIBUTE;
    case 'ldapgroups_in_attr':
      return $servers[$sid]->ldapgroups_in_attr;
    case 'ldapgroups_attr':
      return !empty($servers[$sid]->ldapgroups_attr) ? unserialize($servers[$sid]->ldapgroups_attr) : array();
    case 'ldapgroups_as_entries':
      return $servers[$sid]->ldapgroups_as_entries;
    case 'ldapgroups_entries':
      return !empty($servers[$sid]->ldapgroups_entries) ? unserialize($servers[$sid]->ldapgroups_entries) : array();
    case 'ldapgroups_entries_attribute':
      return !empty($servers[$sid]->ldapgroups_entries_attribute) ? $servers[$sid]->ldapgroups_entries_attribute : LDAPGROUPS_DEFAULT_ENTRIES_ATTRIBUTE;
    case 'ldapgroups_mappings':
      return !empty($servers[$sid]->ldapgroups_mappings) ? unserialize($servers[$sid]->ldapgroups_mappings) : array();
    case 'ldapgroups_mappings_filter':
      return $servers[$sid]->ldapgroups_mappings_filter;
    case 'ldapgroups_filter_php':
      return $servers[$sid]->ldapgroups_filter_php;
    case 'ldapgroups_groups':
      return !empty($servers[$sid]->ldapgroups_groups) ? unserialize($servers[$sid]->ldapgroups_groups) : array();
  }
}

