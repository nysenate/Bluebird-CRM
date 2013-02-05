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
  $shortopts = 'd';
  $longopts = array('dryrun');
  $stdusage = civicrm_script_usage();
  $usage = "";
  $contactOpts = array();

  $optlist = civicrm_script_init($shortopts, $longopts);
  if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    $usage = '[--dryrun]';
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
  }

  require_once 'CRM/Core/Config.php';
  $config = CRM_Core_Config::singleton();

  $optDry = $optlist['dryrun'];

  require_once 'api/api.php';
  require_once 'CRM/Core/Error.php';
  require_once 'CRM/Core/DAO.php';
  
  //log the execution of script
  CRM_Core_Error::debug_log_message('dedupeSubRecords.php');

  $sTime = microtime(true);

  //record types to process
  $types = array(
    'phone' => array(
      'groupBys' => array(
        'contact_id',
        'location_type_id',
        'phone',
        'phone_type_id',
        'phone_ext'
      ),
      'orderBys' => array(
        'is_primary ASC',
        'id DESC',
      ),
    ),
    'email' => array(
      'groupBys' => array(
        'contact_id',
        'location_type_id',
        'email',
        'signature_text',
        'signature_html',
      ),
      'orderBys' => array(
        'is_primary ASC',
        'on_hold DESC',
        'id DESC',
      )
    ),
    'im' => array(
      'groupBys' => array(
        'contact_id',
        'name',
        'location_type_id',
        'provider_id',
      ),
      'orderBys' => array(
        'is_primary ASC',
        'id DESC',
      ),
    ),
    'website' => array(
      'groupBys' => array(
        'contact_id',
        'url',
        'website_type_id',
      ),
      'orderBys' => array(
        'id DESC',
      ),
    ),
  );

  echo "\nbegin processing dedupeSubRecords.php...\n\n";

  foreach ( $types as $type => $details ) {
    //get order and group bys
    $orderByList = implode(', ', $details['orderBys']);
    $groupByList = implode(', ', $details['groupBys']);

    //remove duplicate records; prefer removing record with larger id (newer)
    CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS tmpDedupe_{$type};");
    $sql = "CREATE TABLE tmpDedupe_{$type} ( id INT(10), PRIMARY KEY (id) )
            SELECT id
            FROM (
              SELECT *
              FROM civicrm_{$type}
              ORDER BY {$orderByList} ) as rec1
            GROUP BY {$groupByList}
            HAVING count(id) > 1;";
    CRM_Core_DAO::executeQuery($sql);

    $count = CRM_Core_DAO::singleValueQuery("SELECT count(id) FROM tmpDedupe_{$type}");

    if ( $optDry ) {
      CRM_Core_DAO::executeQuery('SET group_concat_max_len = 100000');
      $sql = "SELECT GROUP_CONCAT(id) FROM tmpDedupe_{$type}";
      $recs = CRM_Core_DAO::singleValueQuery($sql);
      if ( $recs ) {
        echo "The following {$count} {$type} records would be removed:\n{$recs}\n\n";
      }
    }
    else {
      echo "Removing {$count} duplicate {$type} records from {$optlist['site']}\n";
      $sql = "
        DELETE FROM civicrm_{$type}
        WHERE id IN ( SELECT id FROM tmpDedupe_{$type} );
      ";
      CRM_Core_DAO::executeQuery($sql);
    }
  }

  $eTime = microtime(true);
  $diffTime = $eTime - $sTime;
  echo "Time taken to complete: {$diffTime} secs.\n\n";
}

run();
