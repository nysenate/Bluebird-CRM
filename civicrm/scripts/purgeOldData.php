<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2014-02-07

// ./purgeOldData.php -S skelos --dryrun --date 20140101
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

class CRM_purgeOldData
{
  function run()
  {
    global $_SERVER;

    require_once 'script_utils.php';

    // Parse the options
    $shortopts = "nt:d:";
    $longopts = array("dryrun", "types=", "date=");
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--types ACN] --date YYYYMMDD';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    if (empty($optlist['date'])) {
      bbscript_log("fatal", "The date option must be defined. Use format: YYYYMMDD");
      exit(1);
    }
    else {
      //make sure date in acceptable format
      $optDate = date('Ymd', strtotime($optlist['date']));
    }

    //get instance settings for source and destination
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    bbscript_log("trace", "bbcfg_source", $bbcfg_source);

    $civicrm_root = $bbcfg_source['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    //determine if we need to restrict by record type
    $types = array(
      'A' => 'activity',
      'C' => 'case',
      'N' => 'note',
    );

    if ($optlist['types']) {
      $optTypes = str_split(strtoupper($optlist['types']));
      $selectedTypes = array();
      foreach ($optTypes as $type) {
        if (!in_array($type, array_keys($types))) {
          bbscript_log("fatal", "You selected invalid options for the record type parameter. Please enter any combination of {A,C,N} (activities, cases, notes), with no spaces between the characters.");
          exit(1);
        }
        else {
          $selectedTypes[$type] = $types[$type];
        }
      }

      $types = $selectedTypes;
    }

    bbscript_log("info", "records to be purged: ".implode(', ', $types));

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    self::purgeData($bbcfg, $optlist['dryrun'], $types, $optDate);
  }//run


  function purgeData($bbcfg, $dryrun, $types, $optDate)
  {
    //field map
    $typeMap = array(
      'activity' => array(
        'dateField' => 'activity_date_time',
      ),
      'case' => array(
        'dateField' => 'start_date',
      ),
      'note' => array(
        'dateField' => 'modified_date',
        'additionalWhere' => ' OR modified_date IS NULL ',
      ),
    );

    $optDate = date('Y-m-d', strtotime($optDate));
    $count = array();

    foreach ($types as $type) {
      bbscript_log("info", "processing records: $type");

      //get records of each type earlier than date
      $additionalWhere = CRM_Utils_Array::value('additionalWhere', $typeMap[$type], '');
      $sql = "
        SELECT id
        FROM civicrm_{$type}
        WHERE {$typeMap[$type]['dateField']} < '{$optDate}'
          $additionalWhere
      ";
      $r = CRM_Core_DAO::executeQuery($sql);

      while ($r->fetch()) {
        $count[$type] ++;

        $params = array(
          'version' => 3,
          'id' => $r->id,
        );

        if ($dryrun) {
          bbscript_log('info', "{$type} record ID to be deleted: {$r->id}");
        }
        else {
          $del = civicrm_api($type, 'delete', $params);
          //bbscript_log('debug', 'record delete: ', $del);
        }

        if ($count[$type] % 1000 === 0) {
          bbscript_log('info', "{$count[$type]} {$type} records have been deleted...");
        }
      }
    }

    bbscript_log('info', 'total count of records deleted: ', $count);
  }//purgeData

}//end class

//run the script if called directly
$purgeData = new CRM_purgeOldData();
$purgeData->run();
