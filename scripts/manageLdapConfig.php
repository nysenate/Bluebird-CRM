<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2010-12-02
// Revised: 2010-12-14
//

function listLdapauth($dbcon, $colname = '*')
{
  $sql = "SELECT $colname FROM ldapauth;";
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
          echo $fldname.": [array]\n";
          foreach ($val as $n => $v) {
            echo "\t$n => $v\n";
          }
        }
      }
    }
  }
  return true;
} // listLdapauth() 



function setField($dbcon, $fldname, $fldval)
{
  $sql = "UPDATE ldapauth SET $fldname = '$fldval';";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  else {
    return true;
  }
} // setField()



function getList($dbcon, $fldname)
{
  $sql = "SELECT $fldname FROM ldapauth;";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }

  $row = mysql_fetch_assoc($result);
  if ($row[$fldname]) {
    $valmap = unserialize($row[$fldname]);
    return $valmap;
  }
  else {
    return null;
  }
} // getList()



function storeList($dbcon, $fldname, $listval)
{
  // Re-number the numeric indices
  $merged_list = array_merge($listval);
  $sql = "UPDATE ldapauth SET $fldname = '".serialize($merged_list)."';";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  return true;
} // storeList()



function addToList($dbcon, $fldname, $fldval)
{
  // Get the current list of serialized values from the field.
  if (($fldlist = getList($dbcon, $fldname)) === false) {
    return false;
  }

  // If a "|" is part of the new value, then break it into key/value.
  // Otherwise, assume a numerical index and use the next increment.
  $fldval_parts = explode("|", $fldval);

  // Add the new value (plus optional key) to the list.
  if (count($fldval_parts) == 1) {
    // Don't add an auto-indexed value if it is already present.
    if (!in_array($fldval, $fldlist)) {
      $fldlist[] = $fldval;
    }
  }
  else {
    $key = $fldval_parts[0];
    $val = $fldval_parts[1];
    $fldlist[$key] = $val;
  }

  return storeList($dbcon, $fldname, $fldlist);
} // addToList()



function deleteFromList($dbcon, $fldname, $fldval)
{
  // Get the current list of serialized values from the field.
  if (($fldlist = getList($dbcon, $fldname)) === false) {
    return false;
  }
  else if ($fldlist == null) {
    return false;
  }

  $fldval_parts = explode("|", $fldval);

  if (count($fldval_parts) == 1) {
    $key = array_search($fldval, $fldlist);
    if ($key !== false) {
      unset($fldlist[$key]);
    }
    else {
      return false;
    }
  }
  else {
    $key = $fldval_parts[0];
    if (isset($fldlist[$key])) {
      unset($fldlist[$key]);
    }
    else {
      return false;
    }
  }

  return storeList($dbcon, $fldname, $fldlist);
} // deleteFromList()



/***************************************************************************
** Main program
***************************************************************************/

$prog = basename($argv[0]);

if ($argc != 6 && $argc != 7) {
  echo "Usage: $prog cmd dbhost dbuser dbpass dbname [param]\n".
       "   cmd can be:\n".
       "      listAll, listEntries, listGroups, listMappings,\n".
       "      setName, setServer, setPort,\n".
       "      addEntry, addGroup, addMapping\n".
       "      delEntry, delGroup, delMapping\n".
       "      clearEntries, clearGroups, clearMappings\n";
  exit(1);
}
else {
  $cmd = $argv[1];
  $dbhost = $argv[2];
  $dbuser = $argv[3];
  $dbpass = $argv[4];
  $dbname = $argv[5];
  $param  = ($argc > 6) ? $argv[6] : "";
  
  $dbcon = mysql_connect($dbhost, $dbuser, $dbpass);
  if (!$dbcon) {
    echo mysql_error()."\n";
    exit(1);
  }

  if (!mysql_select_db($dbname, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    mysql_close($dbcon);
    exit(1);
  }

  $rc = true;

  if ($cmd == 'listAll') {
    $rc = listLdapauth($dbcon);
  }
  else if ($cmd == 'listEntries') {
    $rc = listLdapauth($dbcon, "ldapgroups_entries");
  }
  else if ($cmd == 'listGroups') {
    $rc = listLdapauth($dbcon, "ldapgroups_groups");
  }
  else if ($cmd == 'listMappings') {
    $rc = listLdapauth($dbcon, "ldapgroups_mappings");
  }
  elseif ($cmd == 'setName') {
    $rc = setField($dbcon, 'name', $param);
  }
  elseif ($cmd == 'setServer') {
    $rc = setField($dbcon, 'server', $param);
  }
  elseif ($cmd == 'setPort') {
    $rc = setField($dbcon, 'port', $param); 
  }
  elseif ($cmd == 'addEntry') {
    $rc = addToList($dbcon, 'ldapgroups_entries', $param);
  }
  elseif ($cmd == 'addGroup') {
    $rc = addToList($dbcon, 'ldapgroups_groups', $param);
  }
  elseif ($cmd == 'addMapping') {
    $rc = addToList($dbcon, 'ldapgroups_mappings', $param);
  }
  elseif ($cmd == 'delEntry') {
    $rc = deleteFromList($dbcon, 'ldapgroups_entries', $param);
  }
  elseif ($cmd == 'delGroup') {
    $rc = deleteFromList($dbcon, 'ldapgroups_groups', $param);
  }
  elseif ($cmd == 'delMapping') {
    $rc = deleteFromList($dbcon, 'ldapgroups_mappings', $param);
  }
  elseif ($cmd == 'clearEntries') {
    $rc = setField($dbcon, "ldapgroups_entries", "");
  }
  elseif ($cmd == 'clearGroups') {
    $rc = setField($dbcon, "ldapgroups_groups", "");
  }
  elseif ($cmd == 'clearMappings') {
    $rc = setField($dbcon, "ldapgroups_mappings", "");
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
