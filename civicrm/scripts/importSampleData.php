<?php

/*
 * Project: BluebirdCRM
 * Authors: Brian Shaughnessy
 * Organization: New York State Senate
 * Date: 2013-04-27
 *
 * Usage:
 * php importSampleData.php -S INSTANCE --dryrun --system
 *
 * --system = only import system data (no contacts)
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
    $shortopts = 'd:s';
    $longopts = array('dryrun', 'system');
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === null) {
        $stdusage = civicrm_script_usage();
        $usage = '[--dryrun] [--system]';
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
    $data = array(
      'defaultSystem' => $scriptPath.'/sampleData/defaultSystem.xml',
      'sampleContacts' => $scriptPath.'/sampleData/sampleContacts.xml',
    );

    if ( !file_exists($dataFile) ) {
      bbscript_log("fatal", "The sample data file does not exist.");
      exit();
    }

    if ( $optDry ) {
      bbscript_log("info", "Running in dryrun mode. No data will be altered.");
    }

    //clean out all existing data
    self::purgeData();

    //begin import
    self::importData();

    bbscript_log("info", "Completed instance cleanup and sample data import for {$bbcfg['name']}.");

  }//run

  /*
   * before importing sample data we purge the instance of all existing data
   * this is done to selective tables in order to retain system settings, option lists, and other data common to all sites
   */
  function purgeData() {
    global $optDry;

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
      'civicrm_location_type',
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
      'civicrm_msg_template',
      'civicrm_note',
      'civicrm_persistent',
      'civicrm_phone',
      'civicrm_prevnext_cache',
      'civicrm_project',
      'civicrm_queue_item',
      'civicrm_relationship',
      'civicrm_report_instance',
      'civicrm_saved_search',
      'civicrm_setting',
      'civicrm_sms_provider',
      'civicrm_subscription_history',
      'civicrm_survey',
      'civicrm_tag',
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
        echo 'truncating...'.$tbl."\n";
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
        WHERE label NOT IN ({$dashRetainList});
      ";
      CRM_Core_DAO::executeQuery($sql);

      $sql = "
        DELETE FROM civicrm_setting WHERE name = 'navigation';
      ";
      CRM_Core_DAO::executeQuery($sql);
    }

    //seed the senateroot contact id = 1
  }//purgeData

  /*
   * given source and destination details, create a table and populate with contacts to be migrated
   * also construct external ID to be used as FK during import
   * query criteria: exclude trashed contacts; only include those with a BOE address in destination district
   * if no contacts are found to migrate, return FALSE so we can exit immediately.
   */
  function buildContactTable($source, $dest) {
    //create table to store contact IDs with constructed external_id
    $tbl = "migrate_{$source['num']}_{$dest['num']}";
    CRM_Core_DAO::executeQuery( "DROP TABLE IF EXISTS $tbl;", CRM_Core_DAO::$_nullArray );

    $sql = "
      CREATE TABLE $tbl
      (contact_id int not null primary key, external_id varchar(40) not null)
      ENGINE = myisam;
    ";
    CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    //check for existence of redist contact cache table
    $redistTbl = "redist_report_contact_cache";
    $sql = "SHOW TABLES LIKE '{$redistTbl}'";
    if ( !CRM_Core_DAO::singleValueQuery($sql) ) {
      bbscript_log("fatal",
        "Redistricting contact cache table for this district does not exist. Exiting migration process.");
      exit();
    }

    //retrieve contacts from redistricting table
    $sql = "
      INSERT INTO $tbl
      SELECT rrcc.contact_id,
        CONCAT('SD{$source['num']}_BB', rrcc.contact_id, '_EXT', c.external_identifier) external_id
      FROM redist_report_contact_cache rrcc
      JOIN civicrm_contact c
        ON rrcc.contact_id = c.id
        AND c.is_deleted = 0
      WHERE rrcc.district = {$dest['num']}
      GROUP BY rrcc.contact_id
    ";

    //original query to pull contacts
    /*$sql = "
      INSERT INTO $tbl
      SELECT a.contact_id, CONCAT('SD{$source['num']}_BB', a.contact_id, '_EXT', c.external_identifier) external_id
      FROM civicrm_address a
      JOIN civicrm_value_district_information_7 di
        ON a.id = di.entity_id
        AND di.ny_senate_district_47 = {$dest['num']}
      JOIN civicrm_contact c
        ON a.contact_id = c.id
        AND c.is_deleted = 0
      WHERE a.location_type_id = ".LOC_TYPE_BOE."
      GROUP BY a.contact_id
    ";*/
    //bbscript_log("trace", "buildContactTable sql insertion", $sql);
    CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    //also retrieve current employer contacts and insert in the table
    $sql = "
      INSERT INTO $tbl
      SELECT c.employer_id,
        CONCAT('SD{$source['num']}_CE_BB', c.employer_id, '_EXT', cce.external_identifier) external_id
      FROM redist_report_contact_cache rrcc
      JOIN civicrm_contact c
        ON rrcc.contact_id = c.id
        AND c.is_deleted = 0
        AND c.employer_id IS NOT NULL
        AND rrcc.contact_type = 'Individual'
      JOIN civicrm_contact cce
        ON c.employer_id = cce.id
        AND cce.is_deleted = 0
      WHERE rrcc.district = {$dest['num']}
      GROUP BY rrcc.contact_id
    ";

    //original query to pull current employers
    /*$sql = "
      INSERT INTO $tbl
      SELECT c.employer_id, CONCAT('SD{$source['num']}_CE_BB', c.employer_id, '_EXT', cce.external_identifier) external_id
      FROM civicrm_address a
      JOIN civicrm_value_district_information_7 di
        ON a.id = di.entity_id
        AND di.ny_senate_district_47 = {$dest['num']}
      JOIN civicrm_contact c
        ON a.contact_id = c.id
        AND c.is_deleted = 0
        AND c.employer_id IS NOT NULL
      JOIN civicrm_contact cce
        ON c.employer_id = cce.id
        AND cce.is_deleted = 0
      WHERE a.location_type_id = ".LOC_TYPE_BOE."
      GROUP BY a.contact_id
    ";*/

    //bbscript_log("trace", "buildContactTable sql insertion", $sql);
    CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    $count = CRM_Core_DAO::singleValueQuery("SELECT count(*) FROM $tbl");
    //bbscript_log("trace", "buildContactTable $count", $count);

    if ( $count ) {
      return $tbl;
    }
    else {
      return FALSE;
    }
  }//buildContactTable

  function exportContacts($migrateTbl, $optDry = FALSE) {
    require_once 'CRM/Contact/DAO/Contact.php';

    //get field list
    $c = new CRM_Contact_DAO_Contact();
    $fields = $c->fields();
    //bbscript_log("trace", "exportContacts fields", $fields);

    foreach ( $fields as $field ) {
      $fieldNames[] = $field['name'];
    }

    //unset these from select statement
    unset($fieldNames[array_search('id', $fieldNames)]);
    unset($fieldNames[array_search('external_identifier', $fieldNames)]);
    unset($fieldNames[array_search('primary_contact_id', $fieldNames)]);
    unset($fieldNames[array_search('employer_id', $fieldNames)]);
    unset($fieldNames[array_search('source', $fieldNames)]);

    $select = 'external_id external_identifier, '.implode(', ',$fieldNames);
    //bbscript_log("trace", "exportContacts select", $select);

    $sql = "
      SELECT $select
      FROM $migrateTbl mt
      JOIN civicrm_contact
        ON mt.contact_id = civicrm_contact.id
    ";
    $contacts = CRM_Core_DAO::executeQuery($sql);
    //bbscript_log("trace", 'exportContacts sql', $sql);

    $contactsAttr = get_object_vars($contacts);
    //bbscript_log("trace", 'exportContacts contactsAttr', $contactsAttr);

    $data = array();

    //cycle through contacts and write to array
    while ( $contacts->fetch() ) {
      //bbscript_log("trace", 'exportContacts contacts', $contacts);
      foreach ( $contacts as $f => $v ) {
        if ( !array_key_exists($f, $contactsAttr) ) {
          $data['import'][$contacts->external_identifier]['contact'][$f] = addslashes($v);
        }
      }
      $data['import'][$contacts->external_identifier]['contact']['source'] = 'Redist2012';
    }

    //add to master global export
    self::prepareData($data, $optDry, 'exportContacts data');
  }//exportContacts

  /*
   * process related records for a contact
   * this function handles the switch to determine if we use a common function or need to
   * process the data in a special way
   * it also triggers the data write to screen or file
   */
  function processData($rType, $IDs, $optDry) {
    require_once 'CRM/Core/DAO/Email.php';
    require_once 'CRM/Core/DAO/Phone.php';
    require_once 'CRM/Core/DAO/Website.php';
    require_once 'CRM/Core/DAO/Address.php';
    require_once 'CRM/Core/DAO/IM.php';
    require_once 'CRM/Core/DAO/Note.php';

    global $customGroups;
    $data = $contactData = array();

    switch($rType) {
      case 'email':
      case 'phone':
      case 'website':
      case 'address':
        $data = self::exportStandard($rType, $IDs, 'contact_id', null);
        break;
      case 'im':
        $data = self::exportStandard($rType, $IDs, 'contact_id', 'CRM_Core_DAO_IM');
        break;
      case 'note':
        $data = self::exportStandard($rType, $IDs, 'entity_id', null);
        break;
      case 'activity':
        break;
      case 'case':
        break;
      case 'relationship':
        break;
      default:
        //if a custom set, use exportStandard but pass set name as DAO
        if ( in_array($rType, $customGroups) ) {
          $data = self::exportStandard($rType, $IDs, 'entity_id', $rType);
        }
    }

    if ( !empty($data) ) {
      $contactData['import'][$IDs['external_id']] = $data;

      //send to prepare data
      self::prepareData($contactData, $optDry, "{$rType} records to be migrated");
    }
  }//processData

  /*
   * standard related record export function
   * we use the record type to retrieve the DAO and the foreign key to link to the contact record
   */
  function exportStandard($rType, $IDs, $fk = 'contact_id', $dao = null) {
    global $daoFields;
    global $customGroups;
    global $source;
    global $addressDistInfo;
    global $attachmentIDs;

    //get field list from dao
    if ( !$dao ) {
      //assume dao is in the core path
      $dao = 'CRM_Core_DAO_'.ucfirst($rType);
    }
    //bbscript_log("trace", "exportStandard dao", $dao);

    //if field list has not already been constructed, generate now
    if ( !isset($daoFields[$dao]) ) {
      //bbscript_log("trace", "exportStandard building field list for $dao");

      //construct field list from DAO or custom set
      if ( in_array($dao, $customGroups) ) {
        $fields = self::getCustomFields($dao);
      }
      else {
        $d = new $dao;
        $fields = $d->fields();
      }
      //bbscript_log("trace", "exportStandard fields", $fields);

      $daoFields[$dao] = array();
      foreach ( $fields as $field ) {
        if ( in_array($dao, $customGroups) ) {
          $daoFields[$dao][] = $field['column_name'];
        }
        else {
          $daoFields[$dao][] = $field['name'];
        }
      }

      //unset various fields from select statement
      $skipFields = array(
        'id',
        $fk,
        'signature_text',
        'signature_html',
        'master_id',
        'interest_in_volunteering__17',
        'active_constituent__18',
        'friend_of_the_senator__19',
      );
      foreach ($skipFields as $fld) {
        $fldKey = array_search($fld, $daoFields[$dao]);
        if ( $fldKey !== FALSE ) {
          unset($daoFields[$dao][$fldKey]);
        }
      }
    }
    //bbscript_log("trace", "exportStandard $dao fields", $daoFields[$dao]);

    $select = "id, ".implode(', ',$daoFields[$dao]);
    //bbscript_log("trace", "exportContacts select", $select);

    //set table name
    $tableName = "civicrm_{$rType}";
    if ( in_array($dao, $customGroups) ) {
      $tableName = self::getCustomFields($rType, FALSE);
    }

    //get records for contact
    $sql = "
      SELECT $select
      FROM $tableName rt
      WHERE rt.{$fk} = {$IDs['contact_id']}
    ";
    $sql .= self::additionalWhere($rType);
    //bbscript_log("trace", 'exportStandard sql', $sql);
    $rt = CRM_Core_DAO::executeQuery($sql);

    $rtAttr = get_object_vars($rt);
    //bbscript_log("trace", 'exportStandard rtAttr', $rtAttr);

    //cycle through records and write to file
    //count records that exist to determine if we need to write
    $recordData = array();
    $recordCount = 0;
    while ( $rt->fetch() ) {
      //bbscript_log("trace", 'exportStandard rt', $rt);

      //first check for record existence
      if ( !self::checkExist($rType, $rt) ) {
        continue;
      }
      //bbscript_log("trace", "exportStandard {$rType} record exists, proceed...");

      $data = array();
      foreach ( $rt as $f => $v ) {
        //we include id in the select so we can reference, but do not include in the insert
        if ( !array_key_exists($f, $rtAttr) && $f != 'id' ) {
          $data[$f] = addslashes($v);

          //account for address custom fields
          if ( $rType == 'address' && $f == 'name' ) {
            //construct key and temporarily store in address.name
            $data[$f] = "SD{$source['num']}_BB{$IDs['contact_id']}_ADD{$rt->id}";

            //store source address id and address key to build district info select
            $addressDistInfo[$rt->id] = $data[$f];
          }

          //account for file attachments
          if ( $rType == 'Attachments' && !empty($v) ) {
            //store to later process
            $attachmentIDs[] = $v;
          }
        }
      }
      $recordData[$rType][] = $data;
      $recordCount++;
    }
    //bbscript_log("trace", 'exportStandard $recordData', $recordData);
    //bbscript_log("trace", 'exportStandard $addressDistInfo', $addressDistInfo);

    //only return string to write if we actually have values
    if ( $recordCount ) {
      return $recordData;
    }
  }//exportStandard

  /*
   * collect array of extKeys that must be reconstructed as employee/employer relationships
   * array( employeeKey => employerKey )
   */
  function exportCurrentEmployers($migrateTable, $optDry) {
    $data = array();
    $sql = "
      SELECT mtI.external_id employeeKey, mtO.external_id employerKey
      FROM {$migrateTable} mtI
      JOIN civicrm_contact c
        ON mtI.contact_id = c.id
      JOIN {$migrateTable} mtO
        ON c.employer_id = mtO.contact_id
      WHERE c.employer_id IS NOT NULL
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);

    while ( $dao->fetch() ) {
      $data['employment'][$dao->employeeKey] = $dao->employerKey;
    }

    if ( !empty($data) ) {
      self::prepareData($data, $optDry, 'employee/employer array');
    }

  }//exportCurrentEmployers

  /*
   * prepare address custom fields (district information) for export
   * this is done by creating a unique key ID in the _address.name field during the
   * address export. the address ID and key ID was stored in $addressDistInfo
   * which we can now use to retrieve the records and construct the SQL
   */
  function exportDistrictInfo($addressDistInfo, $optDry) {
    $tbl = self::getCustomFields('District_Information', FALSE);
    $flds = self::getCustomFields('District_Information', TRUE);
    $addressIDs = implode(', ', array_keys($addressDistInfo));
    $addressData = array();

    //bbscript_log("trace", 'exportDistrictInfo $flds', $flds);
    //bbscript_log("trace", 'exportDistrictInfo $addressDistInfo', $addressDistInfo);

    //get fields
    $fldCol = array();
    foreach ( $flds as $fld ) {
      $fldCol[] = $fld['column_name'];
    }
    $select = implode(', ', $fldCol);

    //get all district info records
    $sql = "
      SELECT entity_id, $select
      FROM $tbl
      WHERE entity_id IN ({$addressIDs});
    ";
    //bbscript_log("trace", 'exportDistrictInfo $sql', $sql);

    $di = CRM_Core_DAO::executeQuery($sql);
    while ( $di->fetch() ) {
      //bbscript_log("trace", 'exportDistrictInfo di', $di);

      //first check for record existence
      if ( !self::checkExist('District_Information', $di) ) {
        continue;
      }
      //bbscript_log("trace", "exportDistrictInfo District_Information record exists, proceed...");

      $data = array();
      foreach ( $flds as $fid => $f ) {
        $data[$f['column_name']] = addslashes($di->$f['column_name']);
      }
      $addressData['districtinfo'][$addressDistInfo[$di->entity_id]] = $data;
      $recordCount++;
    }

    //send to prep function if records exist
    if ( $recordCount ) {
      self::prepareData($addressData, $optDry, 'custom address data');
    }
  }//exportDistrictInfo

  /*
   * process activities for the contact
   */
  function exportActivities($migrateTbl, $optDry) {
    global $attachmentIDs;

    $data = $actCustFields = array();
    $actCustTbl = self::getCustomFields('Activity_Details', FALSE);
    $actCustFld = self::getCustomFields('Activity_Details', TRUE);
    //bbscript_log("trace", 'exportActivities $actCustFld', $actCustFld);

    foreach ( $actCustFld as $field ) {
      $actCustFields[$field['name']] = $field['column_name'];
    }

    //get all activities (non bulk email) for contacts
    $sql = "
      SELECT at.activity_id, a.*, ad.*, GROUP_CONCAT(mt.external_id SEPARATOR '|') targetIDs
      FROM civicrm_activity_target at
      JOIN {$migrateTbl} mt
        ON at.target_contact_id = mt.contact_id
      JOIN civicrm_activity a
        ON at.activity_id = a.id
      LEFT JOIN {$actCustTbl} ad
        ON a.id = ad.entity_id
      WHERE a.is_deleted = 0
        AND a.is_current_revision = 1
      GROUP BY at.activity_id
    ";
    //bbscript_log("trace", 'exportActivities $sql', $sql);
    $activities = CRM_Core_DAO::executeQuery($sql);

    //get dao attributes
    $activityAttr = get_object_vars($activities);

    while ( $activities->fetch() ) {
      //bbscript_log("trace", 'exportActivities $activities', $activities);

      foreach ($activities as $f => $v) {
        if ( !array_key_exists($f, $activityAttr) ) {
          if ( in_array($f, $actCustFields) ) {
            $data['activities'][$activities->activity_id]['custom'][$f] = addslashes($v);
          }
          elseif ($f == 'targetIDs') {
            $data['activities'][$activities->activity_id]['targets'] = explode('|', $v);
          }
          else {
            $data['activities'][$activities->activity_id]['activity'][$f] = addslashes($v);
          }
        }
      }
      //remove id field
      unset($data['activities'][$activities->activity_id]['activity']['id']);

      //get attachments
      $sql = "
        SELECT *
        FROM civicrm_entity_file
        WHERE entity_table = 'civicrm_activity'
          AND entity_id = {$activities->activity_id}
      ";
      $actAttach = CRM_Core_DAO::executeQuery($sql);
      while ( $actAttach->fetch() ) {
        $attachmentIDs[] = $actAttach->file_id;
        $data['activities'][$activities->activity_id]['attachments'][] = $actAttach->file_id;
      }
    }

    //bbscript_log("trace", 'exportActivities $data', $data);
    self::prepareData($data, $optDry, 'exportActivities');
  }//exportActivities

  /*
   * process cases for the contact
   * because cases are complex, let's retrieve via api rather than sql
   * NOTE: we are not transferring case tags or case activity tags
   */
  function exportCases($migrateTbl, $optDry) {
    global $attachmentIDs;

    $data = array();
    $actCustTbl = self::getCustomFields('Activity_Details', FALSE);
    $actCustFld = self::getCustomFields('Activity_Details', TRUE);
    //bbscript_log("trace", 'exportCases $actCustFld', $actCustFld);

    $sql = "
      SELECT mt.*, cc.case_id
      FROM {$migrateTbl} mt
      JOIN civicrm_case_contact cc
        ON mt.contact_id = cc.contact_id
    ";
    $contactCases = CRM_Core_DAO::executeQuery($sql);

    while ( $contactCases->fetch() ) {
      //cases for contact
      $params = array(
        'version' => 3,
        'case_id' => $contactCases->case_id,
      );
      $case = civicrm_api('case', 'get', $params);
      //bbscript_log("trace", 'exportCases $case', $case);

      //unset some values to make it easier to later import
      unset($case['values'][$contactCases->case_id]['id']);
      unset($case['values'][$contactCases->case_id]['client_id']);
      unset($case['values'][$contactCases->case_id]['contacts']);

      $caseActivityIDs = $case['values'][$contactCases->case_id]['activities'];
      unset($case['values'][$contactCases->case_id]['activities']);

      //cycle through and retrieve case activity data
      $caseActivities = array();
      foreach ( $caseActivityIDs as $actID ) {
        $params = array(
          'version' => 3,
          'id' => $actID,
        );
        $activity = civicrm_api('activity', 'getsingle', $params);
        //bbscript_log("trace", 'exportCases $activity', $activity);
        unset($activity['id']);
        unset($activity['source_contact_id']);

        //retrieve custom data fields for activities manually
        $sql = "
          SELECT *
          FROM $actCustTbl
          WHERE entity_id = $actID
        ";
        $actCustom = CRM_Core_DAO::executeQuery($sql);
        while ( $actCustom->fetch() ) {
          foreach ( $actCustFld as $fldID => $fld ) {
            $activity["custom_{$fldID}"] = $actCustom->$fld['column_name'];
          }
        }

        //retrieve attachments
        $sql = "
          SELECT *
          FROM civicrm_entity_file
          WHERE entity_table = 'civicrm_activity'
            AND entity_id = {$actID}
        ";
        $actAttach = CRM_Core_DAO::executeQuery($sql);
        while ( $actAttach->fetch() ) {
          $attachmentIDs[] = $actAttach->file_id;
          $activity['attachments'][] = $actAttach->file_id;
        }

        $caseActivities[] = $activity;
      }

      //assign activities
      $case['values'][$contactCases->case_id]['activities'] = $caseActivities;

      //assign to data array
      $data[$contactCases->external_id][] = $case['values'][$contactCases->case_id];
    }

    $casesData = array('cases' => $data);
    //bbscript_log("trace", 'exportCases $casesData', $casesData);

    self::prepareData($casesData, $optDry, 'case records');
  }//exportCases

  /*
   * process tags for the contact
   */
  function exportTags($migrateTbl, $optDry) {
    global $source;
    $keywords = $issuecodes = $positions = $tempother = array();

    $kParent = 296;
    $iParent = 291;
    $pParent = 292;

    $kPrefix = 'RD '.substr($source['name'], 0, 5).': ';

    //first get all tags associated with contacts
    $sql = "
      SELECT t.*
      FROM civicrm_entity_tag et
      JOIN {$migrateTbl} mt
        ON et.entity_id = mt.contact_id
        AND et.entity_table = 'civicrm_contact'
      JOIN civicrm_tag t
        ON et.tag_id = t.id
      GROUP BY t.id
    ";
    $allTags = CRM_Core_DAO::executeQuery($sql);

    while ( $allTags->fetch() ) {
      switch ( $allTags->parent_id ) {
        case $kParent:
          $keywords[$allTags->id] = array(
            'name' => $kPrefix.$allTags->name,
            'desc' => $allTags->description,
          );
          break;
        case $pParent:
          $positions[$allTags->id] = array(
            'name' => $allTags->name,
            'desc' => $allTags->description,
          );
          break;
        case $iParent:
          $issuecodes[$allTags->id] = array(
            'name' => $allTags->name,
            'desc' => $allTags->description,
          );
          break;
        default:
          $tempother[$allTags->id] = array(
            'parent_id' => $allTags->parent_id,
            'name' => $allTags->name,
            'desc' => $allTags->description,
          );
      }
    }

    //get issue code tree
    self::_getIssueCodeTree($issuecodes, $tempother);

    $tags = array(
      'keywords' => $keywords,
      'issuecodes' => $issuecodes,
      'positions' => $positions,
    );

    //now retrieve contacts/tag mapping
    $entityTags = array();
    $sql = "
      SELECT et.tag_id, mt.external_id
      FROM civicrm_entity_tag et
      JOIN {$migrateTbl} mt
        ON et.entity_id = mt.contact_id
        AND et.entity_table = 'civicrm_contact'
    ";
    $eT = CRM_Core_DAO::executeQuery($sql);
    while ( $eT->fetch() ) {
      $entityTags[$eT->external_id][] = $eT->tag_id;
    }
    //bbscript_log("trace", 'exportTags $entityTags', $entityTags);

    $tags['entities'] = $entityTags;

    //send tags to prep
    self::prepareData(array('tags' => $tags), $optDry, 'tags');
  }//exportTags

  /*
   * build issue code tree
   * tree depth is fixed to 5
   * level 1 is the main parent Issue Codes
   * level 2 is constructed earlier and passed to this function
   *   ...except when the function is called recursively, in which case we need to account for it
   * level 3-5 must be built
   */
  function _getIssueCodeTree(&$issuecodes, $tempother) {
    if ( empty($tempother) ) {
      return;
    }

    $level3 = $level4 = array();

    //keep track of all issue codes as we go
    $allIssueCodes = array_keys($issuecodes);

    //level 2: when called recursively, we have to account for parent being the main issue code root
    foreach ( $tempother as $tID => $tag ) {
      if ( $tag['parent_id'] == 291 ) {
        $issuecodes[$tID]['name'] = $tag['name'];
        $issuecodes[$tID]['desc'] = $tag['desc'];
        unset($tempother[$tID]);

        $allIssueCodes[] = $tID;
      }
    }

    //level 3
    foreach ( $tempother as $tID => $tag ) {
      if ( array_key_exists($tag['parent_id'], $issuecodes) ) {
        $issuecodes[$tag['parent_id']]['children'][$tID]['name'] = $tag['name'];
        $issuecodes[$tag['parent_id']]['children'][$tID]['desc'] = $tag['desc'];
        unset($tempother[$tID]);

        //tag => parent
        $level3[$tID] = $tag['parent_id'];
        $allIssueCodes[] = $tID;
      }
    }

    //level 4
    foreach ( $tempother as $tID => $tag ) {
      if ( array_key_exists($tag['parent_id'], $level3) ) {
        //parent exists in level 3
        $level3id = $tag['parent_id'];
        $level2id = $level3[$level3id];
        $issuecodes[$level2id]['children'][$level3id]['children'][$tID]['name'] = $tag['name'];
        $issuecodes[$level2id]['children'][$level3id]['children'][$tID]['desc'] = $tag['desc'];
        unset($tempother[$tID]);

        //tag => parent
        $level4[$tID] = $tag['parent_id'];
        $allIssueCodes[] = $tID;
      }
    }

    //level 5
    foreach ( $tempother as $tID => $tag ) {
      if ( array_key_exists($tag['parent_id'], $level4) ) {
        //parent exists in level 4
        $level4id = $tag['parent_id'];
        $level3id = $level4[$level4id];
        $level2id = $level3[$level3id];
        $issuecodes[$level2id]['children'][$level3id]['children'][$level4id]['children'][$tID]['name'] = $tag['name'];
        $issuecodes[$level2id]['children'][$level3id]['children'][$level4id]['children'][$tID]['desc'] = $tag['desc'];
        unset($tempother[$tID]);

        $allIssueCodes[] = $tID;
      }
    }

    //if we have tags left over, it's because the tag assignment skipped a level and we need to reconstruct
    //this isn't easily done. what we will do is find the immediate parent and store it, then search for those parents,
    //see if they exist in our current list, and construct if needed
    if ( !empty($tempother) ) {
      $leftOver = array_keys($tempother);
      $leftOverList = implode(',', $leftOver);
      //bbscript_log("trace", '_getIssueCodeTree $leftOver', $leftOver);

      $sql = "
        SELECT p.*
        FROM civicrm_tag p
        JOIN civicrm_tag t
          ON p.id = t.parent_id
        WHERE t.id IN ({$leftOverList})
      ";
      //bbscript_log("trace", '_getIssueCodeTree $sql', $sql);
      $leftTags = CRM_Core_DAO::executeQuery($sql);

      while ( $leftTags->fetch() ) {
        $tempother[$leftTags->id] = array(
          'parent_id' => $leftTags->parent_id,
          'name' => $leftTags->name,
          'desc' => $leftTags->description,
        );
      }

      //call this function recursively
      self::_getIssueCodeTree($issuecodes, $tempother);
    }

    //bbscript_log("trace", '_getIssueCodeTree $issuecodes', $issuecodes);
    //bbscript_log("trace", '_getIssueCodeTree $tempother', $tempother);
  }//_getIssueCodeTree

  /*
   * although we collected the attachments data earlier, we still have to retrieve the filename
   * in order to copy the file to the new instance
   */
  function _getAttachments($optDry) {
    global $attachmentIDs;
    global $source;

    $attachmentDetails = array();

    if ( empty($attachmentIDs) ) {
      return;
    }

    $attachmentsList = implode(',', $attachmentIDs);
    $sql = "
      SELECT *
      FROM civicrm_file
      WHERE id IN ($attachmentsList)
    ";
    $attachments = CRM_Core_DAO::executeQuery($sql);

    while ( $attachments->fetch() ) {
      $attachmentDetails[$attachments->id] = array(
        'file_type_id' => $attachments->file_type_id,
        'mime_type' => $attachments->mime_type,
        'uri' => $attachments->uri,
        'upload_date' => $attachments->upload_date,
        'source_file_path' => $source['files'].'/'.$source['domain'].'/civicrm/custom/'.$attachments->uri,
      );
    }
    //bbscript_log("trace", '_getAttachments $attachmentDetails', $attachmentDetails);

    self::prepareData( array('attachments' => $attachmentDetails), $optDry, '_getAttachments' );
  }//_getAttachments

  /*
   * construct additional WHERE clause attributes by record type
   * return sql statement with prepended AND
   */
  function additionalWhere($rType) {
    switch($rType) {
      case 'note':
        $sql = " AND privacy = 0 ";
        break;
      default:
        $sql = '';
    }
    return $sql;
  }

  /*
   * given a custom data group name, return array of fields
   */
  function getCustomFields($name, $flds = TRUE) {
    $group = civicrm_api('custom_group', 'getsingle', array('version' => 3, 'name' => $name ));
    if ( $flds ) {
      $fields = civicrm_api('custom_field', 'get', array('version' => 3, 'custom_group_id' => $group['id']));
      //bbscript_log("trace", 'getCustomFields fields', $fields);
      return $fields['values'];
    }
    else {
      return $group['table_name'];
    }
  }//getCustomFields

  function getValue($string) {
    if ($string == FALSE) {
      return "null";
    }
    else {
      return $string;
    }
  }

  /*
   * this function is an intermediate step to the writeData function, and is called by each export prep step
   * if this is a dry run, we print to screen (with DEBUG level or lower)
   * in this step, we add the array element to the master export global variable which will later be
   * encoded and saved to a file
   */
  function prepareData($valArray, $optDry = FALSE, $msg = '') {
    global $exportData;
    //bbscript_log("debug", 'global exportData when prepareData is called', $exportData);

    if ( $optDry ) {
      //if dryrun, print passed array when DEBUG level set
      bbscript_log("debug", $msg, $valArray);
    }

    //combine existing exportData array with array passed to function
    //typecast passed variable to make sure it's an array
    //$exportData = $exportData + (array)$valArray;
    $exportData = array_merge_recursive($exportData, (array)$valArray);
  }//prepareData

  /*
   * write data to file in json encoded format
   * if dryrun option is selected, do nothing but return a message to the user
   */
  function writeData($data, $fileResource, $optDry = FALSE, $structured = FALSE) {

    if ( $optDry ) {
      //bbscript_log("info", 'Exported array:', $data);
      bbscript_log("info", 'Dryrun is enabled... output has not been written to file.', $exportDataJSON);
    }
    else {
      if ($structured) {
        fwrite($fileResource, $data);
      }
      else {
        $exportDataJSON = json_encode($data);
        fwrite($fileResource, $exportDataJSON);
      }
    }
  }

  /*
   * avoid writing empty records by first checking if a value exists
   * this function defines required fields by type
   * we pass an object, check against required fields, and return TRUE or FALSE
   */
  function checkExist($rType, $obj) {
    //if any of the fields listed have a value, we consider it existing
    $req = array(
      'phone' => array(
        'phone',
        'phone_ext',
      ),
      'email' => array(
        'email',
      ),
      'website' => array(
        'url',
      ),
      'address' => array(
        'street_adddress',
        'supplemental_address_1',
        'supplemental_address_2',
        'city',
      ),
      'District_Info' => array(
        'congressional_district_46',
        'ny_senate_district_47',
        'ny_assembly_district_48',
        'election_district_49',
        'county_50',
        'county_legislative_district_51',
        'town_52',
        'ward_53',
        'school_district_54',
        'new_york_city_council_55',
        'neighborhood_56',
        'last_import_57',
      ),
      'Additional_Constituent_Information' => array(
        'professional_accreditations_16',
        'skills_areas_of_interest_20',
        'honors_and_awards_21',
        'voter_registration_status_23',
        'boe_date_of_registration_24',
        'individual_category_42',
        'other_gender_45',
        'ethnicity1_58',
        'contact_source_60',
        'record_type_61',
        'other_ethnicity_62',
        'religion_63',
      ),
      'Contact_Details' => array(
        'privacy_options_note_64',
      ),
      'Organization_Constituent_Information' => array(
        'charity_registration__dos__25',
        'employer_identification_number___26',
        'organization_category_41',
      ),
    );

    //only care about types that we are requiring values for
    if ( array_key_exists($rType, $req) ) {
      $exists = FALSE;
      foreach ( $req[$rType] as $reqField ) {
        if ( !empty($obj->$reqField) ) {
          $exists = TRUE;
          break;
        }
      }
      return $exists;
    }
    else {
      return TRUE;
    }
  }//checkExists
}


//run the script
$importData = new CRM_migrateContacts();
$importData->run();
