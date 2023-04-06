<?php
/*
** Project: BluebirdCRM
** Author: Brian Shaughnessy
** Organization: New York State Senate
** Date: 2012-01-03
**
** merge duplicate contacts safely
**
*/

require_once 'script_utils.php';

error_reporting(E_ERROR | E_PARSE | E_WARNING);

function run()
{
  $prog = basename(__FILE__);
  $shortopts = 'd:l';
  $longopts = ['dryrun', 'log='];
  $stdusage = civicrm_script_usage();
  $usage = "";
  $contactOpts = [];

  $optlist = civicrm_script_init($shortopts, $longopts);
  if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    $usage = '[--dryrun]';
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
  }

  //use the log level passed to params or existing level via parent script
  set_bbscript_log_level($optlist['log'] ?? get_bbscript_log_level());

  require_once 'CRM/Core/Config.php';
  $config = CRM_Core_Config::singleton();

  $optDry = $optlist['dryrun'];

  require_once 'api/api.php';
  require_once 'CRM/Core/Error.php';
  require_once 'CRM/Core/DAO.php';

  //log the execution of script
  bbscript_log(LL::INFO, 'begin processing dedupeSubRecords.php...');

  $sTime = microtime(true);

  //record types to process
  $types = [
    'phone' => [
      'groupBys' => [
        'contact_id',
        'location_type_id',
        'phone',
        'phone_type_id',
        'phone_ext'
      ],
      'orderBys' => [
        'is_primary ASC',
        'id DESC',
      ],
    ],
    'email' => [
      'groupBys' => [
        'contact_id',
        'location_type_id',
        'email',
        'signature_text',
        'signature_html',
      ],
      'orderBys' => [
        'is_primary ASC',
        'on_hold DESC',
        'id DESC',
      ]
    ],
    'im' => [
      'groupBys' => [
        'contact_id',
        'name',
        'location_type_id',
        'provider_id',
      ],
      'orderBys' => [
        'is_primary ASC',
        'id DESC',
      ],
    ],
    'website' => [
      'groupBys' => [
        'contact_id',
        'url',
        'website_type_id',
      ],
      'orderBys' => [
        'id DESC',
      ],
    ],
  ];

  foreach ($types as $type => $details) {
    $tmpTbl = 'nyss_temp_dedupe_'.$type;
    //get order and group bys
    $orderByList = implode(', ', $details['orderBys']);
    $groupByList = implode(', ', $details['groupBys']);

    //remove duplicate records; prefer removing record with larger id (newer)
    CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS $tmpTbl;");

    $sql = "
      CREATE TABLE $tmpTbl (id INT(10), PRIMARY KEY (id))
      IGNORE SELECT ANY_VALUE(id)
      FROM (
        SELECT *
        FROM civicrm_{$type}
        ORDER BY {$orderByList}
      ) as rec1
      GROUP BY {$groupByList}
      HAVING count(id) > 1;
    ";
    bbscript_log(LL::TRACE, '$sql', $sql);
    CRM_Core_DAO::executeQuery($sql);

    $count = CRM_Core_DAO::singleValueQuery("SELECT count(id) FROM $tmpTbl");
    bbscript_log(LL::TRACE, '$count', $count);

    if ($optDry) {
      CRM_Core_DAO::executeQuery('SET group_concat_max_len = 100000');
      $sql = "SELECT GROUP_CONCAT(id) FROM $tmpTbl";
      $recs = CRM_Core_DAO::singleValueQuery($sql);
      if ($recs) {
        bbscript_log(LL::TRACE, "The following {$count} {$type} records would be removed:\n{$recs}");
      }
    }
    else {
      bbscript_log(LL::TRACE, "Removing {$count} duplicate {$type} records from {$optlist['site']}");
      $sql = "
        DELETE FROM civicrm_{$type}
        WHERE id IN (SELECT id FROM $tmpTbl);
      ";
      bbscript_log(LL::TRACE, '$sql', $sql);
      CRM_Core_DAO::executeQuery($sql);
    }

    CRM_Core_DAO::executeQuery("DROP TABLE $tmpTbl;");
  }

  $eTime = microtime(true);
  $diffTime = $eTime - $sTime;
  bbscript_log(LL::TRACE, "Time taken to complete: {$diffTime} secs.");
}

run();
