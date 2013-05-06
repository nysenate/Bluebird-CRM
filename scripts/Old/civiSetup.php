<?php

$script_dir = realpath(dirname(__FILE__));

include_once $script_dir.'/functions.inc.php';

$timestart = microtime(1); // note 1

if (sizeof($argv) < 3) {
  echo "civiSetup.php <config> copysite <copyname> <newname> (e.g. config=default, copyname=crm20, newname=crm90)\n";
  echo "civiSetup.php <config> deletesite <name>\n";
  exit;
}

try {
  $config = strToLower($argv[1]);
  $function = strToLower($argv[2]);
  if (isset($argv[3])) {
    $SC['dbName'] = $argv[3];
  }
  else {
    $SC['dbName'] = "NODB";
  }
  if (isset($argv[4])) {
    $SC['dbToName'] = $argv[4]; 
    $tableName = $argv[4]; //this is for tablecopy function function
  }
  else {
    $SC['dbToName'] = $SC['dbName'];
    $tableName = null;
  }
}
catch (Exception $e) {
  echo "ARGUMENT ERROR";
  exit;
}

// Must wait to include config.php, since it depends on $config being set.
include_once $script_dir.'/config.php';

switch ($function) {
  case "copysite":
    if (!confirmCheck('copysite')) exit;
    copyDatabases();
    updateDatabasePaths();
    #copyCiviTemplateFiles();
    clearCache();
    hitSite();
    fixPermissions();
    break;

  case "deletesite":
    if (!confirmCheck('deletesite')) exit;
    runCmd("rm -rf ".$SC['toRootDir'].$SC['toDrupalRootDir']."sites/{$SC['dbName']}{$SC['toRootDomain']}");
    runCmd("{$SC['mysqlTo']} -e\"drop database {$SC['dbToCiviPrefix']}{$SC['dbName']}\"");
    runCmd("{$SC['mysqlTo']} -e\"drop database {$SC['dbToDrupalPrefix']}{$SC['dbName']}\"");
    break;

  case "listsites":
    listSites();
    break;

  case "importtags":
    importTags();
    break;

  case "fp":
  case "fixpermissions":
    fixPermissions();
    break;

  case "exporttable":
    runCmd("{$SC['mysqldumpTo']} --opt {$SC['dbCiviPrefix']}{$SC['dbName']} {$SC['dbCiviTablePrefix']}{$tableName}>{$SC['tmp']}{{$SC['dbName']}}.{$SC['dbCiviTablePrefix']}{$tableName}.sql","exporting table {$tableName}.sql");
    break;

  case "exportalldatabases":
    runCmd("{$SC['mysqldumpTo']} --opt --all-databases >{$SC['tmp']}alldatabases.sql","exporting all databases to {$SC['tmp']}alldatabases.sql");
    break;

  case "backupdatabase":
    $SC['dbToName'] = $argv[3];
    $postFix = (isset($argv[4])) ? $argv[4] : "";
    runCmd("{$SC['mysqldumpTo']} --opt  {$SC['dbToCiviPrefix']}{$SC['dbToName']} >{$SC['tmp']}{$SC['dbToCiviPrefix']}{$SC['dbToName']}_{$postFix}.sql","backing up {$SC['dbToCiviPrefix']}{$SC['dbToName']} to {$SC['tmp']}{$SC['dbToCiviPrefix']}{$SC['dbToName']}_{$postFix}.sql");
    runCmd("{$SC['mysqldumpTo']} --opt  {$SC['dbToDrupalPrefix']}{$SC['dbToName']} >{$SC['tmp']}{$SC['dbToDrupalPrefix']}{$SC['dbToName']}_{$postFix}.sql","backing up {$SC['dbToDrupalPrefix']}{$SC['dbToName']} to {$SC['tmp']}{$SC['dbToDrupalPrefix']}{$SC['dbToName']}_{$postFix}.sql");
    break;

  case "importdatabase":
    runCmd("{$SC['mysqlTo']} -e\"create database {$argv[3]}\"");
    runCmd("{$SC['mysqlTo']} {$argv[3]} < {$argv[4]}");
    break;

  case "copydatabase":
    if (!confirmCheck('copydb')) exit;
    copyDatabases();
    updateDatabasePaths();
    break;

  case "removecontactdata":
    if (!confirmCheck('removecontactdata')) exit;
    removeContactData();
    break;

  case "exportcustomdata":
    runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{$SC['dbName']} civicrm_custom_group >{$SC['tmp']}cg.sql");
    runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{$SC['dbName']} civicrm_custom_field >{$SC['tmp']}cf.sql");
    //runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{{$SC['dbName']}} civicrm_option_group >{$SC['tmp']}og.sql");
    //runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{{$SC['dbName']}} civicrm_option_value >{$SC['tmp']}ov.sql");
    runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{$SC['dbName']} civicrm_value_activity_details_6 >{$SC['tmp']}ad6.sql");
    runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{$SC['dbName']} civicrm_value_attachments_5 >{$SC['tmp']}a5.sql");
    runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{$SC['dbName']} civicrm_value_constituent_information_1 >{$SC['tmp']}ci1.sql");
    runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{$SC['dbName']} civicrm_value_contact_source_4 >{$SC['tmp']}cs4.sql");
    runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{$SC['dbName']} civicrm_value_organization_constituent_informa_3 >{$SC['tmp']}ci3.sql");
    runCmd("{$SC['mysqldump']} {$SC['dbCiviPrefix']}{$SC['dbName']} civicrm_value_district_information_7 >{$SC['tmp']}di7.sql");
    break;

  case "importcustomdata":
    if (!confirmCheck('importcustomdata')) exit;
    runCmd("{$SC['mysqlTo']} {$SC['dbToCiviPrefix']}{$SC['dbName']} <{$SC['tmp']}cg.sql");
    runCmd("{$SC['mysqlTo']} {$SC['dbToCiviPrefix']}{$SC['dbName']} <{$SC['tmp']}cf.sql");

    //create the custom tables related to custom groups
    runCmd("{$SC['mysqlTo']} {$SC['dbToCiviPrefix']}{$SC['dbName']} <{$SC['tmp']}ad6.sql");
    runCmd("{$SC['mysqlTo']} {$SC['dbToCiviPrefix']}{$SC['dbName']} <{$SC['tmp']}a5.sql");
    runCmd("{$SC['mysqlTo']} {$SC['dbToCiviPrefix']}{$SC['dbName']} <{$SC['tmp']}ci1.sql");
    runCmd("{$SC['mysqlTo']} {$SC['dbToCiviPrefix']}{$SC['dbName']} <{$SC['tmp']}cs4.sql");
    runCmd("{$SC['mysqlTo']} {$SC['dbToCiviPrefix']}{$SC['dbName']} <{$SC['tmp']}ci3.sql");
    runCmd("{$SC['mysqlTo']} {$SC['dbToCiviPrefix']}{$SC['dbName']} <{$SC['tmp']}di7.sql");
    break;

  case "copymissingoptions";
    copymissingoptions();
    break;

  case "compareoptions";
    compareoptions();
    break;

  case "cc":
  case "clearcache":
    clearCache();
    break;

  case "resetdomainconfig":
    if (!confirmCheck('resetdomainconfig')) exit;
    resetDomainConfig();
    break;

  case "fixdomainconfig":
    if (!confirmCheck('fixdomainconfig')) exit;
    updateDatabasePaths();
    break;

  case "showdomainconfig":
    showConfigBackend();
    break;

  case "testfunction":
    testFunction();
    break;

  case "clearcachestats":
    apc_clear_cache();
    apc_clear_cache('user');
    break;

  case "showcividbinfo":
    mysql_connect($SC['dbToHost'], $SC['dbToUser'],$SC['dbToPassword']) or die(mysql_error());
    //mysql_select_db($SC['dbToCiviPrefix'].$SC['dbToName']) or die(mysql_error());
    $sql = "SELECT concat('TOTAL ROWS: ',TABLE_SCHEMA) as t, SUM(TABLE_ROWS) as countRows FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE 'senate_%' GROUP BY t;";
    $result = mysql_query($sql);
    $total = 0;
    while ($row = mysql_fetch_assoc($result)) {
      $out[$row['t']] = number_format($row['countRows'],0);
      $total += $row['countRows'];  
    }

    $sql = "SELECT concat('CONTACTS: ',TABLE_SCHEMA) as t, SUM(TABLE_ROWS) as countRows FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE 'senate_%'AND TABLE_NAME LIKE '%_contact' GROUP BY TABLE_SCHEMA;";
    $result = mysql_query($sql);
    $total=0;
    while ($row = mysql_fetch_assoc($result)) {
      $out[$row['t']] = number_format($row['countRows'],0);
      $total+=$row['countRows'];
    }
    print_r($out);
    print_r("total rows: ".number_format($total,0));
    break;
} // switch

