<?php

/**
 * Author:      Brian Shaughnessy, Ken Zalewski
 * Date:        2014-12-16
 * Revised:     2015-02-01 - added --db-action; fixed DB record deletion
 * Description: This script compares a site's custom data file folder with
 *              the files registered in the CiviCRM DB.  If a file exists but
 *              has no entry in the DB, the file is archived or deleted.
 *              Similarly, if a DB record exists but there is no corresponding
 *              on-disk file, the DB record is deleted.
 *              Default behavior is to list only.
**/

require_once 'script_utils.php';

$prog = basename(__FILE__);
$shortopts = 'f:d:';
$longopts = array('file-action=', 'db-action=');
$stdusage = civicrm_script_usage();
$usage = "[--file-action|-f {list | archive | delete}] [--db-action|-d {list | delete}]";

$optlist = civicrm_script_init($shortopts, $longopts);
if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage");
  exit(1);
}

//drupal_script_init();
$file_action = $optlist['file-action'] ? $optlist['file-action'] : 'list';
$db_action = $optlist['db-action'] ? $optlist['db-action'] : 'list';
$allowedActions = array('list', 'archive', 'delete');

if (!in_array($file_action, array('list', 'archive', 'delete'))) {
  echo "$prog: $file_action: The requested file action is not valid.\n";
  exit(1);
}
else if (!in_array($db_action, array('list', 'delete'))) {
  echo "$prog: $db_action: The requested database action is not valid.\n";
  exit(1);
}

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();

//get all files from data folder
echo "$prog: Retrieving all files from site custom data folder\n";
$fileDir = $config->customFileUploadDir;
$filesInDir = scandir($fileDir);
$skip = array('.', '..', '.htaccess', 'inbox', 'archive');
$filesInDir = array_diff($filesInDir, $skip);

//get all files from DB
echo "$prog: Retrieving all files registered in the database\n";
$registeredFiles = array();
$sql = "SELECT id, uri FROM civicrm_file";
$dbfile = CRM_Core_DAO::executeQuery($sql);
while ($dbfile->fetch()) {
  $registeredFiles[$dbfile->id] = $dbfile->uri;
}

// Compute the list of files that are not registered in the DB.
$danglingFiles = array_diff($filesInDir, $registeredFiles);
$danglingFileCount = count($danglingFiles);
echo "$prog: Found $danglingFileCount files that are not registered in the DB\n";

// Compute the list of file records in the DB that are not on disk.
$danglingRecs = array_diff($registeredFiles, $filesInDir);
$danglingRecCount = count($danglingRecs);
echo "$prog: Found $danglingRecCount file records in the DB that are not in the custom data folder\n";

if ($danglingFileCount > 0) {
  echo "$prog: Performing action '$file_action' on $danglingFileCount files\n";

  if ($file_action == 'archive' && !file_exists($fileDir.'archive')) {
    echo "$prog: Creating archive directory for dangling files\n";
    mkdir($fileDir.'archive');
  }

  foreach ($danglingFiles as $file) {
    switch ($file_action) {
      case 'list':
        echo "$prog: $file: File not registered in DB\n";
        break;

      case 'archive':
        rename($fileDir.$file, $fileDir.'archive/'.$file);
        break;

      case 'delete':
        unlink($fileDir.$file);
        break;
    }
  }
}
else {
  echo "$prog: There are no dangling files; skipping '$file_action' action\n";
}

if ($danglingRecCount > 0) {
  echo "$prog: Performing action '$db_action' on $danglingRecCount DB records\n";

  foreach ($danglingRecs as $fileID => $file) {
    switch ($db_action) {
      case 'list':
        echo "$prog: $file [id=$fileID]: Registered file not found in filesystem\n";
        break;

      case 'delete':
        deleteRecord($file, $fileID);
        break;
    }
  }
}
else {
  echo "$prog: There are no dangling DB file records; skipping '$db_action' action\n";
}

echo "$prog: Finished cleaning up files.\n";
exit(0);


function deleteRecord($file, $fileID)
{
  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_entity_file
    WHERE file_id = $fileID");

  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_file
    WHERE id = $fileID AND uri = '$file'");
} // deleteRecord()

