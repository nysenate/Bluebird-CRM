<?php

/**
 * Author:      Brian Shaughnessy
 * Date:        2014-12-16
 * Description: This utility script compares a sites custom data file folder with the files registered in CiviCRM.
 * If a file exists but has no entry in the DB, the file is archived or deleted. Default behavior is to list only.
 */

require_once 'script_utils.php';

$prog = basename(__FILE__);
$shortopts = 'a';
$longopts = array('action=');
$stdusage = civicrm_script_usage();
$usage = "[--action|-a actionval]";

$optlist = civicrm_script_init($shortopts, $longopts);
if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage");
  exit(1);
}

//drupal_script_init();
$action = ($optlist['action']) ? $optlist['action'] : 'list';
$allowedActions = array('list', 'archive', 'delete');

if (!in_array($action, $allowedActions)) {
  echo "the action you requested is not valid.\n";
  exit(1);
}

echo "perform action: {$action}\n";

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();
//print_r($config);

//get all files from data folder
echo "retrieving all files from site data folder...\n";
$fileDir = $config->customFileUploadDir;
$files = scandir($fileDir);
//print_r($files);

$skip = array('.', '..', '.htaccess', 'inbox', 'archive');

//get all files from DB
echo "retrieving all files registered in the database...\n";
$registeredFiles = array();
$sql = "
  SELECT id, uri
  FROM civicrm_file
";
$dbfile = CRM_Core_DAO::executeQuery($sql);
while ($dbfile->fetch()) {
  $registeredFiles[$dbfile->id] = $dbfile->uri;
}

//cycle through files and see if they are registered in the db; if not -- perform action
echo "performing action on files...\n";
foreach ($files as $file) {
  if (!in_array($file, $registeredFiles) && !in_array($file, $skip)) {
    switch ($action) {
      case 'list':
        echo "file not registered in DB: {$file}\n";
        break;

      case 'archive':
        archiveFile($file, $fileDir);
        break;

      case 'delete':
        deleteFile($file, $fileDir);
        break;
    }
  }
}

//cycle through db and see if the file exists; if not -- perform action
echo "performing action on DB records...\n";
foreach ($registeredFiles as $fileID => $file) {
  if (!in_array($file, $files) && !in_array($file, $skip)) {
    switch ($action) {
      case 'list':
        echo "registered file not found in filesystem: {$file}\n";
        break;

      case 'archive':
        echo "note: we are not able to archive DB file records\n";
        break;

      case 'delete':
        deleteRecord($file, $fileID);
        break;
    }
  }
}

function archiveFile($file, $dir) {
  echo "archiving file: {$file}\n";
  if (!file_exists($dir.'archive')) {
    mkdir($dir.'archive');
  }

  rename($dir.$file, $dir.'archive/'.$file);
}

function deleteFile($file, $dir) {
  echo "deleting file: {$file}\n";

  unlink($dir.$file);
}

function deleteRecord($file, $fileID) {
  echo "deleting file record from DB: {$fileID}|{$file}\n";

  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_file
    WHERE id = {$fileID}
      AND uri = '{$file}'
  ");
}

echo "finished cleaning up files.\n";

exit(0);
