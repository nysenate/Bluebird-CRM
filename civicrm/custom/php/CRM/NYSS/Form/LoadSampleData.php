<?php

/**
 * This class generates form components for DedupeRules
 *
 */
class CRM_NYSS_Form_LoadSampleData extends CRM_Core_Form {

  /**
   * Function to preprocessing
   *
   * @return None
   * @access public
   */
  function preProcess() {
    $bbcfg = get_bluebird_instance_config();
    //CRM_Core_Error::debug_var('bbcfg',$bbcfg);

    //TODO allowable instances should be retrieved from bluebird.cfg
    $allowedInstances = [
      'demo',
      'sample',
      'sd99',
      'training1',
      'training2',
      'training3',
      'training4',
      'brian',
    ];

    if ( !in_array($bbcfg['shortname'], $allowedInstances) ) {
      $url = CRM_Utils_System::url( 'civicrm','reset=1' );
      CRM_Core_Error::statusBounce( 'This instance is locked and may not have contacts purged and sample data loaded.', $url );
    }
  }

  /**
   * Function to process the form
   *
   * @access public
   * @param output_status Determines if the status will be output.  This should
   *                      be set to false when running from the CLI.
   * @return None
   */
  static function loadData() {
    global $user;

    $action = CRM_Utils_Array::value('action', $_GET);

    $purge = '--purge';
    if ($action == 'purgeData') {
      $purge = '--purge-only';
    }

    $sTime = microtime(TRUE);

    //get script
    $bbcfg = get_bluebird_instance_config();
    $script = $bbcfg['app.rootdir'].'/civicrm/scripts/importSampleData.php';

    $config = CRM_Core_Config::singleton();
    $logFile = $config->configAndLogDir.'loadSample_output.log';

    //truncate the log file before running the script
    $f = fopen($logFile, 'w');
    fclose($f);

    //run script
    $uid = "{$user->uid}/{$user->mail}";
    exec("php $script -S {$bbcfg['shortname']} --system {$purge} --log=info --uid={$uid} 1>{$logFile}");

    $eTime = microtime(TRUE);
    $diffTime = ($eTime - $sTime)/60;

    //return processing time
    echo $diffTime;
    CRM_Utils_System::civiExit();
  }

  static function getOutput() {
    $config = CRM_Core_Config::singleton();
    $logFile = $config->configAndLogDir.'loadSample_output.log';

    //$i = 0;
    $output = '';
    $finished = FALSE;

    //process file
    $fhandle = fopen($logFile, "r");
    while (!feof($fhandle)) {
      $line = fgets($fhandle);
      $i++;
      //CRM_Core_Error::debug_var('i',$i);

      $output .= str_replace("\n", '</p><p>', $line);
      //CRM_Core_Error::debug_var('output',$output);

      if (strpos($line, 'Completed instance cleanup') !== FALSE) {
        $finished = TRUE;
      }

      sleep(5);
    }

    //print final output from partial set
    if (!empty($output)) {
      echo $output;
    }

    if ($finished) {
      echo 'SCRIPTCOMPLETE';
    }

    fclose($fhandle);
    CRM_Utils_System::civiExit();
  }
}