$elapsed_time = microtime(1) - $timestart;
echo "\n\nelapsed time = $elapsed_time sec\n\n";



function copyDatabases()
{
  global $SC;
  copyDatabase($SC['dbCiviPrefix'].$SC['dbName'],$SC['dbToCiviPrefix'].$SC['dbToName']);
  copyDatabase($SC['dbDrupalPrefix'].$SC['dbName'],$SC['dbToDrupalPrefix'].$SC['dbToName']);
} // copyDatabases()



function copyDatabase($from, $to)
{
  global $SC;
  runCmd("{$SC['mysqldump']} --opt {$from} >{$SC['tmp']}{$from}.sql");
  runCmd("{$SC['mysqlTo']} -e\"create database  {$to}\"");
  runCmd("{$SC['mysqlTo']} {$to} <{$SC['tmp']}{$from}.sql");
} // copyDatabase()



function copyCiviTemplateFiles()
{
  global $SC;

  runCmd("{$SC['copy']} -R -p {$SC['toTemplateDir']}sitesFiles {$SC['toRootDir']}{$SC['toDrupalRootDir']}sites/{$SC['dbToName']}{$SC['toRootDomain']}");
  $dSettings = "{$SC['toRootDir']}{$SC['toDrupalRootDir']}sites/{$SC['dbToName']}{$SC['toRootDomain']}/settings.php";  
  $cSettings = "{$SC['toRootDir']}{$SC['toDrupalRootDir']}sites/{$SC['dbToName']}{$SC['toRootDomain']}/civicrm.settings.php";
 
  fileStrReplace($dSettings,"%%DBDRUPAL%%",$SC['dbToDrupalPrefix'].$SC['dbToName']);
  fileStrReplace($dSettings,"%%URL%%",$SC['dbToName'].$SC['toRootDomain']);
  fileStrReplace($dSettings,"%%DBHOST%%",$SC['dbToHost']);
  fileStrReplace($dSettings,"%%DBUSR%%",$SC['dbToUser']);
  fileStrReplace($dSettings,"%%DBPWD%%",$SC['dbToPassword']);
  fileStrReplace($dSettings,"%%ROOTDIR%%",$SC['toRootDir'].$SC['toDrupalRootDir']);
  fileStrReplace($cSettings,"%%DBDRUPAL%%",$SC['dbToDrupalPrefix'].$SC['dbToName']);
  fileStrReplace($cSettings,"%%DBCIVI%%",$SC['dbToCiviPrefix'].$SC['dbToName']);
  fileStrReplace($cSettings,"%%URL%%",$SC['dbToName'].$SC['toRootDomain']);
  fileStrReplace($cSettings,"%%DBHOST%%",$SC['dbToHost']);
  fileStrReplace($cSettings,"%%DBUSR%%",$SC['dbToUser']);
  fileStrReplace($cSettings,"%%DBPWD%%",$SC['dbToPassword']);
  fileStrReplace($cSettings,"%%ROOTDIR%%",$SC['toRootDir'].$SC['toDrupalRootDir']);
  fileStrReplace($cSettings,"%%DRUPALROOTDIR%%",$SC['toDrupalRootDir']);
} // copyCiviTemplateFiles()



