<?php

/*
 * Project: BluebirdCRM
 * Authors: Brian Shaughnessy
 * Organization: New York State Senate
 * Date: 2023-09-25
 * Issue: #15839
 *
 * Cleanup script to set First/Last name from migration file where imported name was "Contact ExternalID"
 * */

// ./migrateContacts_15839.php -S sd99 --filename=migrate --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

class CRM_migrateContacts_15839 {

  function run() {
    global $_SERVER;

    //set memory limit so we don't max out
    ini_set('memory_limit', '5G');

    require_once realpath(dirname(__FILE__)).'/../script_utils.php';

    // Parse the options
    $shortopts = "f:nl:";
    $longopts = ["filename=", "dryrun", "log="];
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === NULL) {
      $stdusage = civicrm_script_usage();
      $usage = '--filename FILENAME  [--dryrun]  [--log LEVEL]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    if (empty($optlist['filename'])) {
      bbscript_log(LL::FATAL, "No filename provided. You must provide a filename to process the cleanup.");
      exit();
    }

    if (!empty($optlist['log'])) {
      set_bbscript_log_level($optlist['log']);
    }

    //get instance settings which represents the destination instance
    $bbcfg_dest = get_bluebird_instance_config($optlist['site']);
    //bbscript_log(LL::TRACE, '$bbcfg_dest', $bbcfg_dest);

