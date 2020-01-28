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
        if ($file != '.' && $file != '..' && !is_dir($dir.$file) && preg_match('/.*\.zip/', $file)) {
          $time = filemtime($dir.$file);
          $files[$time] = [
            'file' => $file,
            'time' => $time,
            'time_formatted' => date('m/d/Y g:ia', $time),
            'btn_restore_url' => CRM_Utils_System::url('civicrm/backup/restore', "file={$file}"),
            'btn_delete_url' => CRM_Utils_System::url('civicrm/backup/delete', "file={$file}"),
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

  static function delete($fileName) {
    $config = self::getConfig();

    if (!empty($fileName) && unlink($config['bkupdir'].$fileName)) {
      return TRUE;
    }

    return FALSE;
  }

  static function restore($fileName) {
    $config = self::getConfig();

    $approot = $config['bbcfg']['app.rootdir'];
    $instance = $config['bbcfg']['shortname'];

    if (!$fileName) {
      return FALSE;
    }

    $fullFileName = $config['bkupdir'].$fileName;

    passthru("$approot/scripts/restoreInstance.sh $instance --archive-file $fullFileName --ok >/dev/null", $err);

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

    shell_exec("$approot/scripts/dumpInstance.sh $instance --zip --archive-file $fullFilePath");

    if (file_exists($fullFilePath)) {
      return TRUE;
    }

    return FALSE;
  }
}
