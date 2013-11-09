<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2010-12-02
// Revised: 2013-06-21
//

require_once 'common_funcs.php';

define('SERVER_ID', 'nyss_ldap');


function getVariableValue($dbcon, $name)
{
  $sql = "SELECT value FROM variable WHERE name='$name';";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  else {
    $row = mysql_fetch_assoc($result);
    $val = unserialize($row['value']);
    mysql_free_result($result);
    return $val;
  }
} // getVariableValue()



function getLdapAuthentication($dbcon)
{
  return getVariableValue($dbcon, 'ldap_authentication_conf');
} // getLdapAuthentication()



function listFields($dbcon, $table, $colnames = '*')
{
  $sql = "SELECT $colnames FROM $table WHERE sid='".SERVER_ID."';";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  else {
    while ($row = mysql_fetch_assoc($result)) {
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
  mysql_free_result($result);
  return true;
} // listFields()



function listLdapServer($dbcon, $colnames = '*')
{
  echo "=== LDAP Server Info ===\n";
  return listFields($dbcon, 'ldap_servers', $colnames);
} // listLdapServer()



function listLdapAuthentication($dbcon)
{
  echo "=== LDAP Authentication Info ===\n";
  $val = getLdapAuthentication($dbcon);
  if ($val !== false) {
    print_r($val);
    return true;
  }
  else {
    return false;
  }
} // listLdapAuthentication()



function listLdapAuthorization($dbcon, $colnames = '*')
{
  echo "=== LDAP Authorization Info ===\n";
  return listFields($dbcon, 'ldap_authorization', $colnames);
} // listLdapAuthorization()



function setVariableValue($dbcon, $name, $val)
{
  $sval = serialize($val);
  $qval = mysql_real_escape_string($sval, $dbcon);
  $sql = "UPDATE variable SET value='$qval' WHERE name='$name';";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  else {
    return true;
  }
} // setVariableValue()



function storeLdapAuthentication($dbcon, $val)
{
  return setVariableValue($dbcon, 'ldap_authentication_conf', $val);
} // storeLdapAuthentication()



function setAuthenticationField($dbcon, $fldname, $fldval)
{
  $authConfig = getLdapAuthentication($dbcon);
  if (isset($authConfig[$fldname])) {
    $authConfig[$fldname] = $fldval;
    return storeLdapAuthentication($dbcon, $authConfig);
  }
  else {
    echo "Field [$fldname] does not exist in the LDAP authentication config\n";
    return false;
  }
} // setAuthenticationField()



function setField($dbcon, $tabname, $fldname, $fldval)
{
  $sql = "UPDATE $tabname SET $fldname = '$fldval' where sid='".SERVER_ID."';";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  else {
    return true;
  }
} // setField()



function setServerField($dbcon, $fldname, $fldval)
{
  return setField($dbcon, 'ldap_servers', $fldname, $fldval);
} // setServerField()



function setAuthorizationField($dbcon, $fldname, $fldval)
{
  return setField($dbcon, 'ldap_authorization', $fldname, $fldval);
} // setAuthorizationField()



function setEntries($dbcon, $entries)
{
  $entries = preg_replace('/[ ]*(,[ ]*)+/', "\n", $entries);
  return setAuthorizationField($dbcon, 'derive_from_entry_entries', $entries);
} // setEntries()



function setMappings($dbcon, $mappings)
{
  $mappings = preg_replace('/[ ]*(,[ ]*)+/', "\n", $mappings);
  return setAuthorizationField($dbcon, 'mappings', $mappings);
} // setMappings()



function setPhpAuth($dbcon, $codeText)
{
  return setAuthenticationField($dbcon, 'allowTestPhp', $codeText);
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

  $bootstrap = bootstrapScript($prog, $instance, DB_TYPE_DRUPAL);
  if ($bootstrap == null) {
    echo "$prog: Unable to bootstrap this script; exiting\n";
    exit(1);
  }

  $dbcon = $bootstrap['dbcon'];

  $rc = true;

  if ($cmd == 'listAll') {
    $rc = listLdapServer($dbcon);
    echo "\n";
    $rc = listLdapAuthentication($dbcon) && $rc;
    echo "\n";
    $rc = listLdapAuthorization($dbcon) && $rc;
  }
  else if ($cmd == 'listServer') {
    $rc = listLdapServer($dbcon);
  }
  else if ($cmd == 'listAuthentication') {
    $rc = listLdapAuthentication($dbcon);
  }
  else if ($cmd == 'listAuthorization') {
    $rc = listLdapAuthorization($dbcon);
  }
  else if ($cmd == 'listEntries') {
    $rc = listLdapAuthorization($dbcon, 'derive_from_entry_entries');
  }
  else if ($cmd == 'listMappings') {
    $rc = listLdapAuthorization($dbcon, 'mappings');
  }
  else if ($cmd == 'setName') {
    $rc = setServerField($dbcon, 'name', $param);
  }
  else if ($cmd == 'setHost') {
    $rc = setServerField($dbcon, 'address', $param);
  }
  else if ($cmd == 'setPort') {
    $rc = setServerField($dbcon, 'port', $param);
  }
  else if ($cmd == 'setEntries') {
    $rc = setEntries($dbcon, $param);
  }
  else if ($cmd == 'setMappings') {
    $rc = setMappings($dbcon, $param);
  }
  else if ($cmd == 'setPhpAuth') {
    $rc = setPhpAuth($dbcon, $param);
  }
  else {
    echo "$prog: $cmd: Unknown command\n";
    $rc = false;
  }

  mysql_close($dbcon);

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