function fileStrReplace($filename, $find, $replace)
{
  global $SC;

  cLog(0,'INFO',"replacing text in $filename: '$find' with '$replace'");

  if ($SC['noExec']) return;

  $content = file_get_contents($filename);
  $content = str_replace($find,$replace, $content);

  $f = fopen($filename,'w');
  if ($f) {
    fwrite($f, $content);
    fclose($f);
  }
  else {
    echo 'failed to update file';
  }
} // fileStrReplace()



function hitSite()
{
  global $SC;

  $cmd = "wget -O /dev/null http://{$SC['httpauth']}:{$SC['httppwd']}@{$SC['dbToName']}{$SC['toRootDomain']}";
  runCmd($cmd);
} // hitSite()



function clearCache()
{
  global $SC;

  $m = $SC['mysqlTo'];

  //clear drupal cache
  echo "clear drupal cache...\n";
  $sql = "truncate cache;truncate cache_page;truncate cache_form;truncate cache_update;truncate cache_menu;truncate cache_block;truncate cache_filter;truncate sessions;";
  $cmd = "{$m} {$SC['dbToDrupalPrefix']}{$SC['dbToName']} -e \"$sql\"";
  runCmd($cmd);

  //clear civi database cache
  echo "clear civiCRM cache...\n";
  $sql = "truncate {$SC['dbToCiviTablePrefix']}cache;truncate {$SC['dbToCiviTablePrefix']}menu;truncate {$SC['dbToCiviTablePrefix']}uf_match";
  $cmd = "{$m} {$SC['dbToCiviPrefix']}{$SC['dbToName']} -e \"$sql\"";
  runCmd($cmd);

  //clear civi template cache
  echo "clear civiCRM template cache...\n";
  runCmd("rm -rf ".$SC['toRootDir'].$SC['toDrupalRootDir']."sites/{$SC['dbToName']}{$SC['toRootDomain']}/files/civicrm/templates_c/*");
  runCmd("rm -rf ".$SC['toRootDir'].$SC['toDrupalRootDir']."sites/{$SC['dbToName']}{$SC['toRootDomain']}/files/css/*");
  runCmd("rm -rf ".$SC['toRootDir'].$SC['toDrupalRootDir']."sites/{$SC['dbToName']}{$SC['toRootDomain']}/files/js/*");
} // clearCache()



