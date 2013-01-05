<?php

// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-11-21

// ./migrateContacts.php -S skelos --dest 45 --file --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DRY_COUNT', 25);
define('DEFAULT_LOG_LEVEL', 'TRACE');
define('LOC_TYPE_BOE', 6);

//create global variables
$daoFields =
  $customGroups =
  $source =
  $dest =
  $addressDistInfo =
  $attachmentIDs =
  $exportData =
  array();

function run() {

  global $daoFields;
  global $customGroups;
  global $source;
  global $dest;
  global $addressDistInfo;
  global $exportData;
  global $_SERVER;

  require_once 'script_utils.php';

  // Parse the options
  $shortopts = "d:fn:i:k:t";
  $longopts = array("dest=", "file", "dryrun", "import=", "notrash", "trashonly");
  $optlist = civicrm_script_init($shortopts, $longopts);

  if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dest ID|DISTNAME] [--file] [--dryrun] [--import FILENAME] [--notrash] [--trashonly]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
  }

  //get instance settings for source and destination
  $bbcfg_source = get_bluebird_instance_config($optlist['site']);
  //bbscript_log("trace", "bbcfg_source", $bbcfg_source);

  $civicrm_root = $bbcfg_source['drupal.rootdir'].'/sites/all/modules/civicrm';
  $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
  if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
    CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from migrateContacts.');
    return FALSE;
  }

  $source = array(
    'name' => $optlist['site'],
    'num' => $bbcfg_source['district'],
    'db' => $bbcfg_source['db.civicrm.prefix'].$bbcfg_source['db.basename'],
    'files' => $bbcfg_source['data.rootdir'],
    'domain' => $optlist['site'].'.'.$bbcfg_source['base.domain'],
  );

  //destination may be passed as the instance name OR district ID
  if ( is_numeric($optlist['dest']) ) {
    $dest['num'] = $optlist['dest'];

    //retrieve the instance config using the district ID
    $bbFullConfig = get_bluebird_config();
    //bbscript_log("trace", "bbFullConfig", $bbFullConfig);
    foreach ( $bbFullConfig as $group => $details ) {
      if ( strpos($group, 'instance:') !== false ) {
        if ( $details['district'] == $optlist['dest'] ) {
          $dest['name'] = substr($group, 9);
          $bbcfg_dest = get_bluebird_instance_config($dest['name']);
          $dest['db'] = $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'];
          $dest['files'] = $bbcfg_dest['data.rootdir'];
          $dest['domain'] = $dest['name'].'.'.$bbcfg_dest['base.domain'];
          break;
        }
      }
    }
  }
  else {
    $bbcfg_dest = get_bluebird_instance_config($optlist['dest']);
    $dest = array(
      'name' => $optlist['dest'],
      'num' => $bbcfg_dest['district'],
      'db' => $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'],
      'files' => $bbcfg_dest['data.rootdir'],
      'domain' => $optlist['dest'].'.'.$bbcfg_dest['base.domain'],
    );
  }
  //bbscript_log("trace", "$source", $source);
  //bbscript_log("trace", "$dest", $dest);

  //if either dest or source unset, exit
  if ( empty($dest['db']) || empty($source['db']) ) {
    bbscript_log("fatal", "Unable to retrieve configuration for either source or destination instance.");
    exit();
  }

  // Initialize CiviCRM
  require_once 'CRM/Core/Config.php';
  $config = CRM_Core_Config::singleton();
  $session = CRM_Core_Session::singleton();

  //retrieve/set other options
  $optFile = $optlist['file'];
  $optDry = $optlist['dryrun'];

  //set import folder based on environment
  $fileDir = '/data/importData/migrate_'.$bbcfg_source['install_class'];
  if ( !file_exists($fileDir) ) {
    mkdir( $fileDir, 0775, TRUE );
  }

  //if importing from file, check for required values
  if ( !empty($optlist['import']) ) {
    //check for existence of file to import
    $importFile = $fileDir.'/'.$optlist['import'];
    if ( !file_exists($importFile) ) {
      bbscript_log("fatal", "The import file you have specified does not exist. It must reside in {$fileDir}.");
      exit();
    }

    //call import function and end script
    importData($source, $dest, $importFile, $optDry);
    exit();
  }

  //initialize global export array
  $exportData = array();

  //get contacts to migrate and construct in migration table
  $migrateTbl = buildContactTable($source, $dest);

  //if no contacts found we can exit immediately
  if ( !$migrateTbl ) {
    bbscript_log("fatal", "No contacts can be migrated to district #{$dest['num']} ({$dest['name']}).");
    exit();
  }

  //if trashonly, use constructed migration table to trash and then end script
  if ( $optlist['trashonly'] ) {
    //basic option logic check
    if ( $optlist['notrash'] ) {
      bbscript_log("fatal", "You have selected the trashonly AND notrash options -- which makes no sense at all. Exiting the script so you can decide what you want to do or read a book on simple logic.");
      exit();
    }

    //call trash function and end script
    trashContacts($migrateTbl);
    exit();
  }

  //set filename and create file
  $today = date('Ymd_Hi');
  $fileName = $migrateTbl.'_'.$today.'.log';
  $filePath = $fileDir.'/'.$fileName;
  $fileResource = '';
  prepareData(array('filename' => $filePath), $optDry, 'full filepath/filename');
  if ( !$optDry ) {
    $fileResource = fopen($filePath, 'w');
  }

  prepareData(array('source' => $source, 'dest' => $dest), $optDry, 'source and destination details');

  //get contacts and write data
  exportContacts($migrateTbl, $optDry);

  //related records that we will be exporting with the contact
  $recordTypes = array(
    'email',
    'phone',
    'website',
    'im',
    'address',
    'note',
    //'activity',
    //'case',
    //'relationship',
    //'group',
    'Additional_Constituent_Information',
    'Organization_Constituent_Information',
    'Attachments',
    'Contact_Details',
  );

  //customGroups that we may work with;
  $customGroups = array(
    'Additional_Constituent_Information',
    'Organization_Constituent_Information',
    'Attachments',
    'Activity_Details',
    'District_Information',
    'Contact_Details',
  );

  //cycle through contacts, get related records, and construct data
  $mC = CRM_Core_DAO::executeQuery("SELECT * FROM {$migrateTbl};");
  //bbscript_log("trace", "mC", $mC);

  while ( $mC->fetch() ) {
    $IDs = array(
      'contact_id' => $mC->contact_id,
      'external_id' => $mC->external_id,
    );
    foreach ( $recordTypes as $rType ) {
      processData($rType, $IDs, $optDry);
    }
  }

  //process activities
  exportActivities($migrateTbl, $optDry);

  //process cases
  exportCases($migrateTbl, $optDry);

  //process tags
  exportTags($migrateTbl, $optDry);

  //process current employers
  exportCurrentEmployers($migrateTbl, $optDry);

  //process district information (address custom fields)
  exportDistrictInfo($addressDistInfo, $optDry);

  //get attachment details
  _getAttachments();

  //construct group related values so we can store to our master array
  $group = array(
    'group' => array(
      'name' => "Migration_{$source['num']}_{$dest['num']}",
      'title' => "Migrated Contacts (SD{$source['num']} to SD{$dest['num']})",
      'description' => "Contacts migrated from SD{$source['num']} ({$source['name']}) to SD{$dest['num']} ({$dest['name']})",
    ),
  );
  prepareData($group, $optDry, 'group values to store migrated contacts');

  //write completed exportData to file
  writeData($exportData, $fileResource, $optDry);

  //import data if not --file
  if ( !$optFile && !$optDry ) {
    importData($source, $dest, $importFile, $optDry);
  }

  //trash contacts in source db after migration IF full processing (i.e. --file=FALSE and --dryrun=FALSE)
  if ( !$optFile && !$optDry && !$optlist['notrash']) {
    trashContacts($migrateTbl);
  }

  bbscript_log("info", "Completed contact migration from district {$source['num']} ({$source['name']}) to district {$dest['num']} ({$dest['name']}).");

}//run

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

  $sql = "
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
  ";
  //bbscript_log("trace", "buildContactTable sql insertion", $sql);
  CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

  //also retrieve current employer contacts and insert in the table
  $sql = "
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
  ";
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
  prepareData($data, $optDry, 'exportContacts data');
}//exportContacts

