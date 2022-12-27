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
define('TEST_IMPORT', 0);
define('TEST_IMPORT_COUNT', 50);

class CRM_ImportSampleData {

  function run() {

    global $shortopts;
    global $longopts;
    global $optDry;
    global $BB_LOG_LEVEL;
    global $LOG_LEVELS;

    require_once 'script_utils.php';

    // Parse the options
    $shortopts = 'd:s:p:o:g:l:k:u';
    $longopts = array('dryrun', 'system', 'purge', 'purge-only', 'generate', 'log=', 'skiplogs', 'uid=');
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--system] [--purge] [--purge-only] [--generate] [--log LEVEL] [--skiplogs|-k] [--uid USERID|-u]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    if ( empty($BB_LOG_LEVEL) && !empty($optlist['log']) ) {
      $BB_LOG_LEVEL = $LOG_LEVELS[strtoupper($optlist['log'])][0];
    }
    elseif ( empty($BB_LOG_LEVEL) ) {
      $BB_LOG_LEVEL = $LOG_LEVELS[DEFAULT_LOG_LEVEL][0];
    }

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    bbscript_log(LL::TRACE, "bbcfg", $bbcfg);

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    //retrieve/set options
    //CRM_Core_Error::debug_var('optlist', $optlist);
    $optDry = $optlist['dryrun'];
    $scriptPath = $bbcfg['app.rootdir'].'/civicrm/scripts';

    require_once 'libs/Spyc.php';

    //check if we should generate data
    if ( $optlist['generate'] ) {
      self::generateData($scriptPath);
      exit();
    }

    if ( $optDry ) {
      bbscript_log(LL::INFO, "Running in dryrun mode. No data will be altered.");
    }

    //clean out all existing data
    if ( $optlist['purge'] || $optlist['purge-only'] ) {
      bbscript_log(LL::INFO, 'purging old data... ');
      self::purgeData($optlist['uid']);
    }

    //completely purge log db unless explicit skip
    if ( $optlist['skiplogs'] == FALSE ) {
      //disable logging
      $script = $bbcfg['app.rootdir'].'/civicrm/scripts/logDisable.php';
      exec("php $script -S {$bbcfg['shortname']}");

      //drop logging db
      bbscript_log(LL::INFO, 'dropping and recreating logging database... ');
      $logDB = $bbcfg['db.log.prefix'].$bbcfg['db.basename'];
      $sql = "
        DROP DATABASE IF EXISTS {$logDB}
      ";
      CRM_Core_DAO::executeQuery($sql);

      //recreate logging db
      $sql = "
        CREATE DATABASE IF NOT EXISTS {$logDB}
      ";
      CRM_Core_DAO::executeQuery($sql);
    }

    //process system data
    $sys = array(
      'tag.yml',
      'group.yml',
    );
    if ( $optlist['system'] && !$optDry ) {
      bbscript_log(LL::INFO, 'importing system data... ');
      self::importSystem($sys, $scriptPath);
    }

    //proceed with import unless purge only
    if ( $optlist['purge-only'] == FALSE ) {
      $data = array(
        'organizations.yml',
        'individuals.yml',
        'activity.yml',
        'entity_tag.yml',
      );

      foreach ( $data as $file ) {
        $type = str_replace('.yml', '', $file);
        bbscript_log(LL::INFO, "importing {$type} data...");
        self::importData($file, $scriptPath);
      }
    }

    //re-enable logging
    if ( $optlist['skiplogs'] == FALSE ) {
      bbscript_log(LL::INFO, "re-enabling logging...");
      $script = $bbcfg['app.rootdir'].'/civicrm/scripts/logEnable.php';
      exec("php $script -S {$bbcfg['shortname']}");
    }

