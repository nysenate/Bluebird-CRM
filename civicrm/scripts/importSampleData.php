<?php

/*
 * Project: BluebirdCRM
 * Authors: Brian Shaughnessy
 * Organization: New York State Senate
 * Date: 2013-04-27
 *
 * http://dev.nysenate.gov/issues/6109
 * http://dev.nysenate.gov/projects/bluebird/wiki/Sample_Data_CleanupImport
 *
 * Usage:
 * php importSampleData.php -S INSTANCE --dryrun --purge
 *
 * --system = only import system data (no contacts)
 * --purge = purge data before importing
 * --dryrun = don't import data; print to screen only
 */

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'TRACE');
define('LOC_TYPE_BOE', 6);

class CRM_ImportSampleData {

  function run() {

    global $shortopts;
    global $longopts;
    global $optDry;

    require_once 'script_utils.php';

    // Parse the options
    $shortopts = 'd:s:p';
    $longopts = array('dryrun', 'system', 'purge');
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === null) {
        $stdusage = civicrm_script_usage();
        $usage = '[--dryrun] [--system] [--purge]';
        error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
        exit(1);
    }

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    //bbscript_log("trace", "bbcfg", $bbcfg);

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    //retrieve/set options
    $optDry = $optlist['dryrun'];
    $scriptPath = $bbcfg['app.rootdir'].'/civicrm/scripts';

    require_once 'libs/Spyc.php';

    $data = array(
      //'system.yml',
      'organizations.yml',
      //'individuals.yml',
    );

    if ( $optDry ) {
      bbscript_log("info", "Running in dryrun mode. No data will be altered.");
    }

    //clean out all existing data
    if ( $optlist['purge'] ) {
      self::purgeData();
    }

    //begin import
    foreach ( $data as $file ) {
      self::importData($file, $scriptPath);
    }