/*
 * process related records for a contact
 * this function handles the switch to determine if we use a common function or need to
 * process the data in a special way
 * it also triggers the data write to screen or file
 */
function processData($rType, $IDs, $optDry) {
  global $customGroups;
  $data = $contactData = array();

  switch($rType) {
    case 'email':
    case 'phone':
    case 'website':
    case 'address':
      $data = exportStandard($rType, $IDs, 'contact_id', null);
      break;
    case 'im':
      $data = exportStandard($rType, $IDs, 'contact_id', 'CRM_Core_DAO_IM');
      break;
    case 'note':
      $data = exportStandard($rType, $IDs, 'entity_id', null);
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
        $data = exportStandard($rType, $IDs, 'entity_id', $rType);
      }
  }

  if ( !empty($data) ) {
    $contactData['import'][$IDs['external_id']] = $data;

    //send to prepare data
    prepareData($contactData, $optDry, "{$rType} records to be migrated");
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
      $fields = getCustomFields($dao);
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
    $tableName = getCustomFields($rType, FALSE);
  }

  //get records for contact
  $sql = "
    SELECT $select
    FROM $tableName rt
    WHERE rt.{$fk} = {$IDs['contact_id']}
  ";
  $sql .= additionalWhere($rType);
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
    if ( !checkExist($rType, $rt) ) {
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
    prepareData($data, $optDry, 'employee/employer array');
  }

}//exportCurrentEmployers