//CAREFUL: REQUIRES SETTING PATHS MANUALLY IN INTERFACE
function resetDomainConfig()
{
  global $SC;

  runCmd("{$SC['mysqlTo']} {$SC['dbToCiviPrefix']}{$SC['dbToName']} -e\"update civicrm_domain set config_backend=null\"");
} // resetDomainConfig()



function fixPermissions()
{
  global $script_dir;
  runCmd("$script_dir/fixPermissions.sh", "setting permissions");
} // fixPermissions()



function copymissingoptions()
{
  global $SC;

  cLog(0,"INFO","connecting to database {$SC['dbHost']}:{$SC['dbCiviPrefix']}{$SC['dbName']}");

  mysql_connect($SC['dbHost'], $SC['dbUser'],$SC['dbPassword']) or die(mysql_error());
  mysql_select_db($SC['dbCiviPrefix'].$SC['dbName']) or die(mysql_error());

  //turn off so that option group values can be inserted - component_id is the culprit
  $result = mysql_query("SET foreign_key_checks = 0;") or die(mysql_error());
  $result = mysql_query("SELECT * FROM civicrm_option_group;") or die(mysql_error());

  $aGroup = array();  
  while ($row = mysql_fetch_assoc($result)) {
    $aGroup[] = $row;
  }

  foreach ($aGroup as $group) {
    cLog(0,"INFO","checking group {$group['name']}");
    unset($result);
    unset($row);
    $sql = "SELECT * FROM {$SC['dbToCiviPrefix']}{$SC['dbToName']}.civicrm_option_group where name='".$group['name']."'";
    $result = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_assoc( $result );
//print_r($row);
    if (!is_array($row)) {
      cLog(0,"INFO","found missing group {$group['name']}");
      $sqlfields="";
      $sqlvals="";
      //make sure we don't carry over the id  
      unset ($group['id']);
  
      foreach ($group as $key=>$val) {
        $sqlfields .= ",".$key;
        $sqlvals .= (strlen($val)==0) ? ",null" : ",'".$val."'";
      }

      $sqlfields = substr($sqlfields, 1);
      $sqlvals = substr($sqlvals, 1);

      $sql = "INSERT INTO {$SC['dbToCiviPrefix']}{$SC['dbToName']}.civicrm_option_group({$sqlfields}) VALUES({$sqlvals});";
      if ($SC['noExec']) cLog(0,"INFO", $sql);
      else mysql_query($sql);
    }
  }

  //going through groups on source db
  foreach ($aGroup as $group) {
    cLog(0,"INFO","checking values for group {$group['name']} id {$group['id']}");
    //get the id for the name in the target db
    $sql = "SELECT id, name FROM {$SC['dbToCiviPrefix']}{$SC['dbToName']}.civicrm_option_group where name='".$group['name']."';";
    $result = mysql_query($sql) or die(mysql_error());
    $row=mysql_fetch_assoc( $result );
    $targetGroupID = $row['id'];
    $targetGroupName = $row['name'];

    //go through all the values on source
    $sql ="SELECT * FROM {$SC['dbCiviPrefix']}{$SC['dbName']}.civicrm_option_value where option_group_id='".$group['id']."';";
    $result = mysql_query($sql) or die(mysql_error());
    $aVals = array();
    while ($row = mysql_fetch_assoc($result)) {
      $aVals[]=$row;
    }

    foreach ($aVals as $val) {
      //try to get new value
      $sql = "SELECT * FROM {$SC['dbToCiviPrefix']}{$SC['dbToName']}.civicrm_option_value where option_group_id={$targetGroupID} AND (name like '{$val['name']}' OR label like '{$val['name']}')";
      $result = mysql_query($sql) or die(mysql_error());
      $row = mysql_fetch_assoc( $result );

      //didn't find the value, so insert it
      if (!is_array($row)) {
        cLog(0,"INFO","found missing value {$val['name']} - {$val['label']} in group {$group['name']} id {$group['id']}:  new group {$targetGroupName} id {$targetGroupID} name: {$val['name']} - {$val['label']}");
        $sqlfields="";
        $sqlvals="";
        //make sure we don't carry over the id
        unset ($val['id']);

        //set the new groupID
        $val['option_group_id'] = $targetGroupID;

        foreach ($val as $key=>$val) {
          $sqlfields .= ",".$key;
          $sqlvals .= (strlen($val)==0) ? ",null" : ",'".$val."'";
        }

        $sqlfields = substr($sqlfields,1);
        $sqlvals = substr($sqlvals,1);
        $sql = "INSERT INTO {$SC['dbToCiviPrefix']}{$SC['dbToName']}.civicrm_option_value({$sqlfields}) VALUES({$sqlvals});";
        if ($SC['noExec']) cLog(0,"INFO", $sql);
        else mysql_query($sql);
      }
    }
  }
} // copymissingoptions()