    bbscript_log(LL::INFO, "completed instance cleanup and sample data import for: {$bbcfg['shortname']}.");

  }//run

  /*
   * before importing sample data we purge the instance of all existing data
   * this is done to selective tables in order to retain system settings, option lists, and other data common to all sites
   */
  function purgeData($uid) {
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
      'civicrm_activity_contact',
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
      'civicrm_mailing_event_sendgrid_delivered',
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
      'civicrm_queue_item',
      'civicrm_relationship',
      'civicrm_saved_search',
      'civicrm_sms_provider',
      'civicrm_subscription_history',
      'civicrm_survey',
      'civicrm_tell_friend',
      'civicrm_uf_match',
      'civicrm_website',
      'civicrm_value_activity_details_6',
      'civicrm_value_attachments_5',
      'civicrm_value_constituent_information_1',
      'civicrm_value_contact_details_8',
      'civicrm_value_district_information_7',
      'civicrm_value_organization_constituent_informa_3',
      'nyss_changelog_detail',
      'nyss_changelog_summary',
    );
    if ( $optDry ) {
      bbscript_log(LL::TRACE, 'The following tables would be truncated: ', $tblTruncate);
    }
    else {
      bbscript_log(LL::INFO, 'truncating tables... ');
      foreach ( $tblTruncate as $tbl ) {
        bbscript_log(LL::DEBUG, "truncating: $tbl");
        $sql = "TRUNCATE TABLE {$tbl};";
        CRM_Core_DAO::executeQuery($sql);
      }
    }

    //additionally, we must implement special handling for several tables
    bbscript_log(LL::TRACE, 'Tables with select row/field deletion: civicrm_dashboard, civicrm_setting');
    if ( !$optDry ) {
      $dashRetain = array(
        'Activities',
        'All Activities, Last 7 Days',
        'All Cases',
        'Bluebird News',
        'Case Dashboard Dashlet',
        'Matched Inbound Emails, Last 7 Days',
        'My Cases',
        'Twitter',
      );
      $dashRetainList = implode("','", $dashRetain);
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
    bbscript_log(LL::INFO, "seeding database with bluebird admin contact...");
    $params = array(
      'first_name' => 'Bluebird',
      'last_name' => 'Administrator',
      'contact_type' => 'Individual',
      'api.email.create' => array(
        'email' => 'bluebird.admin@nysenate.gov',
        'location_type_id' => 1,
        'is_primary' => 1,
      ),
      'api.phone.create' => array(
        'phone' => '800-BlueBird',
        'location_type_id' => 1,
        'phone_type_id' => 1,
        'is_primary' => 1,
      ),
    );
    $c = self::iAPI('contact', 'create', $params);

    if ( $c['id'] ) {
      $sql = "
        INSERT INTO civicrm_uf_match ( domain_id, uf_id, uf_name, contact_id )
        VALUES ( 1, 1, 'bluebird.admin@nysenate.gov', {$c['id']} )
      ";
      CRM_Core_DAO::executeQuery($sql);
    }

    //seed the logged-in contact via drupal user object
    if ( !empty($uid) && $uid != 1 && $uid != '/' ) {
      $u = explode('/', $uid);
      bbscript_log(LL::INFO, "seeding database with logged in user contact...");
      $params = array(
        'email' => $u[1],
        'contact_type' => 'Individual',
        'api.email.create' => array(
          'email' => $u[1],
          'location_type_id' => 1,
        ),
      );
      $c = self::iAPI('contact', 'create', $params);

      if ( $c['id'] ) {
        $sql = "
          INSERT INTO civicrm_uf_match ( domain_id, uf_id, uf_name, contact_id )
          VALUES ( 1, {$u[0]}, '{$u[1]}', {$c['id']} )
        ";
        CRM_Core_DAO::executeQuery($sql);
      }
    }
  }//purgeData

  function importData($file = NULL, $scriptPath = NULL, $data = NULL) {
    global $fkMap;

    $type = str_replace('.yml', '', $file);
    bbscript_log(LL::TRACE, "raw type: $type");

    $errors = array();
    $i = 0;

    switch ( $type ) {
      case 'individuals':
      case 'organizations':
      case 'households':
        $type = 'contact';
        break;
      default:
    }
    bbscript_log(LL::TRACE, "api type: $type");

    if ( !$data ) {
      $filename = $scriptPath.'/sampleData/'.$file;
      bbscript_log(LL::TRACE, "filename: $filename");
      $data = Spyc::YAMLLoad($filename);
    }
    else {
      echo "\ndata was passed to importData internally...\n";
    }

    foreach ( $data as $params ) {
      //bbscript_log(LL::TRACE, "import params", $params);
      if ( isset($params['fk']) ) {
        $fk = $params['fk'];
        unset($params['fk']);

        self::_checkFK($fk, $fkMap, $type, $params);
      }

      if ( !empty($params['employer_id']) ) {
        $params['employer_id'] = $fkMap[$type][$params['employer_id']];
        //bbscript_log(LL::TRACE, "params after fkMap", $params);
        //bbscript_log(LL::TRACE, "fkMap", $fkMap);
      }

      if ( $type == 'activity' ) {
        $params['source_contact_id'] = CRM_Utils_Array::value($params['source_contact_id'], $fkMap, 1);
        $params['target_contact_id'] = $fkMap['contact'][$params['target_contact_id']];
      }

      if ( $type == 'entity_tag' ) {
        $params['entity_id'] = $fkMap['contact'][$params['entity_id']];
      }

      //in v1.5.0 we have problems passing address custom fields via nested api
      if ( isset($params['api.address.create']['api.custom_value.create']) ) {
        $distInfo = $params['api.address.create']['api.custom_value.create'];
        $params['api.address.create'] = array_merge($params['api.address.create'], $distInfo);
        unset($params['api.address.create']['api.custom_value.create']);
      }

      //bbscript_log(LL::TRACE, "params before iAPI", $params);
      $r = self::iAPI($type, 'create', $params);
      //bbscript_log(LL::TRACE, "r", $r);

      if ( $r['is_error'] ) {
        $errors[] = $params;
      }
      elseif ( $type == 'contact' ) {
        bbscript_log(LL::DEBUG, "imported: {$r['values'][$r['id']]['display_name']}");
      }
      else {
        bbscript_log(LL::DEBUG, "imported: {$type}");
      }

      if ( $fk ) {
        $fkMap[$type][$fk] = $r['id'];
      }

      $i++;
      if ( $i % 500 == 0 ) {
        bbscript_log(LL::INFO, "{$i} {$type} records imported... ");
      }

      if ( TEST_IMPORT && $i > TEST_IMPORT_COUNT ) {
        break;
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
      bbscript_log(LL::DEBUG, "remaining error records", $errors);
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
            //bbscript_log(LL::TRACE, "tagSQL", $tagSQL);
            CRM_Core_DAO::executeQuery($tagSQL);
          }
          break;

        case 'group':
          //need to setup the mailing exclusion saved search criteria manually, then load groups
          $sqls = array(
            "INSERT INTO `civicrm_saved_search`
            (`id`, `form_values`, `mapping_id`, `search_custom_id`, `where_clause`, `select_tables`, `where_tables`)
            VALUES
            (5, 'a:7:{s:5:\"qfKey\";s:37:\"0115d58ba08db0ff037fa76a39374c60_3224\";s:6:\"mapper\";a:4:{i:1;a:1:{i:0;a:2:{i:0;s:10:\"Individual\";i:1;s:11:\"is_deceased\";}}i:2;a:1:{i:0;a:2:{i:0;s:10:\"Individual\";i:1;s:11:\"do_not_mail\";}}i:3;a:1:{i:0;a:2:{i:0;s:9:\"Household\";i:1;s:11:\"do_not_mail\";}}i:4;a:1:{i:0;a:2:{i:0;s:12:\"Organization\";i:1;s:11:\"do_not_mail\";}}}s:8:\"operator\";a:4:{i:1;a:1:{i:0;s:1:\"=\";}i:2;a:1:{i:0;s:1:\"=\";}i:3;a:1:{i:0;s:1:\"=\";}i:4;a:1:{i:0;s:1:\"=\";}}s:5:\"value\";a:4:{i:1;a:1:{i:0;s:1:\"1\";}i:2;a:1:{i:0;s:1:\"1\";}i:3;a:1:{i:0;s:1:\"1\";}i:4;a:1:{i:0;s:1:\"1\";}}s:4:\"task\";s:2:\"13\";s:8:\"radio_ts\";s:6:\"ts_all\";s:11:\"uf_group_id\";s:2:\"11\";}', 5, NULL, ' (  ( contact_a.is_deceased = 1 AND contact_a.contact_type IN (''Individual'') )  OR  ( contact_a.do_not_mail = 1 AND contact_a.contact_type IN (''Individual'') )  OR  ( contact_a.do_not_mail = 1 AND contact_a.contact_type IN (''Household'') )  OR  ( contact_a.do_not_mail = 1 AND contact_a.contact_type IN (''Organization'') )  ) ', 'a:11:{s:15:\"civicrm_contact\";i:1;s:15:\"civicrm_address\";i:1;s:22:\"civicrm_state_province\";i:1;s:15:\"civicrm_country\";i:1;s:13:\"civicrm_email\";i:1;s:13:\"civicrm_phone\";i:1;s:10:\"civicrm_im\";i:1;s:19:\"civicrm_worldregion\";i:1;s:6:\"gender\";i:1;s:17:\"individual_prefix\";i:1;s:17:\"individual_suffix\";i:1;}', 'a:1:{s:15:\"civicrm_contact\";i:1;}');",
          );
          foreach ( $sqls as $sql ) {
            CRM_Core_DAO::executeQuery($sql);
          }

          //now import groups
          foreach ( $data as $row ) {
            $params = $row;
            $r = self::iAPI('group', 'create', $params);
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
      //bbscript_log(LL::TRACE, '_importAPI $customMap', $customMap);

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
      bbscript_log(LL::DEBUG, "_importAPI entity:{$entity} action:{$action} params:", $params);
    }

    if ( !is_array($params) ) {
      bbscript_log(LL::DEBUG, "_importAPI params not array", $paramsOrig);
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
        bbscript_log(LL::DEBUG, "_importAPI error", $api);
        bbscript_log(LL::TRACE, "_importAPI entity: {$entity} // action: {$action}", $params);
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