    bbscript_log("info", "Completed instance cleanup and sample data import for {$bbcfg['name']}.");

  }//run

  /*
   * before importing sample data we purge the instance of all existing data
   * this is done to selective tables in order to retain system settings, option lists, and other data common to all sites
   */
  function purgeData() {
    global $optDry;

    CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS=0;');

    $tblTruncate = array(
      'civicrm_acl',
      'civicrm_acl_cache',
      'civicrm_acl_contact_cache',
      'civicrm_acl_entity_role',
      'civicrm_action_log',
      'civicrm_action_mapping',
      'civicrm_action_schedule',
      'civicrm_activity',
      'civicrm_activity_assignment',
      'civicrm_activity_target',
      'civicrm_address',
      'civicrm_address_format',
      'civicrm_batch',
      'civicrm_cache',
      'civicrm_case',
      'civicrm_case_activity',
      'civicrm_case_contact',
      'civicrm_contact',
      'civicrm_dashboard_contact',
      'civicrm_dedupe_exception',
      'civicrm_email',
      'civicrm_entity_batch',
      'civicrm_entity_file',
      'civicrm_entity_tag',
      'civicrm_file',
      'civicrm_group',
      'civicrm_group_contact',
      'civicrm_group_contact_cache',
      'civicrm_group_nesting',
      'civicrm_group_organization',
      'civicrm_im',
      'civicrm_job_log',
      'civicrm_line_item',
      'civicrm_log',
      'civicrm_mailing',
      'civicrm_mailing_event_bounce',
      'civicrm_mailing_event_confirm',
      'civicrm_mailing_event_delivered',
      'civicrm_mailing_event_forward',
      'civicrm_mailing_event_opened',
      'civicrm_mailing_event_queue',
      'civicrm_mailing_event_reply',
      'civicrm_mailing_event_subscribe',
      'civicrm_mailing_event_trackable_url_open',
      'civicrm_mailing_event_unsubscribe',
      'civicrm_mailing_group',
      'civicrm_mailing_job',
      'civicrm_mailing_recipients',
      'civicrm_mailing_spool',
      'civicrm_mailing_trackable_url',
      'civicrm_menu',
      'civicrm_note',
      'civicrm_persistent',
      'civicrm_phone',
      'civicrm_prevnext_cache',
      'civicrm_project',
      'civicrm_queue_item',
      'civicrm_relationship',
      'civicrm_saved_search',
      'civicrm_sms_provider',
      'civicrm_subscription_history',
      'civicrm_survey',
      //'civicrm_tag',
      'civicrm_task',
      'civicrm_task_status',
      'civicrm_tell_friend',
      'civicrm_uf_match',
      'civicrm_website',
    );
    if ( $optDry ) {
      bbscript_log('trace', 'The following tables would be truncated: ', $tblTruncate);
    }
    else {
      bbscript_log('trace', 'Truncating tables: ');
      foreach ( $tblTruncate as $tbl ) {
        echo 'truncating... '.$tbl."\n";
        $sql = "TRUNCATE TABLE {$tbl};";
        CRM_Core_DAO::executeQuery($sql);
      }
    }

    //additionally, we must implement special handling for several tables
    bbscript_log('trace', 'Tables with select row/field deletion: civicrm_dashboard, civicrm_setting');
    if ( !$optDry ) {
      $dashRetain = array(
        'All Cases',
      );
      $dashRetainList = implode(',', $dashRetain);
      $sql = "
        DELETE FROM civicrm_dashboard
        WHERE label NOT IN ('{$dashRetainList}');
      ";
      CRM_Core_DAO::executeQuery($sql);

      $sql = "
        DELETE FROM civicrm_setting WHERE name = 'navigation';
      ";
      CRM_Core_DAO::executeQuery($sql);
    }

    //TODO seed the senateroot contact id = 1
  }//purgeData

  function importData($file, $scriptPath) {
    global $fkMap;

    $type = str_replace('.yml', '', $file);

    switch ( $type ) {
      case 'individuals':
      case 'organizations':
      case 'households':
        $type = 'contact';
        break;
      default:
    }

    $filename = $scriptPath.'/sampleData/'.$file;
    bbscript_log("trace", "filename: $filename");
    $data = Spyc::YAMLLoad($filename);

    foreach ( $data as $params ) {
      //bbscript_log("trace", "import params", $params);
      if ( isset($params['fk']) ) {
        $fk = $params['fk'];
        unset($params['fk']);

        self::_checkFK($fk, $fkMap, $type, $params);
      }

      if ( !empty($params['employer_id']) ) {
        $params['employer_id'] = $fkMap[$type][$params['employer_id']];
        //bbscript_log("trace", "params after fkMap", $params);
        //bbscript_log("trace", "fkMap", $fkMap);
      }

      //bbscript_log("trace", "params before iAPI", $params);
      $r = self::iAPI($type, 'create', $params);
      //bbscript_log("trace", "r", $r);

      echo "imported: {$r['values'][$r['id']]['display_name']} \n";

      if ( $fk ) {
        $fkMap[$type][$fk] = $r['id'];
      }

    }
  }//importData

  /*
   * wrapper for civicrm_api
   * allows us to determine action based on dryrun status and perform other formatting actions
   */
  function iAPI($entity, $action, $params) {
    global $optDry;
    global $customMap;

    //record types which are custom groups
    $customGroups = array(
      'Additional_Constituent_Information', 'Attachments',
      'Contact_Details', 'Organization_Constituent_Information',
      'District_Information'
    );
    $dateFields = array(
      'last_import_57', 'boe_date_of_registration_24'
    );

    //prepare custom fields
    if ( in_array($entity, $customGroups) ) {
      //get fields and construct array if not already constructed
      if ( !isset($customMap[$entity]) || empty($customMap[$entity]) ) {
        $customDetails = self::getCustomFields($entity);
        foreach ( $customDetails as $field ) {
          $customMap[$entity][$field['column_name']] = 'custom_'.$field['id'];
        }
      }
      //bbscript_log("trace", '_importAPI $customMap', $customMap);

      //cycle through custom fields and convert column name to custom_## format
      foreach ( $params as $col => $v ) {
        //if a date type column, strip punctuation
        if ( in_array($col, $dateFields) ) {
          $v = str_replace(array('-', ':', ' '), '', $v);
        }
        if ( array_key_exists($col, $customMap[$entity]) ) {
          $params[$customMap[$entity][$col]] = $v;
          unset($params[$col]);
        }
      }

      //change entity value for api
      $entity = 'custom_value';
    }

    //clean the params array
    $paramsOrig = $params;
    $params = self::_cleanArray($params);

    if ( $optDry ) {
      bbscript_log("debug", "_importAPI entity:{$entity} action:{$action} params:", $params);
    }

    if ( !is_array($params) ) {
      bbscript_log("debug", "_importAPI params not array", $paramsOrig);
      return;
    }

    if ( !$optDry || $action == 'get' ) {
      //add api version
      $params['version'] = 3;
      if ( DEFAULT_LOG_LEVEL == 'TRACE' ) {
        $params['debug'] = 1;
      }

      $api = civicrm_api($entity, $action, $params);

      if ( $api['is_error'] ) {
        bbscript_log("debug", "_importAPI error", $api);
        bbscript_log("debug", "_importAPI entity: {$entity} // action: {$action}", $params);
      }
      return $api;
    }
  }//iAPI

  /*
   * determine if we need to do a foreign key lookup and what type
   */
  function _checkFK($fk, $fkMap, $type, &$params) {
    switch ( $type ) {
      case 'contact':
        break;
    }
  }//_checkFK

  /*
   * given an array, cycle through and unset any elements with no value
   */
  function _cleanArray($data) {
    if ( is_array($data) && !empty($data) ) {
      foreach ( $data as $f => $v ) {
        if ( empty($v) && $v !== 0 ) {
          unset($data[$f]);
        }
        if ( is_string($v) ) {
          $data[$f] = stripslashes($v);
        }
      }
    }
    return $data;
  }//_cleanArray
}


//run the script
$importData = new CRM_ImportSampleData();
$importData->run();