function compareoptions()
{
        global $SC;

        cLog(0,"INFO","connecting to database {$SC['dbHost']}:{$SC['dbCiviPrefix']}{$SC['dbName']}");

        mysql_connect($SC['dbHost'], $SC['dbUser'],$SC['dbPassword']) or die(mysql_error());
        mysql_select_db($SC['dbCiviPrefix'].$SC['dbName']) or die(mysql_error());

        //turn off so that option group values can be inserted - component_id is the culprit
        $result = mysql_query("SET foreign_key_checks = 0;") or die(mysql_error());

        $result = mysql_query("SELECT * FROM civicrm_option_group;") or die(mysql_error());

        $aGroup = array();
        while ($row = mysql_fetch_assoc( $result )) $aGroup[]=$row;

        foreach ($aGroup as $group) {

                //cLog(0,"INFO","checking group {$group['name']}");

                unset($result);
                unset($row);

                $sql = "SELECT * FROM {$SC['dbToCiviPrefix']}{$SC['dbToName']}.civicrm_option_group where name='".$group['name']."'";

                $result = mysql_query($sql) or die(mysql_error());

                $row = mysql_fetch_assoc( $result );
//print_r($row);
                if (!is_array($row)) {

                        cLog(0,"INFO","found missing group {$group['name']}");

                        $sqlfields="";
                        $sqlvals="";

                        //make sure we don't carry over the id
                        unset ($group['id']);

                        foreach ($group as $key=>$val) {

                                $sqlfields .= ",".$key;
                                $sqlvals .= (strlen($val)==0) ? ",null" : ",'".$val."'";
                        }

                        $sqlfields = substr($sqlfields,1);
                        $sqlvals = substr($sqlvals,1);

                        $sql = "INSERT INTO {$SC['dbToCiviPrefix']}{$SC['dbToName']}.civicrm_option_group({$sqlfields}) VALUES({$sqlvals});";
                        if (!$SC['noExec']) mysql_query($sql);
                }
        }
  
  //going through groups on source db
  foreach ($aGroup as $group) {


    //cLog(0,"INFO","checking values for group {$group['name']} id {$group['id']}");

                //get the id for the name in the target db
                $sql = "SELECT id, name FROM {$SC['dbToCiviPrefix']}{$SC['dbToName']}.civicrm_option_group where name='".$group['name']."';";
                $result = mysql_query($sql) or die(mysql_error());
                $row=mysql_fetch_assoc( $result );
                $targetGroupID = $row['id'];
    $targetGroupName = $row['name'];

    //go through all the values on source
          $sql ="SELECT * FROM {$SC['dbCiviPrefix']}{$SC['dbName']}.civicrm_option_value where option_group_id='".$group['id']."';";
    $result = mysql_query($sql) or die(mysql_error());
    $aVals=array();
          while ($row = mysql_fetch_assoc( $result )) $aVals[]=$row;
    
    foreach ($aVals as $val) {

      //try to get new value
      $sql = "SELECT * FROM {$SC['dbToCiviPrefix']}{$SC['dbToName']}.civicrm_option_value where option_group_id={$targetGroupID} AND (name like '{$val['name']}' OR label like '{$val['name']}')";
                  $result = mysql_query($sql) or die(mysql_error());
      $row = mysql_fetch_assoc( $result );

      //didn't find the value, so insert it
                  if (!is_array($row)) {
      
                          cLog(0,"INFO","TARGET MISSING: {$val['name']} - {$val['label']} in group {$group['name']} id {$group['id']}");
      }
    }
  }
} // compareoptions()



