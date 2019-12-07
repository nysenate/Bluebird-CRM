<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2010-12-02
// Revised: 2013-06-21
// Revised: 2014-07-23 - migrated from PHP mysql interface to PDO
//

require_once 'common_funcs.php';

define('SERVER_ID', 'nyss_ldap');


function getVariableValue($dbh, $name)
{
  $sql = "SELECT value FROM variable WHERE name='$name';";
  $stmt = $dbh->query($sql);
  if (!$stmt) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $val = unserialize($row['value']);
    $stmt = null;
    return $val;
  }
} // getVariableValue()



function getLdapAuthentication($dbh)
{
  return getVariableValue($dbh, 'ldap_authentication_conf');
} // getLdapAuthentication()



function listFields($dbh, $table, $colnames = '*')
{
  $sql = "SELECT $colnames FROM $table WHERE sid='".SERVER_ID."';";
  $stmt = $dbh->query($sql);
  if (!$stmt) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      foreach ($row as $fldname => $fldval) {
        $val = @unserialize($fldval);
        if ($val === false) {
          echo $fldname.': '.$fldval."\n";
        }
        else {
          echo $fldname.": ".print_r($val, true)."\n";
        }
      }
    }
  }
  $stmt = null;
  return true;
} // listFields()



function listLdapServer($dbh, $colnames = '*')
{
  echo "=== LDAP Server Info ===\n";
  return listFields($dbh, 'ldap_servers', $colnames);
} // listLdapServer()



function listLdapAuthentication($dbh)
{
  echo "=== LDAP Authentication Info ===\n";
  $val = getLdapAuthentication($dbh);
  if ($val !== false) {
    print_r($val);
    return true;
  }
  else {
    return false;
  }
} // listLdapAuthentication()



function listLdapAuthorization($dbh, $colnames = '*')
{
  echo "=== LDAP Authorization Info ===\n";
  return listFields($dbh, 'ldap_authorization', $colnames);
} // listLdapAuthorization()



function setVariableValue($dbh, $name, $val)
{
  $sval = serialize($val);
  $qval = $dbh->quote($sval);
  $sql = "UPDATE variable SET value=$qval WHERE name='$name';";
  $result = $dbh->exec($sql);
  if ($result === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    return true;
  }
} // setVariableValue()



function storeLdapAuthentication($dbh, $val)
{
  return setVariableValue($dbh, 'ldap_authentication_conf', $val);
} // storeLdapAuthentication()



function setAuthenticationField($dbh, $fldname, $fldval)
{
  $authConfig = getLdapAuthentication($dbh);
  if (isset($authConfig[$fldname])) {
    $authConfig[$fldname] = $fldval;
    return storeLdapAuthentication($dbh, $authConfig);
  }
  else {
    echo "Field [$fldname] does not exist in the LDAP authentication config\n";
    return false;
  }
} // setAuthenticationField()



function setFields($dbh, $tabname, $fields)
{
  if (count($fields) == 0) {
    echo "Must pass in at least one field name/value pair\n";
    return false;
  }

  $sql_set = $delim = '';
  foreach ($fields as $fldname => $fldval) {
    $sql_set .= "$delim $fldname = '$fldval'";
    if (empty($delim)) {
      $delim = ',';
    }
  }

  $sql = "UPDATE $tabname SET $sql_set where sid='".SERVER_ID."';";
  $result = $dbh->exec($sql);
  if ($result === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    return true;
  }
} // setField()



function setField($dbh, $tabname, $fldname, $fldval)
{
  return setFields($dbh, $tabname, [ $fldname => $fldval ]);
} // setField()



function setServerFields($dbh, $fields)
{
  return setFields($dbh, 'ldap_servers', $fields);
} // setServerFields()



function setServerField($dbh, $fldname, $fldval)
{
  return setField($dbh, 'ldap_servers', $fldname, $fldval);
} // setServerField()



function setAuthorizationField($dbh, $fldname, $fldval)
{
  return setField($dbh, 'ldap_authorization', $fldname, $fldval);
} // setAuthorizationField()



function setServer($dbh, $serverFieldStr)
{
  $fields = [];
  $field_list = explode('|', $serverFieldStr);
  foreach ($field_list as $field) {
    $s = explode('=', $field, 2);
    $fldname = trim($s[0]);
    $fldval = isset($s[1]) ? trim($s[1]) : '';
    if ($fldname == 'basedn') {
      // Special case:  Convert the BaseDN into a serialized array.
      $fldval = serialize([$fldval]);
    }
    $fields[$fldname] = $fldval;
  }

  return setServerFields($dbh, $fields);
} // setServer()