/*
 * prepare address custom fields (district information) for export
 * this is done by creating a unique key ID in the _address.name field during the
 * address export. the address ID and key ID was stored in $addressDistInfo
 * which we can now use to retrieve the records and construct the SQL
 */
function exportDistrictInfo($addressDistInfo, $optDry) {
  $tbl = getCustomFields('District_Information', FALSE);
  $flds = getCustomFields('District_Information', TRUE);
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
    if ( !checkExist('District_Information', $di) ) {
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
    prepareData($addressData, $optDry, 'custom address data');
  }
}//exportDistrictInfo

/*
 * process activities for the contact
 */
function exportActivities($migrateTbl, $optDry) {
  $data = $actCustFields = array();
  $actCustTbl = getCustomFields('Activity_Details', FALSE);
  $actCustFld = getCustomFields('Activity_Details', TRUE);
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
  }

  //bbscript_log("trace", 'exportActivities $data', $data);
  prepareData($data, $optDry, 'exportActivities');
}//exportActivities

/*
 * process cases for the contact
 * because cases are complex, let's retrieve via api rather than sql
 * NOTE: we are not transferring case tags or case activity tags
 */
function exportCases($migrateTbl, $optDry) {
  $data = array();
  $actCustTbl = getCustomFields('Activity_Details', FALSE);
  $actCustFld = getCustomFields('Activity_Details', TRUE);
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
      $caseActivities[] = $activity;
    }

    //assign activities
    $case['values'][$contactCases->case_id]['activities'] = $caseActivities;

    //assign to data array
    $data[$contactCases->external_id][] = $case['values'][$contactCases->case_id];
  }

  $casesData = array('cases' => $data);
  //bbscript_log("trace", 'exportCases $casesData', $casesData);

  prepareData($casesData, $optDry, 'case records');
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
  _getIssueCodeTree($issuecodes, $tempother);

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
  prepareData($tags, $optDry, 'tags');
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
    _getIssueCodeTree($issuecodes, $tempother);
  }

  //bbscript_log("trace", '_getIssueCodeTree $issuecodes', $issuecodes);
  //bbscript_log("trace", '_getIssueCodeTree $tempother', $tempother);
}//_getIssueCodeTree