function showConfigBackend()
{
  global $SC;
  cLog(0,"INFO","connecting to database {$SC['dbToHost']}:{$SC['dbToCiviPrefix']}{$SC['dbToName']} to update database paths");

  mysql_connect($SC['dbToHost'], $SC['dbToUser'],$SC['dbToPassword']) or die(mysql_error());
  mysql_select_db($SC['dbToCiviPrefix'].$SC['dbToName']) or die(mysql_error());
  $result = mysql_query("SELECT id, config_backend FROM civicrm_domain;") or die(mysql_error());

  //get the only row
  $row = mysql_fetch_assoc($result);
  print_r(unserialize($row["config_backend"]));
} // showConfigBackend()



function updateDatabasePaths()
{
  global $SC;

  cLog(0,"INFO","connecting to database {$SC['dbToHost']}:{$SC['dbToCiviPrefix']}{$SC['dbToName']} to update database paths");

  mysql_connect($SC['dbToHost'], $SC['dbToUser'],$SC['dbToPassword']) or die(mysql_error());
  mysql_select_db($SC['dbToCiviPrefix'].$SC['dbToName']) or die(mysql_error());

  cLog(0,"INFO","replacing {$SC['drupalRootDir']}sites/{$SC['dbName']}{$SC['rootDomain']} with {$SC['toDrupalRootDir']}sites/{$SC['dbToName']}{$SC['toRootDomain']}");

  $result = mysql_query("SELECT id, config_backend FROM civicrm_domain;") or die(mysql_error());  

  //get the only row
  while ($row = mysql_fetch_assoc( $result )) {
    //get the config_backend    
    $cb = unserialize($row['config_backend']);

    //update the variables
    foreach ($cb as $key=>$val) {
      if (is_string($cb[$key])) {
        //$cb[$key]= str_replace($SC['dbName'],$SC['dbToName'], $cb[$key]);
        $cb[$key]= str_replace("{$SC['drupalRootDir']}sites/{$SC['dbName']}{$SC['rootDomain']}","{$SC['toDrupalRootDir']}sites/{$SC['dbToName']}{$SC['toRootDomain']}", $cb[$key]);
        $cb[$key]= str_replace("{$SC['dbName']}{$SC['rootDomain']}","{$SC['dbToName']}{$SC['toRootDomain']}", $cb[$key]);        
      }
    }
    //save back to db
    $sql = "UPDATE civicrm_domain set config_backend='".serialize($cb)."';";
    if ($SC['noExec']) cLog(0,"INFO",$sql);
    else mysql_query($sql) or die(mysql_error());
  }
  
  //runCmd("{$SC['mysql']} {$SC['dbCiviPrefix']}{{$SC['dbToName']}} -e\"update {$SC['dbCiviTablePrefix']}domain set config_backend=REPLACE(config_backend,'{{$SC['dbName']}}{$SC['rootDomain']}','{{$SC['dbToName']}}{$SC['rootDomain']}')\"");
} // updateDatebasePaths()