    $civicrm_root = $bbcfg_dest['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    /*if (!CRM_Utils_System::loadBootstrap([], FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from migrateContactsImport.');
      return FALSE;
    }*/

    $dest = [
      'name' => $optlist['site'],
      'num' => $bbcfg_dest['district'],
      'db' => $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'],
      'files' => $bbcfg_dest['data.rootdir'],
      'domain' => $optlist['site'].'.'.$bbcfg_dest['base.domain'],
      'appdir' => $bbcfg_dest['app.rootdir'],
      'install_class' => $bbcfg_dest['install_class'],
    ];
    //bbscript_log(LL::TRACE, "$dest", $dest);

    //if dest unset/irretrievable, exit
    if (empty($dest['db'])) {
      bbscript_log(LL::FATAL, "Unable to retrieve configuration for destination instance.");
      exit();
    }

    // Initialize CiviCRM
    $config = CRM_Core_Config::singleton();
    CRM_Core_Session::singleton();

    //override geocode method
    $config->geocodeMethod = '';

    //retrieve/set other options
    $optDry = $optlist['dryrun'];

    //set import folder based on environment
    $fileDir = '/data/redistricting/bluebird_'.$bbcfg_dest['install_class'].'/migrate';
    if (!file_exists($fileDir)) {
      mkdir( $fileDir, 0775, TRUE );
    }

    //check for existence of file to import
    $importFile = $fileDir.'/'.$optlist['filename'];
    if (!file_exists($importFile)) {
      bbscript_log(LL::FATAL, "The import file you have specified does not exist. It must reside in {$fileDir}.");
      exit(1);
    }

    //call main import function
    $this->importData($dest, $importFile, $optDry);
  }

  function importData($dest, $importFile, $optDryParam) {
    global $optDry;
    global $exportData;

    bbscript_log(LL::INFO, __METHOD__);

    //set global to value passed to function
    $optDry = $optDryParam;

    //bbscript_log(LL::TRACE, "importData dest", $dest);
    bbscript_log(LL::INFO, "cleanup up data using... $importFile");

    //retrieve data from file and set to variable as array
    $exportData = json_decode(file_get_contents($importFile), TRUE);
    //bbscript_log(LL::TRACE, 'importData $exportData', $exportData);

    //parse the import file source/dest, compare with params and return a warning message if values do not match
    if (!$optDry && $exportData['dest']['name'] != $dest['name']) {
      bbscript_log(LL::FATAL, 'The destination defined in the import file does not match the parameters passed to the script. Exiting the script as a mismatched destination could create significant data problems. Please investigate and then rerun the script.');
      exit();
    }

    $exportData['dest']['app'] = $dest['appdir'];

    $results = $this->cleanContacts($exportData);

    $source = $exportData['source'];

    bbscript_log(LL::INFO, "Completed contact cleanup from district {$source['num']} ({$source['name']}) to district {$dest['num']} ({$dest['name']}) using {$importFile}.");

    bbscript_log(LL::INFO, "Migration cleanup statistics:", $results);

    //log to file
    if (!$optDry) {
      //set import folder based on environment
      $fileDir = '/data/redistricting/bluebird_'.$dest['install_class'].'/MigrationReports';
      if (!file_exists($fileDir)) {
        mkdir( $fileDir, 0775, TRUE );
      }

      $reportFile = $fileDir.'/'.$source['name'].'_'.$dest['name'].'_cleanup_15839.txt';
      $fileResource = fopen($reportFile, 'w');

      $content = [
        'options' => $exportData['options'] ?? NULL,
        'stats' => $results,
      ];

      $content = print_r($content, TRUE);
      fwrite($fileResource, $content);
    }

    //now run cleanup scripts
    $dryParam = ($optDry) ? "--dryrun" : '';
    $scriptPath = $dest['appdir'].'/civicrm/scripts';
    $logLevel = get_bbscript_log_level();

    //TODO rebuild greetings?
  }

  function cleanContacts($exportData) {
    global $optDry;

    bbscript_log(LL::INFO, __METHOD__);
    bbscript_log(LL::TRACE, '$exportData', $exportData);

    //initialize stats array
    $stats = [
      'Individual' => 0,
      'Organization' => 0,
      'Household' => 0,
      'Total' => 0,
      'Errors' => [],
      'Not Found' => [],
    ];

    //only a subset of previously imported records need to be cleaned
    //pull those from the DB and then update from the import file
    $sql = "
      SELECT id, external_identifier, contact_type
      FROM civicrm_contact
      WHERE ((contact_type = 'Individual' AND first_name = 'Contact' AND last_name LIKE 'SD{$exportData['source']['num']}_%')
        OR (contact_type = 'Organization' AND organization_name LIKE 'Organization SD{$exportData['source']['num']}_%')
        OR (contact_type = 'Household' AND household_name LIKE 'Household SD{$exportData['source']['num']}_%'))
        AND is_deleted = 0
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);

    $fileData = $exportData['import'];

    while ($dao->fetch()) {
      if (isset($fileData[$dao->external_identifier])) {
        bbscript_log(LL::TRACE, __METHOD__.' details', $fileData[$dao->external_identifier]['contact']);
        bbscript_log(LL::DEBUG, __METHOD__." extID = {$dao->external_identifier}");

        try {
          $update = \Civi\Api4\Contact::update(FALSE)
            ->addWhere('id', '=', $dao->id);

          switch ($dao->contact_type) {
            case 'Individual':
              $update->addValue('first_name', $fileData[$dao->external_identifier]['contact']['first_name']);
              $update->addValue('last_name', $fileData[$dao->external_identifier]['contact']['last_name']);
              break;

            case 'Organization':
              $update->addValue('organization_name', $fileData[$dao->external_identifier]['contact']['organization_name']);
              break;

            case 'Household':
              $update->addValue('household_name', $fileData[$dao->external_identifier]['contact']['household_name']);
              break;

            default:
          }

          if (!$optDry) {
            $update->execute();
          }

          $stats[$dao->contact_type] ++;
          $stats['Total'] ++;
        }
        catch (CRM_Core_Exception $e) {
          Civi::log()->debug(__METHOD__, ['e' => $e]);
          $stats['Errors'] = $dao->external_identifier;
        }
      }
      else {
        $stats['Not Found'] = $dao->external_identifier;
      }
    }

    return $stats;
  }
}

//run the script
$importData = new CRM_migrateContacts_15839();
$importData->run();