/*
 * although we collected the attachments data earlier, we still have to retrieve the filename
 * in order to copy the file to the new instance
 */
function _getAttachments() {
  global $attachmentIDs;
  global $source;
  global $exportData;

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

  $exportData['attachments'] = $attachmentDetails;
}//_getAttachments

/*
 * trash contacts in source database if not FILE, DRYRUN, or NOTRASH
 * we use the api to ensure all associated records are dealt with correctly
 */
function trashContacts($migrateTbl) {
  $sql = "
    SELECT *
    FROM {$migrateTbl}
  ";
  $contacts = CRM_Core_DAO::executeQuery($sql);

  while ( $contacts->fetch() ) {
    $params = array(
      'version' => 3,
      'id' => $contacts->contact_id,
    );
    civicrm_api('contact', 'delete', $params);
  }
  bbscript_log("info", 'Contacts in the source database have been trashed.');
}//trashContacts

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
function writeData($data, $fileResource, $optDry = FALSE) {
  $exportDataJSON = json_encode($data);

  if ( $optDry ) {
    //bbscript_log("info", 'Exported array:', $data);
    bbscript_log("info", 'Dryrun is enabled... output has not been written to file.', $exportDataJSON);
  }
  else {
    fwrite($fileResource, $exportDataJSON);
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
      'interest_in_volunteering__17',
      'active_constituent__18',
      'friend_of_the_senator__19',
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

function importData($source, $dest, $importFile, $optDry) {
  global $exportData;

  //get data; if the import was internal, we can just use our global exportData array, else retrieve from file
  if ( empty($exportData) ) {
    //retrieve from file
    $exportData = json_decode(file_get_contents($importFile));

    //parse the import file source/dest, compare with params and return a warning message if values do not match
    if ( $exportData['source']['name'] != $source['name'] ||
      $exportData['dest']['name'] != $dest['name'] ) {
      bbscript_log('fatal', 'The source/destination defined in the import file does not match the parameters passed to the script. Exiting the script as a mismatched source/destination could create significant data problems. Please investigate and then rerun the script.');
    }
  }

  //TODO process the import
  //TODO when processing tags, increase field length to varchar(80)

  //create group and add migrated contacts
  addToGroup($exportData, $optDry);

  bbscript_log("info", "Successfully imported from district {$source['num']} ({$source['name']}) to district {$dest['num']} ({$dest['name']}) using {$importFile}.");

}//importData

/*
 * create group in destination database and add all contacts
 */
function addToGroup($exportData, $optDry) {
  global $source;
  global $dest;

  $g = $exportData['group'];

  //create group in destination database
  $sql = "
    INSERT IGNORE INTO {$dest['db']}.civicrm_group
    ( name, title, description, is_active, visibility, is_hidden, is_reserved )
    VALUES
    ( '{$g['name']}', '{$g['title']}', '{$g['description']}', 1, 'User and User Admin Only', 0, 0 );
  ";
  CRM_Core_DAO::executeQuery($sql);

  //get newly created group
  $sql = "
    SELECT id FROM civicrm_group WHERE name = '{$g['name']}';
  ";
  $groupID = CRM_Core_DAO::singleValueQuery($sql);

  //error handling
  if ( !$groupID ) {
    bbscript_log("fatal", "Unable to retrieve migration group ({$g['title']}) and add contacts to group.");
    return;
  }

  //contacts
  $contactsList = implode(',', array_keys($exportData['import']));

  //add contacts to group
  $sqlInsert = "
    INSERT INTO {$dest['db']}.civicrm_group_contact
    ( group_id, contact_id, status )
    VALUES
    SELECT {$groupID} group_id, id contact_id, 'Added' status
    FROM civicrm_contact
    WHERE external_identifier IN ('{$contactsList}');
  ";

  bbscript_log("info", "Imported contacts added to group: {$g['title']}");
}//addToGroup

//run the script
run();