function listSites()
{
  global $SC;
  print_r($SC);

  //use toRootDir so the destination is used
  $handle = opendir($SC['toRootDir'].$SC['toDrupalRootDir'].'sites/');

  while (false !== ($file = readdir($handle))) {
    if ($file != 'all' && $file != 'default' && $file != '.' && $file != '..') {
      $arr[] = $file; 
    }
  }

  sort($arr);
  print_r($arr);
} // listSites()



function importTags()
{
  global $SC;

  //masterTagList
  $aTags = array();

  //check whether exists
  mysql_connect($SC['dbToHost'], $SC['dbToUser'],$SC['dbToPassword']) or die(mysql_error());
  mysql_select_db($SC['dbToCiviPrefix'].$SC['dbToName']) or die(mysql_error());

  //readTagFile
  while ($aTag = getLineAsArray($SC['tagFile'])) {
    //if we already have the tag, skip it
    if (isset($aTags[$aTag[0]])) {
      cLog(0,"INFO","already processed tag {$aTag[0]}");
      continue;
    }

    cLog(0,"INFO","processing {$aTag[0]}");

    //otherwise, get it from civi
    $aCiviTag = getTag($aTag[0]);

    //get the parent tag
    $aCiviParentTag = getTag($aTag[1]);
    $parentID = (strlen($aCiviParentTag)>0) ? $aCiviParentTag['id'] : "null";

    //not found
    if (!$aCiviTag) {
      $sql = "INSERT INTO civicrm_tag(name,description,parent_id,used_for) values('{$aTag[0]}','{$aTag[0]}',{$parentID},'civicrm_contact')";
      if ($SC['noExec']) cLog(0,'INFO',$sql);
      else mysql_query($sql);

      //now get the id for that tag
      $result = mysql_query("SELECT id,name FROM civicrm_tag where name='".$aTag[0]."';") or die(mysql_error());
      $aCiviTag = mysql_fetch_assoc($result);
    }
    else {
      cLog(0,"INFO","{$aTag[0]} already in DB.");
    }  

    //save tag in master list
    $aTags[$aCiviTag['name']] = $aCiviTag['id'];
  }
} // importTags()



function getTag($name)
{
  $result = mysql_query("SELECT id,name FROM civicrm_tag where name='$name';") or die(mysql_error());
  $aCiviTag = mysql_fetch_assoc($result);
  return $aCiviTag;
} // getTag()



function testFunction()
{
  global $SC;
  cLog(0,"INFO","connecting to database {$SC['dbToHost']}:{$SC['dbToCiviPrefix']}{$SC['dbToName']} to update database paths");

  mysql_connect($SC['dbToHost'], $SC['dbToUser'],$SC['dbToPassword']) or die(mysql_error());
  mysql_select_db($SC['dbToCiviPrefix'].$SC['dbToName']) or die(mysql_error());

  cLog(0,"INFO","replacing {$SC['drupalRootDir']}sites/{$SC['dbName']}{$SC['rootDomain']} with {$SC['toDrupalRootDir']}sites/{$SC['dbToName']}{$SC['toRootDomain']}");

  $result = mysql_query("SELECT id, config_backend FROM civicrm_domain;") or die(mysql_error());

  //get the only row
  while ($row = mysql_fetch_assoc( $result )) {
    //get the config_backend
    $cb = unserialize($row['config_backend']);
    //update the variables
    foreach ($cb as $key=>$val) {
      if (is_string($cb[$key])) {
        //$cb[$key]= str_replace($SC['dbName'],$SC['dbToName'], $cb[$key]);
        $cb[$key]= str_replace("nyssdev","nyss", $cb[$key]);
      }
    }
    //save back to db
    $sql = "UPDATE civicrm_domain set config_backend='".serialize($cb)."';";
    if ($SC['noExec']) cLog(0,"INFO",$sql);
    else mysql_query($sql) or die(mysql_error());
  }

  //runCmd("{$SC['mysql']} {$SC['dbCiviPrefix']}{{$SC['dbToName']}} -e\"update {$SC['dbCiviTablePrefix']}domain set config_backend=REPLACE(config_backend,'{{$SC['dbName']}}{$SC['rootDomain']}','{{$SC['dbToName']}}{$SC['rootDomain']}')\"");
} // testFunction()
?>
