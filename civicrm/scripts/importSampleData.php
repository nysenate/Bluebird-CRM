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

define('DEFAULT_LOG_LEVEL', 'INFO');
define('LOC_TYPE_BOE', 6);

class CRM_ImportSampleData {

  function run() {

    global $shortopts;
    global $longopts;
    global $optDry;
    global $BB_LOG_LEVEL;

    require_once 'script_utils.php';

    // Parse the options
    $shortopts = 'd:s:p:g:l';
    $longopts = array('dryrun', 'system', 'purge', 'generate', 'log');
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--system] [--purge] [--generate] [--log=LEVEL]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    if ( empty($BB_LOG_LEVEL) && !empty($optlist['log']) ) {
      $BB_LOG_LEVEL = $optlist['log'];
    }
    elseif ( empty($BB_LOG_LEVEL) ) {
      $BB_LOG_LEVEL = DEFAULT_LOG_LEVEL;
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

    //check if we should generate data
    if ( $optlist['generate'] ) {
      self::generateData($scriptPath);
      exit();
    }

    //process system data
    $sys = array(
      'tag.yml',
    );
    if ( $optlist['system'] && !$optDry ) {
      self::importSystem($sys, $scriptPath);
    }

    $data = array(
      'organizations.yml',
      'individuals.yml',
      'activity.yml',
      'entity_tag.yml',
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
      bbscript_log('info', 'Truncating tables... ');
      foreach ( $tblTruncate as $tbl ) {
        bbscript_log('debug', "truncating: $tbl");
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

    //seed the senateroot contact id = 1
    echo "\nseeding database with bluebird admin contact... \n";
    $params = array(
      'first_name' => 'Bluebird',
      'last_name' => 'Administrator',
      'contact_type' => 'Individual',
      'api.email.create' => array(
        'email' => 'bluebird.admin@nysenate.gov',
        'location_type_id' => 1,
      ),
      'api.uf_match.create' => array(
        'uf_id' => 1,
      ),
    );
    self::iAPI('contact', 'create', $params);
  }//purgeData

  function importData($file = NULL, $scriptPath = NULL, $data = NULL) {
    global $fkMap;

    $type = str_replace('.yml', '', $file);
    $errors = array();

    switch ( $type ) {
      case 'individuals':
      case 'organizations':
      case 'households':
        $type = 'contact';
        break;
      default:
    }

    if ( !$data ) {
      $filename = $scriptPath.'/sampleData/'.$file;
      bbscript_log("trace", "filename: $filename");
      $data = Spyc::YAMLLoad($filename);
    }

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

      if ( $type == 'activity' ) {
        $params['source_contact_id'] = CRM_Utils_Array::value($params['source_contact_id'], $fkMap, 1);
        $params['target_contact_id'] = $fkMap['contact'][$params['target_contact_id']];
      }

      if ( $type == 'entity_tag' ) {
        $params['entity_id'] = $fkMap['contact'][$params['entity_id']];
      }

      //bbscript_log("trace", "params before iAPI", $params);
      $r = self::iAPI($type, 'create', $params);
      //bbscript_log("trace", "r", $r);

      if ( $r['is_error'] ) {
        $errors[] = $params;
      }
      elseif ( $type == 'contact' ) {
        bbscript_log("debug", "imported: {$r['values'][$r['id']]['display_name']}");
      }
      else {
        bbscript_log("debug", "imported: {$type}");
      }

      if ( $fk ) {
        $fkMap[$type][$fk] = $r['id'];
      }
    }

    //reprocess errors
    if ( !empty($errors) && !defined('PROCESS_ERRORS') ) {
      $errorCount = count($errors);
      echo "\nattempting to reprocess error records. total to process: {$errorCount}... \n";

      define('PROCESS_ERRORS', 1);
      self::importData(NULL, NULL, $errors);
    }
    elseif ( !empty($errors) && defined('PROCESS_ERRORS') ) {
      bbscript_log("debug", "remaining error records", $errors);
    }

  }//importData

  function importSystem($sys, $scriptPath) {
    foreach ( $sys as $file ) {
      $type = str_replace('.yml', '', $file);
      $filename = $scriptPath.'/sampleData/'.$file;
      $data = Spyc::YAMLLoad($filename);

      switch ( $type ) {
        case 'tag':
          //we want to preserve the IDs, so we will do a straight sql import
          CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS=0;');
          CRM_Core_DAO::executeQuery("TRUNCATE civicrm_tag");
          foreach ( $data as $row ) {
            $tagCols = implode(', ', array_keys($row));
            $tagVals = CRM_Core_DAO::escapeStrings($row);
            $tagSQL = "
              INSERT INTO civicrm_tag ({$tagCols})
              VALUES (".$tagVals.")
            ";
            //bbscript_log("trace", "tagSQL", $tagSQL);
            CRM_Core_DAO::executeQuery($tagSQL);
          }
          break;
      }
    }
  }//importSystem

  function generateData($scriptPath) {
    echo "generating sample data... \n";

    $data = array();
    $test = 0;
    $i = 0;
    $t = 'senate_dev_c_training';

    $sql = "
      SELECT id, contact_type, first_name, middle_name, last_name, prefix_id, suffix_id, gender_id, birth_date, employer_id
      FROM {$t}.civicrm_contact
      WHERE contact_type = 'Individual'
        AND id != 1
        AND first_name != ''
        AND last_name != '';
    ";
    $c = CRM_Core_DAO::executeQuery($sql);
    while ( $c->fetch() ) {
      $contact = array(
        'fk' => $c->id,
        'contact_type' => $c->contact_type,
        'first_name' => $c->first_name,
        'middle_name' => $c->middle_name,
        'last_name' => $c->last_name,
        'prefix_id' => $c->prefix_id,
        'suffix_id' => $c->suffix_id,
        'gender_id' => $c->gender_id,
        'birth_date' => $c->birth_date,
        'employer_id' => $c->employer_id,
      );

      //address
      $sql = "
        SELECT id, location_type_id, is_primary, street_address, supplemental_address_1, supplemental_address_2, supplemental_address_3, city, state_province_id, postal_code_suffix, postal_code
        FROM {$t}.civicrm_address
        WHERE contact_id = {$c->id}
      ";
      $a = CRM_Core_DAO::executeQuery($sql);
      while ( $a->fetch() ) {
        $diData = array();
        $sql = "
          SELECT *
          FROM {$t}.civicrm_value_district_information_7
          WHERE entity_id = {$a->id}
        ";
        $di = CRM_Core_DAO::executeQuery($sql);
        while ( $di->fetch() ) {
          $diData['custom_46'] = $di->congressional_district_46;
          $diData['custom_47'] = $di->ny_senate_district_47;
          $diData['custom_48'] = $di->ny_assembly_district_48;
          $diData['custom_49'] = $di->election_district_49;
          $diData['custom_50'] = $di->county_50;
          $diData['custom_51'] = $di->county_legislative_district_51;
          $diData['custom_52'] = $di->town_52;
          $diData['custom_53'] = $di->ward_53;
          $diData['custom_54'] = $di->school_district_54;
          $diData['custom_55'] = $di->new_york_city_council_55;
          $diData['custom_56'] = $di->neighborhood_56;
        }

        $contact['api.address.create'][] = array(
          'location_type_id' => $a->location_type_id,
          'is_primary' => $a->is_primary,
          'street_address' => $a->street_address,
          'supplemental_address_1' => $a->supplemental_address_1,
          'supplemental_address_2' => $a->supplemental_address_2,
          'supplemental_address_3' => $a->supplemental_address_3,
          'city' => $a->city,
          'state_province_id' => $a->state_province_id,
          'postal_code_suffix' => $a->postal_code_suffix,
          'postal_code' => $a->postal_code,
          'api.custom_value.create' => $diData,
        );


      }

      //email
      $sql = "
        SELECT location_type_id, email, is_primary, on_hold, is_bulkmail
        FROM {$t}.civicrm_email
        WHERE contact_id = {$c->id}
      ";
      $e = CRM_Core_DAO::executeQuery($sql);
      while ( $e->fetch() ) {
        $contact['api.email.create'][] = array(
          'location_type_id' => $e->location_type_id,
          'email' => $e->email,
          'is_primary' => $e->is_primary,
          'on_hold' => $e->on_hold,
          'is_bulkmail' => $e->is_bulkmail,
        );
      }

      //phone
      $sql = "
        SELECT location_type_id, phone, is_primary, phone_type_id
        FROM {$t}.civicrm_phone
        WHERE contact_id = {$c->id}
      ";
      $p = CRM_Core_DAO::executeQuery($sql);
      while ( $p->fetch() ) {
        $contact['api.phone.create'][] = array(
          'location_type_id' => $p->location_type_id,
          'phone' => $p->phone,
          'is_primary' => $p->is_primary,
          'phone_type_id' => $p->phone_type_id,
        );
      }

      //notes
      $sql = "
        SELECT subject, note
        FROM {$t}.civicrm_note
        WHERE entity_id = {$c->id}
          AND entity_table = 'civicrm_contact'
      ";
      $n = CRM_Core_DAO::executeQuery($sql);
      while ( $p->fetch() ) {
        $contact['api.note.create'][] = array(
          'subject' => $n->subject,
          'note' => $n->note,
        );
      }

      //custom data
      $sql = "
        SELECT *
        FROM {$t}.civicrm_value_constituent_information_1
        WHERE entity_id = {$c->id}
      ";
      $n = CRM_Core_DAO::executeQuery($sql);
      while ( $p->fetch() ) {
        $contact['api.custom_value.create'][] = array(
          'custom_16' => $n->professional_accreditations_16,
          'custom_17' => $n->interest_in_volunteering__17,
          'custom_18' => $n->active_constituent__18,
          'custom_19' => $n->friend_of_the_senator__19,
          'custom_20' => $n->skills_areas_of_interest_20,
          'custom_21' => $n->honors_and_awards_21,
          'custom_23' => $n->voter_registration_status_23,
          'custom_24' => $n->boe_date_of_registration_24,
          'custom_42' => $n->individual_category_42,
          'custom_45' => $n->other_gender_45,
          'custom_58' => $n->ethnicity1_58,
          'custom_60' => $n->contact_source_60,
          'custom_61' => $n->record_type_61,
          'custom_62' => $n->other_ethnicity_62,
          'custom_63' => $n->religion_63,
        );
      }

      $sql = "
        SELECT *
        FROM {$t}.civicrm_value_contact_details_8
        WHERE entity_id = {$c->id}
      ";
      $n = CRM_Core_DAO::executeQuery($sql);
      while ( $p->fetch() ) {
        $contact['api.custom_value.create'][] = array(
          'custom_64' => $n->privacy_options_note_64,
        );
      }

      //CRM_Core_Error::debug_var('contact', $contact);
      $data[] = self::_cleanArray($contact);

      $i++;
      if ( $test && $i > $test ) {
        break;
      }
    }

    $ydata = Spyc::YAMLDump($data);

    $filename = $scriptPath.'/sampleData/individuals-generated.yml';
    $fileResource = fopen($filename, 'w');
    fwrite($fileResource, $ydata);

    echo "finished generating data file.\n";
  }//generateData

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
        bbscript_log("trace", "_importAPI entity: {$entity} // action: {$action}", $params);
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
      case 'activity':
        break;
    }
  }//_checkFK

  /*
   * given an array, cycle through and unset any elements with no value
   */
  function _cleanArray($data) {
    if ( is_array($data) && !empty($data) ) {
      foreach ( $data as $f => $v ) {
        //CRM_Core_Error::debug_var('f', $f);
        //CRM_Core_Error::debug_var('v', $v);
        if ( empty($v) && $v !== 0 ) {
          //CRM_Core_Error::debug_var('f unset', $f);
          unset($data[$f]);
        }
        elseif ( is_string($v) ) {
          $data[$f] = stripslashes($v);
        }
        elseif ( is_array($v) ) {
          //iterate deeper
          $data[$f] = self::_cleanArray($v);
        }
      }
    }
    return $data;
  }//_cleanArray
}


//run the script
$importData = new CRM_ImportSampleData();
$importData->run();
