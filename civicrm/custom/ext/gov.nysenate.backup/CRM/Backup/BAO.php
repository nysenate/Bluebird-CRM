<?php

class CRM_Backup_BAO {
  static function getConfig() {
    $bbcfg = get_bluebird_instance_config();
    //Civi::log()->debug(__FUNCTION__, ['bbcfg' => $bbcfg]);

    if (empty($bbcfg)) {
      throw new CRM_Core_Exception('Unable to retrieve configuration for instance.');
    }

    $instance = get_config_value($bbcfg, 'shortname', null);
    $approot = get_config_value($bbcfg, 'app.rootdir', null);
    $dataroot = get_config_value($bbcfg, 'data.rootdir', null);
    $datadirname = get_config_value($bbcfg, 'data_dirname', null);
    $bkupdirname = get_config_value($bbcfg, 'backup.ui.dirname', null);

    if (!$instance || !$approot || !$dataroot || !$datadirname || !$bkupdirname) {
      throw new CRM_Core_Exception('Please ensure that shortname, app.rootdir, data.rootdir, data_dirname, and backup.ui.dirname are all set properly in the configuration file.');
    }

    // Absolute path to the backup directory for this instance.
    $bkupdir = "$dataroot/$datadirname/$bkupdirname/";
    //Civi::log()->debug(__FUNCTION__, ['$bkupdir' => $bkupdir]);

    if (!is_dir($bkupdir)) {
      if (!mkdir($bkupdir, 0755)) {
        throw new CRM_Core_Exception("Unable to create backup directory [$bkupdir].");
      }
    }

    return [
      'bbcfg' => $bbcfg,
      'bkupdir' => $bkupdir,
    ];
  }

  static function getBackups($dir, $cfg) {
    //fetch all instance backup files from the filesystem
    $files = [];
    if ($handle = opendir($dir)) {
      while (false !== ($file = readdir($handle))) {
        if ($file != '.' && $file != '..' && !is_dir($dir.$file) && preg_match('/\.zip$/', $file)) {
          $time = filemtime($dir.$file);
          $files[$time] = [
            'file' => $file,
            'time' => $time,
            'time_formatted' => date('m/d/Y g:ia', $time),
            'btn_restore_url' => CRM_Utils_System::url('civicrm/backup/restore', 'file='.urlencode($file)),
            'btn_delete_url' => CRM_Utils_System::url('civicrm/backup/delete', 'file='.urlencode($file)),
          ];
        }
      }
    }
    closedir($handle);

    // Sort by time for convenience
    krsort($files);

    // TODO: Weird format, could improve at some point to be an object {file1:time1, file2:time2, etc}
    return array_values($files);
  }

  /**
   * Check if a user supplied filename really exists in the Backup Dir.
   * @param $fileName String indicates the name of the backup file to be checked/resolved
   * @return String|NULL absolute path of given file if it exists or NULL if the file does not exist
   */
  static function resolveBackupFile($fileName) {
    if (!is_string($fileName) || $fileName === '' || basename($fileName) !== $fileName) {
      return NULL;
    }

    $config = self::getConfig();
    $backups = array_column(self::getBackups($config['bkupdir'], $config['bbcfg']), 'file');

    if (!in_array($fileName, $backups, TRUE)) {
      return NULL;
    }

    $fullFileName = $config['bkupdir'].$fileName;
    return is_file($fullFileName) ? $fullFileName : NULL;
  }

  static function delete($fileName) {
    $fullFileName = self::resolveBackupFile($fileName);

    if ($fullFileName && unlink($fullFileName)) {
      return TRUE;
    }

    return FALSE;
  }

  static function restore($fileName) {
    $config = self::getConfig();

    $approot = $config['bbcfg']['app.rootdir'];
    $instance = $config['bbcfg']['shortname'];

    $fullFileName = self::resolveBackupFile($fileName);
    if (!$fullFileName) {
      return FALSE;
    }

    //disable logging
    Civi::settings()->set('logging', FALSE);
    $logging = new CRM_Logging_Schema;
    $logging->disableLogging();

    $cmd = escapeshellarg("$approot/scripts/restoreInstance.sh").' '.escapeshellarg($instance)
      .' --archive-file '.escapeshellarg($fullFileName).' --ok >/dev/null';
    passthru($cmd, $err);

    //re-enable logging
    Civi::settings()->set('logging', TRUE);
    $logging = new CRM_Logging_Schema;
    $logging->fixSchemaDifferences(TRUE);
    Civi::service('sql_triggers')->rebuild(NULL, TRUE);

    if ($err == 0) {
      return TRUE;
    }

    return FALSE;
  }

  static function create($fileName) {
    //strip file ending as we will add later
    if (substr($fileName, -4) == '.zip') {
      $fileName = str_replace('.zip', '', $fileName);
    }

    $fileName = preg_replace(array('/(?![ \-])\W/','/ /'), ['','_'], $fileName);
    $fileName = substr($fileName, 0, 50);
    $fileName .= '.zip';

    $config = self::getConfig();
    $fullFilePath = $config['bkupdir'].$fileName;

    //if the file already exists tack on date string
    if (file_exists($fullFilePath)) {
      $dateTime = date('YmdHis');
      $fullFilePath = substr($fullFilePath, 0, -4)."-{$dateTime}.zip";
    }

    $approot = $config['bbcfg']['app.rootdir'];
    $instance = $config['bbcfg']['shortname'];

    /*Civi::log()->debug(__FUNCTION__, [
      'fullFilePath' => $fullFilePath,
      'approot' => $approot,
      'instance' => $instance,
    ]);*/

    shell_exec(escapeshellarg("$approot/scripts/dumpInstance.sh").' '.escapeshellarg($instance)
      .' --zip --archive-file '.escapeshellarg($fullFilePath));

    if (file_exists($fullFilePath)) {
      return TRUE;
    }

    return FALSE;
  }
}