function setEntries($dbh, $entries)
{
  $entries = preg_replace('/[ ]*(,[ ]*)+/', "\n", $entries);
  return setAuthorizationField($dbh, 'derive_from_entry_entries', $entries);
} // setEntries()



/**
 ** Given a string of mappings, first convert the string to a mapping object
 ** as required by the ldap_authorization module, then serialize and save it.
 ** The input string must contain a comma-separated list of mappings, where
 ** each mapping is of the form:  [LDAP group]|[Drupal role]
 ** In other words, each mapping in the string is pipe-delimited.
 ** The ldap_authorization module stores each mapping as an array with
 ** 6 data points.  All of the mappings are then stored in a result array.
 ** The result array is serialized and stored in the "mapping" field of
 ** the ldap_authorization table.
 */
function setMappings($dbh, $mapping_str)
{
  $result = [];
  $mappings = explode(',', $mapping_str);
  foreach ($mappings as $line) {
    $mapping = explode('|', $line);
    if (count($mapping) == 2) {
      $ldap_group = trim($mapping[0]);
      $drupal_role = trim($mapping[1]);
      $new_mapping = [];
      $new_mapping['user_entered'] = $drupal_role;
      $new_mapping['from'] = $ldap_group;
      $new_mapping['normalized'] = $drupal_role;
      $new_mapping['simplified'] = $drupal_role;
      $new_mapping['valid'] = true;
      $new_mapping['error_message'] = '';
      $result[] = $new_mapping;
    }
  }
  return setAuthorizationField($dbh, 'mappings', serialize($result));
} // setMappings()



function setPhpAuth($dbh, $codeText)
{
  return setAuthenticationField($dbh, 'allowTestPhp', $codeText);
} // setPhpAuth()


/***************************************************************************
** Main program
***************************************************************************/

$prog = basename($argv[0]);

if ($argc != 3 && $argc != 4) {
  echo "Usage: $prog instance cmd [param]\n".
       "   cmd can be:\n".
       "      listAll, listEntries, listMappings,\n".
       "      listServer, listAuthentication, listAuthorization,\n".
       "      setName, setHost, setPort,\n".
       "      setEntries, setMappings, setPhpAuth\n";
  exit(1);
}
else {
  $instance = $argv[1];
  $cmd = $argv[2];
  $param  = ($argc > 3) ? $argv[3] : "";

  $bootstrap = bootstrap_script($prog, $instance, DB_TYPE_DRUPAL);
  if ($bootstrap == null) {
    echo "$prog: Unable to bootstrap this script; exiting\n";
    exit(1);
  }

  $dbh = $bootstrap['dbrefs'][DB_TYPE_DRUPAL];

  $rc = true;

  if ($cmd == 'listAll') {
    $rc = listLdapServer($dbh);
    echo "\n";
    $rc = listLdapAuthentication($dbh) && $rc;
    echo "\n";
    $rc = listLdapAuthorization($dbh) && $rc;
  }
  else if ($cmd == 'listServer') {
    $rc = listLdapServer($dbh);
  }
  else if ($cmd == 'listAuthentication') {
    $rc = listLdapAuthentication($dbh);
  }
  else if ($cmd == 'listAuthorization') {
    $rc = listLdapAuthorization($dbh);
  }
  else if ($cmd == 'listEntries') {
    $rc = listLdapAuthorization($dbh, 'derive_from_entry_entries');
  }
  else if ($cmd == 'listMappings') {
    $rc = listLdapAuthorization($dbh, 'mappings');
  }
  else if ($cmd == 'setName') {
    $rc = setServerField($dbh, 'name', $param);
  }
  else if ($cmd == 'setHost') {
    $rc = setServerField($dbh, 'address', $param);
  }
  else if ($cmd == 'setPort') {
    $rc = setServerField($dbh, 'port', $param);
  }
  else if ($cmd == 'setServer') {
    $rc = setServer($dbh, $param);
  }
  else if ($cmd == 'setEntries') {
    $rc = setEntries($dbh, $param);
  }
  else if ($cmd == 'setMappings') {
    $rc = setMappings($dbh, $param);
  }
  else if ($cmd == 'setPhpAuth') {
    $rc = setPhpAuth($dbh, $param);
  }
  else {
    echo "$prog: $cmd: Unknown command\n";
    $rc = false;
  }

  $dbh = null;

  if ($rc) {
    echo "Operation was successful.\n";
    exit(0);
  }
  else {
    echo "Operation failed.\n";
    exit(1);
  }
}
?>
